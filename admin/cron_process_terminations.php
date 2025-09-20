<?php
// This script is intended to be run by a cron job or scheduled task.
// It should not be accessible via a web browser for security.

// Set a long execution time in case there are many records to process.
set_time_limit(300); 

// Define a base path to make includes easier.
// This assumes the cron script is in the 'admin' directory.
define('BASE_PATH', dirname(__DIR__));

include BASE_PATH . '/admin/database.php';

// --- Start Processing ---
$log_message = "[" . date('Y-m-d H:i:s') . "] Cron job started.\n";

// Find approved termination requests that are 24 hours or older
$sql = "SELECT id, tenant_id, property_id 
        FROM termination_requests 
        WHERE status = 'Approved' 
          AND approved_at IS NOT NULL 
          AND approved_at <= NOW() - INTERVAL 24 HOUR";

$result = $conn->query($sql);

if ($result === false) {
    $log_message .= "Error querying for pending terminations: " . $conn->error . "\n";
    // In a real application, you would log this to a file.
    echo $log_message;
    exit;
}

if ($result->num_rows > 0) {
    $log_message .= "Found " . $result->num_rows . " requests to process.\n";

    while ($request = $result->fetch_assoc()) {
        $requestId = $request['id'];
        $tenantId = $request['tenant_id'];
        $propertyId = $request['property_id'];

        $conn->begin_transaction();

        try {
            // 1. Set tenant to 'Unactive'
            $stmt_tenant = $conn->prepare("UPDATE tenants SET status = 'Unactive' WHERE tenant_id = ?");
            $stmt_tenant->bind_param("i", $tenantId);
            $stmt_tenant->execute();
            $stmt_tenant->close();

            // 2. Set property to 'Available'
            $stmt_property = $conn->prepare("UPDATE properties SET status = 'Available' WHERE pro_id = ?");
            $stmt_property->bind_param("i", $propertyId);
            $stmt_property->execute();
            $stmt_property->close();

            // 3. Update the termination request status to 'Completed' to prevent re-processing
            $stmt_request = $conn->prepare("UPDATE termination_requests SET status = 'Completed' WHERE id = ?");
            $stmt_request->bind_param("i", $requestId);
            $stmt_request->execute();
            $stmt_request->close();

            $conn->commit();
            $log_message .= "  - Successfully processed request ID: " . $requestId . "\n";

        } catch (Exception $e) {
            $conn->rollback();
            $log_message .= "  - FAILED to process request ID: " . $requestId . ". Error: " . $e->getMessage() . "\n";
        }
    }
} else {
    $log_message .= "No pending termination requests to process.\n";
}

$conn->close();
$log_message .= "Cron job finished.\n\n";

// For debugging, you can echo the log. For production, you'd append to a log file.
echo nl2br($log_message);

?>