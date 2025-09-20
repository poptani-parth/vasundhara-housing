<?php
session_start();
header('Content-Type: application/json');

include '../database.php';

$response = ['success' => false, 'message' => 'An error occurred.'];

if (!isset($_SESSION['tenant_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Unauthorized access or invalid request.';
    http_response_code(401);
    echo json_encode($response);
    exit();
}

$userId = $_SESSION['tenant_id'];
$input = json_decode(file_get_contents('php://input'), true);

$oldAgreementId = $input['old_agreement_id'] ?? null;
$newEndDate = $input['new_end_date'] ?? null;

if (empty($oldAgreementId) || empty($newEndDate)) {
    $response['message'] = 'Missing required data for re-application.';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

try {
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // 1. Fetch the old agreement data, ensuring it belongs to the current tenant
    $stmt_old = $conn->prepare("SELECT * FROM rental_agreements WHERE agreement_id = ? AND tenant_id = ?");
    $stmt_old->bind_param("ii", $oldAgreementId, $userId);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();
    $oldAgreement = $result_old->fetch_assoc();
    $stmt_old->close();

    if (!$oldAgreement) {
        throw new Exception('Could not find the original rental agreement or you do not have permission to access it.');
    }

    // 2. Calculate new dates and validate
    $oldEndDateObj = new DateTime($oldAgreement['ending_date']);
    $newStartDateObj = (clone $oldEndDateObj)->modify('+1 day');
    $newEndDateObj = new DateTime($newEndDate);

    if ($newEndDateObj <= $newStartDateObj) {
        throw new Exception('The new end date must be after the new start date.');
    }

    // 3. Prepare and execute the INSERT statement
    $sql = "INSERT INTO rental_agreements (
        tenant_id, pro_id, tenantName, fatherName, email, number, address, aadharNumber,
        month_rent_no, month_rent_word, starting_date, ending_date, place,
        witness1_name, witness1_aadhar, witness2_name, witness2_aadhar,
        tenant_photo, tenant_aadhar, tenant_sign, witness1_sign, witness2_sign
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_new = $conn->prepare($sql);
    if (!$stmt_new) {
        throw new Exception('Failed to prepare new agreement statement: ' . $conn->error);
    }

    $newStartDateStr = $newStartDateObj->format('Y-m-d');
    $newEndDateStr = $newEndDateObj->format('Y-m-d');

    $stmt_new->bind_param("iissssssisssssssssssss", $oldAgreement['tenant_id'], $oldAgreement['pro_id'], $oldAgreement['tenantName'], $oldAgreement['fatherName'], $oldAgreement['email'], $oldAgreement['number'], $oldAgreement['address'], $oldAgreement['aadharNumber'], $oldAgreement['month_rent_no'], $oldAgreement['month_rent_word'], $newStartDateStr, $newEndDateStr, $oldAgreement['place'], $oldAgreement['witness1_name'], $oldAgreement['witness1_aadhar'], $oldAgreement['witness2_name'], $oldAgreement['witness2_aadhar'], $oldAgreement['tenant_photo'], $oldAgreement['tenant_aadhar'], $oldAgreement['tenant_sign'], $oldAgreement['witness1_sign'], $oldAgreement['witness2_sign']);

    if ($stmt_new->execute()) {
        $response['success'] = true;
        $response['message'] = 'New agreement created successfully! Your lease has been renewed.';
    } else {
        if ($conn->errno == 1062) {
            throw new Exception('A database constraint prevented creating the new agreement. Please ask the administrator to remove the UNIQUE constraint from the `aadharNumber` column in the `rental_agreements` table to allow for agreement history.');
        }
        throw new Exception('Failed to create the new agreement: ' . $stmt_new->error);
    }

    $stmt_new->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>