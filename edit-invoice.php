<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

if (!isset($_GET['id'])) {
    echo "Invoice ID missing.";
    exit;
}

$id = intval($_GET['id']);
$query = "SELECT invoices.*, customers.name AS customer_name FROM invoices 
          JOIN customers ON invoices.customer_id = customers.id 
          WHERE invoices.id = $id";
$result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) === 0) {
    echo "Invoice not found.";
    exit;
}
$invoice = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Invoice</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9fdfc;
        }
        .main-content {
            margin-left: 270px;
            padding: 80px 30px;
        }
        h2 {
            color: #1abc9c;
            margin-bottom: 25px;
            text-align: center;
        }
        form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"],
        input[type="date"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        button {
            background-color: #1abc9c;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #16a085;
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2>Edit Invoice</h2>
    <form method="POST" action="update-invoice.php">
        <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">

        <label for="customer">Customer</label>
        <select name="customer_id" id="customer" required>
            <?php
            $customers = mysqli_query($conn, "SELECT id, name FROM customers");
            while ($cust = mysqli_fetch_assoc($customers)):
            ?>
                <option value="<?= $cust['id'] ?>" <?= $cust['id'] == $invoice['customer_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cust['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="date">Date</label>
        <input type="date" name="created_at" id="date" value="<?= date('Y-m-d', strtotime($invoice['created_at'])) ?>" required>

        <label for="total">Total Amount</label>
        <input type="number" step="0.01" name="total_amount" id="total" value="<?= $invoice['total_amount'] ?>" required>

        <label for="discount">Discount Amount</label>
        <input type="number" step="0.01" name="discount_amount" id="discount" value="<?= $invoice['discount_amount'] ?>" required>

        <label for="gst">GST Amount</label>
        <input type="number" step="0.01" name="gst_amount" id="gst" value="<?= $invoice['gst_amount'] ?>" required>

        <button type="submit">Update Invoice</button>
    </form>
</div>
</body>
</html>
