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

$property_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$property_id) {
    $response['message'] = 'Invalid Property ID provided.';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

try {
    // Check if any tenant is assigned to this property
    $stmt_check = $conn->prepare("SELECT COUNT(*) as tenant_count FROM tenants WHERE property_id = ?");
    $stmt_check->bind_param("i", $property_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();
    $stmt_check->close();

    if ($row['tenant_count'] > 0) {
        $response['message'] = 'Cannot delete property. It is currently assigned to a tenant.';
        http_response_code(409); // 409 Conflict
        echo json_encode($response);
        exit();
    }

    // If not linked, proceed with deletion
    $stmt_delete = $conn->prepare("DELETE FROM properties WHERE pro_id = ?");
    $stmt_delete->bind_param("i", $property_id);
    
    if ($stmt_delete->execute() && $stmt_delete->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Property deleted successfully.';
    } else {
        $response['message'] = 'Failed to delete property or property not found.';
    }
    $stmt_delete->close();

} catch (Exception $e) {
    $response['message'] = 'Database operation failed: ' . $e->getMessage();
    http_response_code(500);
}

$conn->close();
echo json_encode($response);
?>