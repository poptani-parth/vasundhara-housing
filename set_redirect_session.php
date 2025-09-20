<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set session variables to guide the user on the login page
    $_SESSION['registration_required'] = true;
    $_SESSION['toast_message'] = $_POST['message'] ?? 'Please register to continue.';
    $_SESSION['toast_type'] = 'info'; // Use 'info' for this kind of instructional message
    $_SESSION['prefill_name'] = $_POST['prefill_name'] ?? '';
    $_SESSION['post_registration_redirect'] = $_POST['redirect_to'] ?? '';

    echo json_encode(['success' => true]);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>