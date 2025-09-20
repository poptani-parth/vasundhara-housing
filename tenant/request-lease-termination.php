<?php
session_start();
header('Content-Type: application/json');

include '../database.php';

$response = ['success' => false, 'message' => 'An error occurred.'];

if (!isset($_SESSION['tenant_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Unauthorized access or invalid request.';
    http_response_code(401);
    echo json_encode($response);
    exit();
}

$userId = $_SESSION['tenant_id'];
$input = json_decode(file_get_contents('php://input'), true);
$reason = $input['reason'] ?? '';

if (empty(trim($reason))) {
    $response['message'] = 'A reason for termination is required.';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

try {
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // First, check if there's already a pending request for this tenant
    $stmt_check = $conn->prepare("SELECT id FROM termination_requests WHERE tenant_id = ? AND status = 'Pending'");
    $stmt_check->bind_param("i", $userId);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        throw new Exception('You already have a pending termination request.');
    }
    $stmt_check->close();

    // Get the tenant's property_id
    $stmt_prop = $conn->prepare("SELECT property_id FROM tenants WHERE tenant_id = ?");
    $stmt_prop->bind_param("i", $userId);
    $stmt_prop->execute();
    $result_prop = $stmt_prop->get_result();
    $tenant_data = $result_prop->fetch_assoc();
    $propertyId = $tenant_data['property_id'] ?? null;
    $stmt_prop->close();

    if (!$propertyId) {
        throw new Exception('Could not find an associated property for your account.');
    }

    /*
    SQL to create the required table:
    CREATE TABLE `termination_requests` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `tenant_id` int(11) NOT NULL,
      `property_id` int(11) NOT NULL,
      `request_date` datetime NOT NULL DEFAULT current_timestamp(),
      `reason` text NOT NULL,
      `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
      `admin_remark` text DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    */
    $sql = "INSERT INTO termination_requests (tenant_id, property_id, reason) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $userId, $propertyId, $reason);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Lease termination request submitted successfully. The admin has been notified.';
    } else {
        throw new Exception('Failed to submit your request: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(400); // Use 400 for client-side errors like duplicate requests
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>