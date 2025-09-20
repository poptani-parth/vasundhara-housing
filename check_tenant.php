<?php
header('Content-Type: application/json');
include 'database.php';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

if (isset($_POST['tenant_name'])) {
    $tenantName = trim($_POST['tenant_name']);
    
    if (!empty($tenantName)) {
        $stmt = $conn->prepare("SELECT tenant_id FROM tenants WHERE tenant_name = ?");
        $stmt->bind_param("s", $tenantName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response = ['status' => 'registered'];
        } else {
            $response = ['status' => 'not_registered'];
        }
        $stmt->close();
    } else {
        $response['message'] = 'Tenant name cannot be empty.';
    }
} else {
    $response['message'] = 'Tenant name not provided.';
}

echo json_encode($response);
?>
