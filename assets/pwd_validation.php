<?php
$message = '';
$messageType = '';
$showLoginForm = true;


// Password validation function for registration
function validatePassword($password) {
    // Example: Minimum 8 characters, at least one letter and one number
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Za-z]/', $password)) {
        return "Password must contain at least one letter.";
    }
    if (!preg_match('/\d/', $password)) {
        return "Password must contain at least one number.";
    }
    return true;
}
function validateEmail($email) {
    if (empty($email)) {
        return "Email is required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format.";
    }
    return true;
}

function validateName($name) {
    if (empty($name)) {
        return "Name is required.";
    }
    if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        return "Only letters and white space allowed in name.";
    }
    return true;
}
?>