<?php

$servername = "localhost"; // Database server name, typically 'localhost'
$username = "root"; // Database username
$password = ""; // Database password (empty for no password)
$dbname = "vasundharahousing"; // Name of your database

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // If connection fails, terminate script and display error
    die("Connection failed: " . $conn->connect_error);
}
?>