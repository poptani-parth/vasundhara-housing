<?php 
header('Content-Type: application/json');
include './database.php';
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Validate month and year
if ($month < 1 || $month > 12) {
    $month = date('n');
}
if ($year < 2000 || $year > date('Y') + 1) { // Allow next year for future reports
    $year = date('Y');
}

// Create start and end dates for the selected month and year
$startDate = date('Y-m-01', strtotime("$year-$month-01"));
$endDate = date('Y-m-t', strtotime("$year-$month-01"));
$reportPeriod = date("F Y", strtotime($startDate));

// SQL to get payments for the specified period
$sql = "SELECT 
            p.payment_id, 
            p.amount, 
            p.remark, 
            p.payment_period, 
            p.status,
            t.tenant_name,
            prop.pro_nm,
            prop.month_rent
        FROM payments AS p
        JOIN tenants AS t ON p.tenant_id = t.tenant_id
        JOIN properties AS prop ON p.property_id = prop.pro_id
        WHERE p.payment_period BETWEEN ? AND ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}
$stmt->close();

// Calculate summary
$totalRevenue = 0;
$totalDue = 0;
$paidCount = 0;
$dueCount = 0;

foreach ($payments as $payment) {
    if ($payment['status'] === 'Paid') {
        $totalRevenue += $payment['amount'];
        $paidCount++;
    } else { // Due or Overdue
        $totalDue += $payment['amount'];
        $dueCount++;
    }
}

$response = [
    'reportTitle' => "Financial Report for " . $reportPeriod,
    'summary' => [
        'totalRevenue' => $totalRevenue,
        'totalDue' => $totalDue,
        'paidCount' => $paidCount,
        'dueCount' => $dueCount,
        'totalTransactions' => count($payments)
    ],
    'payments' => $payments,
];

echo json_encode($response);
$conn->close();
?>