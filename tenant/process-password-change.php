<?php
session_start();
header('Content-Type: application/json');
include '../database.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['tenant_id'])) {
    $response['message'] = 'Authentication required. Please log in again.';
    http_response_code(401);
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    http_response_code(405);
    echo json_encode($response);
    exit();
}

$tenant_id = $_SESSION['tenant_id'];
$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

if (empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
    $response['message'] = 'All password fields are required.';
    echo json_encode($response);
    exit();
}

if (strlen($new_password) < 6) {
    $response['message'] = 'New password must be at least 6 characters long.';
    echo json_encode($response);
    exit();
}

if ($new_password !== $confirm_new_password) {
    $response['message'] = 'New passwords do not match.';
    echo json_encode($response);
    exit();
}

try {
    // Fetch current password hash
    $stmt = $conn->prepare("SELECT password FROM tenants WHERE tenant_id = ?");
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tenant = $result->fetch_assoc();
    $stmt->close();

    if (!$tenant) {
        throw new Exception('Tenant not found.');
    }

    // Verify old password
    if (!password_verify($old_password, $tenant['password'])) {
        $response['message'] = 'Incorrect old password.';
        echo json_encode($response);
        exit();
    }

    // Hash new password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in database
    $stmt_update = $conn->prepare("UPDATE tenants SET password = ? WHERE tenant_id = ?");
    $stmt_update->bind_param("si", $new_password_hash, $tenant_id);
    $stmt_update->execute();

    if ($stmt_update->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Password updated successfully.';
    } else {
        $response['message'] = 'Password could not be updated. It might be the same as the old password.';
    }
    $stmt_update->close();

} catch (Exception $e) {
    $response['message'] = 'A database error occurred: ' . $e->getMessage();
    http_response_code(500);
}

$conn->close();
echo json_encode($response);
?>