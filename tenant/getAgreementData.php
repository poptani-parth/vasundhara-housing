<?php
session_start();
header('Content-Type: application/json');
include '../database.php';

$response = ['success' => false, 'message' => 'An error occurred.'];

if (!isset($_SESSION['tenant_id']) || $_SESSION['type_tenant'] !== 'tenant') {
    $response['message'] = 'User not logged in.';
    http_response_code(401);
    echo json_encode($response);
    exit();
}

$userId = $_SESSION['tenant_id'];
$agreementId = $_GET['agreement_id'] ?? null;

try {
    $sql = "SELECT ra.*, t.tenant_name, p.pro_nm AS property_name, p.pro_type, p.bed, p.bath
            FROM rental_agreements ra
            JOIN tenants t ON ra.tenant_id = t.tenant_id
            JOIN properties p ON ra.pro_id = p.pro_id
            WHERE ra.tenant_id = ?";
    
    $params = [$userId];
    $types = 'i';

    if ($agreementId && is_numeric($agreementId)) {
        $sql .= " AND ra.agreement_id = ?";
        $params[] = $agreementId;
        $types .= 'i';
    } else {
        // Fallback to the latest agreement if no specific ID is requested
        $sql .= " ORDER BY ra.starting_date DESC LIMIT 1";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $agreementData = $result->fetch_assoc();
    $stmt->close();

    if ($agreementData) {
        $response['success'] = true;
        $response['data'] = $agreementData;
    } else {
        $response['message'] = 'No rental agreement found for this account.';
    }

} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
}

$conn->close();
echo json_encode($response);
?>