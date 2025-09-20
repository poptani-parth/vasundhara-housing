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
$searchYear = $_GET['year'] ?? null;

if (!$searchYear || !is_numeric($searchYear)) {
    $response['message'] = 'Year parameter is missing or invalid.';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

try {
    // 1. Fetch tenant details
    $stmt_tenant = $conn->prepare("
        SELECT t.tenant_name, t.contact_number AS tenant_phone, t.email AS tenant_email, ra.address AS tenant_address
        FROM tenants t
        LEFT JOIN rental_agreements ra ON t.tenant_id = ra.tenant_id
        WHERE t.tenant_id = ?
    ");
    $stmt_tenant->bind_param("i", $userId);
    $stmt_tenant->execute();
    $tenantDetails = $stmt_tenant->get_result()->fetch_assoc();
    $stmt_tenant->close();

    if (!$tenantDetails) {
        throw new Exception('Tenant details not found.');
    }

    // 2. Fetch payments for the given year
    $sql_payments = "
        SELECT p.payment_id, p.amount, p.payment_period, p.remark, pr.pro_nm AS property_name
        FROM payments p
        JOIN properties pr ON p.property_id = pr.pro_id
        WHERE p.tenant_id = ? AND YEAR(p.payment_period) = ? AND p.status = 'Paid'
        ORDER BY p.payment_period ASC
    ";
    $stmt_payments = $conn->prepare($sql_payments);
    $stmt_payments->bind_param("ii", $userId, $searchYear);
    $stmt_payments->execute();
    $paymentsResult = $stmt_payments->get_result();
    $payments = $paymentsResult->fetch_all(MYSQLI_ASSOC);
    $stmt_payments->close();

    $response['success'] = true;
    $response['data'] = [
        'tenantDetails' => $tenantDetails,
        'payments' => $payments,
        'period' => $searchYear
    ];

} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
}

$conn->close();
echo json_encode($response);
?>