<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: view-invoice.php?error=" . urlencode("Invoice ID is missing."));
    exit;
}

$invoice_id = (int)$_GET['id'];

// Begin transaction to rollback if needed
$conn->begin_transaction();
try {
    // Restore product quantities
    $items = $conn->query("SELECT product_id, quantity FROM invoice_items WHERE invoice_id = $invoice_id");
    while ($row = $items->fetch_assoc()) {
        $pid = $row['product_id'];
        $qty = $row['quantity'];
        $conn->query("UPDATE products SET quantity = quantity + $qty WHERE id = $pid");
    }

    // Delete invoice items
    $conn->query("DELETE FROM invoice_items WHERE invoice_id = $invoice_id");

    // Delete invoice
    $conn->query("DELETE FROM invoices WHERE id = $invoice_id");

    $conn->commit();
    header("Location: view-invoice.php?success=" . urlencode("Invoice deleted successfully."));
} catch (Exception $e) {
    $conn->rollback();
    header("Location: view-invoice.php?error=" . urlencode("Failed to delete invoice."));
}
exit;
?>
