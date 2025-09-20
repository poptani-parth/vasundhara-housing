<?php
include 'database.php';
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
// Check if tenant_id is set in the URL
if (isset($_GET['tenant_id'])) {
    $tenant_id = $_GET['tenant_id'];

    // Prepare a statement to get all tenant data, including document links
    $sql = "SELECT * FROM tenants WHERE tenant_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tenant = $result->fetch_assoc();
    $stmt->close();
} else {
    // Redirect if no tenant_id is provided
    header("Location: approveTenant.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            animation: fadeIn 1s ease-in;
        }
        .details-container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-link">&larr; Back to Dashboard</a>

    <?php if ($tenant): ?>
        <div class="details-container">
            <h1>Tenant Details</h1>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($tenant['tenant_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($tenant['email']); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($tenant['contact_number']); ?></p>
            <h3>Submitted Documents</h3>
            <ul>
                <?php
                // This part assumes your 'tenants' table has columns for documents, e.g., 'document_link_1', 'document_link_2'
                // You would need to adapt this to your specific database schema
                if (!empty($tenant['document_link_1'])) {
                    echo '<li><a href="' . htmlspecialchars($tenant['document_link_1']) . '" target="_blank">View Photo ID</a></li>';
                }
                if (!empty($tenant['document_link_2'])) {
                    echo '<li><a href="' . htmlspecialchars($tenant['document_link_2']) . '" target="_blank">View Proof of Income</a></li>';
                }
                // Add more document links as needed
                ?>
            </ul>
        </div>
    <?php else: ?>
        <p>Tenant not found.</p>
    <?php endif; ?>

</body>
</html>