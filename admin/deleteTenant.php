<?php
session_start();
header('Content-Type: application/json');
include './database.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security check
if (!isset($_SESSION['admin_id'])) {
    $response['message'] = 'Unauthorized access.';
    http_response_code(403);
    echo json_encode($response);
    exit();
}

$tenant_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$tenant_id) {
    $response['message'] = 'Invalid Tenant ID provided.';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

try {
    // Check if the tenant is assigned to a property
    $stmt_check = $conn->prepare("SELECT property_id FROM tenants WHERE tenant_id = ?");
    $stmt_check->bind_param("i", $tenant_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $tenant = $result->fetch_assoc();
    $stmt_check->close();

    if (!$tenant) {
        $response['message'] = 'Tenant not found.';
        http_response_code(404);
        echo json_encode($response);
        exit();
    }

    // If property_id is not null, they are linked to a property
    if (!empty($tenant['property_id'])) {
        $response['message'] = 'This tenant is currently assigned to a property and cannot be deleted.';
        http_response_code(409); // 409 Conflict
        echo json_encode($response);
        exit();
    }

    // If not linked, proceed with deletion
    $stmt_delete = $conn->prepare("DELETE FROM tenants WHERE tenant_id = ?");
    $stmt_delete->bind_param("i", $tenant_id);
    
    if ($stmt_delete->execute() && $stmt_delete->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Tenant deleted successfully.';
    } else {
        $response['message'] = 'Failed to delete tenant or tenant not found.';
    }
    $stmt_delete->close();

} catch (Exception $e) {
    $response['message'] = 'Database operation failed: ' . $e->getMessage();
    http_response_code(500);
}

$conn->close();
echo json_encode($response);
?>