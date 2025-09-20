<?php
session_start();
header('Content-Type: application/json');

include '../database.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['tenant_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Unauthorized access or invalid request.';
    echo json_encode($response);
    exit();
}

$userId = $_SESSION['tenant_id'];
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['amount']) || !isset($input['period']) || !isset($input['property_id'])) {
    $response['message'] = 'Incomplete payment data provided (amount, period, or property ID is missing).';
    echo json_encode($response);
    exit();
}

$amount = $input['amount'];
$paymentPeriodString = $input['period']; // e.g., "August 2024"
$propertyId = $input['property_id'];
$paymentPeriodForDb = date('Y-m-01', strtotime($paymentPeriodString)); // Convert to YYYY-MM-01 format

try {
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // Check for duplicate payment for the same month
    $sql_check = "SELECT payment_id FROM payments WHERE tenant_id = ? AND property_id = ? AND DATE_FORMAT(payment_period, '%Y-%m') = ? AND status = 'Paid'";
    $stmt_check = $conn->prepare($sql_check);
    $paymentMonthYm = date('Y-m', strtotime($paymentPeriodForDb));
    $stmt_check->bind_param("iis", $userId, $propertyId, $paymentMonthYm);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        throw new Exception("A payment for this period has already been recorded.");
    }
    $stmt_check->close();

    // Insert the new payment record into the 'payments' table. Use CURDATE() for DATE type.
    $sql = "INSERT INTO payments (tenant_id, property_id, amount, payment_date, status, payment_period, remark) VALUES (?, ?, ?, CURDATE(), 'Paid', ?, ?)";
    $stmt = $conn->prepare($sql);
    $remark = 'Rent for ' . htmlspecialchars($paymentPeriodString);
    // Use 'd' for amount as it can be a float/decimal.
    $stmt->bind_param("iidss", $userId, $propertyId, $amount, $paymentPeriodForDb, $remark);
    $stmt->bind_param("iddss", $userId, $propertyId, $amount, $paymentPeriodForDb, $remark);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Payment recorded successfully!';
    } else {
        throw new Exception('Failed to record payment in the database: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['message'] = 'Error processing payment: ' . $e->getMessage();
}

echo json_encode($response);
?>
