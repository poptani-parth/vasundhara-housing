<?php
session_start();
include './database.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred.'];

if (!isset($_SESSION['admin_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Unauthorized access or invalid request.';
    http_response_code(401);
    echo json_encode($response);
    exit();
}

$requestId = $_POST['request_id'] ?? null;
$action = $_POST['action'] ?? null;
$adminRemark = trim($_POST['admin_remark'] ?? '');

if (empty($requestId) || !is_numeric($requestId) || !in_array($action, ['approve', 'reject'])) {
    $response['message'] = 'Invalid request data provided.';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$conn->begin_transaction();

try {
    // 1. Get request details
    $stmt_info = $conn->prepare("SELECT tenant_id, property_id, status FROM termination_requests WHERE id = ?");
    $stmt_info->bind_param("i", $requestId);
    $stmt_info->execute();
    $result_info = $stmt_info->get_result();
    $request_data = $result_info->fetch_assoc();
    $stmt_info->close();

    if (!$request_data) {
        throw new Exception("Termination request not found.");
    }
    if ($request_data['status'] !== 'Pending') {
        throw new Exception("This request has already been processed.");
    }

    // 2. Update the termination_requests table
    if ($action === 'approve') {
        // --- APPROVAL LOGIC ---
        $newStatus = 'Approved';
        
        // Update the request status and set the approval timestamp
        $stmt_update_request = $conn->prepare("UPDATE termination_requests SET status = ?, admin_remark = ?, approved_at = NOW() WHERE id = ?");
        $stmt_update_request->bind_param("ssi", $newStatus, $adminRemark, $requestId);
        $stmt_update_request->execute();
        $stmt_update_request->close();

        // --- Send a notice to the tenant ---
        $tenantId = $request_data['tenant_id'];
        
        // Get tenant name for the notice
        $stmt_tenant_name = $conn->prepare("SELECT tenant_name FROM tenants WHERE tenant_id = ?");
        $stmt_tenant_name->bind_param("i", $tenantId);
        $stmt_tenant_name->execute();
        $tenant_result = $stmt_tenant_name->get_result();
        $tenant_info = $tenant_result->fetch_assoc();
        $tenant_name = $tenant_info['tenant_name'] ?? 'Tenant';
        $stmt_tenant_name->close();

        $notice_subject = "Lease Termination Approved";
        $notice_message = "Dear " . $tenant_name . ",\n\nYour request to terminate your lease has been approved. Please ensure the property is vacated within 24 hours from the time of this notice.";

        if (!empty($adminRemark)) {
            $notice_message .= "\n\nAdmin Remark: " . $adminRemark;
        }

        $notice_message .= "\n\nFurther details regarding your security deposit will be communicated shortly.\n\nThank you,\nVasundhara Housing Management";

        $stmt_notice = $conn->prepare("INSERT INTO tenantnotice (tenant_id, subject, message, recipient, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt_notice->bind_param("isss", $tenantId, $notice_subject, $notice_message, $tenant_name);
        $stmt_notice->execute();
        $stmt_notice->close();

        $response['message'] = "Request approved. A notice has been sent to the tenant to vacate within 24 hours.";
    } elseif ($action === 'reject') {
        // --- REJECTION LOGIC ---
        $newStatus = 'Rejected';
        $stmt_update_request = $conn->prepare("UPDATE termination_requests SET status = ?, admin_remark = ? WHERE id = ?");
        $stmt_update_request->bind_param("ssi", $newStatus, $adminRemark, $requestId);
        $stmt_update_request->execute();
        $stmt_update_request->close();

        // --- Send a rejection notice to the tenant ---
        $tenantId = $request_data['tenant_id'];
        
        // Get tenant name for the notice
        $stmt_tenant_name = $conn->prepare("SELECT tenant_name FROM tenants WHERE tenant_id = ?");
        $stmt_tenant_name->bind_param("i", $tenantId);
        $stmt_tenant_name->execute();
        $tenant_result = $stmt_tenant_name->get_result();
        $tenant_info = $tenant_result->fetch_assoc();
        $tenant_name = $tenant_info['tenant_name'] ?? 'Tenant';
        $stmt_tenant_name->close();

        $notice_subject = "Update on Your Lease Termination Request";
        $notice_message = "Dear " . $tenant_name . ",\n\nWe have reviewed your request to terminate your lease. Unfortunately, your request has been rejected at this time.";

        if (!empty($adminRemark)) {
            $notice_message .= "\n\nReason: " . $adminRemark;
        }
        $notice_message .= "\n\nPlease contact us if you have any questions.\n\nThank you,\nVasundhara Housing Management";

        $stmt_notice = $conn->prepare("INSERT INTO tenantnotice (tenant_id, subject, message, recipient, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt_notice->bind_param("isss", $tenantId, $notice_subject, $notice_message, $tenant_name);
        $stmt_notice->execute();
        $stmt_notice->close();

        $response['message'] = "Request has been successfully rejected and a notice has been sent to the tenant.";
    }

    $conn->commit();
    $response['success'] = true;
} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
?>