<?php
// This file is loaded via AJAX, so it has access to the session
session_start();

// Include the database connection file.
// The file should contain a mysqli connection, e.g., $conn = mysqli_connect(...)
include 'database.php';

// Check if the user is logged in
if (!isset($_SESSION['tenant_id'])) {
    echo '<div class="text-center text-red-500 p-8">Please log in to view this page.</div>';
    exit();
}

// Get the tenant ID from the session
$tenantId = $_SESSION['tenant_id'];
$tenantDetails = null;

// Check if the database connection is successful
if (!$conn) {
    echo '<div class="text-center text-red-500 p-8">Database connection failed.</div>';
    exit();
}

// SQL query to fetch tenant and property details
// Added 't.lease_start_date' and 't.lease_end_date' to the SELECT statement
$sql = "SELECT 
            ra.tenantName, 
            ra.number, 
            ra.email, 
            ra.starting_date,
            ra.ending_date,
            p.pro_nm, 
            p.month_rent
        FROM rental_agreements ra
        JOIN properties p ON ra.pro_id = p.pro_id
        WHERE ra.tenant_id = ?";

// Prepare the statement to prevent SQL injection
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    // Bind the tenant ID to the prepared statement
    mysqli_stmt_bind_param($stmt, "i", $tenantId);
    
    // Execute the statement
    mysqli_stmt_execute($stmt);
    
    // Get the result
    $result = mysqli_stmt_get_result($stmt);

    // Fetch the single row of results
    $tenantDetails = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

} else {
    echo '<div class="text-center text-red-500 p-8">Failed to prepare the SQL statement: ' . mysqli_error($conn) . '</div>';
    exit();
}
?>

<div class="space-y-8 animate-slide-up-fade-in">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Tenancy Details</h2>
            <p class="text-gray-600 mt-1">A summary of your current rental agreement and property information.</p>
        </div>
    </div>

    <?php if ($tenantDetails && !empty($tenantDetails['starting_date'])) : ?>
        <?php
            $startDate = new DateTime($tenantDetails['starting_date']);
            $endDate = new DateTime($tenantDetails['ending_date']);
            $today = new DateTime();
            
            $totalDuration = $startDate->diff($endDate)->days;
            $elapsedDuration = $startDate->diff($today)->days;
            
            if ($elapsedDuration < 0) $elapsedDuration = 0;
            if ($elapsedDuration > $totalDuration) $elapsedDuration = $totalDuration;
            
            $progressPercentage = ($totalDuration > 0) ? ($elapsedDuration / $totalDuration) * 100 : 0;
        ?>
        <div class="relative bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-2xl p-6 shadow-lg overflow-hidden">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/10 rounded-full opacity-50"></div>
            <div class="absolute -left-16 -bottom-20 w-56 h-56 bg-white/10 rounded-full opacity-50"></div>
            <div class="relative z-10">
                <h3 class="text-xl font-semibold mb-4">Lease Period</h3>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-center sm:text-left">
                        <p class="text-sm text-blue-200">Start Date</p>
                        <p class="text-lg font-bold"><?php echo $startDate->format('F j, Y'); ?></p>
                    </div>
                    <div class="text-blue-300 text-2xl hidden sm:block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </div>
                    <div class="text-center sm:text-right">
                        <p class="text-sm text-blue-200">End Date</p>
                        <p class="text-lg font-bold"><?php echo $endDate->format('F j, Y'); ?></p>
                    </div>
                </div>
                <div class="w-full bg-white/20 rounded-full h-2.5 mt-4">
                    <div class="bg-white h-2.5 rounded-full" style="width: <?php echo $progressPercentage; ?>%"></div>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="bg-gray-100 border-l-4 border-gray-400 text-gray-700 p-4 rounded-r-lg">
            <p class="font-bold">Lease Information</p>
            <p>Lease details are not available. Please contact your landlord for more information.</p>
        </div>
    <?php endif; ?>

    <?php if ($tenantDetails) : ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-3"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>Your Information</h3>
            <div class="space-y-4 text-gray-700 text-sm">
                <div class="flex justify-between items-center border-b pb-2"><span class="text-gray-500">Name</span><span class="font-semibold text-gray-800"><?php echo htmlspecialchars($tenantDetails['tenantName']); ?></span></div>
                <div class="flex justify-between items-center border-b pb-2"><span class="text-gray-500">Email</span><span class="font-semibold text-gray-800"><?php echo htmlspecialchars($tenantDetails['email']); ?></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-500">Contact</span><span class="font-semibold text-gray-800"><?php echo htmlspecialchars($tenantDetails['number']); ?></span></div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-3"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>Property Details</h3>
            <div class="space-y-4 text-gray-700 text-sm">
                <div class="flex justify-between items-center border-b pb-2"><span class="text-gray-500">Property</span><span class="font-semibold text-gray-800"><?php echo htmlspecialchars($tenantDetails['pro_nm']); ?></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-500">Monthly Rent</span><span class="font-semibold text-gray-800">₹<?php echo number_format($tenantDetails['month_rent'], 2); ?> / month</span></div>
            </div>
        </div>
    </div>
    
    <?php else : ?>
        <div class="text-center text-gray-500 p-8">No active tenancy details found for this account.</div>
    <?php endif; ?>
</div>