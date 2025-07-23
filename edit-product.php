<?php
session_start();
require_once 'db.php';
require_once 'navbar.php';
require_once 'sidebar.php';

$product_id = $_GET['id'] ?? null;
$message = '';
$message_type = ''; // 'success' or 'error'

if (!$product_id || !is_numeric($product_id)) {
    header("Location: view-products.php");
    exit;
}

// Get product details first
$product = [];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: view-products.php");
    exit;
}

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = trim($_POST['name'] ?? '');
    $hsn_code = trim($_POST['hsn_code'] ?? '');
    $category_id = $_POST['category_id'] ?? 0;
    $purchase_price = str_replace(',', '.', $_POST['purchase_price'] ?? '0'); // Handle decimal formats
    $sale_price = str_replace(',', '.', $_POST['sale_price'] ?? '0');
    $gst = str_replace(',', '.', $_POST['gst'] ?? '0');
    $quantity = $_POST['quantity'] ?? 0;

    // Convert to proper types
    $category_id = (int)$category_id;
    $purchase_price = (float)$purchase_price;
    $sale_price = (float)$sale_price;
    $gst = (float)$gst;
    $quantity = (int)$quantity;

    // Validation
    $errors = [];
    if (empty($name)) {
        $errors[] = "Product name is required";
    }
    if ($category_id <= 0) {
        $errors[] = "Please select a valid category";
    }
    if ($purchase_price <= 0) {
        $errors[] = "Purchase price must be positive";
    }
    if ($sale_price <= 0) {
        $errors[] = "Sale price must be positive";
    }
    if ($sale_price < $purchase_price) {
        $errors[] = "Sale price cannot be less than purchase price";
    }
    if ($gst < 0 || $gst > 100) {
        $errors[] = "GST must be between 0 and 100";
    }
    if ($quantity < 0) {
        $errors[] = "Quantity cannot be negative";
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE products SET 
                name = ?, 
                hsn_code = ?, 
                category_id = ?, 
                purchase_price = ?, 
                sale_price = ?, 
                gst = ?, 
                quantity = ? 
                WHERE id = ?");
            
            $stmt->bind_param("ssiddiii", 
                $name, 
                $hsn_code, 
                $category_id, 
                $purchase_price, 
                $sale_price, 
                $gst, 
                $quantity, 
                $product_id);

            if ($stmt->execute()) {
                $message = "Product updated successfully!";
                $message_type = "success";
                
                // Refresh product data after update
                $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $product = $result->fetch_assoc();
                $stmt->close();
            } else {
                throw new Exception("Database update failed");
            }
        } catch (Exception $e) {
            $message = "Error updating product: " . $e->getMessage();
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

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input:focus, select:focus {
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
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2>Edit Product</h2>
    <div class="form-container">
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="name">Product Name*</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>

            <label for="hsn_code">HSN Code</label>
            <input type="text" id="hsn_code" name="hsn_code" value="<?= htmlspecialchars($product['hsn_code'] ?? '') ?>">

            <label for="category_id">Category*</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php 
                if ($categories) {
                    $categories->data_seek(0); // Rewind pointer
                    while ($category = $categories->fetch_assoc()): 
                ?>
                    <option value="<?= $category['id'] ?>" 
                        <?= ($category['id'] == ($product['category_id'] ?? 0)) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php 
                    endwhile;
                } 
                ?>
            </select>

            <label for="purchase_price">Purchase Price (₹)*</label>
            <input type="number" id="purchase_price" name="purchase_price" step="1" min="0" 
                   value="<?= htmlspecialchars($product['purchase_price'] ?? 0) ?>" required>

            <label for="sale_price">Sale Price (₹)*</label>
            <input type="number" id="sale_price" name="sale_price" step="1" min="0" 
                   value="<?= htmlspecialchars($product['sale_price'] ?? 0) ?>" required>

            <label for="gst">GST (%)*</label>
            <input type="number" id="gst" name="gst" step="1" min="0" max="100" 
                   value="<?= htmlspecialchars($product['gst'] ?? 0) ?>" required>

            <label for="quantity">Quantity in Stock*</label>
            <input type="number" id="quantity" name="quantity" min="0" 
                   value="<?= htmlspecialchars($product['quantity'] ?? 0) ?>" required>

            <button type="submit">Update Product</button>
        </form>
    </div>
</div>
</body>
</html>