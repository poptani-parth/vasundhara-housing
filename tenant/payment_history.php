<?php
session_start();
include '../database.php';

// Check if the user is logged in
if (!isset($_SESSION['tenant_id']) || $_SESSION['type_tenant'] !== 'tenant') {
    http_response_code(401);
    echo json_encode(['error' => 'Please log in to view this page.']);
    exit();
}

$userId = $_SESSION['tenant_id'];
$payments = [];

try {
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }
    
    // This block handles all AJAX requests, both filtered and non-filtered (for reset)
    if (isset($_GET['is_ajax'])) {
        $searchMonth = $_GET['month'] ?? null;

        $sql = "SELECT p.payment_id, p.amount, p.payment_period, p.remark, pr.pro_nm AS property_name
                FROM payments p
                JOIN properties pr ON p.property_id = pr.pro_id
                WHERE p.tenant_id = ?";
        
        $params = [$userId];
        $types = 'i';

        if ($searchMonth) {
            $sql .= " AND DATE_FORMAT(p.payment_period, '%Y-%m') = ?";
            $params[] = $searchMonth;
            $types .= 's';
        }

        $sql .= " ORDER BY p.payment_period DESC";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $payments[] = $row;
        }
        mysqli_stmt_close($stmt);
        header('Content-Type: application/json');
        echo json_encode($payments);
        mysqli_close($conn);
        exit();
    } else {
        // Fetch all payments for this tenant for the initial page load
        $sql = "SELECT p.payment_id, p.amount, p.remark, p.payment_period, pr.pro_nm AS property_name
                FROM payments p
                JOIN properties pr ON p.property_id = pr.pro_id
                WHERE p.tenant_id = ? ORDER BY p.payment_period DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $payments[] = $row;
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
} catch (Exception $e) {
    echo '<div class="text-center text-red-500 p-8">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit();
}
?>

<div class="space-y-8 animate-slide-up-fade-in">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Payment History</h2>
            <p class="text-gray-600 mt-1">A complete record of all your past payments.</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <input type="month" id="search-month" class="flex-1 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200" value="<?php echo date('Y-m'); ?>">
            <button id="search-button" class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
                Search Payments
            </button>
            <button id="reset-button" class="w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg shadow-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 transition duration-200">
                Reset
            </button>
            <button id="download-statement-btn" class="w-full sm:w-auto px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-200 flex items-center justify-center gap-2">
                <i class="ri-download-2-line"></i> Download Annual Statement
            </button>
        </div>
    </div>

    <div id="payment-list-container" class="mt-6 space-y-4">
        <?php if (empty($payments)) : ?>
            <div id="no-payments-message" class="text-center text-gray-500 p-8 bg-gray-50 rounded-lg border-2 border-dashed">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                <p class="mt-4">You have no payment history yet.</p>
            </div>
        <?php else : ?>
            <?php foreach ($payments as $payment) : ?>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-green-100">
                            <span class="text-green-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg></span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Rent for <?php echo htmlspecialchars($payment['property_name']); ?> - <?php echo (new DateTime($payment['payment_period']))->format('F Y'); ?></p>
                            <p class="text-sm text-gray-500">Paid on <?php echo (new DateTime($payment['payment_period']))->format('F j, Y'); ?></p>
                            <p class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars($payment['remark']); ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-green-600">₹<?php echo number_format($payment['amount'], 2); ?></p>
                        <button data-id="<?php echo $payment['payment_id']; ?>" class="print-invoice-btn mt-1 text-xs text-blue-600 hover:underline">View Invoice</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // This script runs when the content is loaded.
    // All event handlers are delegated in index.php for robustness.
</script>
