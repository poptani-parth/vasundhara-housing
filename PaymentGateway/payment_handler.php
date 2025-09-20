<?php
session_start();
ob_start();
// database
include '../database.php';

// --- Determine return URL early ---
// This is done before any session data is cleared.
$return_url = '../index.php'; // Default return URL
$return_text = 'Return to Home';
if (isset($_SESSION['type_tenant']) && $_SESSION['type_tenant'] === 'tenant') {
    $return_url = '../tenant/index.php';
    $return_text = 'Return to Dashboard';
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}


// --- Get and Validate Input ---
$payment_method = $_POST['payment-method'] ?? null;

// These fields can be passed from the form. If not, we'll try to fetch them.
$tenant_id = $_SESSION['tenant_id'] ?? null;
$property_id = $_SESSION['property_id'] ?? null;
// Get amount and period from session, which was set on the payment selection page
$amount = $_SESSION['payment_amount'] ?? null;
$payment_period_string = $_SESSION['payment_period'] ?? date('F Y');
$payment_period_for_db = date('Y-m-01', strtotime($payment_period_string));

// Initialize variables for display and database
$status = 'error';
$message = 'Payment failed. Please try again.';
$db_status = 'Due';

// --- Main Processing Block ---

// We need a tenant_id and payment method to proceed.
if (!$payment_method || !$tenant_id) {
    $message = 'Invalid submission. Tenant ID and payment method are required.';
    goto render_page;
}

// Final validation to ensure we have all necessary data now.
if (!$property_id || !$amount || $amount <= 0) {
    $message = 'Could not determine a valid property or amount for payment. Your session may have expired. Please try again.';
    goto render_page;
}

// --- Simulate Payment Gateway ---
// In a real application, you would connect to a payment gateway API here.
// Simulate a payment success with a 90% chance.
$isSuccess = (rand(1, 10) > 1);

switch ($payment_method) {
    case 'upi-qr-simulation':
        if ($isSuccess) {
            $status = 'success';
            $message = 'QR Code payment was successful! Thank you.';
            $db_status = 'Paid';
        }
        break;
    case 'card':
        if ($isSuccess) {
            $status = 'success';
            $message = 'Card payment was successful! Thank you.';
            $db_status = 'Paid';
        }
        break;
    case 'upi':
        $upi_id = trim($_POST['upi_id'] ?? '');
        if (empty($upi_id)) {
            $message = 'UPI ID is required for UPI payments.';
        } elseif ($isSuccess) {
            $status = 'success';
            $message = 'UPI payment was successful! Thank you.';
            $db_status = 'Paid';
        }
        break;
    case 'netbanking':
        $netbanking_bank = trim($_POST['netbanking-bank'] ?? '');
        $ifsc_code = strtoupper(trim($_POST['ifsc_code'] ?? ''));
        $other_bank_name = trim($_POST['other_bank_name'] ?? '');

        if (empty($netbanking_bank)) {
            $message = 'A bank must be selected for Net Banking payments.';
            break;
        }

        if ($netbanking_bank === 'other') {
            if (empty($other_bank_name)) {
                $message = 'Bank Name is required when selecting "Other Bank".';
                break;
            }
            if (empty($ifsc_code)) {
                $message = 'IFSC Code is required.';
                break;
            }
            if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc_code)) {
                $message = 'The provided IFSC code has an invalid format.';
                break;
            }
        } else {
            $bankIfscMap = ['sbi' => 'SBIN', 'icici' => 'ICIC', 'hdfc' => 'HDFC', 'axis' => 'UTIB', 'pnb' => 'PUNB'];
            if (array_key_exists($netbanking_bank, $bankIfscMap) && (empty($ifsc_code) || strpos($ifsc_code, $bankIfscMap[$netbanking_bank]) !== 0)) {
                $message = 'The provided IFSC code does not match the selected bank.';
                break;
            }
        }

        if ($isSuccess) {
            $status = 'success';
            $message = 'Net Banking payment was successful! Thank you.';
            $db_status = 'Paid';
        }
        break;
    case 'wallet':
        $wallet_provider = trim($_POST['wallet_provider'] ?? '');
        if (empty($wallet_provider)) {
            $message = 'A wallet provider must be selected for wallet payments.';
        } elseif ($isSuccess) {
            $status = 'success';
            $message = 'Wallet payment was successful! Thank you.';
            $db_status = 'Paid';
        }
        break;
    default:
        $message = 'Unknown payment method selected.';
        break;
}

// --- Database Insertion ---
// We attempt to record every transaction attempt.
$remark = "Rent for " . htmlspecialchars($payment_period_string);

// Append specific payment details to the remark for reference, keeping payment_method clean.
$payment_details_for_remark = '';
$base_payment_method = $payment_method; // Keep the original method name

switch ($base_payment_method) {
    case 'upi-qr-simulation':
        $payment_details_for_remark = " (QR Code)";
        break;
    case 'card':
        $card_number = $_POST['card-number'] ?? null;
        if ($card_number) {
            $last_four = substr(str_replace(' ', '', $card_number), -4);
            $payment_details_for_remark = " (Card ending in XXXX " . $last_four . ")";
        }
        break;
    case 'upi':
        $upi_id = trim($_POST['upi_id'] ?? '');
        if ($upi_id) {
            $payment_details_for_remark = " (UPI ID: " . htmlspecialchars($upi_id) . ")";
        }
        break;
    case 'netbanking':
        $netbanking_bank = $_POST['netbanking-bank'] ?? null;
        $ifsc_code = $_POST['ifsc_code'] ?? null;
        $other_bank_name = $_POST['other_bank_name'] ?? null;

        $bank_display_name = '';
        if ($netbanking_bank === 'other' && !empty($other_bank_name)) {
            $bank_display_name = htmlspecialchars($other_bank_name);
        } elseif ($netbanking_bank !== 'other' && !empty($netbanking_bank)) {
            $bank_display_name = ucfirst(htmlspecialchars($netbanking_bank));
        }

        if (!empty($bank_display_name)) {
            $payment_details_for_remark = " (Bank: " . $bank_display_name;
            if ($ifsc_code) {
                $payment_details_for_remark .= " / IFSC: " . htmlspecialchars($ifsc_code);
            }
            $payment_details_for_remark .= ")";
        }
        break;
    case 'wallet':
        $wallet_provider = $_POST['wallet_provider'] ?? null;
        if ($wallet_provider) {
            $payment_details_for_remark = " (Wallet: " . ucfirst(htmlspecialchars($wallet_provider)) . ")";
        }
        break;
}
if (!empty($payment_details_for_remark)) {
    $remark .= $payment_details_for_remark;
}

try {
    // Add payment_date to the INSERT statement and use CURDATE() for its value.
    $stmt = $conn->prepare("INSERT INTO payments (tenant_id, property_id, amount, payment_period, remark, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    // Bind parameters for the insert statement. Use 'i' for IDs and 'd' for amount (double/decimal).
    $stmt->bind_param("iidssss", $tenant_id, $property_id, $amount, $payment_period_for_db, $remark, $base_payment_method, $db_status);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $stmt->close();
    // Unset only the temporary payment-related session variables.
    // Using session_unset() would log the user out.
    unset($_SESSION['property_id'], $_SESSION['payment_period'], $_SESSION['payment_amount']);
    
    // Only mark property as unavailable if payment was successful
    if ($db_status === 'Paid') {
        $updatePropertyStmt = $conn->prepare("UPDATE properties SET status = 'Unavailable' WHERE pro_id = ?");
        if ($updatePropertyStmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $updatePropertyStmt->bind_param("i", $property_id);
        if (!$updatePropertyStmt->execute()) {
            throw new Exception("Execute failed: " . $updatePropertyStmt->error);
        }
        $updatePropertyStmt->close();
    }
} catch (Exception $e) {
    // Log the error for the admin and update the user message
    error_log("Payment DB insertion failed: " . $e->getMessage());
    if ($status === 'success') {
        $message .= " <br><strong>Important:</strong> There was an issue recording your payment. Please contact support.";
        
    }
}

render_page:
$conn->close();

ob_end_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>
     <script src="../assets/js/tailwind.js"></script>
    <link href="../assets/css/tailwind.css" rel="stylesheet"/>
    <link href="../assets/css/all.min.css" rel="stylesheet"/>
    <link href="../assets/css/google_fonts.css" rel="stylesheet"/>
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="../assets/fonts/interFont.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .pop-in {
            animation: pop-in 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards;
        }
        @keyframes pop-in {
            from { transform: scale(0.5); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-8 text-center">
        <h1 class="text-4xl font-extrabold text-gray-900 leading-tight mb-2 text-center">Vasundhara Housing</h1>
        
        <?php if ($status === 'success'): ?>
            <i class="fas fa-check-circle text-green-600 text-7xl mb-4 pop-in"></i>
            <h2 class="text-3xl font-bold text-green-600 mb-4">Payment Successful!</h2>
            <p class="text-lg text-gray-700"><?php echo $message; ?></p>
        <?php else: ?>
            <i class="fas fa-times-circle text-red-600 text-7xl mb-4 pop-in"></i>
            <h2 class="text-3xl font-bold text-red-600 mb-4">Payment Failed</h2>
            <p class="text-lg text-gray-700"><?php echo $message; ?></p>
        <?php endif; ?>

        <a href="<?php echo $return_url; ?>" class="inline-block mt-8 bg-blue-500 text-white font-bold py-3 px-6 rounded-xl hover:bg-blue-600 transition-colors shadow-lg">
            <?php echo $return_text; ?>
        </a>
    </div>

</body>
</html>
