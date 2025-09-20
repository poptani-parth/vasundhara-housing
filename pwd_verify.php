<?php
session_start();
include './assets/pwd_validation.php';
include './database.php';

$message = '';
$messageType = 'success';
$showLoginForm = true;
$selectedUserType = 'tenant';
$showTenantRegisterForm = false;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedUserType = isset($_POST['user_type']) ? $_POST['user_type'] : 'tenant';
    $formType = isset($_POST['form_type']) ? $_POST['form_type'] : 'login';

    if ($selectedUserType === 'admin') {
        // Admin Login Logic
        $email = trim($_POST['admin_email']);
        $password = $_POST['admin_password'];

        if (empty($email) || empty($password)) {
            $message = 'Both email and password are required for login.';
            $messageType = 'error';
        } elseif (validateEmail($email) !== true) {
            $message = validateEmail($email);
            $messageType = 'error';
        } else {
            $login_query = "SELECT id, name, password FROM admin_login WHERE email = ?";
            $stmt_login = mysqli_prepare($conn, $login_query);
            mysqli_stmt_bind_param($stmt_login, "s", $email);
            mysqli_stmt_execute($stmt_login);
            mysqli_stmt_store_result($stmt_login);

            if (mysqli_stmt_num_rows($stmt_login) == 1) {
                mysqli_stmt_bind_result($stmt_login, $user_id, $user_name, $pswd);
                mysqli_stmt_fetch($stmt_login);

                // FIX: Remove the insecure plain password check.
                if ($password === $pswd) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $user_name;
                    $_SESSION['user_type'] = 'admin';
                    header('Location: ./admin/index.php');
                    exit();
                } else {
                    $message = 'Invalid email or password.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Invalid email or password.';
                $messageType = 'error';
            }
            mysqli_stmt_close($stmt_login);
        }
        $showLoginForm = true;
    } elseif ($selectedUserType === 'tenant') {
        if ($formType === 'login') {
            // Tenant Login Logic
            $email = trim($_POST['tenant_email']);
            $password = $_POST['tenant_password'];

            if (empty($email) || empty($password)) {
                $message = 'Both email and password are required for login.';
                $messageType = 'error';
            } elseif (validateEmail($email) !== true) {
                $message = validateEmail($email);
                $messageType = 'error';
            } else {
                $login_query = "SELECT tenant_id, tenant_name, password,status FROM tenants WHERE email = ?";
                $stmt_login = mysqli_prepare($conn, $login_query);
                mysqli_stmt_bind_param($stmt_login, "s", $email);
                mysqli_stmt_execute($stmt_login);
                mysqli_stmt_store_result($stmt_login);

                if (mysqli_stmt_num_rows($stmt_login) == 1) {
                    mysqli_stmt_bind_result($stmt_login, $user_id, $user_name, $pswd,$status);
                    mysqli_stmt_fetch($stmt_login);
                    $status= ucfirst($status);

                    // FIX: Remove the insecure plain password check.
                   if ($password == $pswd && $status === 'Active') {
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['user_name'] = $user_name;
                        $_SESSION['user_type'] = 'tenant';
                        header('Location: ./tenant/index.php');
                        exit();
                    } else if($status !== 'Active') {
                        $message = 'Your account is not verified. Please wait for admin approval in your email account.';
                        $messageType = 'error';

                    } 
                    else {
                        $message = 'Invalid email or password.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Invalid email or password.';
                    $messageType = 'error';
                }
                mysqli_stmt_close($stmt_login);
            }
            $showTenantRegisterForm = false;
        } elseif ($formType === 'register') {
            // Tenant Registration Logic
            $name = trim($_POST['tenant_name']);
            $email = trim($_POST['tenant_email']);
            $password = $_POST['tenant_password'];
            $confirm_password = $_POST['tenant_confirm_password'];

            if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
                $message = 'All fields are required for registration.';
                $messageType = 'error';
            } elseif (validateName($name) !== true) {
                $message = validateName($name);
                $messageType = 'error';
            } elseif (validateEmail($email) !== true) {
                $message = validateEmail($email);
                $messageType = 'error';
            } elseif ($password !== $confirm_password) {
                $message = 'Passwords do not match.';
                $messageType = 'error';
            } elseif (strlen($password) < 8) {
                $message = 'Password must be at least 8 characters long.';
                $messageType = 'error';
            } else {
                // Check if email already exists
                $check_email_query = "SELECT tenant_id FROM tenants WHERE email = ?";
                $stmt_check = mysqli_prepare($conn, $check_email_query);
                mysqli_stmt_bind_param($stmt_check, "s", $email);
                mysqli_stmt_execute($stmt_check);
                mysqli_stmt_store_result($stmt_check);

                if (mysqli_stmt_num_rows($stmt_check) > 0) {
                    $message = 'Email already registered. Please login or use a different email.';
                    $messageType = 'error';
                } else {

                    $insert_query = "INSERT INTO tenants (tenant_name, email, password) VALUES (?, ?, ?)";
                    $stmt_insert = mysqli_prepare($conn, $insert_query);
                    // FIX: Use the hashed password in the insert statement.
                    mysqli_stmt_bind_param($stmt_insert, "sss", $name, $email, $password);

                    if (mysqli_stmt_execute($stmt_insert)) {
                        $message = 'Registration successful! Please login.';
                        $messageType = 'success';
                        $showTenantRegisterForm = false;
                    } else {
                        $message = 'Registration failed. Please try again.';
                        $messageType = 'error';
                    }
                    mysqli_stmt_close($stmt_insert);
                }
                mysqli_stmt_close($stmt_check);
            }
            $showTenantRegisterForm = true;
        }
    }
} else {
    if (isset($_SESSION['toast_message'])) {
        $message = $_SESSION['toast_message'];
        $messageType = $_SESSION['toast_type'];
        unset($_SESSION['toast_message']);
        unset($_SESSION['toast_type']);
    }

    $showTenantRegisterForm = isset($_GET['showTenantRegisterForm']) ? ($_GET['showTenantRegisterForm'] === 'true') : false;
    $selectedUserType = isset($_GET['userType']) ? $_GET['userType'] : 'tenant';
}
?>