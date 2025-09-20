<?php
// displayMaintenance.php
include '../database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
// Get filter and search parameters from the POST request
$status = $_POST['status'] ?? 'All';
$search = $_POST['search'] ?? '';
$date = $_POST['date'] ?? '';

// Base SQL query
$sql = "SELECT m.request_id, m.description, m.request_date, m.status, t.tenant_name, p.pro_nm AS property_name
        FROM maintenance_requests m 
        JOIN tenants t ON m.tenant_id = t.tenant_id
        JOIN properties p ON m.property_id = p.pro_id";

$conditions = [];
$params = [];
$types = '';

// Add conditions based on filters
if ($status !== 'All') {
    $conditions[] = "m.status = ?";
    $params[] = $status;
    $types .= 's';
}

if (!empty($search)) {
    // Search by tenant name, property name, or description
    $conditions[] = "(t.tenant_name LIKE ? OR p.pro_nm LIKE ? OR m.description LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

if (!empty($date)) {
    $conditions[] = "DATE(m.request_date) = ?";
    $params[] = $date;
    $types .= 's';
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY m.request_date DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status_color = match ($row['status']) {
            'Pending' => 'bg-yellow-100 text-yellow-800',
            'In Progress' => 'bg-blue-100 text-blue-800',
            'Completed' => 'bg-green-100 text-green-800',
            'Cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };

        echo '<tr class="hover:bg-gray-50 transition-colors">';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#' . htmlspecialchars($row['request_id']) . '</td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900">' . htmlspecialchars($row['tenant_name']) . '</div></td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">' . htmlspecialchars($row['property_name']) . '</td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">' . date('d M, Y', strtotime($row['request_date'])) . '</td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-center"><span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ' . $status_color . '">' . htmlspecialchars($row['status']) . '</span></td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">';
        echo '      <button data-id="' . $row['request_id'] . '" class="view-maintenance-btn text-indigo-600 hover:text-indigo-900">View Details</button>';
        echo '  </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6" class="text-center py-12 px-6">';
    echo '<div class="mx-auto w-fit">';
    echo '<i data-lucide="wrench" class="w-16 h-16 mx-auto text-gray-400"></i>';
    echo '<h3 class="mt-2 text-xl font-semibold text-gray-800">No Maintenance Requests Found</h3>';
    echo '<p class="mt-1 text-gray-500">Try adjusting your search or filter criteria.</p>';
    echo '</div>';
    echo '</td></tr>';
}

$stmt->close();
$conn->close();
?>