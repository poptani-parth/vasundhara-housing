<?php
include './database.php'; // Provides $conn
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
// --- 1. Initialize variables from POST data ---
$search = isset($_POST['search']) ? $_POST['search'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'All';

// --- 2. Build the SQL query with JOINs ---
$sql = "SELECT 
            p.payment_id, 
            p.amount, 
            p.remark, 
            p.payment_period, 
            p.status,
            t.tenant_name,
            t.tenant_id,
            prop.pro_nm
        FROM payments AS p
        JOIN tenants AS t ON p.tenant_id = t.tenant_id
        JOIN properties AS prop ON p.property_id = prop.pro_id
        WHERE 1=1";

$params = [];
$types = '';

// Add search condition (tenant name OR property name)
if (!empty($search)) {
    $sql .= " AND (t.tenant_name LIKE ? OR prop.pro_nm LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

// Add status condition
if ($status !== 'All') {
    $sql .= " AND p.status = ?";
    $params[] = $status;
    $types .= 's';
}

$sql .= " ORDER BY p.payment_period DESC";

// --- 3. Prepare and execute the statement ---
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// --- 4. Generate HTML output ---
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Determine status color based on payment status
        $status_classes = match ($row['status']) {
            'Paid' => 'bg-green-100 text-green-800',
            'Due' => 'bg-yellow-100 text-yellow-800',
            'Overdue' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };

        echo '<tr class="hover:bg-gray-50 transition-colors">';
        echo '  <td class="px-6 py-4 max-w-xs break-words"><div class="text-sm font-medium text-gray-900">' . htmlspecialchars($row['tenant_name']) . '</div><div class="text-sm text-gray-500">' . htmlspecialchars($row['pro_nm']) . '</div></td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-semibold text-gray-800">₹' . number_format($row['amount']) . '</div></td>';
        echo '  <td class="px-6 py-4 text-sm text-gray-600 max-w-xs break-words">' . htmlspecialchars($row['remark']) . '</td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">' . date('d M, Y', strtotime($row['payment_period'])) . '</td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-center"><span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ' . $status_classes . '">' . htmlspecialchars($row['status']) . '</span></td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">';
        // If payment is due, show a button to send a notice
        if ($row['status'] === 'Due' || $row['status'] === 'Overdue') {
            echo '<button class="send-due-notice-btn text-xs bg-yellow-500 text-white font-semibold py-1 px-3 rounded-lg hover:bg-yellow-600 transition-colors"
                data-tenant-id="' . $row['tenant_id'] . '"
                data-tenant-name="' . htmlspecialchars($row['tenant_name']) . '"
                data-period="' . htmlspecialchars(date('F Y', strtotime($row['payment_period']))) . '"
                data-amount="' . $row['amount'] . '">
                <i class="ri-mail-send-line mr-1"></i>Send Notice
            </button>';
        } else {
            // Otherwise, show the standard invoice button
            echo '      <button data-id="' . $row['payment_id'] . '" class="print-invoice-btn inline-flex items-center gap-1 px-5 py-2 text-xs font-bold text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors">';
            echo '          Invoice';
            echo '      </button>';
        }
        echo '  </td>';
        echo '</tr>';
    }
    // Re-initialize Lucide icons after AJAX load
    echo '<script>lucide.createIcons();</script>';
} else {
    echo '<tr><td colspan="6" class="text-center py-12 px-6">';
    echo '<div class="mx-auto w-fit">';
    echo '<i data-lucide="search-x" class="w-16 h-16 mx-auto text-gray-400"></i>';
    echo '<h3 class="mt-2 text-xl font-semibold text-gray-800">No Payments Found</h3>';
    echo '<p class="mt-1 text-gray-500">Try adjusting your search or filter criteria.</p>';
    echo '</div>';
    echo '</td></tr>';
    echo '<script>lucide.createIcons();</script>'; // Re-initialize icons for the "not found" message
}

$stmt->close();
$conn->close();