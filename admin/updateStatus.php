<?php

include './database.php'; // Make sure this path is correct
session_start();

// Set header to return JSON for all responses
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

if ($id === null || $status === null) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Property ID and Status are required.']);
    exit;
}

// Validate status against an allowed list to prevent invalid data
$allowedStatuses = ['Available', 'Booked', 'Unavailable', 'Maintenance'];
if (!in_array($status, $allowedStatuses)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid status value provided.']);
    exit;
}

// Prepare and execute the update query
$sql = "UPDATE properties SET status = ? WHERE pro_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Property status updated successfully.']);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();