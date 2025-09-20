<?php
include 'database.php';
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// --- Function to Fetch Data ---
function getImageUrl($path)
{
    if (!empty($path)) {
        // The path from DB is relative to project root, e.g., 'uploads/tenant_agreements/...'
        // This script is in the 'admin' folder, so we need to go up one level.
        $full_path_on_disk = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);

        if (file_exists($full_path_on_disk)) {
            // The web path should be relative to the admin folder.
            return '../' . $path;
        }
    }
    return ''; // No image path provided
}

// Fetch single tenant details with all fields
function getTenantDetails($conn, $tenantId)
{
    $stmt = $conn->prepare("SELECT * FROM tenants WHERE tenant_id = ?");
    $stmt->bind_param("i", $tenantId);
    $stmt->execute();
    $result = $stmt->get_result();
    $tenant = $result->fetch_assoc();
    $stmt->close();
    return $tenant;
}

function getRentDetails($conn, $tenantId)
{
    $stmt = $conn->prepare("SELECT * FROM rental_agreements WHERE tenant_id = ?");
    $stmt->bind_param("i", $tenantId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rentDetails = $result->fetch_assoc();
    $stmt->close();
    return $rentDetails;
}

function getPropertyDetails($conn, $propertyId)
{
    if (empty($propertyId)) {
        return null;
    }
    $stmt = $conn->prepare("SELECT pro_nm FROM properties WHERE pro_id = ?");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    $stmt->close();
    return $property;
}

// --- NEW FUNCTION TO FETCH PAYMENT HISTORY ---
function getPaymentHistory($conn, $tenantId)
{
    // Selecting specific columns is better than SELECT *
    $stmt = $conn->prepare("SELECT payment_id, payment_period, amount, remark, status FROM payments WHERE tenant_id = ? ORDER BY payment_period DESC");
    if (!$stmt) {
        // Handle prepare error if needed, for now, we assume it works.
        return [];
    }
    $stmt->bind_param("i", $tenantId);
    $stmt->execute();
    $result = $stmt->get_result();
    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    $stmt->close();
    return $payments;
}

// --- Fetch Data for Display ---
$tenant = null;
$rentDetails = null;
$property = null;
$payments = []; // Initialize payments array

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $tenantId = $_GET['id'];
    $tenant = getTenantDetails($conn, $tenantId);
    if ($tenant) {
        $rentDetails = getRentDetails($conn, $tenant['tenant_id']);
        $property = getPropertyDetails($conn, $tenant['property_id']);
        // Fetch payment history
        $payments = getPaymentHistory($conn, $tenantId);
    }
}

// Initialize image URLs with empty strings to avoid undefined variable warnings
$tenantsign = '';
$adharcard = '';
$tenantPhoto = '';
$witness1_aadhar_img = '';
$witness1_sign_img = '';
$witness2_aadhar_img = '';
$witness2_sign_img = '';

// Retrieve image URLs only if $rentDetails is not null
if ($rentDetails) {
    $tenantsign = getImageUrl($rentDetails['tenant_sign'] ?? '');
    $adharcard = getImageUrl($rentDetails['tenant_aadhar'] ?? '');
    $tenantPhoto = getImageUrl($rentDetails['tenant_photo'] ?? '');
    $witness1_aadhar_img = getImageUrl($rentDetails['witness1_aadhar'] ?? '');
    $witness1_sign_img = getImageUrl($rentDetails['witness1_sign'] ?? '');
    $witness2_aadhar_img = getImageUrl($rentDetails['witness2_aadhar'] ?? '');
    $witness2_sign_img = getImageUrl($rentDetails['witness2_sign'] ?? '');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tenant ? htmlspecialchars($tenant['tenant_name']) . ' Details' : 'Tenant Not Found'; ?> - Vasundhara Housing</title>
     <script src="../assets/js/tailwind.js"></script>
    <link href="../assets/css/tailwind.css" rel="stylesheet"/>
    <link href="../assets/css/all.min.css" rel="stylesheet"/>
    <link href="../assets/css/google_fonts.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/fonts/interFont.css">
    <link rel="stylesheet" href="./style.css">
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .modal {
            transition: opacity 0.3s ease-out, visibility 0.3s ease-out;
            z-index: 9999;
            opacity: 0;
            /* Hidden by default */
            visibility: hidden;
            /* Hidden by default */
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* Semi-transparent background */
        }

        .modal-content {
            transition: transform 0.3s ease-out;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .modal-active {
            opacity: 1;
            visibility: visible;
        }

        .modal-active .modal-content {
            transform: scale(1);
        }
    </style>
</head>

<body class="bg-gray-100 font-sans p-5 **text-lg font-bold**">
    <div class="max-w-4xl mx-auto bg-white p-6 md:p-10 rounded-xl shadow-lg animate-fadeIn">

        <?php if ($tenant): ?>
            <div class="flex justify-between items-center mb-6 pb-4 border-b">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800">Tenant Details</h1>
                <a href="index.php" class="text-blue-600 hover:text-blue-800 flex items-center transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
            </div>

            <div class="overflow-x-auto shadow-md rounded-lg mb-8">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-3">Field</th>
                            <th scope="col" class="px-6 py-3">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Agreement ID</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($rentDetails['agreement_id'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Tenant Name</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($tenant['tenant_name']); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Father's Name</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($rentDetails['fatherName'] ?? ''); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Email</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($rentDetails['email'] ?? ''); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Contact Number</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($rentDetails['number'] ?? ''); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Address</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($rentDetails['address'] ?? ''); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Aadhaar Number</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($rentDetails['aadharNumber'] ?? ''); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Property Name</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($property['pro_nm'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Monthly Rent No.</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($rentDetails['month_rent_no'] ?? ''); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Starting Date</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($rentDetails['starting_date'] ?? ''); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">End Date</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($rentDetails['ending_date'] ?? ''); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Status</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $tenant['status'] === 'Active' ? 'bg-green-200 text-green-900' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo htmlspecialchars($tenant['status']); ?>
                                </span>
                            </td>
                        </tr>

                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Tenant Photo</td>
                            <td class="px-6 py-4">
                                <?php if (!empty($tenantPhoto)): ?>
                                    <button onclick="showImageModal('<?php echo htmlspecialchars($tenantPhoto); ?>')" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200">
                                        <i class="fas fa-eye mr-2"></i> View Image
                                    </button>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm">No photo available.</p>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Aadhaar Document</td>
                            <td class="px-6 py-4">
                                <?php if (!empty($adharcard)): ?>
                                    <button onclick="showImageModal('<?php echo htmlspecialchars($adharcard); ?>')" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200">
                                        <i class="fas fa-eye mr-2"></i> View Image
                                    </button>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm">No Aadhaar document available.</p>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Tenant Signature</td>
                            <td class="px-6 py-4">
                                <?php if (!empty($tenantsign)): ?>
                                    <button onclick="showImageModal('<?php echo htmlspecialchars($tenantsign); ?>')" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200">
                                        <i class="fas fa-eye mr-2"></i> View Image
                                    </button>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm">No signature available.</p>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr class="bg-gray-100">
                            <th colspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Witness 1 Details</th>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Witness 1 Name</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($rentDetails['witness1_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Witness 1 Aadhaar</td>
                            <td class="px-6 py-4">
                                <?php if (!empty($witness1_aadhar_img)): ?>
                                    <button onclick="showImageModal('<?php echo htmlspecialchars($witness1_aadhar_img); ?>')" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200">
                                        <i class="fas fa-eye mr-2"></i> View Image
                                    </button>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm">No document available.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Witness 1 Signature</td>
                            <td class="px-6 py-4">
                                <?php if (!empty($witness1_sign_img)): ?>
                                    <button onclick="showImageModal('<?php echo htmlspecialchars($witness1_sign_img); ?>')" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200">
                                        <i class="fas fa-eye mr-2"></i> View Image
                                    </button>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm">No signature available.</p>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr class="bg-gray-100">
                            <th colspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Witness 2 Details</th>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Witness 2 Name</td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($rentDetails['witness2_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Witness 2 Aadhaar</td>
                            <td class="px-6 py-4">
                                <?php if (!empty($witness2_aadhar_img)): ?>
                                    <button onclick="showImageModal('<?php echo htmlspecialchars($witness2_aadhar_img); ?>')" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200">
                                        <i class="fas fa-eye mr-2"></i> View Image
                                    </button>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm">No document available.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">Witness 2 Signature</td>
                            <td class="px-6 py-4">
                                <?php if (!empty($witness2_sign_img)): ?>
                                    <button onclick="showImageModal('<?php echo htmlspecialchars($witness2_sign_img); ?>')" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200">
                                        <i class="fas fa-eye mr-2"></i> View Image
                                    </button>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm">No signature available.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 pb-2 border-b">Payment History</h2>
                <?php if (!empty($payments)): ?>
                    <div class="overflow-x-auto shadow-md rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Payment ID</th>
                                    <th scope="col" class="px-6 py-3">Payment Period</th>
                                    <th scope="col" class="px-6 py-3">Amount</th>
                                    <th scope="col" class="px-6 py-3">Remark / Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            <?php echo htmlspecialchars($payment['payment_id']); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php echo date('F Y', strtotime($payment['payment_period'])); ?>
                                        </td>
                                        <td class="px-6 py-4">₹<?php echo number_format(htmlspecialchars($payment['amount']), 2); ?></td>
                                        <td class="px-6 py-4">
                                            <?php echo htmlspecialchars($payment['remark'] ?? 'N/A'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-gray-500">
                        <p>No payment history found for this tenant.</p>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="text-center py-10">
                <h1 class="text-3xl text-red-600 font-semibold mb-4">Tenant Not Found</h1>
                <p class="text-gray-600">The tenant you are looking for does not exist or the ID is invalid.</p>
                <a href="index.php" class="mt-6 inline-block px-6 py-3 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition-colors duration-200">
                    Back to Dashboard
                </a>
            </div>
        <?php endif; ?>

    </div>

    <div id="imageModal" class="modal">
        <div class="modal-content transform scale-95 relative w-full max-w-4xl bg-white rounded-lg shadow-xl">
            <button onclick="hideImageModal()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 transition-colors duration-200 z-10">
                <i class="fas fa-times-circle text-3xl"></i>
            </button>
            <div class="p-6">
                <img id="modalImage" src="" alt="Full size image" class="rounded-lg mx-auto max-w-full max-h-[85vh]">
            </div>
        </div>
    </div>

    <script>
        // Get the modal and the image element
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');

        // Function to show the modal with the specified image or a placeholder
        function showImageModal(imagePath) {
            if (imagePath) {
                modalImage.src = imagePath;
                modalImage.alt = 'Full size image';
            } else {
                modalImage.src = '../assets/images/notFoundImage.png'; // Path to your placeholder image
                modalImage.alt = 'Image Not Found Placeholder';
            }
            modal.classList.add('modal-active');
            document.body.style.overflow = 'hidden'; // Prevent body scroll
        }

        // Function to hide the modal
        function hideImageModal() {
            modal.classList.remove('modal-active');
            document.body.style.overflow = ''; // Re-enable body scroll
        }

        // Hide the modal when clicking outside the image
        modal.addEventListener('click', (e) => {
            if (e.target.id === 'imageModal') {
                hideImageModal();
            }
        });

        // Handle ESC key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                hideImageModal();
            }
        });
    </script>

</body>

</html>

<?php
$conn->close();
?>