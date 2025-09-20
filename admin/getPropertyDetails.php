<?php

include './database.php';
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$propertyId = filter_var($_GET['id'] ?? null, FILTER_SANITIZE_NUMBER_INT);

$property = null; // Initialize property variable
$tenant = null;   // Initialize tenant variable

if ($propertyId && is_numeric($propertyId)) {
    // Prepare and execute the SQL query to fetch property details
    $stmt = $conn->prepare("SELECT * FROM properties WHERE pro_id = ?"); // Fetch all columns
    $stmt->bind_param("i", $propertyId); // 'i' for integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $property = $result->fetch_assoc();

        // If property is booked, fetch tenant details
        if ($property['status'] === 'Booked') {
            $tenantStmt = $conn->prepare("SELECT tenant_name, contact_number, email FROM tenants WHERE property_id = ?");
            $tenantStmt->bind_param("i", $propertyId);
            $tenantStmt->execute();
            $tenantResult = $tenantStmt->get_result();
            if ($tenantResult->num_rows > 0) {
                $tenant = $tenantResult->fetch_assoc();
            }
            $tenantStmt->close();
        }
    }
    $stmt->close();
}

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

// Collect all image paths for the slider
$imagePaths = [];
if ($property) {
    if (!empty($property['outdoor_img'])) $imagePaths[] = getImageUrl($property['outdoor_img']);
    if (!empty($property['hall_img'])) $imagePaths[] = getImageUrl($property['hall_img']);
    if (!empty($property['bedroom_img'])) $imagePaths[] = getImageUrl($property['bedroom_img']);
    if (!empty($property['kitchen_img'])) $imagePaths[] = getImageUrl($property['kitchen_img']);
}

// If no images are found, add a default placeholder
if (empty($imagePaths)) {
    $imagePaths[] = '../assets/images/notFoundImage.png'; // Placeholder image
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Details - Vasundhara Housing</title>
     <script src="../assets/js/tailwind.js"></script>
    <link href="../assets/css/tailwind.css" rel="stylesheet"/>
    <link href="../assets/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/RemixIcon-master/fonts/remixicon.css">
    <link href="../assets/css/google_fonts.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/fonts/interFont.css">
    <link rel="stylesheet" href="./style.css">
    <style>

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9; /* slate-100 */
        }
        .thumbnail-image.active {
            border-color: #4f46e5; /* indigo-600 */
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.5);
        }
        /* For Tailwind prose plugin */
        .prose {
            color: #334155; /* slate-700 */
        }
    </style>
</head>

<body class="bg-slate-100">
    <main class="container mx-auto p-4 sm:p-6 lg:p-8">
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-lg">
            <?php if ($property): ?>
                <!-- Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start mb-6 pb-4 border-b border-gray-200">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-800"><?php echo htmlspecialchars($property['pro_nm']); ?></h1>
                        <p class="text-md text-gray-500 mt-1 flex items-center"><i class="ri-map-pin-line mr-2"></i><?php echo htmlspecialchars($property['street']); ?>, <?php echo htmlspecialchars($property['district']); ?></p>
                    </div>
                    <div class="flex items-center gap-3 mt-4 sm:mt-0">
                        <a href="./index.php" class="text-gray-600 hover:text-blue-600 transition-colors flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100">
                            <i class="ri-arrow-left-line"></i> Back
                        </a>
                        <a href="updateProperty.php?id=<?php echo $propertyId; ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold flex items-center gap-2">
                            <i class="ri-pencil-line"></i> Edit
                        </a>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column -->
                    <div class="lg:col-span-2">
                        <!-- Image Gallery -->
                        <div class="mb-8">
                            <div class="main-image-container rounded-xl shadow-md overflow-hidden mb-4 border">
                                <img id="main-property-image" src="<?php echo $imagePaths[0]; ?>" alt="Main property view" class="w-full h-[300px] md:h-[450px] object-cover transition-opacity duration-300">
                            </div>
                            <?php if (count($imagePaths) > 1): ?>
                            <div class="thumbnail-gallery grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-4">
                                <?php foreach ($imagePaths as $path): ?>
                                    <img src="<?php echo $path; ?>" alt="Property thumbnail" class="thumbnail-image w-full h-20 md:h-24 object-cover rounded-lg cursor-pointer border-2 border-transparent hover:border-indigo-500 transition-all" onclick="changeMainImage(this)">
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Description -->
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 border-b pb-2 mb-4">Description</h2>
                            <div class="prose max-w-none text-gray-700 leading-relaxed">
                                <p><?php echo nl2br(htmlspecialchars($property['pro_dis'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Key Details Card -->
                        <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Key Details</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center"><span class="text-gray-600">Monthly Rent</span><span class="font-bold text-lg text-indigo-600">₹<?php echo number_format($property['month_rent']); ?></span></div>
                                <div class="flex justify-between items-center"><span class="text-gray-600">Status</span><span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $property['status'] === 'Available' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>"><?php echo htmlspecialchars($property['status']); ?></span></div>
                                <div class="flex justify-between items-center"><span class="text-gray-600">Property Type</span><span class="font-semibold text-gray-800"><?php echo htmlspecialchars($property['pro_type']); ?></span></div>
                            </div>
                            <hr class="my-4 border-slate-200">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div><i class="ri-hotel-bed-line text-2xl text-indigo-500"></i><p class="text-sm mt-1"><?php echo htmlspecialchars($property['bed']); ?> Beds</p></div>
                                <div><i class="ri-showers-line text-2xl text-indigo-500"></i><p class="text-sm mt-1"><?php echo htmlspecialchars($property['bath']); ?> Baths</p></div>
                                <div><i class="ri-ruler-2-line text-2xl text-indigo-500"></i><p class="text-sm mt-1"><?php echo htmlspecialchars($property['area_sq']); ?> sqft</p></div>
                            </div>
                        </div>

                        <!-- Tenant Info Card (if booked) -->
                        <?php if ($tenant): ?>
                        <div class="bg-blue-50 p-6 rounded-xl border border-blue-200">
                            <h3 class="text-xl font-bold text-blue-800 mb-4">Tenant Information</h3>
                            <div class="space-y-2 text-sm">
                                <p><strong class="font-semibold text-gray-700">Name:</strong> <?php echo htmlspecialchars($tenant['tenant_name']); ?></p>
                                <p><strong class="font-semibold text-gray-700">Contact:</strong> <?php echo htmlspecialchars($tenant['contact_number']); ?></p>
                                <p><strong class="font-semibold text-gray-700">Email:</strong> <?php echo htmlspecialchars($tenant['email']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Address Card -->
                        <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Address</h3>
                            <address class="not-italic text-gray-700 space-y-1 text-sm">
                                <p><?php echo htmlspecialchars($property['houseno']); ?></p>
                                <p><?php echo htmlspecialchars($property['street']); ?></p>
                                <p><?php echo htmlspecialchars($property['taluka']); ?>, <?php echo htmlspecialchars($property['district']); ?></p>
                                <p><?php echo htmlspecialchars($property['district']); ?>, <?php echo htmlspecialchars($property['state']); ?> - <?php echo htmlspecialchars($property['pincode']); ?></p>
                            </address>
                        </div>
                    </div>
                </div>

                <!-- Map Section -->
                <div class="mt-10">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Location on Map</h2>
                    <div class="w-full h-96 rounded-xl overflow-hidden shadow-md border">
                        <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                                src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=<?php echo urlencode($property['houseno'] . ', ' . $property['street'] . ', ' . $property['district'] . ', ' . $property['state'] . ' ' . $property['pincode']); ?>+(<?php echo urlencode($property['pro_nm']); ?>)&amp;t=&amp;z=15&amp;ie=UTF8&amp;iwloc=B&amp;output=embed">
                        </iframe>
                    </div>
                </div>

            <?php else: ?>
                <div class="text-center py-10">
                    <i class="ri-error-warning-line text-6xl text-red-400"></i>
                    <p class="text-xl text-red-600 font-semibold mt-4">Property not found!</p>
                    <p class="text-gray-600 mt-2">The property you are looking for does not exist or the ID is invalid.</p>
                    <a href="index.php" class="mt-6 inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="ri-arrow-left-line mr-2"></i> Back to Properties
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        const mainImage = document.getElementById('main-property-image');
        const thumbnails = document.querySelectorAll('.thumbnail-image');

        function changeMainImage(thumbnailElement) {
            if (!mainImage || !thumbnailElement) return;

            // Fade out current image
            mainImage.style.opacity = 0;
            
            setTimeout(() => {
                // Change src and fade in
                mainImage.src = thumbnailElement.src;
                mainImage.style.opacity = 1;
            }, 200); // Should be less than transition duration

            // Update active thumbnail border
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            thumbnailElement.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Set the first thumbnail as active on load
            if (thumbnails.length > 0) {
                thumbnails[0].classList.add('active');
            }
        });
    </script>
</body>

</html>