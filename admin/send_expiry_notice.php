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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    http_response_code(405);
    echo json_encode($response);
    exit();
}

$tenant_id = filter_input(INPUT_POST, 'tenant_id', FILTER_VALIDATE_INT);
$tenant_name = htmlspecialchars($_POST['tenant_name'] ?? 'Tenant');

if (!$tenant_id) {
    $response['message'] = 'Invalid tenant ID provided.';
    echo json_encode($response);
    exit();
}

try {
    $subject = "Lease Agreement Expiry Reminder";
    $message = "Dear " . $tenant_name . ",\n\nThis is a friendly reminder that your lease agreement is due to expire soon. Please contact us to discuss renewal options.\n\nThank you,\nVasundhara Housing Management";

    // The recipient column stores the name for display purposes.
    $stmt = $conn->prepare("INSERT INTO tenantnotice (tenant_id, subject, message, recipient, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $tenant_id, $subject, $message, $tenant_name);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Expiry notice sent successfully to ' . $tenant_name . '.';
    } else {
        throw new Exception('Failed to store the notice in the database.');
    }
    $stmt->close();

} catch (Exception $e) {
    $response['message'] = 'Database operation failed: ' . $e->getMessage();
    http_response_code(500);
}

$conn->close();
echo json_encode($response);
?>