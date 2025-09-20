<?php
// updateMaintenanceStatus.php
include '../database.php';
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
$maintenanceId = $_POST['id'] ?? 0;
$status = $_POST['status'] ?? '';

header('Content-Type: application/json');

if ($maintenanceId > 0 && !empty($status)) {
    $sql = "UPDATE maintenance_requests SET status = ? WHERE request_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $maintenanceId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
?>