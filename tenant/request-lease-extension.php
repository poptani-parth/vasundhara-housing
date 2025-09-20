<?php
session_start();
header('Content-Type: application/json');

include '../database.php';

$response = ['success' => false, 'message' => 'An error occurred.'];

if (!isset($_SESSION['tenant_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Unauthorized access or invalid request.';
    echo json_encode($response);
    exit();
}

$userId = $_SESSION['tenant_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['new_end_date']) || empty($input['new_end_date'])) {
    $response['message'] = 'New end date is required.';
    echo json_encode($response);
    exit();
}

$newEndDate = $input['new_end_date'];

try {
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // Start a transaction to ensure atomicity
    $conn->begin_transaction();

    // Get the current lease start and end dates from the database for validation
    $stmt_current = $conn->prepare("SELECT starting_date, ending_date FROM rental_agreements WHERE tenant_id = ?");
    if (!$stmt_current) {
        throw new Exception('Failed to prepare statement to get current agreement details: ' . $conn->error);
    }
    $stmt_current->bind_param("i", $userId);
    $stmt_current->execute();
    $result_current = $stmt_current->get_result();
    $agreement = $result_current->fetch_assoc();
    $stmt_current->close();

    if (!$agreement || empty($agreement['ending_date']) || empty($agreement['starting_date'])) {
        throw new Exception('Could not find current rental agreement to modify.');
    }

    // Server-side validation
    try {
        $newDateObj = new DateTime($newEndDate);
        $newDateObj->setTime(0, 0, 0); // Normalize to midnight for date-only comparison
    } catch (Exception $e) {
        throw new Exception('Invalid date format provided.');
    }
    $currentEndDateObj = new DateTime($agreement['ending_date']);
    $startDateObj = new DateTime($agreement['starting_date']);

    // The new end date cannot be the same as the current one.
    if ($newDateObj->format('Y-m-d') === $currentEndDateObj->format('Y-m-d')) {
        throw new Exception('The new end date cannot be the same as the current one.');
    }

    // The new end date must be after the lease start date.
    if ($newDateObj <= $startDateObj) {
        throw new Exception('The new end date must be after the lease start date.');
    }

    // The new end date cannot be more than 11 months from the start date.
    $maxEndDateObj = (new DateTime($agreement['starting_date']))->modify('+11 months');
    if ($newDateObj > $maxEndDateObj) {
        throw new Exception('The lease cannot be extended beyond 11 months. Please create a new agreement.');
    }

    // Update the ending_date in the rental_agreements table
    $sql = "UPDATE rental_agreements SET ending_date = ? WHERE tenant_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param("si", $newEndDate, $userId);

    if ($stmt->execute()) {
        // Check if any row was actually updated
        if ($stmt->affected_rows > 0) {
            // Commit the transaction to make the change permanent
            if ($conn->commit()) {
                // Final verification: Re-fetch the date to ensure it was saved correctly.
                // This helps diagnose issues where the commit might seem to succeed but doesn't (e.g., with MyISAM tables).
                $stmt_verify = $conn->prepare("SELECT ending_date FROM rental_agreements WHERE tenant_id = ?");
                $stmt_verify->bind_param("i", $userId);
                $stmt_verify->execute();
                $result_verify = $stmt_verify->get_result();
                $verified_agreement = $result_verify->fetch_assoc();
                $stmt_verify->close();

                $response['success'] = true;
                $response['message'] = 'Lease modification request submitted successfully!';
                $response['verified_date'] = $verified_agreement['ending_date'] ?? 'not-found';
            } else {
                throw new Exception('Database commit failed. The change was not saved.');
            }
        } else {
            // This is a critical error. We found the agreement earlier, but the UPDATE affected 0 rows.
            throw new Exception('The lease record was found, but the update failed. No rows were changed. Please contact support.');
        }
    } else {
        throw new Exception('Failed to execute the lease update query: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // If anything went wrong, roll back the transaction
    if ($conn && $conn->thread_id) { // Check if connection is still alive before rollback
        $conn->rollback();
    }
    $response['message'] = $e->getMessage();
    // Set an appropriate HTTP status code for errors. This helps the frontend distinguish between
    // a controlled failure (like a validation error) and an unexpected server crash.
    http_response_code(400); // 400 Bad Request is suitable for client-side errors like invalid dates.
}

echo json_encode($response);
?>