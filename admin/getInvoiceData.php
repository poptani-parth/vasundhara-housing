<?php
header('Content-Type: application/json');

include './database.php';
session_start();

// Security check: ensure user is logged in
if (!isset($_SESSION['admin_id']) && (!isset($_SESSION['tenant_id']) || ($_SESSION['type_tenant'] !== 'tenant'))) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$paymentId = $_GET['payment_id'] ?? null;

if (!$paymentId) {
    echo json_encode(['success' => false, 'message' => 'Payment ID is missing.']);
    exit;
}

// 1. Get the main payment record to find tenant_id and payment_periods
$stmt_main = $conn->prepare("SELECT * FROM payments WHERE payment_id = ?");
$stmt_main->bind_param("i", $paymentId);
$stmt_main->execute();
$main_result = $stmt_main->get_result();
$main_payment = $main_result->fetch_assoc();
$stmt_main->close();

if (!$main_payment) {
    echo json_encode(['success' => false, 'message' => 'Payment record not found.']);
    exit;
}

// Security check: An admin can see any invoice, but a tenant can only see their own.
if (isset($_SESSION['type_admin']) && $_SESSION['type_admin'] === 'admin') {
    // Admin is authorized to view any invoice.
} else if (isset($_SESSION['type_tenant']) && $_SESSION['type_tenant'] === 'tenant') {
    // Tenant is logged in, check if they own the invoice.
    if ($main_payment['tenant_id'] != $_SESSION['tenant_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You are not authorized to view this invoice.']);
        exit;
    }
} else {
    // This case should ideally be caught by the first check, but as a fallback:
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Unauthorized role.']);
    exit;
}

$tenantId = $main_payment['tenant_id'];
$paymentPeriod = $main_payment['payment_period'];

// 2. Fetch all payments for this tenant and period, with property name
$stmt_items = $conn->prepare("
    SELECT p.*, pr.pro_nm AS property_name
    FROM payments p
    LEFT JOIN properties pr ON p.property_id = pr.pro_id
    WHERE p.tenant_id = ? AND p.payment_period = ?
");
$stmt_items->bind_param("is", $tenantId, $paymentPeriod);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$payment_items = [];
$total_amount = 0;
while ($row = $items_result->fetch_assoc()) {
    $payment_items[] = $row;
    $total_amount += $row['amount'];
}
$stmt_items->close();

// 3. Fetch main invoice details (tenant, property, etc.)
$sql_details = "SELECT t.tenant_name, t.contact_number AS tenant_phone, t.email AS tenant_email, ra.address AS tenant_address
        FROM tenants t
        LEFT JOIN rental_agreements ra ON t.tenant_id = ra.tenant_id
        WHERE t.tenant_id = ?";
$stmt_details = $conn->prepare($sql_details);
$stmt_details->bind_param("i", $tenantId);
$stmt_details->execute();
$details_result = $stmt_details->get_result();
$details = $details_result->fetch_assoc();
$stmt_details->close();

if ($details) {
    $data = $details;
    $data['payment_id'] = $paymentId;
    $data['payment_period'] = $main_payment['payment_period'];
    $data['status'] = $main_payment['status']; // Overall status from the clicked payment
    $data['amount'] = $total_amount; // This is now the total amount
    $data['items'] = $payment_items; // Array of payment line items
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not retrieve tenant details for this invoice.']);
}

$conn->close();
?>