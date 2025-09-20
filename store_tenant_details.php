<?php
include './database.php';
session_start();
$property_id = $_SESSION['property_id'] ?? null;
$tenant_id = $_SESSION['tenant_id'] ?? null;

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

        $newName = uniqid('img_') . '.' . $ext;
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tenantDetails'])) {
    // Sanitize inputs
    $tenant_name = $_POST['tenant_name'];
    $tenant_father_name = $_POST['tenant_father_name'];
    $tenant_mobile_number = $_POST['tenant_mobile_number'];
    $tenant_email = $_POST['tenant_email'];
    $tenant_address = $_POST['tenant_address'];
    $tenant_aadhar_number = preg_replace('/\s+/', '', $_POST['tenant_aadhar_number']);
    $rent_amount = $_POST['rent_amount_numeric'];
    $agreement_date = $_POST['agreement_date'];
    $agreement_duration = $_POST['agreement_duration_months'];
    $checkout_date = date('Y-m-d', strtotime($agreement_date . " + $agreement_duration months"));
    $place = $_POST['place'];
    $witness1_name = $_POST['witness1_name'];
    $witness2_name = $_POST['witness2_name'];

    // Handle file uploads
    $uploadDir = 'uploads/tenant_documents/';
    $images = [];
    $fileFields = ['tenant_photo', 'aadhar_card', 'tenant_signature', 'witness1_aadhar_card', 'witness2_aadhar_card', 'witness1_signature', 'witness2_signature'];
    
    foreach ($fileFields as $field) {
        if (!empty($_FILES[$field]['name'])) {
            $result = uploadImage($_FILES[$field], $uploadDir);
            if (isset($result['error'])) {
                die("File upload error for $field: " . $result['error']);
            }
            $images[$field] = $result['success'];
        } else {
            $images[$field] = '';
        }
    }
    
    $rent_amount_words = ''; // You can add a function to convert number to words if needed

    $stmt = $conn->prepare("
        INSERT INTO rental_agreements 
        (tenant_id, pro_id, tenantName, fatherName, email, number, address, aadharNumber, month_rent_no, month_rent_word, starting_date, ending_date, place, witness1_name, witness2_name, tenant_photo, tenant_aadhar, tenant_sign, witness1_aadhar, witness2_aadhar, witness1_sign, witness2_sign) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iisssssissssssssssssss",
        $tenant_id,
        $property_id,
        $tenant_name,
        $tenant_father_name,
        $tenant_email,
        $tenant_mobile_number,
        $tenant_address,
        $tenant_aadhar_number,
        $rent_amount,
        $rent_amount_words,
        $agreement_date,
        $checkout_date,
        $place,
        $witness1_name,
        $witness2_name,
        $images['tenant_photo'],
        $images['aadhar_card'],
        $images['tenant_signature'],
        $images['witness1_aadhar_card'],
        $images['witness2_aadhar_card'],
        $images['witness1_signature'],
        $images['witness2_signature']
    );

    if ($stmt->execute()) {
        header('Location: registerSuccess.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
