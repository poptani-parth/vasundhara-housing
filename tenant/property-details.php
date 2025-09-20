<?php
session_start();
include '../database.php';

// 1. Security: Check if user is logged in as a tenant
if (!isset($_SESSION['tenant_id']) || !isset($_SESSION['type_tenant']) || $_SESSION['type_tenant'] !== 'tenant') {
    echo '<div class="text-center text-red-500 p-8">Please log in to view this page.</div>';
    exit();
}

$userId = $_SESSION['tenant_id'];
$propertyDetails = null;
$imagePaths = [];

// Helper function to create a valid, web-accessible URL from the stored path
function getImageUrlForProperty($path){
    if (!empty($path)) {
        $filename = basename($path); // get just the file name
        $relativePath = '../admin/uploads/property_images/' . $filename;
        $absolutePath = '../admin/uploads/property_images/' . $filename;

        if (file_exists($absolutePath)) {
            return $relativePath;
        }
    }
    return '../assets/images/not_found.png'; // Placeholder for missing image
}

try {
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // 2. Fetch property details assigned to the logged-in tenant in a single query
    $sql = "SELECT p.* 
            FROM properties p
            JOIN tenants t ON p.pro_id = t.property_id
            WHERE t.tenant_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare SQL statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $propertyDetails = $result->fetch_assoc();
    $stmt->close();

    // 3. Process images if property details were found
    if ($propertyDetails) {
        $imagePaths = array_filter([
            getImageUrlForProperty($propertyDetails['outdoor_img']),
            getImageUrlForProperty($propertyDetails['hall_img']),
            getImageUrlForProperty($propertyDetails['bedroom_img']),
            getImageUrlForProperty($propertyDetails['kitchen_img'])
        ]);
    }

    // NEW: Check current month's payment status
    $paymentStatus = 'Due';
    $currentPeriod = date('F Y');
    $showPayButton = true;

    if ($propertyDetails) { // Only check if a property is assigned
        $sql_payment = "SELECT payment_id FROM payments WHERE tenant_id = ? AND payment_period = ? AND status = 'Paid' LIMIT 1";
        $stmt_payment = $conn->prepare($sql_payment);
        $stmt_payment->bind_param("is", $userId, $currentPeriod);
        $stmt_payment->execute();
        $paymentResult = $stmt_payment->get_result();

        if ($paymentResult->fetch_assoc()) {
            $paymentStatus = 'Paid';
            $showPayButton = false;
        }
        $stmt_payment->close();
    }

    // 4. Placeholder for amenities (as it's not in the DB schema provided)
    $amenities = [
        ['icon' => 'ri-wifi-line', 'name' => 'Wi-Fi'],
        ['icon' => 'ri-tv-2-line', 'name' => 'TV'],
        ['icon' => 'ri-restaurant-line', 'name' => 'Kitchen'],
        ['icon' => 'ri-parking-box-line', 'name' => 'Free Parking'],
        ['icon' => 'ri-windy-line', 'name' => 'Air Conditioning'],
        ['icon' => 'ri-water-flash-line', 'name' => 'Pool'],
    ];

    $conn->close();

} catch (Exception $e) {
    // Display a user-friendly error message
    echo '<div class="text-center text-red-500 p-8">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit();
}
?>

<div class="space-y-8 animate-slide-up-fade-in">
    <?php if ($propertyDetails): ?>
        <?php 
            $allImages = !empty($imagePaths) ? $imagePaths : ['../assets/images/notFoundImage.png'];
            $mainImage = reset($allImages);
        ?>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Header -->
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h1 class="text-4xl font-bold text-gray-800"><?php echo htmlspecialchars($propertyDetails['pro_nm']); ?></h1>
                    <p class="text-md text-gray-500 mt-2 flex items-center">
                        <i class="ri-map-pin-line mr-2 text-lg"></i>
                        <?php echo htmlspecialchars($propertyDetails['houseno'] . ', ' . $propertyDetails['street'] . ', ' . $propertyDetails['taluka'] . ', ' . $propertyDetails['district']); ?>
                    </p>
                </div>

                <!-- Image Gallery -->
                <div class="space-y-4">
                    <div class="main-image-container rounded-2xl shadow-lg overflow-hidden border-4 border-white">
                        <img id="main-property-image" src="<?php echo htmlspecialchars($mainImage); ?>" alt="Main property view" class="w-full h-96 object-cover transition-opacity duration-300">
                    </div>
                    <?php if (count($allImages) > 1): ?>
                    <div class="thumbnail-gallery grid grid-cols-4 gap-4">
                        <?php foreach ($allImages as $path): ?>
                            <img src="<?php echo htmlspecialchars($path); ?>" alt="Property thumbnail" class="thumbnail-image w-full h-24 object-cover rounded-xl cursor-pointer border-4 border-transparent hover:border-blue-500 transition-all duration-300">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Description Card -->
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-2xl font-semibold text-gray-800 mb-3">About this property</h3>
                    <div class="prose max-w-none text-gray-600 leading-relaxed"><?php echo nl2br(htmlspecialchars($propertyDetails['pro_dis'])); ?></div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="lg:col-span-1">
                <div class="sticky top-6 space-y-8">
                    <!-- Actions Card -->
                    <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 space-y-4">
                        <div class="flex justify-between items-baseline">
                            <p class="text-3xl font-bold text-gray-800">₹<?php echo number_format($propertyDetails['month_rent']); ?></p>
                            <p class="text-lg font-normal text-gray-500">/ month</p>
                        </div>
                        <hr>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Payment for <?php echo $currentPeriod; ?></h4>
                            <?php if (!$showPayButton): ?>
                                <div class="bg-green-100 text-green-800 text-center font-semibold p-3 rounded-lg flex items-center justify-center gap-2">
                                    <i class="ri-checkbox-circle-fill"></i>
                                    <span>Rent Paid</span>
                                </div>
                            <?php else: ?>
                                <button data-page="pay-rent" class="sidebar-item-nav w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:bg-blue-700 transition duration-300 flex items-center justify-center gap-2">
                                    <i class="ri-bank-card-2-line"></i> Pay Now
                                </button>
                            <?php endif; ?>
                        </div>
                        <button data-page="maintenance" class="sidebar-item-nav w-full bg-gray-800 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:bg-gray-900 transition duration-300 flex items-center justify-center gap-2">
                            <i class="ri-tools-line"></i> Report an Issue
                        </button>
                    </div>

                    <!-- Overview Card -->
                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Overview</h3>
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div class="bg-gray-50 p-3 rounded-lg"><i class="ri-home-4-line text-2xl text-blue-600 mx-auto"></i><p class="mt-1 font-semibold text-sm"><?php echo htmlspecialchars($propertyDetails['pro_type']); ?></p></div>
                            <div class="bg-gray-50 p-3 rounded-lg"><i class="ri-ruler-2-line text-2xl text-blue-600 mx-auto"></i><p class="mt-1 font-semibold text-sm"><?php echo htmlspecialchars($propertyDetails['area_sq']); ?> sqft</p></div>
                            <div class="bg-gray-50 p-3 rounded-lg"><i class="ri-hotel-bed-line text-2xl text-blue-600 mx-auto"></i><p class="mt-1 font-semibold text-sm"><?php echo htmlspecialchars($propertyDetails['bed']); ?> Beds</p></div>
                            <div class="bg-gray-50 p-3 rounded-lg"><i class="ri-showers-line text-2xl text-blue-600 mx-auto"></i><p class="mt-1 font-semibold text-sm"><?php echo htmlspecialchars($propertyDetails['bath']); ?> Baths</p></div>
                        </div>
                    </div>

                    <!-- Amenities Card -->
                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Amenities</h3>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                            <?php foreach ($amenities as $amenity): ?>
                                <div class="flex items-center gap-3">
                                    <i class="<?php echo $amenity['icon']; ?> text-blue-600 text-xl"></i>
                                    <span class="text-gray-700 text-sm"><?php echo htmlspecialchars($amenity['name']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="text-center text-gray-500 p-8 bg-white rounded-2xl shadow-lg border-2 border-dashed">
            <i class="ri-home-smile-2-line text-6xl text-gray-400"></i>
            <p class="mt-4 text-xl font-semibold">No Property Assigned</p>
            <p class="text-sm">You are not currently associated with a property. Please contact support.</p>
        </div>
    <?php endif; ?>
</div>