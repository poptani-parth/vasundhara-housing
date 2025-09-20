<?php
session_start();
include '../database.php';

// Check if the user is logged in
if (!isset($_SESSION['tenant_id']) || $_SESSION['type_tenant'] !== 'tenant') {
    echo '<div class="text-center text-red-500 p-8">Please log in to view this page.</div>';
    exit();
}

$userId = $_SESSION['tenant_id'];
$requests = [];
$propertyId = null;

try {
    // Check if the database connection is successful
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // Step 1: Get the tenant's property ID. This is required for a new request.
    $sql_property = "SELECT property_id FROM tenants WHERE tenant_id = ?";
    $stmt_property = mysqli_prepare($conn, $sql_property);
    mysqli_stmt_bind_param($stmt_property, "i", $userId);
    mysqli_stmt_execute($stmt_property);
    $result_property = mysqli_stmt_get_result($stmt_property);
    $tenantData = mysqli_fetch_assoc($result_property);
    
    if ($tenantData && !empty($tenantData['property_id'])) {
        $propertyId = $tenantData['property_id'];
    }
    mysqli_stmt_close($stmt_property);

    // Step 2: If a property is found, fetch all maintenance requests for this tenant.
    if ($propertyId) {
        $sql = "SELECT request_id, description, request_date, status FROM maintenance_requests WHERE tenant_id = ? ORDER BY request_date DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $requests[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($conn);

} catch (Exception $e) {
    echo '<div class="text-center text-red-500 p-8">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit();
}
?>

<?php
function renderMaintenanceList($requests) {
    if (empty($requests)) {
        echo '<div class="text-center text-gray-500 p-8 bg-gray-50 rounded-lg border-2 border-dashed">';
        echo '<svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
        echo '<p class="mt-4">You have not submitted any maintenance requests yet.</p>';
        echo '</div>';
    } else {
        echo '<div class="space-y-4">';
        foreach ($requests as $request) {
            $statusConfig = match ($request['status']) {
                'Completed' => ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>', 'iconBg' => 'bg-green-100', 'iconColor' => 'text-green-600', 'badge' => 'bg-green-100 text-green-800'],
                'In Progress' => ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 110 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" /></svg>', 'iconBg' => 'bg-blue-100', 'iconColor' => 'text-blue-600', 'badge' => 'bg-blue-100 text-blue-800'],
                default => ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 10.586V6z" clip-rule="evenodd" /></svg>', 'iconBg' => 'bg-yellow-100', 'iconColor' => 'text-yellow-600', 'badge' => 'bg-yellow-100 text-yellow-800'],
            };
            echo '<div class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex items-start gap-4">';
            echo '    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center ' . $statusConfig['iconBg'] . '">';
            echo '        <span class="' . $statusConfig['iconColor'] . '">' . $statusConfig['icon'] . '</span>';
            echo '    </div>';
            echo '    <div class="flex-grow">';
            echo '        <p class="font-semibold text-gray-800">' . htmlspecialchars($request['description']) . '</p>';
            echo '        <p class="text-xs text-gray-500 mt-1">Submitted on: ' . (new DateTime($request['request_date']))->format('F j, Y') . '</p>';
            echo '    </div>';
            echo '    <div class="flex-shrink-0">';
            echo '        <span class="px-3 py-1 text-xs font-semibold rounded-full ' . $statusConfig['badge'] . '">' . htmlspecialchars($request['status']) . '</span>';
            echo '    </div>';
            echo '</div>';
        }
        echo '</div>';
    }
}

if (isset($_GET['is_ajax']) && $_GET['is_ajax'] == '1') {
    renderMaintenanceList($requests);
    exit;
}
?>

<div class="space-y-8 animate-slide-up-fade-in">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Maintenance & Support</h2>
            <p class="text-gray-600 mt-1">Report a new issue or view the status of your existing requests.</p>
        </div>
    </div>

    <div id="alert-message" class="hidden transition-all duration-300 ease-in-out transform scale-95 opacity-0 mb-6 p-4 rounded-lg text-center" role="alert">
        <span id="alert-text"></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Form -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 h-full">
                <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0L7.86 6.87H4.14c-1.66 0-2.64 2.01-1.41 3.24l2.98 2.98-1.42 4.24c-.51 1.53 1.23 2.95 2.64 2.18L10 17.21l3.63 2.25c1.41.77 3.15-.65 2.64-2.18l-1.42-4.24 2.98-2.98c1.23-1.23.25-3.24-1.41-3.24h-3.72L11.49 3.17z" clip-rule="evenodd" /></svg>
                    Report a New Issue
                </h3>
                <?php if ($propertyId) : ?>
                    <form id="maintenance-form" method="POST">
                        <input type="hidden" name="property_id" value="<?php echo htmlspecialchars($propertyId); ?>">
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="description" name="description" rows="6" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200" placeholder="Please describe the problem in detail..." required></textarea>
                        </div>
                        <div class="text-right">
                            <button type="submit" id="submit-btn" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:bg-blue-700 transition duration-300 ease-in-out">
                                Submit Request
                            </button>
                        </div>
                    </form>
                <?php else : ?>
                    <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded-r-lg">
                        <p class="font-bold">Cannot Submit Request</p>
                        <p class="text-sm">You are not currently associated with a property. Please contact your administrator to update your profile.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: List of Requests -->
        <div class="lg:col-span-2">
            <div id="request-history-list" class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Your Request History</h3>
                <?php renderMaintenanceList($requests); ?>
            </div>
        </div>
    </div>
</div>

<script>
    // This script runs when the content is loaded.
    // All event handlers are delegated in index.php for robustness.
</script>