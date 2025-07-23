<?php
session_start();
include 'navbar.php';
include 'sidebar.php';
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $email = $_POST["email"];
    $address = $_POST["address"];

    $sql = "INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $phone, $email, $address);

    if ($stmt->execute()) {
        $message = "✅ Customer added successfully!";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Customer</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9fdfc;
      margin: 0;
    }

    .main-container {
      margin-left: 270px;
      padding: 80px 20px 20px 20px;
      min-height: 100vh;
    }

    .form-box {
      background: white;
      max-width: 600px;
      margin: auto;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #1abc9c;
    }

    label {
      font-weight: 600;
      margin-top: 10px;
      display: block;
    }

    input, textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    button {
      background-color: #1abc9c;
      color: white;
      border: none;
      padding: 12px;
      width: 100%;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #17a589;
    }

    .message {
      text-align: center;
      font-weight: bold;
      margin-bottom: 15px;
      padding: 10px;
      border-radius: 6px;
    }

    .message:empty {
      display: none;
    }

    .message.success {
      background: #e9f9f2;
      color: #27ae60;
    }

    .message.error {
      background: #fdecea;
      color: #e74c3c;
    }
  </style>
</head>
<body>
  <div class="main-container">
    <div class="form-box">
      <h2>Add Customer</h2>

      <?php if (!empty($message)): ?>
        <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
          <?= $message ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <label>Customer Name</label>
        <input type="text" name="name" placeholder="Enter customer name" required>

        <label>Phone</label>
        <input type="text" name="phone" placeholder="Enter phone number" required>

        <label>Email</label>
        <input type="email" name="email" placeholder="Enter email" required>

        <label>Address</label>
        <textarea name="address" rows="3" placeholder="Enter address" required></textarea>

        <button type="submit">Add Customer</button>
      </form>
    </div>
  </div>
</body>
</html>
