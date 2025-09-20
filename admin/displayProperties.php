<?php
include './database.php';
session_start();

function getImageUrl($path)
{
    if (!empty($path)) {
        $filename = basename($path); // get just the file name
        $relativePath = './uploads/property_images/' . $filename;
        $absolutePath = './uploads/property_images/' . $filename;

        if (file_exists($absolutePath)) {
            return $relativePath;
        }
    }
    return '../assets/images/not_found.png'; // Placeholder for missing image
}
// --- 1. Initialize variables and conditions ---
$search = isset($_POST['search']) ? $_POST['search'] : '';
$type = isset($_POST['type']) ? $_POST['type'] : 'All';
$status = isset($_POST['status']) ? $_POST['status'] : 'All';

$sql = "SELECT pro_id, pro_nm, pro_type, month_rent, status, district, outdoor_img FROM properties WHERE 1=1";
$params = [];
$types = '';

// --- 2. Dynamically build the query based on filters ---

// Add search condition for property name OR district
if (!empty($search)) {
    $sql .= " AND (pro_nm LIKE ? OR district LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

// Add property type condition
if ($type !== 'All') {
    $sql .= " AND pro_type = ?";
    $params[] = $type;
    $types .= 's';
}

// Add status condition
if ($status !== 'All') {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= 's';
}

$sql .= " ORDER BY pro_id DESC";

// --- 3. Prepare and execute the statement to prevent SQL injection ---
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Handle SQL prepare error
    echo '<tr><td colspan="5" class="text-center py-12 px-6 text-red-500">Error preparing the SQL statement: ' . htmlspecialchars($conn->error) . '</td></tr>';
    exit;
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// --- 4. Generate HTML output ---
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Determine status color
        $status_color = $row['status'] === 'Available' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';

        echo '<tr class="hover:bg-gray-50 transition-colors">';
        echo '  <td class="px-6 py-4 whitespace-nowrap">';
        echo '      <div class="flex items-center">';
        echo '          <div class="flex-shrink-0 h-12 w-16">';
        echo '              <img class="h-12 w-16 rounded-md object-cover" src="' . getImageUrl($row['outdoor_img']) . '" alt="Property image">';
        echo '          </div>';
        echo '          <div class="ml-4">';
        echo '              <div class="text-sm font-medium text-gray-900">' . htmlspecialchars($row['pro_nm']) . '</div>';
        echo '              <div class="text-sm text-gray-500">' . htmlspecialchars($row['district']) . '</div>';
        echo '          </div>';
        echo '      </div>';
        echo '  </td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-semibold text-gray-800">₹' . number_format($row['month_rent']) . '/month</div></td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">' . htmlspecialchars($row['pro_type']) . '</td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-center">';
        echo '      <select data-id="' . $row['pro_id'] . '" class="property-status-select w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">';
        $statuses = ['Available', 'Unavailable', 'Maintenance'];
        foreach ($statuses as $s) {
            $selected = ($row['status'] == $s) ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($s) . '" ' . $selected . '>' . htmlspecialchars($s) . '</option>';
        }
        echo '      </select>';
        echo '  </td>';
        echo '  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">';
        echo '      <a href="getPropertyDetails.php?id=' . $row['pro_id'] . '" class="text-blue-600 hover:text-blue-900">View</a>';
        echo '      <a href="updateProperty.php?id=' . $row['pro_id'] . '" class="text-indigo-600 hover:text-indigo-900">Edit</a>';
        echo '      <button data-id="' . $row['pro_id'] . '" class="delete-property-btn text-red-600 hover:text-red-900">Delete</button>';
        echo '  </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5" class="text-center py-12 px-6"><div class="mx-auto w-fit"><i data-lucide="home" class="w-16 h-16 mx-auto text-gray-400"></i><h3 class="mt-2 text-xl font-semibold text-gray-800">No Properties Found</h3><p class="mt-1 text-gray-500">Try adjusting your search or filter criteria.</p></div></td></tr>';
    echo '<script>lucide.createIcons();</script>';
}

$stmt->close();
$conn->close();
?>