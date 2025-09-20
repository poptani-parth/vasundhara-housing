<?php
// getMaintenanceRequest.php
include '../database.php';
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$maintenanceId = $_GET['id'] ?? 0;

if ($maintenanceId > 0) {
    $sql = "SELECT m.*, t.tenant_name, p.pro_nm AS property_name
            FROM maintenance_requests m
            JOIN tenants t ON m.tenant_id = t.tenant_id
            JOIN properties p ON m.property_id = p.pro_id
            WHERE m.request_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $maintenanceId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $status_color = match ($row['status']) {
            'Pending' => 'bg-yellow-100 text-yellow-800',
            'In Progress' => 'bg-blue-100 text-blue-800',
            'Completed' => 'bg-green-100 text-green-800',
            'Cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };

        echo '<div class="space-y-6">';
        echo '  <div>';
        echo '      <p class="text-sm text-gray-500">Request from</p>';
        echo '      <p class="text-lg font-semibold text-gray-800">' . htmlspecialchars($row['tenant_name']) . '</p>';
        echo '      <p class="text-sm text-gray-500">for property <span class="font-medium text-gray-700">' . htmlspecialchars($row['property_name']) . '</span></p>';
        echo '  </div>';

        echo '  <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">';
        echo '      <div class="sm:col-span-1"><dt class="text-sm font-medium text-gray-500">Request ID</dt><dd class="mt-1 text-sm text-gray-900">#' . htmlspecialchars($row['request_id']) . '</dd></div>';
        echo '      <div class="sm:col-span-1"><dt class="text-sm font-medium text-gray-500">Request Date</dt><dd class="mt-1 text-sm text-gray-900">' . date('F d, Y', strtotime($row['request_date'])) . '</dd></div>';
        echo '      <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500">Current Status</dt><dd class="mt-1 text-sm text-gray-900"><span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ' . $status_color . '">' . htmlspecialchars($row['status']) . '</span></dd></div>';
        echo '      <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500">Description</dt><dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-md border">' . nl2br(htmlspecialchars($row['description'])) . '</dd></div>';
        echo '  </dl>';

        echo '  <div class="pt-6 border-t">';
        echo '      <h4 class="text-base font-semibold text-gray-800">Update Status</h4>';
        echo '      <div class="flex flex-wrap gap-3 mt-3">';
        
        $statuses = [
            'Pending'     => 'bg-yellow-500 hover:bg-yellow-600 ring-yellow-500',
            'In Progress' => 'bg-blue-500 hover:bg-blue-600 ring-blue-500',
            'Completed'   => 'bg-green-500 hover:bg-green-600 ring-green-500',
            'Cancelled'   => 'bg-red-500 hover:bg-red-600 ring-red-500'
        ];
        foreach ($statuses as $status => $classes) {
            $active_class = ($row['status'] === $status) ? 'ring-2 ring-offset-2' : '';
            echo "<button class='update-status-btn px-4 py-2 rounded-lg text-white text-sm font-semibold transition-all duration-200 {$classes} {$active_class}' data-id='{$row['request_id']}' data-status='{$status}'>{$status}</button>";
        }
        echo '      </div>';
        echo '  </div>';
        echo '</div>';
    } else {
        echo "<p class='text-red-500'>Maintenance request not found.</p>";
    }

    $stmt->close();
} else {
    echo "<p class='text-red-500'>Invalid request ID.</p>";
}

$conn->close();
?>