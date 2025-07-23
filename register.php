<?php
session_start();
include 'db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $message = "❌ All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Invalid email format.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $message = "❌ Email already exists or error occurred.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <style>
    body { font-family: Arial; background: #f5f5f5; }
    .form-box {
      max-width: 400px;
      margin: 80px auto;
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    input, button {
      width: 100%; padding: 10px; margin: 10px 0;
    }
    button {
      background-color: #1abc9c; color: white; border: none;
      font-weight: bold; cursor: pointer;
    }
    .msg { color: red; font-weight: bold; text-align: center; }
  </style>
</head>
<body>
  <div class="form-box">
    <h2>Create Account</h2>
    <?php if ($message): ?>
      <div class="msg"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST" onsubmit="return validateForm()">
      <input type="text" name="name" placeholder="Full Name" id="name" required>
      <input type="email" name="email" placeholder="Email" id="email" required>
      <input type="password" name="password" placeholder="Password (min 6 chars)" id="password" required>
      <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
  </div>

  <script>
    function validateForm() {
      const pass = document.getElementById("password").value;
      if (pass.length < 6) {
        alert("Password must be at least 6 characters long.");
        return false;
      }
      return true;
    }
  </script>
</body>
</html>
