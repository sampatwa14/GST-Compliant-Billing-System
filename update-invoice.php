<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $invoice_id = intval($_POST['invoice_id']);
    $customer_id = intval($_POST['customer_id']);
    $created_at = $_POST['created_at'];
    $total_amount = floatval($_POST['total_amount']);
    $discount_amount = floatval($_POST['discount_amount']);
    $gst_amount = floatval($_POST['gst_amount']);

    $stmt = $conn->prepare("UPDATE invoices 
                            SET customer_id = ?, created_at = ?, total_amount = ?, discount_amount = ?, gst_amount = ?
                            WHERE id = ?");
    $stmt->bind_param("isdddi", $customer_id, $created_at, $total_amount, $discount_amount, $gst_amount, $invoice_id);

    if ($stmt->execute()) {
        header("Location: view-invoice.php?update_success=1");
        exit;
    } else {
        echo "❌ Failed to update invoice: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "❌ Invalid request method.";
}
?>
