<?php
session_start();

$origin = $_SESSION['payment_origin'] ?? 'agreement'; // Default to agreement flow

if ($origin === 'tenant_dashboard') {
    $redirect_url = '../tenant/index.php';
    $message = 'Your payment was successful. You will be redirected to your dashboard.';
    $button_text = 'Back to Dashboard';
} else { // 'agreement' flow
    $redirect_url = '../login.php';
    $message = 'Your application has been submitted for review. You will be notified via email upon approval.';
    $button_text = 'Login Page';
}

// Unset the session variable so it doesn't persist
unset($_SESSION['payment_origin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <script src="./assets/js/tailwind.js"></script>
    <link href="./assets/css/tailwind.css" rel="stylesheet"/>
    <link href="./assets/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/fonts/interFont.css">
    <link href="./assets/css/google_fonts.css" rel="stylesheet"/>
    <link rel="stylesheet" href="./style.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
            <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-800">Payment Successful!</h2>
        <p class="mt-3 text-lg text-gray-600"><?php echo htmlspecialchars($message); ?></p>
        <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="mt-8 inline-block px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors duration-300">
            <?php echo htmlspecialchars($button_text); ?>
        </a>
    </div>

    <script>
        setTimeout(() => {
            window.location.href = "<?php echo $redirect_url; ?>";
        }, 3000); // Redirect after 3 seconds
    </script>
</body>
</html>
