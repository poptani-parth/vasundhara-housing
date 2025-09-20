<?php
session_start();
header('Content-Type: application/json');
include './database.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    http_response_code(405);
    echo json_encode($response);
    exit();
}

$otp = $_POST['otp'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Check session variables
if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_email']) || !isset($_SESSION['reset_otp_expiry'])) {
    $response['message'] = 'Password reset session not found or expired. Please try again.';
    echo json_encode($response);
    exit();
}

// Check if OTP has expired
if (time() > $_SESSION['reset_otp_expiry']) {
    unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_otp_expiry']);
    $response['message'] = 'OTP has expired. Please request a new one.';
    echo json_encode($response);
    exit();
}

// Validate inputs
if (empty($otp) || empty($new_password) || empty($confirm_password)) {
    $response['message'] = 'All fields are required.';
    echo json_encode($response);
    exit();
}

if ($otp != $_SESSION['reset_otp']) {
    $response['message'] = 'Invalid OTP entered.';
    echo json_encode($response);
    exit();
}

if (strlen($new_password) < 6) {
    $response['message'] = 'New password must be at least 6 characters long.';
    echo json_encode($response);
    exit();
}

if ($new_password !== $confirm_password) {
    $response['message'] = 'New passwords do not match.';
    echo json_encode($response);
    exit();
}

try {
    $email = $_SESSION['reset_email'];
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in the database
    $stmt = $conn->prepare("UPDATE tenants SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_password_hash, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Password has been reset successfully. You can now log in with your new password.';
        // Clear session variables after successful reset
        unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_otp_expiry']);
    } else {
        $response['message'] = 'Failed to update password. Please try again.';
    }
    $stmt->close();

} catch (Exception $e) {
    $response['message'] = 'A database error occurred.';
    http_response_code(500);
}

$conn->close();
echo json_encode($response);
?>