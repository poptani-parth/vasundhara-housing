<?php
session_start();
$error_message = $_SESSION['payment_error'] ?? 'Something went wrong with your payment. Please try again.';
unset($_SESSION['payment_error']); // Clear the message after displaying it

$origin = $_SESSION['payment_origin'] ?? 'agreement'; // Default to agreement flow

if ($origin === 'tenant_dashboard') {
    $redirect_url = '../tenant/index.php';
    $button_text = 'Back to Dashboard';
} else { // 'agreement' flow
    $redirect_url = 'index.php'; // Go back to the payment page to try again
    $button_text = 'Try Again';
}

// Unset the session variable so it doesn't persist
unset($_SESSION['payment_origin']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <script src="./assets/js/tailwind.js"></script>
    <link href="./assets/css/tailwind.css" rel="stylesheet"/>
    <link href="./assets/css/all.min.css" rel="stylesheet"/>
    <link href="./assets/css/google_fonts.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/fonts/interFont.css">
    <link rel="stylesheet" href="./style.css">
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full text-center">
         <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
            <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-800">Payment Failed</h2>
        <p class="mt-3 text-lg text-gray-600"><?php echo htmlspecialchars($error_message); ?></p>
        <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="mt-8 inline-block px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors duration-300">
            <?php echo htmlspecialchars($button_text); ?>
        </a>
    </div>

    <script>
        setTimeout(() => {
            window.location.href = "<?php echo $redirect_url; ?>";
        }, 5000); // Redirect after 5 seconds
    </script>
</body>
</html>
