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

$message_id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);

if (!$message_id) {
    $response['message'] = 'Invalid message ID provided.';
    echo json_encode($response);
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM message WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Message deleted successfully.';
    } else {
        $response['message'] = 'Message not found or already deleted.';
    }
    $stmt->close();
} catch (Exception $e) {
    $response['message'] = 'Database operation failed: ' . $e->getMessage();
    http_response_code(500);
}

$conn->close();
echo json_encode($response);
?>