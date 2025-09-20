<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    http_response_code(405); 
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

// Unset only admin-specific session variables to avoid logging out a tenant.
unset($_SESSION['admin_id']);
unset($_SESSION['type_admin']);
unset($_SESSION['admin_name']);

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'You have been logged out.']);
exit();
?>