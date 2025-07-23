<?php
$token = $_GET['token'] ?? '';
?>
<form method="post" action="reset_action.php">
    <input type="hidden" name="token" value="<?= $token ?>">
    <input type="password" name="new_password" placeholder="New Password" required>
    <button type="submit">Reset Password</button>
</form>
