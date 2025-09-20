<?php
include './database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Create an array to hold the response
$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input data
    $name = isset($_POST['fullName']) ? htmlspecialchars(trim($_POST['fullName'])) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? htmlspecialchars(trim($_POST['subject'])) : '';
    $message_body = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';

    // Combine subject and message for storage
    $full_message = "Subject: " . $subject . "\n\n" . $message_body;

    // Basic validation
    if (empty($name) || empty($email) || empty($message_body)) {
        $response['message'] = 'Please fill in all required fields (Name, Email, Message).';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }

    // The 'number' field is not on this form, so we'll let it be NULL in the DB.
    $stmt = $conn->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sss", $name, $email, $full_message);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Your message has been sent successfully!';
        } else {
            $response['message'] = 'Failed to send message. Please try again.';
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