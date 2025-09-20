<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include './database.php';
session_start();

// Security check: ensure user is an admin
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
// Fetch tenants with 'unactive' status and join with properties to get the property name
$sql = "SELECT t.tenant_id, t.tenant_name, t.email, t.contact_number, p.pro_nm
        FROM tenants t
        LEFT JOIN properties p ON t.property_id = p.pro_id
        WHERE t.status = 'unactive'
        ORDER BY t.tenant_id DESC";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Log error to server logs for production
    error_log('Prepare failed: ' . $conn->error);
    // Display a user-friendly error in the table
    echo '<tr><td colspan="4" class="text-center py-12 px-6 text-red-500">A database error occurred. Please contact support.</td></tr>';
    exit();
}
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($tenant = $result->fetch_assoc()) {
        $id = (int)$tenant['tenant_id'];
        $name = htmlspecialchars($tenant['tenant_name']);
        $contact = htmlspecialchars($tenant['contact_number'] ?: 'N/A');
        $email = htmlspecialchars($tenant['email'] ?: 'N/A');
        $property_name = htmlspecialchars($tenant['pro_nm'] ?: 'Not Assigned');
?>
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900"><?php echo $name; ?></div></td>
            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900"><?php echo $contact; ?></div><div class="text-sm text-gray-500"><?php echo $email; ?></div></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo $property_name; ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                <a href="getTenantDetails.php?id=<?php echo $id; ?>" class="text-blue-600 hover:text-blue-900">View</a>
                <button data-id="<?php echo $id; ?>" data-name="<?php echo $name; ?>" data-action="accept" class="action-btn text-green-600 hover:text-green-900">Accept</button>
                <button data-id="<?php echo $id; ?>" data-name="<?php echo $name; ?>" data-action="reject" class="action-btn text-red-600 hover:text-red-900">Reject</button>
            </td>
        </tr>
<?php
    }
} else {
    echo '<tr><td colspan="4" class="text-center py-12 px-6"><div class="mx-auto w-fit"><i data-lucide="user-x" class="w-16 h-16 mx-auto text-gray-400"></i><h3 class="mt-2 text-xl font-semibold text-gray-800">No Pending Requests</h3><p class="mt-1 text-gray-500">There are currently no new tenant requests to review.</p></div></td></tr>';
}

$stmt->close();
$conn->close();
?>