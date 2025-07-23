<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

$message = "";
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "Purchase ID missing.";
    exit;
}

// Fetch current purchase record
$query = $conn->prepare("SELECT * FROM purchases WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
if ($result->num_rows == 0) {
    echo "Purchase record not found.";
    exit;
}
$purchase = $result->fetch_assoc();

// Fetch suppliers and products for dropdowns
$suppliers = $conn->query("SELECT id, name FROM suppliers ORDER BY name ASC");
$products = $conn->query("SELECT id, name FROM products ORDER BY name ASC");

// Update logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_id = $_POST['supplier_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $purchase_price = $_POST['purchase_price'];
    $sale_price = $_POST['sale_price'];
    $hsn_code = $_POST['hsn_code'];
    $purchase_date = $_POST['purchase_date'];

    $stmt = $conn->prepare("UPDATE purchases SET supplier_id=?, product_id=?, quantity=?, purchase_price=?, sale_price=?, hsn_code=?, purchase_date=? WHERE id=?");
    $stmt->bind_param("iiiddssi", $supplier_id, $product_id, $quantity, $purchase_price, $sale_price, $hsn_code, $purchase_date, $id);

    if ($stmt->execute()) {
        $message = "✅ Purchase updated successfully!";
        // Refresh purchase data
        $query = $conn->prepare("SELECT * FROM purchases WHERE id = ?");
        $query->bind_param("i", $id);
        $query->execute();
        $purchase = $query->get_result()->fetch_assoc();
    } else {
        $message = "❌ Failed to update.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Purchase</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9fdfc;
            margin-left: 260px;
        }

        .main-content {
            margin-left: 260px;
            padding: 80px 40px;
            min-height: 100vh;
        }

        h2 {
            color: #1abc9c;
            margin-bottom: 20px;
        }

        .message {
            font-weight: bold;
            margin-bottom: 15px;
            color: green;
        }

        .form-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            max-width: 600px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-top: 15px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            margin-top: 25px;
            background-color: #1abc9c;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #17a589;
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2>Edit Purchase</h2>

    

    <div class="form-box">
        <?php if (!empty($message)): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
        <form method="POST">
            <label for="supplier_id">Supplier</label>
            <select name="supplier_id" required>
                <option value="">Select Supplier</option>
                <?php while ($row = $suppliers->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id'] == $purchase['supplier_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="product_id">Product</label>
            <select name="product_id" required>
                <option value="">Select Product</option>
                <?php while ($row = $products->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id'] == $purchase['product_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="quantity">Quantity</label>
            <input type="number" name="quantity" min="1" value="<?= $purchase['quantity'] ?>" required>

            <label for="purchase_price">Purchase Price</label>
            <input type="number" name="purchase_price" step="1" value="<?= $purchase['purchase_price'] ?>" required>

            <label for="sale_price">Sale Price</label>
            <input type="number" name="sale_price" step="1" value="<?= $purchase['sale_price'] ?>" required>

            <label for="hsn_code">HSN Code</label>
            <input type="text" name="hsn_code" value="<?= $purchase['hsn_code'] ?>" required>

            <label for="purchase_date">Purchase Date</label>
            <input type="date" name="purchase_date" value="<?= $purchase['purchase_date'] ?>" required>

            <button type="submit">Update Purchase</button>
        </form>
    </div>
</div>
</body>
</html>
