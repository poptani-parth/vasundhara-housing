<?php
session_start();
include '../database.php'; // Connect to the database
//tenant id from database
$tenant_id = $_SESSION['tenant_id'] ?? null;
$property_id = $_SESSION['property_id'] ?? null;
$rent_amount = 0;
$lease_start_date = null;
$lease_end_date = null;

// Check if it's a monthly rent payment vs initial booking
$is_monthly_payment = isset($_GET['period']);

if ($property_id && $conn) {
    // Fetch property rent
    $stmt = $conn->prepare("SELECT month_rent FROM properties WHERE pro_id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($property = $result->fetch_assoc()) {
        $rent_amount = $property['month_rent'];
    }
    $stmt->close();

    // Fetch lease start and end date for the tenant to check for proration
    if ($tenant_id) {
        $stmt_lease = $conn->prepare("SELECT starting_date, ending_date FROM rental_agreements WHERE tenant_id = ?");
        $stmt_lease->bind_param("i", $tenant_id);
        $stmt_lease->execute();
        $result_lease = $stmt_lease->get_result();
        if ($lease = $result_lease->fetch_assoc()) {
            $lease_start_date = $lease['starting_date'];
            $lease_end_date = $lease['ending_date'];
        }
        $stmt_lease->close();
    }
}

if ($is_monthly_payment) {
    $payment_period_string = $_GET['period'];
    $total_amount = $rent_amount; // Default to full rent

    // --- NEW: Apply proration if applicable ---
    if (($lease_start_date || $lease_end_date) && $payment_period_string) {
        try {
            $paymentDate = new DateTime('first day of ' . $payment_period_string);
            $daysInMonth = (int)$paymentDate->format('t');
            $isProrated = false;
            $daysToCharge = $daysInMonth;

            $leaseStartDateObj = $lease_start_date ? new DateTime($lease_start_date) : null;
            $leaseEndDateObj = $lease_end_date ? new DateTime($lease_end_date) : null;

            // Case 1: Lease starts and ends in the same month as the payment period.
            if ($leaseStartDateObj && $leaseEndDateObj &&
                $paymentDate->format('Y-m') === $leaseStartDateObj->format('Y-m') &&
                $paymentDate->format('Y-m') === $leaseEndDateObj->format('Y-m')) {
                
                $startDay = (int)$leaseStartDateObj->format('d');
                $endDay = (int)$leaseEndDateObj->format('d');
                $daysToCharge = $endDay - $startDay + 1;
                $isProrated = true;
            }
            // Case 2: Lease starts in the payment period month (but doesn't end in it).
            else if ($leaseStartDateObj && $paymentDate->format('Y-m') === $leaseStartDateObj->format('Y-m')) {
                $startDay = (int)$leaseStartDateObj->format('d');
                if ($startDay > 1) {
                    $daysToCharge = $daysInMonth - $startDay + 1;
                    $isProrated = true;
                }
            }
            // Case 3: Lease ends in the payment period month.
            else if ($leaseEndDateObj && $paymentDate->format('Y-m') === $leaseEndDateObj->format('Y-m')) {
                $endDay = (int)$leaseEndDateObj->format('d');
                if ($endDay < $daysInMonth) {
                    $daysToCharge = $endDay;
                    $isProrated = true;
                }
            }

            if ($isProrated) {
                $daysToCharge = max(0, $daysToCharge);
                $total_amount = round(($rent_amount / $daysInMonth) * $daysToCharge, 2);
            }
        } catch (Exception $e) {
            // In case of a date parsing error, we'll just use the full rent amount as a safe fallback.
        }
    }
    $transaction_note = 'Rent for ' . ($_GET['period'] ?? 'this month');
} else {
    // Logic for initial booking (deposit + first month's rent)
    $total_amount = ($rent_amount / 10) + $rent_amount;
    $transaction_note = 'Property Booking';
    $payment_period_string = date('F Y'); // For initial booking, it's the current month.
}

// Set session variables for the payment handler to use.
$_SESSION['payment_amount'] = $total_amount;
$_SESSION['payment_period'] = $payment_period_string;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway</title>
    <script src="../assets/js/html2pdf.bundle.min.js"></script>
    <script src="../assets/js/tailwind.js"></script>
    <link href="../assets/css/tailwind.css" rel="stylesheet"/>
    <link href="../assets/css/all.min.css" rel="stylesheet"/>
    <link href="../assets/css/google_fonts.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/fonts/interFont.css">
    <link rel="stylesheet" href="./style.css">
    <link href="../assets/RemixIcon-master/fonts/remixicon.css" rel="stylesheet"/>
    <script src="../assets/js/qrcode.min.js"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .payment-option-container {
            border: 1px solid #e5e7eb; /* border-gray-200 */
            border-radius: 0.75rem; /* rounded-xl */
            transition: all 0.2s ease-in-out;
        }
        .payment-option-label {
            display: flex;
            align-items: center;
            gap: 1rem; /* gap-4 */
            padding: 1rem; /* p-4 */
            cursor: pointer;
            font-size: 1.125rem; /* text-lg */
        }
        .payment-option-content {
            padding: 1.5rem; /* p-6 */
            border-top: 1px solid #e5e7eb; /* border-t border-gray-200 */
            background-color: #f9fafb; /* bg-gray-50 */
        }
        .peer:checked ~ .payment-option-label {
            border-color: #3b82f6; /* border-blue-500 */
            background-color: #eff6ff; /* bg-blue-50 */
        }
    </style>
</head>
<body class="bg-gray-50">

    <div class="container mx-auto px-4 py-8 md:py-12">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Secure Checkout</h1>
            <p class="mt-2 text-lg text-gray-500">Complete your payment for Vasundhara Housing</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Payment Methods -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg border border-gray-200 p-6 md:p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Select a Payment Method</h2>
                
                <div class="space-y-4">
                    <!-- UPI Option -->
                    <div class="payment-option-container">
                        <input type="radio" name="payment-method-selector" id="select-upi" class="hidden peer" value="upi" checked>
                        <label for="select-upi" class="payment-option-label">
                            <i class="fab fa-google-pay text-3xl text-gray-600"></i>
                            <span class="font-semibold">Pay with UPI / QR Code</span>
                            <i class="fas fa-check-circle text-blue-600 text-xl hidden peer-checked:block ml-auto"></i>
                        </label>
                        <div class="payment-option-content hidden peer-checked:block">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                                <!-- QR Code Side -->
                                <div class="flex flex-col items-center text-center p-4 bg-gray-100 rounded-lg border">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Scan & Pay</h3>
                                    <div id="qrcode" class="p-2 border-4 border-gray-300 rounded-lg bg-white inline-block shadow-md"></div>
                                    <p class="mt-3 text-sm font-semibold text-gray-600">UPI ID: 7201918520@naviaxis</p>
                                    <form id="qr-payment-form" action="payment_handler.php" method="POST" class="hidden">
                                        <input type="hidden" name="payment-method" value="upi-qr-simulation">
                                    </form>
                                    <p class="mt-4 text-xs text-gray-500">After scanning, you will be prompted to pay ₹<?php echo number_format($total_amount); ?>.</p>
                                    <button id="simulate-qr-payment" class="mt-4 w-full bg-gray-200 text-gray-700 font-bold py-2 rounded-lg hover:bg-gray-300 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm">
                                        Simulate Successful QR Payment
                                    </button>
                                </div>

                                <!-- UPI ID Side -->
                                <div class="flex flex-col justify-center">
                                    <div class="relative md:hidden mb-6">
                                        <div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="w-full border-t border-gray-300"></div></div>
                                        <div class="relative flex justify-center"><span class="bg-gray-50 px-2 text-sm text-gray-500">OR</span></div>
                                    </div>
                                    <form id="upi-form" action="payment_handler.php" method="POST" class="w-full space-y-4">
                                        <input type="hidden" name="payment-method" value="upi">
                                        <div>
                                            <label for="upi_id" class="block text-sm font-medium text-gray-700">Pay using UPI ID</label>
                                            <input type="text" name="upi_id" id="upi_id" placeholder="yourname@bank" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm p-3 text-base focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition-colors shadow-lg text-lg">
                                            Pay ₹<?php echo number_format($total_amount); ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Option -->
                    <div class="payment-option-container">
                        <input type="radio" name="payment-method-selector" id="select-card" class="hidden peer" value="card">
                        <label for="select-card" class="payment-option-label">
                            <i class="fas fa-credit-card text-2xl text-gray-600"></i>
                            <span class="font-semibold">Credit / Debit Card</span>
                            <i class="fas fa-check-circle text-blue-600 text-xl hidden peer-checked:block ml-auto"></i>
                        </label>
                        <div class="payment-option-content hidden peer-checked:block">
                            <form id="card-form" action="payment_handler.php" method="POST" class="space-y-4">
                                <input type="hidden" name="payment-method" value="card">
                                <div>
                                    <label for="card-number" class="block text-sm font-medium text-gray-700">Card Number</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <i class="fas fa-credit-card text-gray-400"></i>
                                        </div>
                                        <input type="text" id="card-number" name="card-number" maxlength="19" class="block w-full rounded-lg border-gray-300 shadow-sm p-3 pl-10 text-base focus:ring-blue-500 focus:border-blue-500" placeholder="0000 0000 0000 0000" required>
                                    </div>
                                </div>
                                <div class="flex space-x-4">
                                    <div class="flex-1">
                                        <label for="expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <i class="fas fa-calendar-alt text-gray-400"></i>
                                            </div>
                                            <input type="text" id="expiry" name="expiry" maxlength="5" class="block w-full rounded-lg border-gray-300 shadow-sm p-3 pl-10 text-base focus:ring-blue-500 focus:border-blue-500" placeholder="MM/YY" required>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <label for="cvc" class="block text-sm font-medium text-gray-700">CVC</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <i class="fas fa-lock text-gray-400"></i>
                                            </div>
                                            <input type="text" id="cvc" name="cvc" maxlength="4" class="block w-full rounded-lg border-gray-300 shadow-sm p-3 pl-10 text-base focus:ring-blue-500 focus:border-blue-500" placeholder="123" required>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="cardholder-name" class="block text-sm font-medium text-gray-700">Cardholder Name</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                        <input type="text" id="cardholder-name" name="cardholder-name" class="block w-full rounded-lg border-gray-300 shadow-sm p-3 pl-10 text-base focus:ring-blue-500 focus:border-blue-500" placeholder="Poptani Parth" required>
                                    </div>
                                </div>
                                <div class="pt-2">
                                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition-colors shadow-lg text-lg">
                                        Pay ₹<?php echo number_format($total_amount); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Net Banking Option -->
                    <div class="payment-option-container">
                        <input type="radio" name="payment-method-selector" id="select-netbanking" class="hidden peer" value="netbanking">
                        <label for="select-netbanking" class="payment-option-label">
                            <i class="fas fa-university text-2xl text-gray-600"></i>
                            <span class="font-semibold">Net Banking</span>
                            <i class="fas fa-check-circle text-blue-600 text-xl hidden peer-checked:block ml-auto"></i>
                        </label>
                        <div class="payment-option-content hidden peer-checked:block">
                            <form id="netbanking-form" action="payment_handler.php" method="POST" class="space-y-4">
                                <input type="hidden" name="payment-method" value="netbanking">
                                <div>
                                    <label for="netbanking-bank" class="block text-sm font-medium text-gray-700">Select Your Bank</label>
                                    <select id="netbanking-bank" name="netbanking-bank" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-3 text-base focus:ring-blue-500 focus:border-blue-500 transition" required>
                                        <option value="">-- Select Bank --</option>
                                        <option value="sbi">State Bank of India</option>
                                        <option value="icici">ICICI Bank</option>
                                        <option value="hdfc">HDFC Bank</option>
                                        <option value="axis">Axis Bank</option>
                                        <option value="pnb">Punjab National Bank</option>
                                        <option value="other">Other Bank</option>
                                    </select>
                                </div>
                                <div id="ifsc-container" class="hidden transition-all duration-300">
                                    <div id="other-bank-container" class="hidden transition-all duration-300">
                                        <label for="other_bank_name" class="block text-sm font-medium text-gray-700">Bank Name</label>
                                        <input type="text" id="other_bank_name" name="other_bank_name" placeholder="Enter your bank's name" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-3 text-base focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="mt-4">
                                        <label for="ifsc_code" class="block text-sm font-medium text-gray-700">IFSC Code</label>
                                        <input type="text" id="ifsc_code" name="ifsc_code" placeholder="Enter bank's IFSC code" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-3 text-base focus:ring-blue-500 focus:border-blue-500 uppercase">
                                        <p id="ifsc-error" class="text-red-500 text-xs mt-1 hidden">IFSC code does not match the selected bank.</p>
                                    </div>
                                </div>
                                <div class="pt-2">
                                    <button type="submit" id="netbanking-submit-btn" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition-colors shadow-lg text-lg disabled:bg-gray-400 disabled:cursor-not-allowed">
                                        Pay ₹<?php echo number_format($total_amount); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 sticky top-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Order Summary</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between text-gray-600">
                            <span>Property Rent</span>
                            <span class="font-medium">₹<?php echo number_format($rent_amount); ?></span>
                        </div>
                        <?php if (!$is_monthly_payment): ?>
                        <div class="flex justify-between text-gray-600">
                            <span>Security Deposit</span>
                            <span class="font-medium">₹<?php echo number_format($rent_amount / 10); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="border-t border-gray-200 my-4"></div>
                        <div class="flex justify-between font-bold text-xl text-gray-800">
                            <span>Total Amount</span>
                            <span>₹<?php echo number_format($total_amount); ?></span>
                        </div>
                    </div>
                    <div class="mt-6 text-xs text-gray-400 text-center">
                        <i class="fas fa-lock"></i> All transactions are secure and encrypted.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // QR Code Generation
            const upiString = `upi://pay?pa=7201918520@naviaxis&pn=Vasundhara%20Housing&am=<?php echo $total_amount; ?>.00&cu=INR&tn=<?php echo urlencode($transaction_note); ?>`;
            new QRCode(document.getElementById("qrcode"), {
                text: upiString,
                width: 200,
                height: 200,
            });

            // QR Payment Simulation
            const simulateBtn = document.getElementById('simulate-qr-payment');
            const qrForm = document.getElementById('qr-payment-form');

            simulateBtn.addEventListener('click', () => {
                // Disable the button to prevent multiple clicks
                simulateBtn.disabled = true;

                let countdown = 10;
                // Add a spinner icon and initial countdown text
                simulateBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Processing... (${countdown}s)`;

                const interval = setInterval(() => {
                    countdown--;
                    simulateBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Processing... (${countdown}s)`;

                    if (countdown <= 0) {
                        clearInterval(interval);
                        simulateBtn.innerHTML = 'Redirecting to confirmation...';
                        qrForm.submit();
                    }
                }, 1000); // 1 second interval
            });

            // --- Card Form Auto-formatting ---
            const cardNumberInput = document.getElementById('card-number');
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function (e) {
                    let value = e.target.value.replace(/\D/g, '');
                    let formattedValue = '';
                    for (let i = 0; i < value.length; i++) {
                        if (i > 0 && i % 4 === 0) {
                            formattedValue += ' ';
                        }
                        formattedValue += value[i];
                    }
                    e.target.value = formattedValue;
                });
            }

            const expiryInput = document.getElementById('expiry');
            if (expiryInput) {
                expiryInput.addEventListener('input', function (e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 2) {
                        value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    }
                    e.target.value = value;
                });
            }

            // --- Net Banking IFSC Validation ---
            const bankSelect = document.getElementById('netbanking-bank');
            const ifscContainer = document.getElementById('ifsc-container');
            const ifscInput = document.getElementById('ifsc_code');
            const ifscError = document.getElementById('ifsc-error');
            const netbankingSubmitBtn = document.getElementById('netbanking-submit-btn');
            const otherBankContainer = document.getElementById('other-bank-container');
            const otherBankInput = document.getElementById('other_bank_name');

            const bankIfscMap = {
                'sbi': 'SBIN', 'icici': 'ICIC', 'hdfc': 'HDFC',
                'axis': 'UTIB', 'pnb': 'PUNB'
            };

            function validateNetbankingForm() {
                const selectedBank = bankSelect.value;
                const ifscValue = ifscInput.value.toUpperCase();

                if (!selectedBank) {
                    netbankingSubmitBtn.disabled = true;
                    ifscError.classList.add('hidden');
                    return;
                }

                if (selectedBank === 'other') {
                    const bankName = otherBankInput.value.trim();
                    const ifscRegex = /^[A-Z]{4}0[A-Z0-9]{6}$/;
                    
                    if (!bankName || !ifscValue) {
                        netbankingSubmitBtn.disabled = true;
                        ifscError.classList.add('hidden');
                        return;
                    }

                    if (!ifscRegex.test(ifscValue)) {
                        ifscError.textContent = 'Invalid IFSC code format. Must be 11 characters (e.g., ABCD0123456).';
                        ifscError.classList.remove('hidden');
                        netbankingSubmitBtn.disabled = true;
                    } else {
                        ifscError.classList.add('hidden');
                        netbankingSubmitBtn.disabled = false;
                    }
                } else { // Known bank
                    if (!ifscValue) {
                        netbankingSubmitBtn.disabled = true;
                        ifscError.classList.add('hidden');
                        return;
                    }
                    const expectedPrefix = bankIfscMap[selectedBank];
                    const isValid = ifscValue.startsWith(expectedPrefix);
                    
                    ifscError.textContent = 'IFSC code does not match the selected bank.';
                    ifscError.classList.toggle('hidden', isValid);
                    netbankingSubmitBtn.disabled = !isValid;
                }
            }

            bankSelect.addEventListener('change', () => {
                const selectedValue = bankSelect.value;
                ifscInput.value = ''; // Clear IFSC on bank change
                otherBankInput.value = ''; // Clear other bank name on change

                if (selectedValue) {
                    ifscContainer.classList.remove('hidden');
                    otherBankContainer.classList.toggle('hidden', selectedValue !== 'other');
                } else {
                    ifscContainer.classList.add('hidden');
                    otherBankContainer.classList.add('hidden');
                }
                validateNetbankingForm();
            });

            ifscInput.addEventListener('input', validateNetbankingForm);
            otherBankInput.addEventListener('input', validateNetbankingForm);

            // Initial validation on page load in case of pre-filled forms
            validateNetbankingForm();
        });
    </script>
</body>
</html>
