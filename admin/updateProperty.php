<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include './database.php'; // Include your database connection file


// Sanitize function (re-defined for self-containment, or could be in a common utility file)
function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
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
    return '../assets/images/not_found.png';
}

// Upload function (re-defined for self-containment)
function uploadImage($file, $uploadDir)
{
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            return ['error' => 'Invalid file type.'];
        }
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            return ['error' => 'File size exceeds 5MB.'];
        }

        $newName = uniqid('prop_') . '.' . $ext;
        $path = $uploadDir . $newName;

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        if (move_uploaded_file($file['tmp_name'], $path)) {
            return ['success' => $path];
        } else {
            return ['error' => 'Upload failed.'];
        }
    }
    return ['no_file' => true]; // Indicates no file was uploaded for this field
}

$message = '';
$messageType = '';
$property = null; // Initialize property variable

// Get property ID from URL for fetching existing data
$propertyId = filter_var($_GET['id'] ?? null, FILTER_SANITIZE_NUMBER_INT);

// Handle form submission for updating property
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['PropertyUpdate'])) {
    $propertyId = filter_var($_POST['propertyId'], FILTER_SANITIZE_NUMBER_INT); // Get ID from hidden field

    if (!$conn) {
        $message = "Database connection failed.";
        $messageType = "red";
    } else {
        // Collect and sanitize form data
        $propertyName = sanitize_input($_POST['propertyName']);
        $propertyType = sanitize_input($_POST['propertyType']);
        $monthlyRent = (int) $_POST['monthlyRent'];
        $propertyDescription = sanitize_input($_POST['propertyDescription']);
        $propertyStatus = sanitize_input($_POST['propertyStatus']);

        $bed = (int) $_POST['bed'];
        $bath = (int) $_POST['bath'];
        $area_sq = (int) $_POST['area_sq'];

        $houseNo = sanitize_input($_POST['addressHouseNo']);
        $street = sanitize_input($_POST['addressStreet']);
        $taluka = sanitize_input($_POST['addressTaluka']);
        $district = sanitize_input($_POST['addressDistrict']);
        $state = sanitize_input($_POST['addressState']);
        $pincode = sanitize_input($_POST['addressPincode']);

        // Validation
        $errors = [];
        if (empty($propertyName)) $errors[] = "Property name is required.";
        if (!filter_var($monthlyRent, FILTER_VALIDATE_INT)) $errors[] = "Invalid rent.";
        if (!preg_match('/^[0-9]{6}$/', $pincode)) $errors[] = "Pincode must be 6 digits.";

        // Check for duplicate name, excluding the current property being updated
        $check = $conn->prepare("SELECT COUNT(*) FROM properties WHERE pro_nm = ? AND pro_id != ?");
        $check->bind_param("si", $propertyName, $propertyId);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();
        if ($count > 0) $errors[] = "Property name already exists.";

        if ($errors) {
            $message = implode(" ", $errors);
            $messageType = "red";
        } else {
            // Fetch current image paths to compare and potentially delete old ones
            $currentImages = [];
            $stmt_current_img = $conn->prepare("SELECT outdoor_img, hall_img, bedroom_img, kitchen_img FROM properties WHERE pro_id = ?");
            $stmt_current_img->bind_param("i", $propertyId);
            $stmt_current_img->execute();
            $res_current_img = $stmt_current_img->get_result();
            if ($res_current_img->num_rows > 0) {
                $currentImages = $res_current_img->fetch_assoc();
            }
            $stmt_current_img->close();

            $uploadDir = 'uploads/property_images/';
            $newImagePaths = []; // To store new image paths or retain old ones

            // Process each image upload
            foreach (['outdoorImage', 'hallImage', 'bedroomImage', 'kitchenImage'] as $imgField) {
                if (isset($_FILES[$imgField]) && $_FILES[$imgField]['error'] === UPLOAD_ERR_OK) {
                    $result = uploadImage($_FILES[$imgField], $uploadDir);
                    if (isset($result['error'])) {
                        $message = $result['error'];
                        $messageType = "red";
                        break; // Stop processing if an upload error occurs
                    }
                    // If new image uploaded successfully, delete old one if it exists
                    if (isset($result['success']) && !empty($currentImages[str_replace('Image', '_img', $imgField)])) {
                        if (file_exists($currentImages[str_replace('Image', '_img', $imgField)])) {
                            unlink($currentImages[str_replace('Image', '_img', $imgField)]);
                        }
                    }
                    $newImagePaths[str_replace('Image', '_img', $imgField)] = $result['success'] ?? null;
                } else {
                    // If no new file uploaded for this field, retain the old path
                    $newImagePaths[str_replace('Image', '_img', $imgField)] = $currentImages[str_replace('Image', '_img', $imgField)] ?? null;
                }
            }

            if (!$message) { // Proceed only if no upload errors
                $stmt = $conn->prepare("UPDATE properties SET
                    pro_nm = ?, pro_type = ?, month_rent = ?, pro_dis = ?, status = ?, bed = ?, bath = ?, area_sq = ?,
                    houseno = ?, street = ?, taluka = ?, district = ?, state = ?, pincode = ?,
                    outdoor_img = ?, hall_img = ?, bedroom_img = ?, kitchen_img = ?
                    WHERE pro_id = ?");

                $stmt->bind_param(
                    "ssissiiisssssissssi",
                    $propertyName,
                    $propertyType,
                    $monthlyRent,
                    $propertyDescription,
                    $propertyStatus,
                    $bed,
                    $bath,
                    $area_sq,
                    $houseNo,
                    $street,
                    $taluka,
                    $district,
                    $state,
                    $pincode,
                    $newImagePaths['outdoor_img'],
                    $newImagePaths['hall_img'],
                    $newImagePaths['bedroom_img'],
                    $newImagePaths['kitchen_img'],
                    $propertyId
                );

                if ($stmt->execute()) {
                    $message = "Property updated successfully!";
                    $messageType = "green";
                } else {
                    $message = "Database update failed: " . $stmt->error;
                    $messageType = "red";
                }
                $stmt->close();
            }
        }
    }
}

// Fetch property data for the form (always fetch, even after POST, to show updated data or errors)
if ($propertyId) {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE pro_id = ?");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $property = $result->fetch_assoc();
    } else {
        $message = "Property not found for update.";
        $messageType = "red";
        $propertyId = null; // Prevent form from rendering
    }
    $stmt->close();
} else {
    $message = "No property ID provided for update.";
    $messageType = "red";
}

$conn->close(); // Close database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Property - Vasundhara Housing</title>
     <script src="../assets/js/tailwind.js"></script>
    <link href="../assets/css/tailwind.css" rel="stylesheet"/>
    <link href="../assets/css/all.min.css" rel="stylesheet"/>
    <link href="../assets/css/google_fonts.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/fonts/interFont.css">
    <link rel="stylesheet" href="./style.css">
    <script src="../assets/all.min.3.4.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
        }

        .file-input-button {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: #4f46e5; /* indigo-600 */
            color: white;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .file-input-button:hover {
            background-color: #4338ca; /* indigo-700 */
            transform: translateY(-2px);
        }

        .file-input-button i {
            margin-right: 0.5rem; /* ri-upload-2-line */
        }

        .custom-file-input {
            display: none;
        }

        #map-container {
            min-height: 300px;
            background-color: #e2e8f0;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748B;
            font-style: italic;
            text-align: center;
            padding: 1rem;
        }
    </style>
</head>

<body class="bg-slate-100">
    <main class="container mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h1 class="text-3xl font-extrabold text-gray-900">Update Property</h1>
            <a href="./index.php" class="text-gray-600 hover:text-indigo-600 flex items-center transition-colors duration-200">
                <i class="ri-arrow-left-line mr-2"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($property): ?>
            <form id="update-property-form" method="post" enctype="multipart/form-data" class="space-y-8 bg-white p-8 rounded-2xl shadow-lg">
                <input type="hidden" name="propertyId" value="<?php echo htmlspecialchars($property['pro_id']); ?>">

                <!-- Section: Basic Info & Amenities -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6">Basic Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="propertyName" class="block text-sm font-medium text-gray-700 mb-1">Property Name</label>
                            <input type="text" id="propertyName" name="propertyName" required
                                value="<?php echo htmlspecialchars($property['pro_nm']); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="propertyType" class="block text-sm font-medium text-gray-700 mb-1">Type (e.g., 2 BHK, Villa)</label>
                            <input type="text" id="propertyType" name="propertyType" required
                                value="<?php echo htmlspecialchars($property['pro_type']); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="monthlyRent" class="block text-sm font-medium text-gray-700 mb-1">Monthly Rent (₹)</label>
                            <input type="number" id="monthlyRent" name="monthlyRent" required min="0" step="100"
                                value="<?php echo htmlspecialchars($property['month_rent']); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="propertyStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="propertyStatus" name="propertyStatus"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="Available" <?php echo ($property['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                                <option value="Booked" <?php echo ($property['status'] == 'Booked') ? 'selected' : ''; ?>>Booked</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label for="bed" class="block text-sm font-medium text-gray-700 mb-1">Bedrooms</label>
                            <input type="number" id="bed" name="bed" required min="0" value="<?php echo htmlspecialchars($property['bed']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="bath" class="block text-sm font-medium text-gray-700 mb-1">Bathrooms</label>
                            <input type="number" id="bath" name="bath" required min="0" value="<?php echo htmlspecialchars($property['bath']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="area_sq" class="block text-sm font-medium text-gray-700 mb-1">Area (sqft)</label>
                            <input type="number" id="area_sq" name="area_sq" required min="0" value="<?php echo htmlspecialchars($property['area_sq']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="mt-6">
                        <label for="propertyDescription" class="block text-sm font-medium text-gray-700 mb-1">Property Description</label>
                        <textarea id="propertyDescription" name="propertyDescription" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Enter a detailed description..."><?php echo htmlspecialchars($property['pro_dis']); ?></textarea>
                    </div>
                </section>

                <!-- Section: Address -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6">Property Address</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="addressHouseNo" class="block text-sm font-medium text-gray-700 mb-1">House No./Name</label>
                            <input type="text" id="addressHouseNo" name="addressHouseNo" value="<?php echo htmlspecialchars($property['houseno']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 123, Green Villa">
                        </div>
                        <div>
                            <label for="addressStreet" class="block text-sm font-medium text-gray-700 mb-1">Street/Area</label>
                            <input type="text" id="addressStreet" name="addressStreet" value="<?php echo htmlspecialchars($property['street']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., MG Road">
                        </div>
                        <div>
                            <label for="addressTaluka" class="block text-sm font-medium text-gray-700 mb-1">Taluka</label>
                            <input type="text" id="addressTaluka" name="addressTaluka" required value="<?php echo htmlspecialchars($property['taluka']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., Rajkot">
                        </div>
                        <div>
                            <label for="addressDistrict" class="block text-sm font-medium text-gray-700 mb-1">District</label>
                            <input type="text" id="addressDistrict" name="addressDistrict" required value="<?php echo htmlspecialchars($property['district']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., Rajkot">
                        </div>
                        <div>
                            <label for="addressState" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                            <input type="text" id="addressState" name="addressState" required value="<?php echo htmlspecialchars($property['state']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., Gujarat">
                        </div>
                        <div>
                            <label for="addressPincode" class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                            <input type="text" id="addressPincode" name="addressPincode" required pattern="[0-9]{6}" value="<?php echo htmlspecialchars($property['pincode']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 360001" maxlength="6">
                        </div>
                    </div>
                </section>

                <!-- Section: Images -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6">Property Images</h2>
                    <p class="text-sm text-gray-500 mb-4">Upload new images only for the ones you want to replace. Existing images will be kept otherwise.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php
                        $imageFields = [
                            'outdoorImage' => ['label' => 'Outdoor', 'db_field' => 'outdoor_img'],
                            'hallImage' => ['label' => 'Hall', 'db_field' => 'hall_img'],
                            'bedroomImage' => ['label' => 'Bedroom', 'db_field' => 'bedroom_img'],
                            'kitchenImage' => ['label' => 'Kitchen', 'db_field' => 'kitchen_img'],
                        ];
                        foreach ($imageFields as $fieldId => $fieldData):
                            $currentImage = $property[$fieldData['db_field']] ?? '';
                        ?>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700"><?php echo $fieldData['label']; ?> Image</label>
                            <div class="w-full h-40 bg-slate-100 rounded-lg flex items-center justify-center border-2 border-dashed border-gray-300">
                                <img id="<?php echo $fieldId; ?>Preview" src="<?php echo getImageUrl($currentImage); ?>" alt="<?php echo $fieldData['label']; ?> Preview" class="h-full w-full object-cover rounded-md">
                            </div>
                            <label for="<?php echo $fieldId; ?>" class="file-input-button w-full justify-center">
                                <i class="ri-upload-2-line"></i> <span>Change Image</span>
                            </label>
                            <input type="file" id="<?php echo $fieldId; ?>" name="<?php echo $fieldId; ?>" accept="image/*" class="custom-file-input" data-preview-target="<?php echo $fieldId; ?>Preview">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Section: Map -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6">Location on Map</h2>
                    <div id="map-container" class="w-full h-96 rounded-xl overflow-hidden shadow-md border bg-slate-200 flex items-center justify-center text-slate-500">
                        <!-- Map iframe will be loaded here by JavaScript -->
                    </div>
                </section>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t mt-6">
                    <button type="button" onclick="window.location.href='index.php'"
                        class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                        Cancel
                    </button>
                    <button type="submit" name="PropertyUpdate"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg shadow-md hover:bg-indigo-700 transition-colors font-semibold">
                        Update Property
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="text-center py-10 bg-white p-8 rounded-2xl shadow-lg">
                <i class="ri-error-warning-line text-6xl text-red-400"></i>
                <p class="text-xl text-red-600 font-semibold"><?php echo htmlspecialchars($message); ?></p>
                <p class="text-gray-600 mt-2">Please return to the dashboard and select a valid property.</p>
                <a href="index.php" class="mt-6 inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="ri-arrow-left-line mr-2"></i> Back to Dashboard
                </a>
            </div>
        <?php endif; ?>
    <div id="toast-notification" class="fixed bottom-5 right-5 bg-green-500 text-white py-3 px-6 rounded-lg shadow-lg opacity-0 transition-all duration-300 ease-out z-50 transform translate-y-5">
        <!-- Toast message will be injected here -->
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toastNotification = document.getElementById('toast-notification');

            function showToast(message, type = 'green', duration = 4000) {
                if (!toastNotification || !message) return;
                toastNotification.textContent = message;
                toastNotification.className = `fixed bottom-5 right-5 text-white py-3 px-6 rounded-lg shadow-lg opacity-0 transition-all duration-300 ease-out z-50 transform translate-y-5`;

                if (type === 'green') toastNotification.classList.add('bg-green-600');
                else if (type === 'red') toastNotification.classList.add('bg-red-600');
                else toastNotification.classList.add('bg-blue-600');

                toastNotification.classList.remove('opacity-0', 'translate-y-5');
                toastNotification.classList.add('opacity-100', 'translate-y-0');

                setTimeout(() => {
                    toastNotification.classList.remove('opacity-100', 'translate-y-0');
                    toastNotification.classList.add('opacity-0', 'translate-y-5');
                    if (type === 'green') {
                        // Redirect after toast fades out
                        setTimeout(() => window.location.href = 'index.php?page=properties.php', 500);
                    }
                }, duration);
            }

            <?php if (!empty($message)): ?>
                showToast(<?php echo json_encode($message); ?>, <?php echo json_encode($messageType); ?>);
            <?php endif; ?>

            // File input preview handler
            document.querySelectorAll('.custom-file-input').forEach(input => {
                input.addEventListener('change', (event) => {
                    const file = event.target.files[0];
                    const previewTargetId = event.target.dataset.previewTarget;
                    const previewElement = document.getElementById(previewTargetId);

                    if (file && previewElement) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewElement.src = e.target.result;
                        }
                        reader.readAsDataURL(file);
                    }
                });
            });

            // --- Map Update Logic ---
            function updateMap() {
                const houseNo = document.getElementById("addressHouseNo").value.trim();
                const street = document.getElementById("addressStreet").value.trim();
                const taluka = document.getElementById("addressTaluka").value.trim();
                const district = document.getElementById("addressDistrict").value.trim();
                const state = document.getElementById("addressState").value.trim();
                const pincode = document.getElementById("addressPincode").value.trim();
                const propertyName = document.getElementById("propertyName").value.trim();

                const mapContainer = document.getElementById("map-container");

                const addressParts = [houseNo, street, taluka, district, state, pincode].filter(Boolean);
                const fullAddress = addressParts.join(', ');

                if (fullAddress && mapContainer) {
                    const mapUrl = `https://maps.google.com/maps?width=100%25&height=600&hl=en&q=${encodeURIComponent(fullAddress)}+(${encodeURIComponent(propertyName)})&t=&z=15&ie=UTF8&iwloc=B&output=embed`;
                    mapContainer.innerHTML = `<iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="${mapUrl}"></iframe>`;
                } else if (mapContainer) {
                    mapContainer.innerHTML = '<p>Enter address details to see the location on the map.</p>';
                }
            }

            // Attach event listeners to all address fields
            const addressFieldIds = ['addressHouseNo', 'addressStreet', 'addressTaluka', 'addressDistrict', 'addressState', 'addressPincode', 'propertyName'];
            addressFieldIds.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.addEventListener('change', updateMap);
                }
            });

            // Initial map load based on pre-filled values
            updateMap();
        });
    </script>
</body>

</html>