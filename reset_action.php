<?php

$token = $_POST['token'];
$new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

// Check token in file
$lines = file('password_reset_tokens.txt');
$found = false;

foreach ($lines as $line) {
    list($email, $saved_token) = explode('|', trim($line));
    if ($saved_token === $token) {
        $found = true;

        // Update password in users.txt
        $users = file('users.txt');
        $updated_users = [];
        foreach ($users as $user_line) {
            list($u_email, $u_pass) = explode('|', trim($user_line));
            if ($u_email == $email) {
                $updated_users[] = "$u_email|$new_password\n";
            } else {
                $updated_users[] = $user_line;
            }
        }
        file_put_contents('users.txt', implode("", $updated_users));

        // Remove used token
        $new_lines = array_filter($lines, fn($l) => !str_contains($l, $token));
        file_put_contents('password_reset_tokens.txt', implode("", $new_lines));

        echo "✅ Password reset successfully.";
        break;
    }
}

if (!$found) {
    echo "❌ Invalid or expired token.";
}
?>
