<?php
include './database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Create an array to hold the response
$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input data
    $name = isset($_POST['inquiryName']) ? htmlspecialchars(trim($_POST['inquiryName'])) : '';
    $email = isset($_POST['inquiryEmail']) ? trim($_POST['inquiryEmail']) : '';
    $phone = isset($_POST['inquiryPhone']) ? htmlspecialchars(trim($_POST['inquiryPhone'])) : '';
    $message = isset($_POST['inquiryMessage']) ? htmlspecialchars(trim($_POST['inquiryMessage'])) : '';

    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
        $messageType = 'error';
        exit;
    }

    // The user requested to store data in a table named `messages`
    // with fields: name, email, number, message.
    // Ensure this table exists in your `vasundharahousing` database.
    $stmt = $conn->prepare("INSERT INTO messages (name, email, number, message) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $name, $email, $phone, $message);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Your inquiry has been submitted successfully!';
        } else {
            $response['message'] = 'Failed to submit inquiry. Please try again.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Database error: could not prepare statement.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);

?>