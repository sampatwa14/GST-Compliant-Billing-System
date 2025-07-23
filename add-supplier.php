<?php
session_start();
include 'db.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';

    $sql = "INSERT INTO suppliers (name, phone, email, address) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $phone, $email, $address);

    if ($stmt->execute()) {
        $msg = "✅ Supplier added successfully!";
    } else {
        $msg = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Supplier - ShivKrupa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9fdfc;
            margin: 0;
        }

        .main-content {
            margin-left: 270px;
            padding: 80px 20px 20px 20px;
            min-height: 100vh;
        }

        .form-box {
            background: #fff;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #1abc9c;
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-top: 15px;
        }

        input, textarea {
            width: 96%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        button {
            width: 100%;
            background-color: #1abc9c;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #17a589;
        }

        .msg {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 6px;
        }

        .msg.success {
            background: #e9f9f2;
            color: #27ae60;
        }

        .msg.error {
            background: #fdecea;
            color: #e74c3c;
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="form-box">
        <h2>Add New Supplier</h2>

        <?php if ($msg): ?>
            <div class="msg <?= strpos($msg, 'successfully') !== false ? 'success' : 'error' ?>">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label for="name">Supplier Name</label>
            <input type="text" name="name" required>

            <label for="phone">Phone</label>
            <input type="text" name="phone">

            <label for="email">Email</label>
            <input type="email" name="email">

            <label for="address">Address</label>
            <textarea name="address" rows="3"></textarea>

            <button type="submit">Add Supplier</button>
        </form>
    </div>
</div>
</body>
</html>
