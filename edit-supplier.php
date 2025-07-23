<?php
session_start();
require_once 'db.php';
require_once 'navbar.php';
require_once 'sidebar.php';

$supplier_id = $_GET['id'] ?? null;
$message = '';
$message_type = ''; // 'success' or 'error'

if (!$supplier_id || !is_numeric($supplier_id)) {
    header("Location: view-suppliers.php");
    exit;
}

// Get supplier details first
$supplier = [];
$stmt = $conn->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$result = $stmt->get_result();
$supplier = $result->fetch_assoc();
$stmt->close();

if (!$supplier) {
    header("Location: view-suppliers.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validation
    $errors = [];
    if (empty($name)) {
        $errors[] = "Supplier name is required";
    }
    if (empty($contact)) {
        $errors[] = "Contact number is required";
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE suppliers SET 
                name = ?, 
                contact = ?, 
                email = ?, 
                address = ? 
                WHERE id = ?");
            
            $stmt->bind_param("ssssi", 
                $name, 
                $contact, 
                $email, 
                $address, 
                $supplier_id);

            if ($stmt->execute()) {
                $message = "Supplier details updated successfully!";
                $message_type = "success";
                
                // Refresh supplier data after update
                $stmt = $conn->prepare("SELECT * FROM suppliers WHERE id = ?");
                $stmt->bind_param("i", $supplier_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $supplier = $result->fetch_assoc();
                $stmt->close();
            } else {
                throw new Exception("Database update failed");
            }
        } catch (Exception $e) {
            $message = "Error updating supplier: " . $e->getMessage();
            $message_type = "error";
            error_log($message);
        }
    } else {
        $message = implode("<br>", $errors);
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Supplier - ShivKrupa</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9fdfc;
        }

        .main-content {
            margin-left: 270px;
            padding: 80px 40px;
            max-width: 900px;
        }

        h2 {
            color: #1abc9c;
            margin-bottom: 25px;
            margin-left: 270px;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-left: 260px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        input:focus, textarea:focus {
            border-color: #1abc9c;
            outline: none;
            box-shadow: 0 0 0 2px rgba(26, 188, 156, 0.2);
        }

        button {
            background-color: #1abc9c;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #16a085;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }

        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            h2 {
                margin-left: 0;
            }
            
            .form-container {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2>Edit Supplier</h2>
    <div class="form-container">
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="name">Supplier Name*</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($supplier['name'] ?? '') ?>" required>

            <label for="contact">Contact Number*</label>
            <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($supplier['contact'] ?? '') ?>" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($supplier['email'] ?? '') ?>">

            <label for="address">Address</label>
            <textarea id="address" name="address"><?= htmlspecialchars($supplier['address'] ?? '') ?></textarea>

            <button type="submit">Update Supplier</button>
        </form>
    </div>
</div>
</body>
</html>