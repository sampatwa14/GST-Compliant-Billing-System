<?php
session_start();
require_once 'db.php'; // Use require_once for critical includes

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: view-products.php");
    exit;
}

// Validate product ID
$product_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$product_id || $product_id <= 0) {
    $_SESSION['error'] = "Invalid product ID.";
    header("Location: view-products.php");
    exit;
}

// Get and validate all fields
$name = trim($_POST['name'] ?? '');
$hsn_code = trim($_POST['hsn_code'] ?? '');
$category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
$purchase_price = filter_input(INPUT_POST, 'purchase_price', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);
$sale_price = filter_input(INPUT_POST, 'sale_price', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);
$gst = filter_input(INPUT_POST, 'gst', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

// Validate required fields
$errors = [];
if (empty($name)) {
    $errors[] = "Product name is required.";
}
if (!$category_id) {
    $errors[] = "Please select a valid category.";
}
if ($purchase_price === false) {
    $errors[] = "Purchase price must be a positive number.";
}
if ($sale_price === false) {
    $errors[] = "Sale price must be a positive number.";
}
if ($quantity === false) {
    $errors[] = "Quantity must be a non-negative integer.";
}

// Business logic validations
if ($sale_price < $purchase_price) {
    $errors[] = "Sale price cannot be less than purchase price.";
}

// If any errors, redirect back with messages
if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: edit-product.php?id=$product_id");
    exit;
}

// Database operation
try {
    // Check if product exists first
    $check_stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows === 0) {
        $_SESSION['error'] = "Product not found.";
        header("Location: view-products.php");
        exit;
    }
    $check_stmt->close();

    // Update product
    $update_stmt = $conn->prepare("UPDATE products SET 
        name = ?, 
        hsn_code = ?, 
        category_id = ?, 
        purchase_price = ?, 
        sale_price = ?, 
        gst = ?, 
        quantity = ? 
        WHERE id = ?");
    
    $update_stmt->bind_param(
        "ssiddiii",
        $name,
        $hsn_code,
        $category_id,
        $purchase_price,
        $sale_price,
        $gst,
        $quantity,
        $product_id
    );

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Product updated successfully!";
    } else {
        throw new Exception("Database update failed.");
    }

    $update_stmt->close();
    header("Location: view-products.php");
    exit;

} catch (Exception $e) {
    // Log the error for debugging
    error_log("Product update error: " . $e->getMessage());
    
    $_SESSION['error'] = "An error occurred while updating the product. Please try again.";
    header("Location: edit-product.php?id=$product_id");
    exit;
}
?>