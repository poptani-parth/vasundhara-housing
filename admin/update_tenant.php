<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include './database.php'; // Adjust path as necessary

$tenantId = null;
$tenant = null;
$message = '';
$messageType = '';

// Fetch existing tenant data if ID is provided in GET request
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $tenantId = (int)$_GET['id'];
    if ($conn) {
        $stmt = $conn->prepare("SELECT 
            t.*, 
            ra.fatherName,
            ra.address,
            ra.aadharNumber,
            ra.month_rent_no,
            ra.starting_date,
            ra.ending_date,
            ra.pro_id
            FROM tenants t
            LEFT JOIN rental_agreements ra ON t.tenant_id = ra.tenant_id
            WHERE t.tenant_id = ?");
        $stmt->bind_param("i", $tenantId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $tenant = $result->fetch_assoc();
        } else {
            $message = "Tenant not found.";
            $messageType = "red";
        }
        $stmt->close();
    }
}

// Handle form submission for updating tenant data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['TenantUpdate'])) {
    // Ensure tenantId is available from hidden input or session
    $tenantId = (int)$_POST['tenantId'];

    if (!$conn) {
        $message = "Database connection failed.";
        $messageType = "red";
    } else {
        // Collect and sanitize form data
        $tenantName = htmlspecialchars(stripslashes(trim($_POST['tenantName'])), ENT_QUOTES, 'UTF-8');
        $contactNumber = htmlspecialchars(stripslashes(trim($_POST['contactNumber'])), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(stripslashes(trim($_POST['email'])), ENT_QUOTES, 'UTF-8');
        $property_id = empty($_POST['property_id']) ? NULL : (int) $_POST['property_id'];
        $tenantStatus = htmlspecialchars(stripslashes(trim($_POST['tenantStatus'])), ENT_QUOTES, 'UTF-8');
        $fatherName = htmlspecialchars(stripslashes(trim($_POST['fatherName'])), ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars(stripslashes(trim($_POST['address'])), ENT_QUOTES, 'UTF-8');
        $aadharNumber = htmlspecialchars(stripslashes(trim($_POST['aadharNumber'])), ENT_QUOTES, 'UTF-8');
        $monthRentNo = htmlspecialchars(stripslashes(trim($_POST['monthRentNo'])), ENT_QUOTES, 'UTF-8');
        $startingDate = htmlspecialchars(stripslashes(trim($_POST['startingDate'])), ENT_QUOTES, 'UTF-8');
        $endDate = htmlspecialchars(stripslashes(trim($_POST['endDate'])), ENT_QUOTES, 'UTF-8');

        // Validation
        $errors = [];
        if (empty($tenantName)) $errors[] = "Tenant name is required.";
        if (!preg_match('/^[0-9]{10}$/', $contactNumber)) $errors[] = "Contact number must be 10 digits.";
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
        if (empty($fatherName)) $errors[] = "Father's name is required.";
        if (empty($address)) $errors[] = "Address is required.";
        if (empty($aadharNumber) || !is_numeric($aadharNumber)) $errors[] = "Aadhaar number is required and must be numeric.";
        if (empty($monthRentNo) || !is_numeric($monthRentNo)) $errors[] = "Monthly rent is required and must be numeric.";
        if (empty($endDate)) $errors[] = "End date is required.";


        // Check for duplicate contact number, excluding the current tenant's own number
        $check = $conn->prepare("SELECT COUNT(*) FROM tenants WHERE contact_number = ? AND tenant_id != ?");
        $check->bind_param("si", $contactNumber, $tenantId);
        $check->execute();
        $check->bind_result($count); $check->fetch(); $check->close();
        if ($count > 0) $errors[] = "Another tenant with this contact number already exists.";

        if (!empty($errors)) {
            $message = implode(" ", $errors);
            $messageType = "red";
        } else {
            // Start a transaction for atomicity
            $conn->begin_transaction();
            try {
                // Prepare the SQL UPDATE statement for tenants table
                $stmt_tenants = $conn->prepare("UPDATE tenants SET
                    tenant_name = ?, contact_number = ?, email = ?, property_id = ?, status = ?
                    WHERE tenant_id = ?");

                $stmt_tenants->bind_param("ssiisi", $tenantName, $contactNumber, $email, $property_id, $tenantStatus, $tenantId);
                $stmt_tenants->execute();

                // Prepare the SQL UPDATE statement for rental_agreements table
                $stmt_rent = $conn->prepare("UPDATE rental_agreements SET
                    email = ?, fatherName = ?, address = ?, aadharNumber = ?, month_rent_no = ?, starting_date = ?, ending_date = ?
                    WHERE tenant_id = ?");

                $stmt_rent->bind_param("sssisss", $email, $fatherName, $address, $aadharNumber, $monthRentNo, $startingDate, $endDate, $tenantId);
                $stmt_rent->execute();

                $conn->commit();
                $message = "Tenant and rental details updated successfully!";
                $messageType = "green";
                
                // Re-fetch updated tenant data to populate the form
                $stmt_reget = $conn->prepare("SELECT 
                    t.*, 
                    ra.fatherName,
                    ra.address,
                    ra.aadharNumber,
                    ra.month_rent_no,
                    ra.starting_date,
                    ra.ending_date
                    FROM tenants t
                    LEFT JOIN rental_agreements ra ON t.tenant_id = ra.tenant_id
                    WHERE t.tenant_id = ?");
                $stmt_reget->bind_param("i", $tenantId);
                $stmt_reget->execute();
                $result_reget = $stmt_reget->get_result();
                $tenant = $result_reget->fetch_assoc();
                $stmt_reget->close();

            } catch (Exception $e) {
                $conn->rollback();
                $message = "Database update failed: " . $e->getMessage();
                $messageType = "red";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Tenant - Vasundhara Housing</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fadeIn { animation: fadeIn 0.5s ease-out forwards; }
        .animate-slideInUp { animation: slideInUp 0.6s ease-out forwards; }
        .form-field-animation { opacity: 0; transform: translateY(10px); animation: slideInUp 0.5s ease-out forwards; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 animate-fadeIn">
    <div class="w-full max-w-2xl bg-white p-8 rounded-xl shadow-lg border border-gray-200 animate-slideInUp">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h1 class="text-3xl font-extrabold text-gray-900">Update Tenant</h1>
            <a href="index.php" class="text-blue-600 hover:text-blue-800 flex items-center transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($tenant): ?>
            <form id="update-tenant-form" method="post" class="space-y-6">
                <input type="hidden" name="tenantId" value="<?php echo htmlspecialchars($tenant['tenant_id'] ?? ''); ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-field-animation" style="animation-delay: 0.1s;">
                        <label for="tenantName" class="block text-lg font-medium text-gray-700 mb-2">Tenant Name</label>
                        <input type="text" id="tenantName" name="tenantName" required
                            value="<?php echo htmlspecialchars($tenant['tenant_name'] ?? ''); ?>"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200">
                    </div>
                    
                    <div class="form-field-animation" style="animation-delay: 0.2s;">
                        <label for="fatherName" class="block text-lg font-medium text-gray-700 mb-2">Father's Name</label>
                        <input type="text" id="fatherName" name="fatherName" required
                            value="<?php echo htmlspecialchars($tenant['fatherName'] ?? ''); ?>"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200">
                    </div>
                    
                    <div class="form-field-animation" style="animation-delay: 0.3s;">
                        <label for="contactNumber" class="block text-lg font-medium text-gray-700 mb-2">Contact Number</label>
                        <input type="tel" id="contactNumber" name="contactNumber" required pattern="+91 [0-9]{10}"
                            value="<?php echo htmlspecialchars($tenant['contact_number'] ?? ''); ?>"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200"
                            maxlength="10">
                    </div>
                    
                    <div class="form-field-animation" style="animation-delay: 0.4s;">
                        <label for="email" class="block text-lg font-medium text-gray-700 mb-2">Email (Optional)</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo htmlspecialchars($tenant['email'] ?? 'parth'); ?>"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200">
                    </div>

                    <div class="form-field-animation" style="animation-delay: 0.5s;">
                        <label for="address" class="block text-lg font-medium text-gray-700 mb-2">Address</label>
                        <input type="text" id="address" name="address" required
                            value="<?php echo htmlspecialchars($tenant['address'] ?? ''); ?>"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200">
                    </div>

                    <div class="form-field-animation" style="animation-delay: 0.6s;">
                        <label for="aadharNumber" class="block text-lg font-medium text-gray-700 mb-2">Aadhaar Number</label>
                        <input type="text" id="aadharNumber" name="aadharNumber" readonly
                            value="<?php echo htmlspecialchars($tenant['aadharNumber'] ?? ''); ?>"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed transition-all duration-200">
                    </div>
                    
                    <div class="form-field-animation" style="animation-delay: 0.7s;">
                        <label for="monthRentNo" class="block text-lg font-medium text-gray-700 mb-2">Monthly Rent No.</label>
                        <input type="number" id="monthRentNo" name="monthRentNo" required
                            value="<?php echo htmlspecialchars($tenant['month_rent_no'] ?? ''); ?>"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200">
                    </div>
                    
                    <div class="form-field-animation" style="animation-delay: 0.8s;">
                        <label for="startingDate" class="block text-lg font-medium text-gray-700 mb-2">Starting Date</label>
                        <input type="date" id="startingDate" name="startingDate" readonly
                            value="<?php echo htmlspecialchars($tenant['starting_date'] ?? ''); ?>"
                             class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed transition-all duration-200">
                    </div>

                    <div class="form-field-animation" style="animation-delay: 0.9s;">
                        <label for="endDate" class="block text-lg font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" id="endDate" name="endDate" required
                            value="<?php echo htmlspecialchars($tenant['ending_date'] ?? ''); ?>"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200">
                    </div>
                    
                    <div class="form-field-animation" style="animation-delay: 1.0s;">
                        <label for="property_id" class="block text-lg font-medium text-gray-700 mb-2">Assigned Property (ID)</label>
                        <input type="number" id="property_id" name="property_id"
                            value="<?php echo htmlspecialchars($tenant['property_id'] ?? ''); ?>"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200"
                            readonly>
                    </div>

                    <div class="form-field-animation" style="animation-delay: 1.1s;">
                        <label for="tenantStatus" class="block text-lg font-medium text-gray-700 mb-2">Status</label>
                        <select id="tenantStatus" name="tenantStatus"
                                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 transition-all duration-200">
                            <option value="Active" <?php echo (($tenant['status'] ?? '') == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Unactive" <?php echo (($tenant['status'] ?? '') == 'Unactive') ? 'selected' : ''; ?>>Unactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-6 border-t mt-6 form-field-animation" style="animation-delay: 1.2s;">
                    <button type="button" onclick="window.location.href='index.php'"
                            class="px-6 py-3 bg-gray-300 text-gray-800 rounded-lg shadow-md hover:bg-gray-400 transition-colors duration-200 font-semibold transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                        Cancel
                    </button>
                    <button type="submit"
                            name="TenantUpdate"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition-colors duration-200 font-semibold transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Update Tenant
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="text-center py-10">
                <p class="text-xl text-red-600 font-semibold mb-4">Tenant Not Found</p>
                <p class="text-gray-600">The tenant you are trying to update does not exist or the ID is invalid.</p>
            </div>
        <?php endif; ?>
    </div>

    <div id="toast-notification" class="fixed bottom-5 right-5 bg-green-500 text-white py-3 px-6 rounded-lg shadow-lg opacity-0 transition-all duration-300 ease-out z-50 transform translate-y-5">
        Toast message
    </div>
      
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toastNotification = document.getElementById('toast-notification');

            function showToast(message, type = 'green', duration = 5000) {
                if (!toastNotification) {
                    console.warn('Toast notification element not found!');
                    return;
                }
                toastNotification.textContent = message;
                toastNotification.className = `fixed bottom-5 right-5 text-white py-3 px-6 rounded-lg shadow-lg opacity-0 transition-all duration-300 ease-out z-50 transform translate-y-5`; 
                
                if (type === 'green') {
                    toastNotification.classList.add('bg-green-500'); 
                    window.setTimeout(() => {
                        window.location.href = 'index.php'; // Redirect to tenants list after success
                    }, 4000);
                } else if (type === 'red') {
                    toastNotification.classList.add('bg-red-500');
                } else if (type === 'blue') {
                    toastNotification.classList.add('bg-blue-500');
                }
                
                toastNotification.classList.remove('opacity-0', 'translate-y-5');
                toastNotification.classList.add('opacity-100', 'translate-y-0');

                if (type !== 'green') { // Only auto-hide if not redirecting
                    setTimeout(() => {
                        toastNotification.classList.remove('opacity-100', 'translate-y-0');
                        toastNotification.classList.add('opacity-0', 'translate-y-5'); 
                    }, duration);
                }
            }

            <?php if (!empty($message)): ?>
                showToast(<?php echo json_encode($message); ?>, <?php echo json_encode($messageType); ?>);
            <?php endif; ?>
        });
    </script>
</body>
</html>
