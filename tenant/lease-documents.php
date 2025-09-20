<?php
session_start();
include '../database.php';

if (!isset($_SESSION['tenant_id']) || $_SESSION['type_tenant'] !== 'tenant') {
    echo '<div class="text-center text-red-500 p-8">Please log in to view this page.</div>';
    exit();
}

$userId = $_SESSION['tenant_id'];
$agreements = [];

// Helper function to create a valid, web-accessible URL from the stored path
function getDocumentUrl($path)
{
    if (empty($path)) return null;
    // The path stored in DB is like 'uploads/tenant_agreements/...'
    // From the tenant folder, we need to go up one level.
    return '../' . $path;
}

// Fetch all agreement details for the tenant, ordered by the most recent first
$stmt = $conn->prepare("SELECT * FROM rental_agreements WHERE tenant_id = ? ORDER BY starting_date DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $agreements[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<div class="space-y-8 animate-slide-up-fade-in">
    <div>
        <h2 class="text-3xl font-bold text-gray-800">Lease Documents</h2>
        <p class="text-gray-600 mt-1">Access and download your current and past lease agreements.</p>
    </div>

    <?php if (!empty($agreements)) : ?>
        <div class="space-y-6">
            <?php foreach ($agreements as $index => $agreement) : ?>
                <?php
                $today = new DateTime('today');
                $startDate = new DateTime($agreement['starting_date']);
                $endDate = new DateTime($agreement['ending_date']);
                $isFirstAgreement = ($index === 0);

                if ($startDate > $today) {
                    // Agreement hasn't started yet
                    $statusClass = 'border-purple-500';
                    $statusBadge = '<span class="text-xs font-semibold px-2 py-1 bg-purple-100 text-purple-800 rounded-full">New Agreement</span>';
                } elseif ($endDate < $today) {
                    // Agreement has ended
                    $statusClass = 'border-gray-300';
                    $statusBadge = '<span class="text-xs font-semibold px-2 py-1 bg-gray-100 text-gray-600 rounded-full">Old Agreement</span>';
                } else {
                    // Agreement is currently active
                    $statusClass = 'border-blue-500';
                    $statusBadge = '<span class="text-xs font-semibold px-2 py-1 bg-blue-100 text-blue-800 rounded-full">Current</span>';
                }
                ?>
                <?php if ($isFirstAgreement) : ?>
                    <?php
                    $documents = [
                        'Tenant Photo' => getDocumentUrl($agreement['tenant_photo']),
                        'Tenant Aadhaar' => getDocumentUrl($agreement['tenant_aadhar']),
                        'Tenant Signature' => getDocumentUrl($agreement['tenant_sign']),
                        'Witness 1 Aadhaar' => getDocumentUrl($agreement['witness1_aadhar']),
                        'Witness 1 Signature' => getDocumentUrl($agreement['witness1_sign']),
                        'Witness 2 Aadhaar' => getDocumentUrl($agreement['witness2_aadhar']),
                        'Witness 2 Signature' => getDocumentUrl($agreement['witness2_sign']),
                    ];
                    ?>
                    <div class="bg-white p-6 rounded-2xl shadow-lg border <?php echo $statusClass; ?> grid grid-cols-1 lg:grid-cols-5 gap-8">
                        <!-- Left Column: Main Agreement Info -->
                        <div class="lg:col-span-2 lg:border-r lg:pr-8 flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-semibold text-gray-800">Lease Agreement</h3>
                                <?php echo $statusBadge; ?>
                            </div>
                            <div class="space-y-3 flex-grow">
                                <p class="text-sm text-gray-500">
                                    From <strong class="text-gray-700"><?php echo (new DateTime($agreement['starting_date']))->format('F j, Y'); ?></strong> to <strong class="text-gray-700"><?php echo (new DateTime($agreement['ending_date']))->format('F j, Y'); ?></strong>
                                </p>
                            </div>
                            <div class="flex flex-col space-y-2 pt-4 mt-auto">
                                <button data-agreement-id="<?php echo $agreement['agreement_id']; ?>" class="view-agreement-btn w-full text-center px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors text-sm">View Agreement</button>
                                <button data-agreement-id="<?php echo $agreement['agreement_id']; ?>" class="download-agreement-btn w-full text-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors text-sm flex items-center justify-center gap-2">
                                    <i class="ri-download-2-line"></i>Download PDF
                                </button>
                            </div>
                        </div>
                        <!-- Right Column: Supporting Documents -->
                        <div class="lg:col-span-3">
                            <h4 class="text-lg font-semibold text-gray-700 mb-4">Supporting Documents</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <?php foreach ($documents as $name => $path) : ?>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 flex items-center justify-between">
                                        <p class="font-medium text-gray-700 text-sm flex-grow pr-2"><?php echo htmlspecialchars($name); ?></p>
                                        <?php if ($path) : ?>
                                            <button class="view-image-btn px-4 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-md hover:bg-blue-200" data-src="<?php echo htmlspecialchars($path); ?>">View Online</button>
                                        <?php else : ?>
                                            <span class="px-3 py-1 bg-gray-100 text-gray-400 text-xs font-medium rounded-md">N/A</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <!-- Compact view for older agreements -->
                    <div class="bg-white p-4 rounded-2xl shadow-lg border <?php echo $statusClass; ?> flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex-grow">
                            <div class="flex items-center gap-4">
                                <h3 class="text-lg font-semibold text-gray-800">Lease Agreement</h3>
                                <?php echo $statusBadge; ?>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                Period: <?php echo (new DateTime($agreement['starting_date']))->format('M j, Y'); ?> to <?php echo (new DateTime($agreement['ending_date']))->format('M j, Y'); ?>
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button data-agreement-id="<?php echo $agreement['agreement_id']; ?>" class="view-agreement-btn px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors text-sm">View</button>
                            <button data-agreement-id="<?php echo $agreement['agreement_id']; ?>" class="download-agreement-btn px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors text-sm flex items-center justify-center gap-2">
                                <i class="ri-download-2-line"></i>Download
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="text-center text-gray-500 p-8 bg-white rounded-2xl shadow-lg border-2 border-dashed">
            <i class="ri-file-excel-2-line text-6xl text-gray-400"></i>
            <p class="mt-4 text-xl font-semibold">No Documents Found</p>
            <p class="text-sm">You do not have any lease agreements on record.</p>
        </div>
    <?php endif; ?>
</div>