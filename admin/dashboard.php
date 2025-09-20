<?php
include './database.php'; 
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
$totalProperties = 0;
$availableProperties = 0;
$bookedProperties = 0;
$totalTenants = 0;
$recentProperties = []; // To store recently added properties

// Fetch data from the database
if ($conn) {
    // Total Properties
    $result = $conn->query("SELECT COUNT(*) AS total FROM properties");
    if ($result) {
        $totalProperties = $result->fetch_assoc()['total'];
    }

    // Available Properties
    $result = $conn->query("SELECT COUNT(*) AS available FROM properties WHERE status = 'Available'");
    if ($result) {
        $availableProperties = $result->fetch_assoc()['available'];
    }

    // Booked Properties
    $result = $conn->query("SELECT COUNT(*) AS Unavailable FROM properties WHERE status = 'Unavailable'");
    if ($result) {
        $bookedProperties = $result->fetch_assoc()['Unavailable'];
    }

    // Total Tenants (Assuming a 'tenants' table exists)
   // You might need to adjust this query based on your actual 'tenants' table structure
    $result = $conn->query("SELECT COUNT(*) AS total_tenants FROM tenants");
    if ($result) {
        $totalTenants = $result->fetch_assoc()['total_tenants'];
    }

    // Recently Added Properties (e.g., last 5)
    $sqlRecent = "SELECT pro_id, pro_nm, pro_type, month_rent, status, outdoor_img
                  FROM properties
                  ORDER BY pro_id DESC
                  LIMIT 5";
    $resultRecent = $conn->query($sqlRecent);
    if ($resultRecent && $resultRecent->num_rows > 0) {
        while ($row = $resultRecent->fetch_assoc()) {
            $recentProperties[] = $row;
        }
    }
}

// Function to get image URL (re-using from displayProperties.php)
function getImageUrl($path)
{
    if (!empty($path)) {
        $filename = basename($path);
        $relativePath = './uploads/property_images/' . $filename; // Adjust path if needed
        $absolutePath = __DIR__ . '/uploads/property_images/' . $filename; // Use __DIR__ for absolute path

        if (file_exists($absolutePath)) {
            return $relativePath;
        }
    }
    return '../assets/images/not_found.png'; // Placeholder for missing image
}

?>

<div class="px-6 py-6 bg-gray-100 min-h-screen">
    <h2 class="text-3xl font-bold text-gray-800 mb-8 animate-fadeIn">Dashboard Overview</h2>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between animate-slideInUp">
            <div>
                <h3 class="text-lg font-semibold text-gray-600">Total Properties</h3>
                <p class="text-4xl font-bold text-blue-600 mt-2"><?php echo $totalProperties; ?></p>
            </div>
            <i class="fas fa-home text-5xl text-blue-300 opacity-70"></i>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between animate-slideInUp ">
            <div>
                <h3 class="text-lg font-semibold text-gray-600">Available Properties</h3>
                <p class="text-4xl font-bold text-green-600 mt-2"><?php echo $availableProperties; ?></p>
            </div>
            <i class="fas fa-check-circle text-5xl text-green-300 opacity-70"></i>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between animate-slideInUp ">
            <div>
                <h3 class="text-lg font-semibold text-gray-600">Booked Properties</h3>
                <p class="text-4xl font-bold text-red-600 mt-2"><?php echo $bookedProperties; ?></p>
            </div>
            <i class="fas fa-times-circle text-5xl text-red-300 opacity-70"></i>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between animate-slideInUp ">
            <div>
                <h3 class="text-lg font-semibold text-gray-600">Total Tenants</h3>
                <p class="text-4xl font-bold text-purple-600 mt-2"><?php echo $totalTenants; ?></p>
            </div>
            <i class="fas fa-users text-5xl text-purple-300 opacity-70"></i>
        </div>
    </div>
    <!-- Expiring Agreements Section -->
<div class="my-8 bg-white p-6 rounded-lg shadow-md animate-slideInUp">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 border-b pb-3 gap-4">
        <h3 class="text-2xl font-bold text-gray-800">Expiring Agreements</h3>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-sm text-gray-600 font-medium">Show within:</span>
            <button data-days="5" class="expiring-filter-btn active">5 Days</button>
            <button data-days="10" class="expiring-filter-btn">10 Days</button>
            <button data-days="15" class="expiring-filter-btn">15 Days</button>
            <button data-days="30" class="expiring-filter-btn">30 Days</button>
        </div>
    </div>
    <div id="expiring-agreements-list" class="space-y-3">
        <!-- Content will be loaded via AJAX -->
        <p class="text-gray-500 text-center py-4">Loading expiring agreements...</p>
    </div>
</div>


    <!-- Recently Added Properties Section -->
    <div class="bg-white p-6 rounded-lg shadow-md animate-slideInUp ">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Recently Added Properties</h3>
        <?php if (!empty($recentProperties)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($recentProperties as $property):
                    $id = (int)$property['pro_id'];
                    $name = htmlspecialchars($property['pro_nm']);
                    $type = htmlspecialchars($property['pro_type']);
                    $statusText = htmlspecialchars($property['status']);
                    $rent = number_format($property['month_rent']);
                    $imagePath = getImageUrl($property['outdoor_img']);
                    $statusClass = $statusText === 'Available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                ?>
                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col items-center text-center">
                        <img src="<?php echo $imagePath; ?>" alt="<?php echo $name; ?>" class="w-full h-40 object-cover rounded-lg mb-3">
                        <h4 class="text-lg font-semibold text-gray-800"><?php echo $name; ?></h4>
                        <p class="text-gray-600 text-sm"><?php echo $type; ?> - ₹ <?php echo $rent; ?>/month</p>
                        <p class="text-sm mt-1">Status:
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                        </p>
                        <a href="./getPropertyDetails.php?id=<?php echo $id; ?>" class="mt-4 px-4 py-2 text-center border border-blue-600 rounded-lg hover:bg-blue-600 transition duration-300 hover:text-white text-blue-600 font-medium w-full">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-600 text-center py-4">No recently added properties to display.</p>
        <?php endif; ?>
    </div>

    <!-- Placeholder for other dynamic content (e.g., charts, notifications) -->
    <div class="mt-8 bg-white p-6 rounded-lg shadow-md animate-slideInUp">
        <h3 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-3">Quick Actions / Notifications</h3>
        <p class="text-gray-600">This section can be expanded with quick links, alerts, or charts.</p>
        <div class="mt-4 flex flex-wrap gap-4">
            <a href="add_properties_form.php" class="px-5 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition-colors flex items-center">
                <i class="fas fa-plus-circle mr-2"></i> Add New Property
            </a>
            <a href="properties.php" class="px-5 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700 transition-colors flex items-center">
                <i class="fas fa-list-alt mr-2"></i> Manage Properties
            </a>
            <!-- Add more quick actions as needed -->
        </div>
    </div>
</div>

<?php
// Close the database connection
if ($conn) {
    $conn->close();
}
?>
