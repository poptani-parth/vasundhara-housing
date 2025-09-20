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

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'A valid email address is required.';
    echo json_encode($response);
    exit();
}

try {
    // Check if email exists in tenants table
    $stmt = $conn->prepare("SELECT tenant_id FROM tenants WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // To prevent email enumeration, we can send a generic success message.
        // But for this demo, we'll be explicit.
        $response['message'] = 'No account found with that email address.';
        echo json_encode($response);
        exit();
    }

    // Generate a 6-digit OTP
    $otp = rand(100000, 999999);

    // Store OTP and email in session with an expiry time (e.g., 10 minutes)
    $_SESSION['reset_otp'] = $otp;
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_otp_expiry'] = time() + (10 * 60); // 10 minutes from now

    // --- SIMULATE SENDING EMAIL ---
    // In a real application, you would use a library like PHPMailer to send an email.
    // For this demonstration, as requested, we will show the OTP in the success message.
    // THIS IS HIGHLY INSECURE AND FOR DEMONSTRATION PURPOSES ONLY.
    $response['success'] = true;
    $response['message'] = 'An OTP has been generated. For this demo, your OTP is: ' . $otp;

} catch (Exception $e) {
    $response['message'] = 'A database error occurred.';
    http_response_code(500);
}

$conn->close();
echo json_encode($response);
?>