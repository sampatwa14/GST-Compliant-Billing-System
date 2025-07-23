<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_id = $_POST['supplier_id'];
    $product_ids = $_POST['product_id']; // array of product_ids
    $quantities = $_POST['quantity'];    // array of quantities

    // Basic validation
    if (!empty($supplier_id) && is_array($product_ids) && is_array($quantities)) {
        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = intval($product_ids[$i]);
            $quantity = intval($quantities[$i]);

            if ($product_id > 0 && $quantity > 0) {
                $stmt = $conn->prepare("INSERT INTO purchases (supplier_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $supplier_id, $product_id, $quantity);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Redirect to view or success page
        header("Location: view-purchase.php?success=1");
        exit;
    } else {
        echo "Invalid form submission.";
    }
} else {
    echo "Invalid request method.";
}
?>
