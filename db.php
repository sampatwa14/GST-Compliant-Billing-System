<?php
$host = '127.0.0.1';    // Use IP instead of 'localhost'
$user = 'root';
$pass = '';             // Default XAMPP password is empty
$dbname = 'billing_software';
$port = 4306;           // ✅ Add your MySQL port here

// Connect with custom port
$conn = new mysqli($host, $user, $pass, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
?>
