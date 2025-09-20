<?php
session_start();
// Use the database connection from the included file
include '../database.php';

// Check if the user is logged in
if (!isset($_SESSION['tenant_id']) || $_SESSION['type_tenant'] !== 'tenant') {
    echo '<div class="text-center text-red-500 p-8">Please log in to view this page.</div>';
    exit();
}

$userId = $_SESSION['tenant_id'];

try {
    // Check if the database connection is successful
    if (!$conn) { // Assuming database.php defines $conn
        throw new Exception("Database connection failed.");
    }

    // Get all agreements for the tenant to determine the current one and if a future one exists.
    $sql_info = "SELECT p.pro_id, p.month_rent, ra.agreement_id, ra.starting_date, ra.ending_date, t.tenant_name
                 FROM tenants t
                 JOIN properties p ON t.property_id = p.pro_id
                 LEFT JOIN rental_agreements ra ON t.tenant_id = ra.tenant_id
                 WHERE t.tenant_id = ? AND t.status = 'Active'
                 ORDER BY ra.starting_date ASC"; // Order from oldest to newest
    $stmt_info = mysqli_prepare($conn, $sql_info);
    mysqli_stmt_bind_param($stmt_info, "i", $userId);
    mysqli_stmt_execute($stmt_info);
    $result_info = mysqli_stmt_get_result($stmt_info);

    $all_agreements = [];
    while ($row = mysqli_fetch_assoc($result_info)) {
        if ($row['agreement_id']) { // Only add if an agreement exists for the row
            $all_agreements[] = $row;
        }
    }
    mysqli_stmt_close($stmt_info);

    // Determine the current/most relevant agreement and check for future ones
    $today = new DateTime();
    $data = null; // This will hold the current agreement
    $future_agreement_exists = false;

    // Find an agreement that is currently active
    foreach ($all_agreements as $agreement) {
        if (new DateTime($agreement['starting_date']) <= $today && new DateTime($agreement['ending_date']) >= $today) {
            $data = $agreement;
            break;
        }
    }

    // If no agreement is currently active, take the most recent one (last in the array)
    if (!$data && !empty($all_agreements)) {
        $data = end($all_agreements);
    }

    // If we found a current agreement, check if a future one exists
    if ($data) {
        $current_agreement_end = new DateTime($data['ending_date']);
        foreach ($all_agreements as $agreement) {
            if (new DateTime($agreement['starting_date']) > $current_agreement_end) {
                $future_agreement_exists = true;
                break;
            }
        }
    }

    if (!$data || !$data['agreement_id']) {
        echo '<div class="bg-white p-8 rounded-2xl shadow-lg text-center">';
        echo '<h2 class="text-2xl font-bold text-gray-800 mb-4">No Rent Information</h2>';
        echo '<p class="text-gray-600">We could not find any active rental information for your account.</p>';
        echo '</div>';
        mysqli_close($conn);
        exit();
    }

    $propertyId = $data['pro_id'];
    $monthlyRent = $data['month_rent'];
    $tenantName = $data['tenant_name'];
    $agreementId = $data['agreement_id'];
    $leaseStartDateString = $data['starting_date'];
    $leaseEndDateString = $data['ending_date'];
    $leaseStartDate = $leaseStartDateString ? new DateTime($leaseStartDateString) : null;
    $leaseEndDate = $leaseEndDateString ? new DateTime($leaseEndDateString) : null;
    $leaseEndDateDisplay = $leaseEndDate ? $leaseEndDate->format('F j, Y') : 'Not Available';

    // --- NEW: Logic to determine if re-application is needed ---
    $showModificationButton = false;
    $showReapplyButton = false;
    if ($leaseStartDate && $leaseEndDate) {
        $today = new DateTime();
        $daysUntilEnd = $today > $leaseEndDate ? -1 : $today->diff($leaseEndDate)->days;

        // Calculate total duration in months
        $interval = $leaseStartDate->diff($leaseEndDate, true); // Use true for absolute difference
        $totalMonths = $interval->y * 12 + $interval->m; // Approximate months

        // Conditions for re-application: lease is long or ending soon.
        $isReapplyConditionMet = ($totalMonths >= 11 || ($daysUntilEnd >= 0 && $daysUntilEnd <= 30));

        if ($future_agreement_exists) {
            // If a future agreement is scheduled, only allow modification of the current lease.
            $showModificationButton = true;
        } elseif ($isReapplyConditionMet) {
            // If no future agreement, show re-apply if conditions are met.
            $showReapplyButton = true;
        } else {
            // Otherwise, show the modification button.
            $showModificationButton = true;
        }
    }

    // --- NEW: Proration Logic ---
    function getProratedRent($monthlyRent, $leaseStartDateStr, $leaseEndDateStr, $paymentPeriodStr) {
        if (!$paymentPeriodStr) {
            return $monthlyRent;
        }

        try {
            $paymentDate = new DateTime('first day of ' . $paymentPeriodStr);
            $daysInMonth = (int)$paymentDate->format('t');
            $isProrated = false;
            $daysToCharge = $daysInMonth;

            $leaseStartDate = $leaseStartDateStr ? new DateTime($leaseStartDateStr) : null;
            $leaseEndDate = $leaseEndDateStr ? new DateTime($leaseEndDateStr) : null;

            // Case 1: Lease starts and ends in the same month as the payment period.
            if ($leaseStartDate && $leaseEndDate &&
                $paymentDate->format('Y-m') === $leaseStartDate->format('Y-m') &&
                $paymentDate->format('Y-m') === $leaseEndDate->format('Y-m')) {

                $startDay = (int)$leaseStartDate->format('d');
                $endDay = (int)$leaseEndDate->format('d');
                $daysToCharge = $endDay - $startDay + 1;
                $isProrated = true;
            }
            // Case 2: Lease starts in the payment period month (but doesn't end in it).
            else if ($leaseStartDate && $paymentDate->format('Y-m') === $leaseStartDate->format('Y-m')) {
                $startDay = (int)$leaseStartDate->format('d');
                if ($startDay > 1) {
                    $daysToCharge = $daysInMonth - $startDay + 1;
                    $isProrated = true;
                }
            }
            // Case 3: Lease ends in the payment period month.
            else if ($leaseEndDate && $paymentDate->format('Y-m') === $leaseEndDate->format('Y-m')) {
                $endDay = (int)$leaseEndDate->format('d');
                if ($endDay < $daysInMonth) {
                    $daysToCharge = $endDay;
                    $isProrated = true;
                }
            }

            if ($isProrated) {
                // Ensure days to charge is not negative
                $daysToCharge = max(0, $daysToCharge);
                return round(($monthlyRent / $daysInMonth) * $daysToCharge, 2);
            }

        } catch (Exception $e) {
            // If date parsing fails, fall back to full rent to avoid errors.
            return $monthlyRent;
        }
        return $monthlyRent;
    }
    // --- NEW LOGIC to find next available payment month ---

    // 1. Fetch all paid periods for this tenant
    $sql_all_payments = "SELECT payment_period FROM payments WHERE tenant_id = ? AND status = 'Paid'";
    $stmt_all_payments = mysqli_prepare($conn, $sql_all_payments);
    mysqli_stmt_bind_param($stmt_all_payments, "i", $userId);
    mysqli_stmt_execute($stmt_all_payments);
    $allPaymentsResult = mysqli_stmt_get_result($stmt_all_payments);

    $paidMonths = []; // Store as 'Y-m' for easy lookup
    while ($payment = mysqli_fetch_assoc($allPaymentsResult)) {
        $paidMonths[date('Y-m', strtotime($payment['payment_period']))] = true;
    }
    mysqli_stmt_close($stmt_all_payments);

    // 2. Determine current month status
    $currentPeriodDisplay = date('F Y');
    $currentMonthYm = date('Y-m');
    $currentMonthStatus = isset($paidMonths[$currentMonthYm]) ? 'Paid' : 'Due';

    // 3. Find the next available month for advance payment
    $advancePeriodForCard = null;
    $nextMonth = new DateTime('first day of next month');

    // Loop to find the first unpaid month, respecting the lease end date
    while (true) {
        // Stop if we are past the lease end date
        if ($leaseEndDate && $nextMonth > $leaseEndDate) {
            break;
        }
        
        $nextMonthYm = $nextMonth->format('Y-m');
        
        // If this month is not paid, we found our advance payment month
        if (!isset($paidMonths[$nextMonthYm])) {
            $advancePeriodForCard = $nextMonth->format('F Y');
            break;
        }
        
        // Otherwise, move to the next month
        $nextMonth->modify('+1 month');
    }

    // 4. Determine the "Next Payment Due" for display
    $nextPaymentDateForDisplay = 'N/A';
    if ($currentMonthStatus === 'Due') {
        $nextPaymentDateForDisplay = $currentPeriodDisplay;
    } elseif ($advancePeriodForCard) {
        $nextPaymentDateForDisplay = $advancePeriodForCard;
    } else {
        // All possible months are paid up to lease end
        $nextPaymentDateForDisplay = 'All rent paid';
    }

    // --- NEW: Calculate potentially prorated amounts for display ---
    $currentMonthRent = getProratedRent($monthlyRent, $leaseStartDateString, $leaseEndDateString, $currentPeriodDisplay);
    $advanceMonthRent = $advancePeriodForCard ? getProratedRent($monthlyRent, $leaseStartDateString, $leaseEndDateString, $advancePeriodForCard) : $monthlyRent;

    // Close the connection only once, at the very end
    mysqli_close($conn);
} catch (Exception $e) {
    echo '<div class="text-center text-red-500 p-8">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit();
}
?>

<div class="space-y-8 animate-slide-up-fade-in">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Pay Rent</h2>
            <p class="text-gray-600 mt-1">Review your payment status and manage your lease.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Left Column: Payment Cards -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Current Month Card -->
            <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Current Month's Rent</h3>
                <div class="flex justify-between items-center border-b pb-4">
                    <div>
                        <p class="text-sm text-gray-500">Monthly Rent Amount</p>
                        <p class="text-4xl font-bold text-blue-600">₹<?= number_format($currentMonthRent, 2) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">For <?php echo htmlspecialchars($currentPeriodDisplay); ?></p>
                        <span class="px-3 py-1 mt-1 inline-flex text-sm font-semibold rounded-full <?php echo ($currentMonthStatus === 'Paid') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo htmlspecialchars($currentMonthStatus); ?>
                        </span>
                    </div>
                </div>
                <?php
                    $_SESSION['property_id'] = $propertyId;
                    $_SESSION['tenant_id'] = $userId;
                ?>
                <?php if ($currentMonthStatus === 'Due') : ?>
                    <div class="mt-6 flex justify-center items-center">
                        <a href="../PaymentGateway/index.php?period=<?php echo urlencode($currentPeriodDisplay); ?>" class="pay-rent-btn w-full bg-blue-600 text-white text-center font-bold py-3 px-20 rounded-lg shadow-md hover:bg-blue-700 transition duration-300 ease-in-out"
                            data-rent="<?php echo htmlspecialchars($currentMonthRent); ?>"
                            data-period="<?php echo htmlspecialchars($currentPeriodDisplay); ?>">
                            Pay Now (₹<?= number_format($currentMonthRent, 2) ?>)
                        </a>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-green-700 mt-4">Rent for <?php echo htmlspecialchars($currentPeriodDisplay); ?> has been paid. Thank you!</p>
                <?php endif; ?>
            </div>

            <!-- Next Month Card -->
            <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Advance Rent Payment</h3>
                <div class="flex justify-between items-center">
                    <?php if ($advancePeriodForCard): ?>
                        <div>
                            <p class="text-sm text-gray-500">Pay in advance for</p>
                            <p class="text-2xl font-bold text-gray-700"><?php echo htmlspecialchars($advancePeriodForCard); ?></p>
                        </div>
                        <a href="../PaymentGateway/index.php?period=<?php echo urlencode($advancePeriodForCard); ?>" class="pay-rent-btn bg-gray-800 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:bg-gray-900 transition duration-300 ease-in-out"
                            data-rent="<?php echo htmlspecialchars($advanceMonthRent); ?>"
                            data-period="<?php echo htmlspecialchars($advancePeriodForCard); ?>">
                            Pay Advance (₹<?= number_format($advanceMonthRent, 2) ?>)
                        </a>
                    <?php else: ?>
                        <div class="text-center w-full">
                            <p class="text-sm text-green-700">All upcoming rent up to the end of your lease has been paid. Thank you!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Lease Management -->
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 h-full flex flex-col">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Lease Management</h3>
                <div class="flex-grow space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Lease End Date</p>
                        <p class="text-lg font-semibold text-gray-800"><?php echo $leaseEndDateDisplay; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Next Payment Due</p>
                        <p class="text-lg font-semibold text-gray-800"><?php echo $nextPaymentDateForDisplay; ?></p>
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <?php if ($showReapplyButton): ?>
                        <button id="re-apply-agreement-btn"
                           data-agreement-id="<?php echo htmlspecialchars($agreementId); ?>"
                           data-ending-date="<?php echo htmlspecialchars($leaseEndDateString); ?>"
                           class="block w-full text-center bg-green-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-green-700 transition duration-300 ease-in-out">
                            Re-apply for New Agreement
                        </button>
                        <p class="text-xs text-center text-gray-500 mt-1">Your current agreement is near its 11-month limit or has expired.</p>
                    <?php elseif ($showModificationButton): ?>
                        <button id="request-lease-modification-btn"
                            data-current-end-date="<?php echo htmlspecialchars($data['ending_date']); ?>"
                            data-start-date="<?php echo htmlspecialchars($data['starting_date']); ?>"
                            class="w-full bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-lg hover:bg-gray-300 transition duration-300 ease-in-out">
                            Request Lease Modification
                        </button>
                    <?php endif; ?>
                    <button id="request-lease-termination-btn" class="w-full bg-red-100 text-red-700 font-semibold py-3 px-4 rounded-lg hover:bg-red-200 transition duration-300 ease-in-out">
                        Request Lease Termination
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    ;
    // // document.addEventListener('DOMContentLoaded', () => {
    //     // Use event delegation for buttons that might be added dynamically or for multiple buttons
    //     document.body.addEventListener('click', function(event) {
    //         if (event.target.classList.contains('pay-rent-btn')) {
    //             const button = event.target;
    //             const rentAmount = button.getAttribute('data-rent');
    //             const paymentPeriod = button.getAttribute('data-period');

    //             const isConfirmed = confirm(`Are you sure you want to pay ₹${parseFloat(rentAmount).toLocaleString('en-IN')} for ${paymentPeriod}?`);

    //             if (isConfirmed) {
    //                 button.textContent = 'Processing...';
    //                 button.disabled = true;

    //                 fetch('process-payment.php', {
    //                         method: 'POST',
    //                         headers: {
    //                             'Content-Type': 'application/json'
    //                         },
    //                         body: JSON.stringify({
    //                             amount: rentAmount,
    //                             period: paymentPeriod,
    //                             property_id: <?php echo json_encode($propertyId); ?>
    //                         })
    //                     })
    //                     .then(response => response.json())
    //                     .then(data => {
    //                         if (data.success) {
    //                             alert('Payment submitted successfully!');
    //                             // Reload the content of the pay-rent page to show the new status
    //                             document.querySelector('.sidebar-item[data-page="pay-rent"]').click();
    //                         } else {
    //                             alert('Payment failed: ' + (data.message || 'Unknown error'));
    //                             button.textContent = 'Pay Now'; // Reset button text on failure
    //                             button.disabled = false;
    //                         }
    //                     })
    //                     .catch(error => {
    //                         console.error('Error:', error);
    //                         alert('An error occurred during the payment process.');
    //                         button.disabled = false;
    //                     });
    //             }
    //         }
    //     });
    // });
</script>