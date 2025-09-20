<?php
// ===== START PHP Logic =====
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include './database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
// Sanitize function
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Upload function
function uploadImage($file, $uploadDir) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            return ['error' => 'Invalid file type.'];
        }
        if ($file['size'] > 5 * 1024 * 1024) {
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
    return ['no_file' => true];
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['PropertyAdd'])) {
    if (!$conn) {
        $message = "Database connection failed.";
        $messageType = "red";
    } else {
        // Collect data
        $propertyName = sanitize_input($_POST['propertyName']);
        $propertyType = sanitize_input($_POST['propertyType']);
        $monthlyRent = (int) $_POST['monthlyRent'];
        $propertyDescription = sanitize_input($_POST['propertyDescription']);
        $propertyStatus = sanitize_input($_POST['propertyStatus']);
        $bed = sanitize_input($_POST['beds']);
        $bath = sanitize_input($_POST['baths']);
        $area_sq = sanitize_input($_POST['area_sq']);

        $houseNo = sanitize_input($_POST['addressHouseNo']);
        $street = sanitize_input($_POST['street']);
        $taluka = sanitize_input($_POST['addressTaluka']);
        $district = sanitize_input($_POST['addressDistrict']);
        $state = sanitize_input($_POST['addressState']);
        $pincode = sanitize_input($_POST['addressPincode']);

        // Validation
        $errors = [];
        if (empty($propertyName)) $errors[] = "Property name is required.";
        if (!filter_var($monthlyRent, FILTER_VALIDATE_INT)) $errors[] = "Invalid rent.";
        if (!preg_match('/^[0-9]{6}$/', $pincode)) $errors[] = "Pincode must be 6 digits.";

        // Check duplicate name
        $check = $conn->prepare("SELECT COUNT(*) FROM properties WHERE pro_nm = ?");
        $check->bind_param("s", $propertyName);
        $check->execute();
        $check->bind_result($count); $check->fetch(); $check->close();
        if ($count > 0) $errors[] = "Property name already exists.";

        if ($errors) {
            $message = implode(" ", $errors);
            $messageType = "red";
        } else {
            // Handle uploads
            $uploadDir = 'uploads/property_images/';
            $images = [];
            foreach (['outdoorImage', 'hallImage', 'bedroomImage', 'kitchenImage'] as $img) {
                $result = uploadImage($_FILES[$img], $uploadDir);
                if (isset($result['error'])) {
                    $message = $result['error'];
                    $messageType = "red";
                    break;
                }
                $images[$img] = $result['success'] ?? null;
            }

            if (!$message) {
                $stmt = $conn->prepare("INSERT INTO properties (
                    pro_nm, pro_type, month_rent, pro_dis,bed,bath,area_sq, status,
                    houseno, street, taluka, district, state, pincode,
                    outdoor_img, hall_img, bedroom_img, kitchen_img
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("ssisiiissssssissss",
                    $propertyName, $propertyType, $monthlyRent, $propertyDescription, $bed, $bath, $area_sq, $propertyStatus,
                    $houseNo, $street, $taluka, $district, $state, $pincode,
                    $images['outdoorImage'], $images['hallImage'],
                    $images['bedroomImage'], $images['kitchenImage']
                );

                if ($stmt->execute()) {
                    $message = "Property added successfully!";
                    $messageType = "green";
                } else {
                    $message = "Database insertion failed.";
                    $messageType = "red";
                }
                $stmt->close();
            }
        }
    }
}
?>
