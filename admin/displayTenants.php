<?php
include './database.php';
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
$search = isset($_POST['search']) ? $_POST['search'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'All';

$sql = "SELECT 
            t.tenant_id, 
            t.tenant_name, 
            t.contact_number, 
            t.email, 
            t.status,
            prop.pro_nm
        FROM tenants AS t
        LEFT JOIN properties AS prop ON t.property_id = prop.pro_id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND (t.tenant_name LIKE ? OR t.email LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

if ($status !== 'All') {
    $sql .= " AND t.status = ?";
    $params[] = $status;
    $types .= 's';
}

$sql .= " ORDER BY t.tenant_name ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status_classes = $row['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        
        echo '<tr class="hover:bg-gray-50 transition-colors">';
        echo '  <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900">' . htmlspecialchars($row['tenant_name']) . '</div></td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900">' . htmlspecialchars($row['contact_number']) . '</div><div class="text-sm text-gray-500">' . htmlspecialchars($row['email']) . '</div></td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">' . htmlspecialchars($row['pro_nm'] ?? 'Not Assigned') . '</td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-center"><span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ' . $status_classes . '">' . htmlspecialchars($row['status']) . '</span></td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">';
        echo '      <a href="getTenantDetails.php?id=' . $row['tenant_id'] . '" class="text-indigo-600 hover:text-indigo-900">View</a>';
        echo '      <button data-id="' . $row['tenant_id'] . '" class="delete-tenant-btn text-red-600 hover:text-red-900">Delete</button>';
        echo '  </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5" class="text-center py-12 px-6"><div class="mx-auto w-fit"><i data-lucide="users-round" class="w-16 h-16 mx-auto text-gray-400"></i><h3 class="mt-2 text-xl font-semibold text-gray-800">No Tenants Found</h3><p class="mt-1 text-gray-500">Try adjusting your search or filter criteria.</p></div></td></tr>';
    echo '<script>lucide.createIcons();</script>';
}

$stmt->close();
$conn->close();