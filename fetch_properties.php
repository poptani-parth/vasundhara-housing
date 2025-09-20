<?php
include 'database.php';

function getImageUrl($path)
{
    if (!empty($path)) {
        $filename = basename($path); // get just the file name
        $relativePath = './admin/uploads/property_images/' . $filename;
        $absolutePath = './admin/uploads/property_images/' . $filename;

        if (file_exists($absolutePath)) {
            return $relativePath;
        }
    }
    return './assets/images/notFoundImage.png'; // Placeholder for missing image
}
$status = $_POST['status'] ?? 'All';
$propertyType = isset($_POST['propertyType']) ? trim($_POST['propertyType']) : '';
$propertyLocation = isset($_POST['propertyLocation']) ? trim($_POST['propertyLocation']) : '';
$minPrice = isset($_POST['minPrice']) ? trim($_POST['minPrice']) : '';
$maxPrice = isset($_POST['maxPrice']) ? trim($_POST['maxPrice']) : '';
// Base query
$query = "SELECT * FROM properties WHERE 1";

// Filter by property type
if (!empty($propertyType)) {
    $query .= " AND pro_type LIKE '%$propertyType%'";
}
// Filter by location (city, taluka, district, state)
if (!empty($propertyLocation)) {
    $propertyLocationEscaped = mysqli_real_escape_string($conn, $propertyLocation);
    $query .= " AND (
        taluka LIKE '%$propertyLocationEscaped%' 
        OR district LIKE '%$propertyLocationEscaped%' 
        OR state LIKE '%$propertyLocationEscaped%'
    )";
}

// Filter by price range
if (!empty($minPrice)) {
    $query .= " AND month_rent >= " . intval($minPrice);
}
if (!empty($maxPrice)) {
    $query .= " AND month_rent <= " . intval($maxPrice);
}

//$query .= " ORDER BY id DESC";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($property = mysqli_fetch_assoc($result)) {
        $imagePath = getImageUrl($property['outdoor_img']);
        $name = htmlspecialchars($property['pro_nm']);
        $taluka = htmlspecialchars($property['taluka']);
        $district = htmlspecialchars($property['district']);
        $bed = htmlspecialchars($property['bed']);
        $bath = htmlspecialchars($property['bath']);
        $area = htmlspecialchars($property['area_sq']);
        $rent = htmlspecialchars($property['month_rent']);
        $id = htmlspecialchars($property['pro_id']);
        $statusClass = $property['status'] === 'Available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';

        echo "
            <div
                class='bg-white rounded-lg shadow-md property-card border border-gray-100 delay-200'>
                <div class='w-full h-48 overflow-hidden rounded-t-lg relative'>
                    <img src='{$imagePath}'
                        alt='{$name}'
                        class='w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500 ease-in-out'>
                    <p class='text-lg'>
                        <span class='px-3 py-1 rounded-full z-100 absolute top-1 right-1 text-sm font-semibold {$statusClass}'>
                            {$property['status']}
                        </span>
                    </p>
                </div>
                <div class='p-4'>
                    <h3 class='text-lg font-bold text-gray-900 mb-1'>{$name}</h3>
                    <div class='flex items-center gap-1 mb-2 text-gray-600'>
                        <i class='ri-map-pin-2-fill text-blue-500 text-base'></i>
                        <span class='text-sm font-medium'>{$name} {$taluka}, {$district}</span>
                    </div>
                    <div class='grid grid-cols-3 gap-1 text-xs text-gray-500 mb-3'>
                        <span class='flex items-center gap-1'><i
                                class='ri-hotel-bed-fill text-blue-400 text-base'></i> {$bed} Bed</span>
                        <span class='flex items-center gap-1'><i
                                class='ri-showers-fill text-blue-400 text-base'></i> {$bath} Bath</span>
                        <span class='flex items-center gap-1'><i
                                class='ri-line-chart-line text-blue-400 text-base'></i> {$area} sq.ft.</span>
                    </div>
                    <div class='flex items-center justify-between pt-3 py-5 border-t border-gray-200'>
                        <h1 class='text-3xl font-bold text-blue-800'>₹ {$rent}<span
                                class='text-xl text-gray-800 font-normal'>/month</span></h1>
                        <a href='./enquirePage.php?id={$id}'>
                            <button
                                class='bg-blue-400 text-white font-bold px-4 py-3 mt-2 rounded-full shadow-md hover:bg-blue-700 transition duration-300 ease-in-out text-[0.8rem] cursor-pointer transform hover:scale-105'>
                                View Details
                            </button>
                        </a>
                    </div>
                </div>
            </div>
        ";
    }
} else {
    echo "<p class='text-center font-bold text-2xl absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-red-500'>No properties found.</p>";
}
