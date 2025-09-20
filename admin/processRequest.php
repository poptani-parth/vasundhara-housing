<?php
header('Content-Type: application/json');
include './database.php';
session_start();

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security check: ensure user is an admin
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    $response['message'] = 'Unauthorized access.';
    http_response_code(403);
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tenant_id'], $_POST['action'])) {
    $tenantId = (int)$_POST['tenant_id'];
    $action = $_POST['action'];

    if ($tenantId <= 0) {
        $response['message'] = 'Invalid Tenant ID.';
        echo json_encode($response);
        exit();
    }

    $conn->begin_transaction();

    try {
        if ($action === 'accept') {
            // Fetch tenant email before updating
            $stmt_email = $conn->prepare("SELECT email FROM tenants WHERE tenant_id = ?");
            $stmt_email->bind_param("i", $tenantId);
            $stmt_email->execute();
            $result_email = $stmt_email->get_result();
            $tenant = $result_email->fetch_assoc();
            $stmt_email->close();

            if (!$tenant) {
                throw new Exception("Tenant not found.");
            }

            // Find the property associated with this tenant's rental agreement
            $property_id_to_assign = null;
            $stmt_get_prop = $conn->prepare("SELECT pro_id FROM rental_agreements WHERE tenant_id = ? ORDER BY agreement_id DESC LIMIT 1");
            $stmt_get_prop->bind_param("i", $tenantId);
            $stmt_get_prop->execute();
            $result_prop = $stmt_get_prop->get_result();
            if ($prop_data = $result_prop->fetch_assoc()) {
                $property_id_to_assign = (int)$prop_data['pro_id'];
            }
            $stmt_get_prop->close();

            if (!$property_id_to_assign) {
                throw new Exception("Could not find a property associated with this tenant's agreement.");
            }

            // Update tenant status to 'Active' and assign the property_id
            $stmt_update = $conn->prepare("UPDATE tenants SET status = 'Active', property_id = ? WHERE tenant_id = ?");
            $stmt_update->bind_param("ii", $property_id_to_assign, $tenantId);
            $stmt_update->execute();
            $stmt_update->close();

            // Also update the property status to 'Unavailable'
            $stmt_update_prop = $conn->prepare("UPDATE properties SET status = 'Unavailable' WHERE pro_id = ?");
            $stmt_update_prop->bind_param("i", $property_id_to_assign);
            $stmt_update_prop->execute();
            $stmt_update_prop->close();

            // Send verification email
            $to = $tenant['email'];
            $subject = "Account Verified - Vasundhara Housing";
            $message_body = "Hello,\n\nYour account has been verified by Vasundhara Housing.\nYou can now log in with your credentials and pay rent online.\n\nThank you,\nVasundhara Housing";
            $headers = "From: no-reply@vasundharahousing.com";

            // Use @ to suppress mail errors if the mail server isn't configured, but provide feedback.
            if (@mail($to, $subject, $message_body, $headers)) {
                $response['message'] = 'Tenant approved and notification email sent.';
            } else {
                $response['message'] = 'Tenant approved, but the notification email could not be sent.';
            }
            $response['success'] = true;

        } elseif ($action === 'reject') {
            // Before deleting, get the associated property ID to make it available again
            $property_id_to_release = null;
            $stmt_get_prop = $conn->prepare("SELECT property_id FROM tenants WHERE tenant_id = ?");
            $stmt_get_prop->bind_param("i", $tenantId);
            $stmt_get_prop->execute();
            $result_prop = $stmt_get_prop->get_result();
            if ($prop_data = $result_prop->fetch_assoc()) {
                $property_id_to_release = $prop_data['property_id'];
            }
            $stmt_get_prop->close();

            // For rejection, we delete the tenant and their rental agreement
            $stmt_del_agreement = $conn->prepare("DELETE FROM rental_agreements WHERE tenant_id = ?");
            $stmt_del_agreement->bind_param("i", $tenantId);
            $stmt_del_agreement->execute();
            $stmt_del_agreement->close();

            $stmt_del_tenant = $conn->prepare("DELETE FROM tenants WHERE tenant_id = ?");
            $stmt_del_tenant->bind_param("i", $tenantId);
            $stmt_del_tenant->execute();

            if ($stmt_del_tenant->affected_rows > 0) {
                // If a property was linked, set its status back to 'Available'
                if ($property_id_to_release) {
                    $stmt_release_prop = $conn->prepare("UPDATE properties SET status = 'Available' WHERE pro_id = ?");
                    $stmt_release_prop->bind_param("i", $property_id_to_release);
                    $stmt_release_prop->execute();
                    $stmt_release_prop->close();
                }
                $response['success'] = true;
                $response['message'] = 'Tenant request rejected and property made available.';
            } else {
                throw new Exception("Tenant not found or already deleted.");
            }
            $stmt_del_tenant->close();
        }
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Database operation failed: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method or missing parameters.';
}

$conn->close();
echo json_encode($response);
?>