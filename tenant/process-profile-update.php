<?php
session_start();
header('Content-Type: application/json');

include '../database.php';

$response = ['success' => false, 'message' => ''];


// Check for valid request method and session
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['tenant_id']) || $_SESSION['type_tenant'] !== 'tenant') {
    $response['message'] = 'Unauthorized access or invalid request.';
    echo json_encode($response);
    exit();
}

$userId = $_SESSION['tenant_id'];
$tenantName = $_POST['tenant_name'] ?? '';
$email = $_POST['email'] ?? '';
$contactNumber = $_POST['contact_number'] ?? '';

// Basic server-side validation
if (empty($tenantName) || empty($email) || empty($contactNumber)) {
    $response['message'] = 'All fields are required.';
    echo json_encode($response);
    exit();
}

// File upload handling
$photoPath = null;
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == UPLOAD_ERR_OK) {
    $targetDir = "../uploads/profile_photos/";
    
    // Ensure the uploads directory exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileExtension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
    
    // Sanitize the tenant name to make a safe filename
    $safeTenantName = preg_replace('/[^a-zA-Z0-9-]/', '_', $tenantName);
    $fileName = $safeTenantName . '_' . $userId . '.' . $fileExtension;
    $targetFile = $targetDir . $fileName;

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetFile)) {
        // Store path relative to the project root, not this script's location
        $photoPath = 'uploads/profile_photos/' . $fileName;
    } else {
        $response['message'] = 'Sorry, there was an error uploading your file.';
        echo json_encode($response);
        exit();
    }
}

// Update database
try {
    $con = mysqli_connect("localhost", "root", "", "vasundharahousing");
    if (!$con) {
        throw new Exception("Database connection failed.");
    }
    
    // Build the SQL query based on whether a new photo was uploaded
    if ($photoPath) {
        $sql = "UPDATE tenants SET tenant_name = ?, email = ?, contact_number = ?, profile_photo = ? WHERE tenant_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $tenantName, $email, $contactNumber, $photoPath, $userId);
    } else {
        $sql = "UPDATE tenants SET tenant_name = ?, email = ?, contact_number = ? WHERE tenant_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $tenantName, $email, $contactNumber, $userId);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully!';
        if ($photoPath) {
            // The client is in the /tenant/ directory, so it needs to go up one level to find the uploads folder.
            $response['profile_photo_url'] = '../' . $photoPath;
        }
    } else {
        $response['message'] = 'Failed to update profile: ' . mysqli_error($con);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($con);

} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

echo json_encode($response);
exit();
?>
