<?php
include './database.php';
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    exit('Unauthorized');
}

$sql = "SELECT 
            tr.id,
            tr.request_date,
            tr.reason,
            tr.status,
            tr.admin_remark,
            t.tenant_name,
            p.pro_nm AS property_name
        FROM termination_requests tr
        JOIN tenants t ON tr.tenant_id = t.tenant_id
        JOIN properties p ON tr.property_id = p.pro_id
        ORDER BY 
            CASE tr.status
                WHEN 'Pending' THEN 1
                WHEN 'Approved' THEN 2
                WHEN 'Completed' THEN 3
                WHEN 'Rejected' THEN 4
            END, 
            tr.request_date DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = htmlspecialchars($row['status']);
        $statusConfig = match ($status) {
            'Approved' => ['badge' => 'bg-blue-100 text-blue-800', 'icon' => 'check-circle'],
            'Rejected' => ['badge' => 'bg-red-100 text-red-800', 'icon' => 'x-circle'],
            'Completed' => ['badge' => 'bg-green-100 text-green-800', 'icon' => 'check-circle-2'],
            default => ['badge' => 'bg-yellow-100 text-yellow-800', 'icon' => 'clock'],
        };

        echo '<div class="p-4 hover:bg-gray-50 transition-colors duration-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">';
        echo '  <div class="flex-grow">';
        echo '      <div class="flex items-center gap-3">';
        echo '          <span class="px-3 py-1 text-xs font-semibold rounded-full ' . $statusConfig['badge'] . '">';
        echo '              <i data-lucide="' . $statusConfig['icon'] . '" class="w-3 h-3 inline-block -mt-0.5 mr-1"></i>' . $status . '</span>';
        echo '          <p class="font-bold text-gray-800">' . htmlspecialchars($row['tenant_name']) . '</p>';
        echo '      </div>';
        echo '      <p class="text-sm text-gray-600 mt-2 ml-1">Property: <span class="font-medium">' . htmlspecialchars($row['property_name']) . '</span></p>';
        echo '      <p class="text-sm text-gray-600 mt-1 ml-1">Reason: <span class="italic">"' . nl2br(htmlspecialchars($row['reason'])) . '"</span></p>';
        if ($status !== 'Pending' && !empty($row['admin_remark'])) {
            echo '      <p class="text-sm text-gray-600 mt-1 ml-1">Admin Remark: <span class="font-medium text-blue-700">"' . nl2br(htmlspecialchars($row['admin_remark'])) . '"</span></p>';
        }
        echo '      <p class="text-xs text-gray-400 mt-2 ml-1">Requested on: ' . (new DateTime($row['request_date']))->format('F j, Y, g:i a') . '</p>';
        echo '  </div>';
        
        if ($status === 'Pending') {
            echo '  <div class="flex-shrink-0 flex items-center gap-2">';
            echo '      <button data-id="' . $row['id'] . '" data-name="' . htmlspecialchars($row['tenant_name']) . '" data-action="approve" class="process-termination-btn px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Approve</button>';
            echo '      <button data-id="' . $row['id'] . '" data-name="' . htmlspecialchars($row['tenant_name']) . '" data-action="reject" class="process-termination-btn px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Reject</button>';
            echo '  </div>';
        }
        
        echo '</div>';
    }
} else {
    echo '<div class="p-12 text-center text-gray-500"><i data-lucide="inbox" class="w-12 h-12 mx-auto text-gray-400"></i><p class="mt-2">No termination requests found.</p></div>';
}
$conn->close();
?>