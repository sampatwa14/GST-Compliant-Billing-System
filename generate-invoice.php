<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_id = $_POST['customer_id'] ?? null;
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['price'] ?? [];
    $warranties = $_POST['warranty'] ?? [];
    $gsts = $_POST['gst'] ?? [];
    $hsn_codes = $_POST['hsn_code'] ?? [];
    $discount = isset($_POST['discount']) ? floatval($_POST['discount']) : 0.0;

    if (!$customer_id || count($product_ids) === 0) {
        header("Location: create-invoice.php?stock_warning=" . urlencode("Please select customer and at least one product."));
        exit;
    }

    for ($i = 0; $i < count($product_ids); $i++) {
        $pid = (int)$product_ids[$i];
        $qty = (int)$quantities[$i];

        $check = $conn->prepare("SELECT name, quantity FROM products WHERE id = ?");
        $check->bind_param("i", $pid);
        $check->execute();
        $check->bind_result($product_name, $available);
        if (!$check->fetch()) {
            $check->close();
            continue;
        }
        $check->close();

        if ($qty > $available) {
            $msg = "Insufficient stock for '{$product_name}'. Available: $available, Requested: $qty";
            header("Location: create-invoice.php?stock_warning=" . urlencode($msg));
            exit;
        }
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO invoices (customer_id, created_at, discount) VALUES (?, NOW(), ?)");
        $stmt->bind_param("id", $customer_id, $discount);
        $stmt->execute();
        $invoice_id = $stmt->insert_id;
        $stmt->close();

        $total_amount = 0;
        $total_gst = 0;

        for ($i = 0; $i < count($product_ids); $i++) {
            $pid = (int)$product_ids[$i];
            $qty = (int)$quantities[$i];
            $price = floatval($prices[$i]);
            $gst = floatval($gsts[$i]);
            $warranty = (int)$warranties[$i];
            $hsn = $hsn_codes[$i] ?? '';

            $base = $price * $qty;
            $gst_amt = ($base * $gst) / 100;
            $line_total = $base + $gst_amt;

            $total_amount += $line_total;
            $total_gst += $gst_amt;

            $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_id, quantity, sale_price, gst, warranty, hsn_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiddis", $invoice_id, $pid, $qty, $price, $gst, $warranty, $hsn);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $stmt->bind_param("ii", $qty, $pid);
            $stmt->execute();
            $stmt->close();
        }

        $discount_amount = ($total_amount * $discount) / 100;
        $final_total = $total_amount - $discount_amount;

        $stmt = $conn->prepare("UPDATE invoices SET total_amount = ?, gst_amount = ?, discount_amount = ? WHERE id = ?");
        $stmt->bind_param("dddi", $final_total, $total_gst, $discount_amount, $invoice_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        header("Location: print-invoice.php?id=" . $invoice_id);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        echo "<div style='color: red; text-align: center; font-weight: bold;'>❌ Error generating invoice: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div style='color: red; text-align: center; font-weight: bold;'>Invalid request method.</div>";
}
?>