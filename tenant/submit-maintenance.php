<?php
session_start();
header('Content-Type: application/json');

// Include the database connection file
include '../database.php';

$response = ['success' => false, 'message' => ''];

// Check if the user is logged in
if (!isset($_SESSION['tenant_id'])) {
    $response['message'] = 'User is not logged in.';
    echo json_encode($response);
    exit();
}

$userId = $_SESSION['tenant_id'];

// Get the POST data from the AJAX request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate the incoming data
if (!isset($data['description']) || empty(trim($data['description'])) || !isset($data['property_id']) || empty($data['property_id'])) {
    $response['message'] = 'Description and Property ID are required.';
    echo json_encode($response);
    exit();
}

$description = $data['description'];
$propertyId = $data['property_id'];
$status = 'Pending'; // Default status for new requests
$requestDate = date('Y-m-d H:i:s'); // Current timestamp

try {
    // Check if the database connection is successful
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }
    
    // SQL query to insert the new maintenance request
    $sql = "INSERT INTO maintenance_requests (tenant_id, property_id, description, request_date, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare the SQL statement: " . mysqli_error($conn));
    }
    
    // Bind parameters
    mysqli_stmt_bind_param($stmt, "iisss", $userId, $propertyId, $description, $requestDate, $status);
    
    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
        $response['message'] = 'Maintenance request submitted successfully.';
    } else {
        throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>