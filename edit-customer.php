<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

$product_id = $_GET['id'] ?? null;
$message = "";

if (!$product_id) {
    header("Location: view-products.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $hsn_code = trim($_POST['hsn_code']);
    $category_id = $_POST['category_id'];
    $purchase_price = floatval($_POST['purchase_price']);
    $sale_price = floatval($_POST['sale_price']);
    $gst = floatval($_POST['gst']);
    $quantity = intval($_POST['quantity']);

    // Prevent negative stock
    if ($quantity < 0) {
        $message = "Stock quantity cannot be negative.";
    } elseif (!$name || !$purchase_price || !$sale_price) {
        $message = "Please fill all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, hsn_code=?, category_id=?, purchase_price=?, sale_price=?, gst=?, quantity=? WHERE id=?");
        $stmt->bind_param("ssiddiii", $name, $hsn_code, $category_id, $purchase_price, $sale_price, $gst, $quantity, $product_id);
        
        if ($stmt->execute()) {
            $message = "Product updated successfully.";
        } else {
            $message = "Failed to update product.";
        }
        $stmt->close();
    }
}

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product - ShivKrupa</title>
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

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background-color: #1abc9c;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }

        .success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2>Edit Product</h2>
    <div class="form-container">
        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'success') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

            <label for="hsn_code">HSN Code</label>
            <input type="text" id="hsn_code" name="hsn_code" value="<?= htmlspecialchars($product['hsn_code']) ?>">

            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?= $category['id'] ?>" 
                        <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="purchase_price">Purchase Price (₹)</label>
            <input type="number" id="purchase_price" name="purchase_price" step="1" 
                   value="<?= htmlspecialchars($product['purchase_price']) ?>" required>

            <label for="sale_price">Sale Price (₹)</label>
            <input type="number" id="sale_price" name="sale_price" step="1" 
                   value="<?= htmlspecialchars($product['sale_price']) ?>" required>

            <label for="gst">GST (%)</label>
            <input type="number" id="gst" name="gst" step="1" 
                   value="<?= htmlspecialchars($product['gst']) ?>" required>

            <label for="quantity">Quantity in Stock</label>
            <input type="number" id="quantity" name="quantity" min="0" 
                   value="<?= htmlspecialchars($product['quantity']) ?>" required>

            <button type="submit">Update Product</button>
        </form>
    </div>
</div>
</body>
</html>