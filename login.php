<?php
// Start the session to manage user login state
session_start();

// Include the necessary database and password verification files.
// Ensure these files exist and contain the correct functions.
// include './assets/pwd_verify.php'; // Using standard PHP password functions for better security.
include './database.php';

// --- Initialize variables ---
$message = '';
$messageType = '';
$selectedUserType = 'tenant';
$showTenantRegisterForm = false; // Default to showing the login form
$redirect_url = '';
$prefill_name = '';

// --- Handle session-based redirection for registration ---
if (isset($_SESSION['registration_required']) && $_SESSION['registration_required']) {
    $message = $_SESSION['toast_message'] ?? '';
    $messageType = $_SESSION['toast_type'] ?? 'info';
    $prefill_name = $_SESSION['prefill_name'] ?? '';
    $redirect_url = $_SESSION['post_registration_redirect'] ?? '';
    
    // Force the UI to the tenant registration form
    $selectedUserType = 'tenant';
    $showTenantRegisterForm = true;

    // Unset the session variables to prevent them from persisting on refresh
    unset($_SESSION['registration_required'], $_SESSION['toast_message'], $_SESSION['toast_type'], $_SESSION['prefill_name'], $_SESSION['post_registration_redirect']);
}

// --- Handle GET parameters (fallback/other uses) ---
if (empty($redirect_url) && isset($_GET['redirect'])) {
    $redirect_url = filter_var($_GET['redirect'], FILTER_SANITIZE_URL);
}
if (empty($prefill_name) && isset($_GET['name'])) {
    $prefill_name = htmlspecialchars($_GET['name']);
}

// --- Retain pre-filled name on POST validation failure ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tenant_name'])) {
    $prefill_name = htmlspecialchars($_POST['tenant_name']);
}

// Check if a form has been submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize and get the user type from the form submission
    $userType = filter_input(INPUT_POST, 'user_type', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($userType) {
        $selectedUserType = $userType;
    }

    // Persist redirect URL across POST requests
    if (isset($_POST['redirect_url'])) {
        $redirect_url = filter_var($_POST['redirect_url'], FILTER_SANITIZE_URL);
    }

    // --- Handle Admin Login ---
    if (isset($_POST['admin_login_btn'])) {
        $email = filter_input(INPUT_POST, 'admin_email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['admin_password'];

        if (empty($email) || empty($password)) {
            $message = "Email and password are required.";
            $messageType = 'error';
        } else {
            // Fetch admin by email
            $stmt = $conn->prepare("SELECT id, name, email, password FROM admin_login WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
            $stmt->close();

            // Verify the admin's existence and password
            // IMPORTANT: The password in the database is plain text. This is a major security risk.
            // It should be updated to a hashed password using password_hash().
            if ($admin && $password === $admin['password']) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['type_admin'] = 'admin';
                $_SESSION['admin_name'] = $admin['name'];
                // Redirect to the admin dashboard on successful login
                header('Location: ./admin/index.php'); // Corrected path to admin dashboard
                exit();
            } else {
                $message = "Invalid email or password.";
                $messageType = 'error';
            }
        }
    }

    // --- Handle Tenant Forms (Login & Register) ---
    if (isset($_POST['tenant_login_btn']) || isset($_POST['tenant_register_btn'])) {
        $formType = filter_input(INPUT_POST, 'form_type', FILTER_SANITIZE_SPECIAL_CHARS);

        // Set the state to show the correct tenant form if there's an error
        $showTenantRegisterForm = ($formType === 'register');

        $email = filter_input(INPUT_POST, 'tenant_email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['tenant_password'];

        // Handle Tenant Registration
        if ($formType === 'register') {
            $name = filter_input(INPUT_POST, 'tenant_name', FILTER_SANITIZE_SPECIAL_CHARS);
            $number = filter_input(INPUT_POST, 'tenant_number', FILTER_SANITIZE_SPECIAL_CHARS);
            $confirmPassword = $_POST['tenant_confirm_password'];

            if (empty($name) || empty($email) || empty($number) || empty($password) || empty($confirmPassword)) {
                $message = "All fields are required for registration.";
                $messageType = 'error';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Please enter a valid email address.";
                $messageType = 'error';
            } elseif ($password !== $confirmPassword) {
                $message = "Passwords do not match.";
                $messageType = 'error';
            } elseif (strlen($password) < 6) {
                $message = "Password must be at least 6 characters long.";
                $messageType = 'error';
            } else {
                // Check if a user with this email or number already exists in 'tenants' table
                $stmt = $conn->prepare("SELECT tenant_id FROM tenants WHERE email = ? OR contact_number = ?");
                $stmt->bind_param("ss", $email, $number);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $message = "An account with this email or phone number already exists.";
                    $messageType = 'error';
                } else {
                    // Use PHP's secure password hashing function
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                    // Insert new tenant into the database
                    $stmt_insert = $conn->prepare("INSERT INTO tenants (tenant_name, email, contact_number, password, status) VALUES (?, ?, ?, ?, 'Unactive')");
                    $stmt_insert->bind_param("ssss", $name, $email, $number, $passwordHash);

                    if ($stmt_insert->execute()) {
                        // Automatically log in the new user
                        $new_tenant_id = $conn->insert_id;
                        $_SESSION['tenant_id'] = $new_tenant_id;
                        $_SESSION['type_tenant'] = 'tenant';
                        $_SESSION['tenant_name'] = $name;

                        // --- Update property status if registering as part of a booking flow ---
                        if (!empty($redirect_url) && strpos($redirect_url, 'rentAgreement.php') !== false) {
                            // Parse the property_id from the redirect URL (e.g., rentAgreement.php?property_id=123)
                            parse_str(parse_url($redirect_url, PHP_URL_QUERY), $query_params);
                            if (isset($query_params['property_id']) && is_numeric($query_params['property_id'])) {
                                $property_id_to_book = (int)$query_params['property_id'];
                                
                                // Update property status to 'Booked' to temporarily reserve it.
                                $stmt_prop = $conn->prepare("UPDATE properties SET status = 'Booked' WHERE pro_id = ? AND status = 'Available'");
                                $stmt_prop->bind_param("i", $property_id_to_book);
                                $stmt_prop->execute();
                                $stmt_prop->close();
                            }
                        }

                        // Redirect using PHP header
                        if (!empty($redirect_url)) {
                            $final_redirect = $redirect_url . (strpos($redirect_url, '?') === false ? '?' : '&') . 'name=' . urlencode($name);
                            header('Location: ' . $final_redirect);
                            exit();
                        } else {
                            // If no redirect URL, go to the tenant dashboard
                            header('Location: ./tenant/index.php');
                            exit();
                        }
                    } else {
                        $message = "Something went wrong. Please try again.";
                        $messageType = 'error';
                    }
                    $stmt_insert->close();
                }
                $stmt->close();
            }
        }

        // Handle Tenant Login
        if ($formType === 'login') {
            if (empty($email) || empty($password)) {
                $message = "Email and password are required.";
                $messageType = 'error';
            } else {
                // Fetch tenant by email
                $stmt = $conn->prepare("SELECT tenant_id, tenant_name, email, password, status FROM tenants WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $tenant = $result->fetch_assoc();
                $stmt->close();

                $password_verified = false;
                if ($tenant) {
                    // Check for hashed password first
                    if (password_verify($password, $tenant['password'])) {
                        $password_verified = true;
                    }
                    // Fallback for old plain-text passwords (and upgrade them)
                    elseif ($password === $tenant['password']) {
                        $password_verified = true;
                        // Upgrade plain-text password to a secure hash
                        $new_hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt_update = $conn->prepare("UPDATE tenants SET password = ? WHERE tenant_id = ?");
                        $stmt_update->bind_param("si", $new_hash, $tenant['tenant_id']);
                        $stmt_update->execute();
                        $stmt_update->close();
                    }
                }

                if ($password_verified) {
                    // Check if the tenant's account is active
                    if ($tenant['status'] === 'Active') {
                        $_SESSION['tenant_id'] = $tenant['tenant_id'];
                        $_SESSION['type_tenant'] = 'tenant';
                        $_SESSION['tenant_name'] = $tenant['tenant_name'];

                        if (!empty($redirect_url)) {
                            $final_redirect = $redirect_url . (strpos($redirect_url, '?') === false ? '?' : '&') . 'name=' . urlencode($tenant['tenant_name']);
                            header('Location: ' . $final_redirect);
                            exit();
                        }
                        // Redirect to the tenant dashboard on successful login
                        header('Location: ./tenant/index.php');
                        exit();
                    } else {
                        $message = "Your account is pending admin approval. You will be notified via email.";
                        $messageType = 'error';
                    }
                } else {
                    $message = "Invalid email or password.";
                    $messageType = 'error';
                }
            }
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register - Vasundhara Housing</title>
    <link rel="stylesheet" href="./assets/fonts/Poppins.css">
    <script src="./assets/js/tailwind.js"></script>
    <link href="./assets/css/tailwind.css" rel="stylesheet"/>
    <link href="./assets/css/all.min.css" rel="stylesheet"/>
    <link href="./assets/RemixIcon-master/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #e0f2fe;
            /* Light blue base */
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            color: #1a202c;
        }

        /* Animated background pattern */
        .hero-bg-pattern {
            background-color: #e0f2fe;
            /* Light blue base */
            background-image: radial-gradient(#bfdbfe 1px, transparent 1px), radial-gradient(#bfdbfe 1px, #e0f2fe 1px);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
            animation: backgroundPan 60s linear infinite;
        }

        @keyframes backgroundPan {
            from {
                background-position: 0 0, 10px 10px;
            }

            to {
                background-position: 100% 100%, 110% 110%;
            }
        }

        /* Form container animations */
        .form-container {
            transition: opacity 0.6s ease-in-out, transform 0.6s ease-in-out;
            opacity: 0;
            transform: translateY(30px);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2.5rem;
            /* Equivalent to p-10 */
        }

        .form-visible {
            opacity: 1;
            transform: translateY(0);
            position: relative;
            /* Make it take up space when visible */
            z-index: 10;
        }

        .form-hidden {
            opacity: 0;
            transform: translateY(30px);
            pointer-events: none;
            /* Disable interactions when hidden */
            position: absolute;
            /* Take out of flow when hidden */
            z-index: 1;
        }

        /* Toast Notification */
        .toast-notification {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            /* Green */
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
            z-index: 1000;
            font-size: 0.9rem;
        }

        .toast-notification.show {
            opacity: 1;
            visibility: visible;
        }

        .input-focus-effect:focus {
            border-color: #3b82f6;
            /* Blue-500 */
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3);
            /* Softer blue glow */
        }

        .btn-primary:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }

        .tab-btn-active {
            background-color: #ffffff;
            color: #2563eb;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .tab-btn-inactive {
            background-color: transparent;
            color: #60a5fa;
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
        }

        .tab-btn-inactive:hover {
            background-color: #dbeafe;
            /* Blue-100 */
            color: #1e40af;
            /* Blue-800 */
        }

        @keyframes slide-up-fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-up-fade-in {
            animation: slide-up-fade-in 0.6s ease-out forwards;
        }
    </style>
</head>

<body class="hero-bg-pattern flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-5xl font-extrabold text-blue-800 tracking-wider drop-shadow-lg">Vasundhara Housing</h1>
            <p class="text-lg text-blue-700 mt-2 opacity-90">Your Dream Home Awaits</p>
        </div>

        <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden min-h-[500px] border border-blue-100 animate-slide-up-fade-in" id="form-card-container">
            <div class="flex justify-center bg-blue-100 p-2 border-b border-blue-200">
                <button id="tenant-type-btn" class="flex-1 py-3 px-4 text-center text-base font-semibold rounded-xl transition-all duration-300
                    <?php echo ($selectedUserType === 'tenant') ? 'tab-btn-active' : 'tab-btn-inactive'; ?>">
                    <i class="ri-user-line mr-2"></i> Tenant
                </button>
                <button id="admin-type-btn" class="flex-1 py-3 px-4 text-center text-base font-semibold rounded-xl transition-all duration-300
                    <?php echo ($selectedUserType === 'admin') ? 'tab-btn-active' : 'tab-btn-inactive'; ?>">
                    <i class="ri-shield-user-line mr-2"></i> Admin
                </button>
            </div>

            <div id="admin-form-container" class="form-container <?php echo ($selectedUserType === 'admin') ? 'form-visible' : 'form-hidden'; ?>">
                <div class="p-8 md:p-10 w-full">
                    <h2 class="text-4xl font-bold text-gray-800 mb-4 text-center">Admin Login</h2>
                    <p class="text-center text-base text-gray-500 mb-8">Secure access for administration.</p>
                    <form id="admin-login-form" class="space-y-6" method="POST" action="login.php<?php echo $redirect_url ? '?redirect=' . urlencode($redirect_url) : ''; ?>">
                        <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($redirect_url); ?>">
                        <input type="hidden" name="user_type" value="admin">
                        <div>
                            <label for="admin-email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" id="admin-email" name="admin_email" placeholder="you@example.com"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm input-focus-effect"
                                value="<?php echo ($selectedUserType === 'admin' && isset($_POST['admin_email'])) ? htmlspecialchars($_POST['admin_email']) : ''; ?>">
                        </div>
                        <div>
                            <div class="flex justify-between items-center">
                                <label for="admin-password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <a href="#" class="forgot-password-link text-sm text-blue-600 hover:underline transition duration-200" data-user-type="admin">Forgot Password?</a>
                            </div>
                            <input type="password" id="admin-password" name="admin_password" placeholder="••••••••"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm input-focus-effect">
                        </div>
                        <button type="submit" name="admin_login_btn"
                            class="w-full bg-blue-600 text-white font-bold py-3.5 px-6 rounded-lg shadow-lg transition-all duration-300 btn-primary">
                            <i class="ri-login-box-line mr-2"></i> Sign In as Admin
                        </button>
                        <div class="text-center mt-6">
                                <a href="index.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 hover:underline transition duration-300 font-medium">
                                    <i class="ri-arrow-left-line mr-1"></i>
                                    Back to Home
                                </a>
                            </div>
                    </form>
                </div>
            </div>

            <div id="tenant-forms-container" class="form-container <?php echo ($selectedUserType === 'tenant') ? 'form-visible' : 'form-hidden'; ?>">
                <div class="p-8 md:p-10 w-full">
                    <div class="flex justify-center bg-blue-100 p-1 rounded-lg mb-6 shadow-sm">
                        <button id="tenant-login-tab" class="flex-1 py-2.5 px-4 text-center text-sm font-semibold rounded-md transition-all duration-300
                            <?php echo (!$showTenantRegisterForm) ? 'tab-btn-active' : 'tab-btn-inactive'; ?>">
                            <i class="ri-lock-line mr-1"></i> Login
                        </button>
                        <button id="tenant-register-tab" class="flex-1 py-2.5 px-4 text-center text-sm font-semibold rounded-md transition-all duration-300
                            <?php echo ($showTenantRegisterForm) ? 'tab-btn-active' : 'tab-btn-inactive'; ?>">
                            <i class="ri-user-add-line mr-1"></i> Register
                        </button>
                    </div>

                    <div id="tenant-login-form-content" class="<?php echo (!$showTenantRegisterForm) ? 'block' : 'hidden'; ?>">
                        <h2 class="text-4xl font-bold text-gray-800 mb-4 text-center">Tenant Login</h2>
                        <p class="text-center text-base text-gray-500 mb-8">Access your personalized tenant dashboard.</p>
                        <form id="tenant-login-form" class="space-y-6" method="POST" action="login.php<?php echo $redirect_url ? '?redirect=' . urlencode($redirect_url) : ''; ?>">
                            <input type="hidden" name="user_type" value="tenant">
                            <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($redirect_url); ?>">
                            <input type="hidden" name="form_type" value="login">
                            <div>
                                <label for="tenant-login-email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="tenant-login-email" name="tenant_email" placeholder="you@example.com"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm input-focus-effect"
                                    value="<?php echo (!$showTenantRegisterForm && isset($_POST['tenant_email'])) ? htmlspecialchars($_POST['tenant_email']) : ''; ?>">
                            </div>
                            <div>
                                <div class="flex justify-between items-center">
                                    <label for="tenant-login-password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                    <a href="#" class="forgot-password-link text-sm text-blue-600 hover:underline transition duration-200" data-user-type="tenant">Forgot Password?</a>
                                </div>
                                <input type="password" id="tenant-login-password" name="tenant_password" placeholder="••••••••"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm input-focus-effect">
                            </div>
                            <button type="submit" name="tenant_login_btn"
                                class="w-full bg-blue-600 text-white font-bold py-3.5 px-6 rounded-lg shadow-lg transition-all duration-300 btn-primary">
                                <i class="ri-login-box-line mr-2"></i> Sign In as Tenant
                            </button>
                            <div class="text-center mt-6">
                                <a href="index.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 hover:underline transition duration-300 font-medium">
                                    <i class="ri-arrow-left-line mr-1"></i>
                                    Back to Home
                                </a>
                            </div>
                        </form>
                    </div>

                    <div id="tenant-register-form-content" class="<?php echo ($showTenantRegisterForm) ? 'block' : 'hidden'; ?>">
                        <h2 class="text-4xl font-bold text-gray-800 mb-4 text-center">Tenant Registration</h2>
                        <p class="text-center text-base text-gray-500 mb-8">Create your account to get started with us.</p>
                        <form id="tenant-register-form" class="space-y-6" method="POST" action="login.php<?php echo $redirect_url ? '?redirect=' . urlencode($redirect_url) : ''; ?>">
                            <input type="hidden" name="user_type" value="tenant">
                            <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($redirect_url); ?>">
                            <input type="hidden" name="form_type" value="register">
                            <div>
                                <label for="tenant-register-name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="tenant-register-name" name="tenant_name" placeholder="Your Full Name"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm input-focus-effect"
                                    value="<?php echo $prefill_name; ?>">
                            </div>
                            <div>
                                <label for="tenant-register-email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="tenant-register-email" name="tenant_email" placeholder="you@example.com"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm input-focus-effect"
                                    value="<?php echo ($showTenantRegisterForm && isset($_POST['tenant_email'])) ? htmlspecialchars($_POST['tenant_email']) : ''; ?>">
                            </div>
                            <div>
                                <label for="tenant-register-number" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                                <input type="tel" id="tenant-register-number" name="tenant_number" placeholder="Your Phone Number"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm input-focus-effect"
                                    value="<?php echo ($showTenantRegisterForm && isset($_POST['tenant_number'])) ? htmlspecialchars($_POST['tenant_number']) : ''; ?>">
                            </div>
                            <div>
                                <label for="tenant-register-password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" id="tenant-register-password" name="tenant_password" placeholder="••••••••"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm input-focus-effect">
                            </div>
                            <div>
                                <label for="tenant-confirm-password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                <input type="password" id="tenant-confirm-password" name="tenant_confirm_password" placeholder="••••••••"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm input-focus-effect">
                            </div>
                            <button type="submit" name="tenant_register_btn"
                                class="w-full bg-blue-600 text-white font-bold py-3.5 px-6 rounded-lg shadow-lg transition-all duration-300 btn-primary">
                                <i class="ri-user-add-line mr-2"></i> Register as Tenant
                            </button>
                            <div class="text-center mt-6">
                                <a href="index.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 hover:underline transition duration-300 font-medium">
                                    <i class="ri-arrow-left-line mr-1"></i>
                                    Back to Home
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Forgot Password Modal -->
    <div id="forgot-password-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full relative animate-slide-up-fade-in">
            <button id="close-forgot-modal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-1 transition-colors duration-200">
                <i class="ri-close-line text-xl"></i>
            </button>
            
            <div class="p-8">
                <div id="forgot-step-1">
                    <div class="text-center mb-6">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                            <i class="ri-mail-send-line text-2xl text-blue-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Forgot Password?</h2>
                        <p class="text-gray-500 mt-2">No worries, we'll help you reset it.</p>
                    </div>
                    <form id="forgot-password-form" class="space-y-5">
                        <div>
                            <label for="forgot-email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="ri-at-line text-gray-400"></i></span>
                                <input type="email" id="forgot-email" name="email" required placeholder="Enter your registered email" class="w-full p-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-3 px-4 rounded-lg text-white font-semibold bg-blue-600 hover:bg-blue-700 transition-all duration-300 btn-primary">
                            <i class="ri-send-plane-line mr-2"></i>Send OTP
                        </button>
                    </form>
                </div>
                
                <div id="forgot-step-2" class="hidden">
                    <div class="text-center mb-6">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                            <i class="ri-key-2-line text-2xl text-blue-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Create New Password</h2>
                        <p class="text-gray-500 mt-2">Enter the OTP and your new password.</p>
                    </div>
                    <form id="reset-password-form" class="space-y-5">
                        <div>
                            <label for="reset-otp" class="block text-sm font-medium text-gray-700 mb-1">One-Time Password (OTP)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="ri-shield-keyhole-line text-gray-400"></i></span>
                                <input type="text" id="reset-otp" name="otp" required placeholder="Enter 6-digit OTP" class="w-full p-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label for="reset-new-password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="ri-lock-password-line text-gray-400"></i></span>
                                <input type="password" id="reset-new-password" name="new_password" required placeholder="••••••••" class="w-full p-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                                <button type="button" class="password-toggle absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="reset-confirm-password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="ri-lock-password-line text-gray-400"></i></span>
                                <input type="password" id="reset-confirm-password" name="confirm_password" required placeholder="••••••••" class="w-full p-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                                <button type="button" class="password-toggle absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="w-full py-3 px-4 rounded-lg text-white font-semibold bg-blue-600 hover:bg-blue-700 transition-all duration-300 btn-primary">
                            <i class="ri-check-double-line mr-2"></i>Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="toast-notification" class="toast-notification"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tenantTypeBtn = document.getElementById('tenant-type-btn');
            const adminTypeBtn = document.getElementById('admin-type-btn');
            const adminFormContainer = document.getElementById('admin-form-container');
            const tenantFormsContainer = document.getElementById('tenant-forms-container');

            const tenantLoginTab = document.getElementById('tenant-login-tab');
            const tenantRegisterTab = document.getElementById('tenant-register-tab');
            const tenantLoginFormContent = document.getElementById('tenant-login-form-content');
            const tenantRegisterFormContent = document.getElementById('tenant-register-form-content');

            const toastNotification = document.getElementById('toast-notification');

            // Function to display the toast notification
            const showToast = (message, type = 'success') => {
                toastNotification.textContent = message;
                const toastColor = type === 'error' ? 'bg-red-500' : 'bg-green-500';
                toastNotification.classList.remove('bg-red-500', 'bg-green-500'); // Remove previous colors
                toastNotification.classList.add(toastColor, 'show');
                setTimeout(() => {
                    toastNotification.classList.remove('show');
                }, 3000); // Hide after 3 seconds
            };

            // PHP driven toast display after page load
            <?php if (!empty($message)): ?>
                showToast(<?php echo json_encode($message); ?>, <?php echo json_encode($messageType); ?>);
            <?php endif; ?>

            // Function to clear all password fields
            const clearPasswords = () => {
                // Check if elements exist before trying to access .value
                const adminPass = document.getElementById('admin-password');
                if (adminPass) adminPass.value = '';
                const tenantLoginPass = document.getElementById('tenant-login-password');
                if (tenantLoginPass) tenantLoginPass.value = '';
                const tenantRegisterPass = document.getElementById('tenant-register-password');
                if (tenantRegisterPass) tenantRegisterPass.value = '';
                const tenantConfirmPass = document.getElementById('tenant-confirm-password');
                if (tenantConfirmPass) tenantConfirmPass.value = '';
            };

            // Function to switch between user types (Admin/Tenant)
            const switchUserType = (type) => {
                if (type === 'tenant') {
                    tenantTypeBtn.classList.add('tab-btn-active');
                    tenantTypeBtn.classList.remove('tab-btn-inactive');
                    adminTypeBtn.classList.remove('tab-btn-active');
                    adminTypeBtn.classList.add('tab-btn-inactive');

                    adminFormContainer.classList.remove('form-visible');
                    adminFormContainer.classList.add('form-hidden');
                    tenantFormsContainer.classList.remove('form-hidden');
                    tenantFormsContainer.classList.add('form-visible');

                    // Ensure tenant login is shown by default when switching to tenant
                    showTenantForm('login');

                } else if (type === 'admin') {
                    adminTypeBtn.classList.add('tab-btn-active');
                    adminTypeBtn.classList.remove('tab-btn-inactive');
                    tenantTypeBtn.classList.remove('tab-btn-active');
                    tenantTypeBtn.classList.add('tab-btn-inactive');

                    tenantFormsContainer.classList.remove('form-visible');
                    tenantFormsContainer.classList.add('form-hidden');
                    adminFormContainer.classList.remove('form-hidden');
                    adminFormContainer.classList.add('form-visible');
                }
                clearPasswords(); // Clear passwords on type switch
            };

            // Function to switch between Tenant Login/Register forms
            const showTenantForm = (form) => {
                if (form === 'login') {
                    tenantLoginTab.classList.add('tab-btn-active');
                    tenantLoginTab.classList.remove('tab-btn-inactive');
                    tenantRegisterTab.classList.remove('tab-btn-active');
                    tenantRegisterTab.classList.add('tab-btn-inactive');

                    tenantLoginFormContent.classList.remove('hidden');
                    tenantRegisterFormContent.classList.add('hidden');
                } else if (form === 'register') {
                    tenantRegisterTab.classList.add('tab-btn-active');
                    tenantRegisterTab.classList.remove('tab-btn-inactive');
                    tenantLoginTab.classList.remove('tab-btn-active');
                    tenantLoginTab.classList.add('tab-btn-inactive');

                    tenantRegisterFormContent.classList.remove('hidden');
                    tenantLoginFormContent.classList.add('hidden');
                }
                clearPasswords(); // Clear passwords on form switch
            };

            // Event Listeners for user type buttons
            if (tenantTypeBtn) tenantTypeBtn.addEventListener('click', () => switchUserType('tenant'));
            if (adminTypeBtn) adminTypeBtn.addEventListener('click', () => switchUserType('admin'));

            // --- Forgot Password Modal Logic ---
            const forgotModal = document.getElementById('forgot-password-modal');
            const closeForgotModalBtn = document.getElementById('close-forgot-modal');
            const forgotLinks = document.querySelectorAll('.forgot-password-link');
            const forgotStep1 = document.getElementById('forgot-step-1');
            const forgotStep2 = document.getElementById('forgot-step-2');
            const forgotForm = document.getElementById('forgot-password-form');
            const resetForm = document.getElementById('reset-password-form');

            const openForgotModal = () => forgotModal.classList.remove('hidden');
            const closeForgotModal = () => {
                forgotModal.classList.add('hidden');
                forgotStep1.classList.remove('hidden');
                forgotStep2.classList.add('hidden');
                forgotForm.reset();
                resetForm.reset();
            };

            forgotLinks.forEach(link => link.addEventListener('click', (e) => {
                e.preventDefault();
                openForgotModal();
            }));
            closeForgotModalBtn.addEventListener('click', closeForgotModal);
            
            forgotModal.addEventListener('click', (e) => {
                // Close modal if backdrop is clicked
                if (e.target === forgotModal) closeForgotModal();

                // Handle password visibility toggle
                const toggleBtn = e.target.closest('.password-toggle');
                if (toggleBtn) {
                    const input = toggleBtn.closest('.relative').querySelector('input');
                    const icon = toggleBtn.querySelector('i');
                    if (input && icon) {
                        if (input.type === 'password') {
                            input.type = 'text';
                            icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
                        } else {
                            input.type = 'password';
                            icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
                        }
                    }
                }
            });

            forgotForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const email = document.getElementById('forgot-email').value;
                const submitBtn = forgotForm.querySelector('button[type="submit"]');
                submitBtn.textContent = 'Sending...';
                submitBtn.disabled = true;

                fetch('forgot-password-request.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ email: email })
                })
                .then(res => res.json())
                .then(data => {
                    showToast(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        forgotStep1.classList.add('hidden');
                        forgotStep2.classList.remove('hidden');
                    }
                })
                .finally(() => {
                    submitBtn.textContent = 'Send OTP';
                    submitBtn.disabled = false;
                });
            });

            resetForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const submitBtn = resetForm.querySelector('button[type="submit"]');
                submitBtn.textContent = 'Resetting...';
                submitBtn.disabled = true;

                const formData = new FormData(resetForm);
                fetch('reset-password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    showToast(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        closeForgotModal();
                    }
                })
                .finally(() => {
                    submitBtn.textContent = 'Reset Password';
                    submitBtn.disabled = false;
                });
            });

            // Event Listeners for tenant form tabs
            if (tenantLoginTab) tenantLoginTab.addEventListener('click', () => showTenantForm('login'));
            if (tenantRegisterTab) tenantRegisterTab.addEventListener('click', () => showTenantForm('register'));

            // Initial state setup based on PHP variables
            const initialSelectedUserType = "<?php echo $selectedUserType; ?>";
            const initialShowTenantRegisterForm = "<?php echo $showTenantRegisterForm ? 'true' : 'false'; ?>";

            if (initialSelectedUserType === "admin") {
                switchUserType('admin');
            } else {
                switchUserType('tenant');
                if (initialShowTenantRegisterForm === "true") {
                    showTenantForm('register');
                } else {
                    showTenantForm('login');
                }
            }

            // Clear password fields on initial load
            clearPasswords();
        });
    </script>
</body>

</html>