<?php
// Set header to return JSON, as this is called by an AJAX request
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}
// Include database connection
include "./database.php"; // Provides $conn

// Initialize response array
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['noticeTitle'], $_POST['noticeMessage'], $_POST['recipientType'])) {
    $subject = trim($_POST['noticeTitle']);
    $message = trim($_POST['noticeMessage']);
    $recipientType = $_POST['recipientType'];

    // --- Basic Validation ---
    if (empty($subject) || empty($message) || empty($recipientType)) {
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit;
    }

    if ($recipientType === 'All Tenants') {
        // --- Case 1: Send to all tenants (broadcast message) ---
        // The tenant_id is NULL, and the recipient is a generic string.
        // The tenant-side query is designed to pick this up.
        $sql = "INSERT INTO tenantnotice (tenant_id, subject, message, recipient, created_at) VALUES (NULL, ?, ?, 'All Tenants', NOW())";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('ss', $subject, $message);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Notice sent to all tenants successfully!";
            } else {
                $response['message'] = "Failed to send notice to all tenants.";
            }
            $stmt->close();
        } else {
            $response['message'] = 'Database error: Could not prepare statement for all tenants. ' . $conn->error;
        }
    } else if (is_numeric($recipientType)) {
        // --- Case 2: Send to a specific tenant ---
        $tenant_id = (int)$recipientType;
        $recipient_name = '';

        // Fetch tenant name for the 'recipient' column using the tenant_id
        $stmt_tenant = $conn->prepare("SELECT tenant_name FROM tenants WHERE tenant_id = ?");
        if ($stmt_tenant) {
            $stmt_tenant->bind_param("i", $tenant_id);
            $stmt_tenant->execute();
            $result_tenant = $stmt_tenant->get_result();
            if ($result_tenant->num_rows > 0) {
                $tenant = $result_tenant->fetch_assoc();
                $recipient_name = $tenant['tenant_name'];
            } else {
                $response['message'] = 'Invalid tenant selected.';
                $stmt_tenant->close();
                $conn->close();
                echo json_encode($response);
                exit;
            }
            $stmt_tenant->close();
        } else {
            $response['message'] = 'Database error: Could not fetch tenant name.';
            $conn->close();
            echo json_encode($response);
            exit;
        }

        $sql = "INSERT INTO tenantnotice (tenant_id, subject, message, recipient, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('isss', $tenant_id, $subject, $message, $recipient_name);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Notice sent successfully to " . htmlspecialchars($recipient_name) . "!";
            } else {
                $response['message'] = "Failed to send notice.";
            }
            $stmt->close();
        } else {
            $response['message'] = 'Database error: Could not prepare statement for specific tenant. ' . $conn->error;
        }
    } else {
        $response['message'] = 'Invalid recipient type specified.';
    }
} else {
    $response['message'] = 'Invalid request or missing data.';
}

$conn->close();
echo json_encode($response);
?>