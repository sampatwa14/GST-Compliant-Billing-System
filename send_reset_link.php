<?php
include 'functions.php';

$email = $_POST['email'];
$token = bin2hex(random_bytes(16));

// Save token to file
file_put_contents('password_reset_tokens.txt', "$email|$token\n", FILE_APPEND);

// Generate reset link
$link = "http://localhost/Billing%20Software/reset-password.php?token=$token";
$subject = "Password Reset Link";
$message = "Click the link to reset your password: <a href='$link'>$link</a>";

// Send email
send_email($email, $subject, $message);

echo "Reset link sent to your email. Check inbox or spam folder.";
?>
