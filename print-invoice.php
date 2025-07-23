<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    echo "✖ Invoice ID is required.";
    exit;
}

$invoice_id = (int)$_GET['id'];

$invoice_sql = "SELECT invoices.*, customers.name, customers.address, customers.phone, customers.email
                FROM invoices 
                JOIN customers ON invoices.customer_id = customers.id 
                WHERE invoices.id = $invoice_id";
$invoice_result = $conn->query($invoice_sql);

if ($invoice_result->num_rows === 0) {
    echo "✖ Invoice not found.";
    exit;
}
$invoice = $invoice_result->fetch_assoc();

$sql = "SELECT ii.quantity, ii.sale_price, ii.gst, ii.warranty, ii.hsn_code, p.name
        FROM invoice_items ii 
        JOIN products p ON ii.product_id = p.id 
        WHERE ii.invoice_id = $invoice_id";
$items = $conn->query($sql);

// Get discounts from session if they exist
$discounts = isset($_SESSION['invoice_discounts'][$invoice_id]) ? $_SESSION['invoice_discounts'][$invoice_id] : [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?= $invoice_id ?></title>
    <style>
       @media print {
    .no-print, .no-print * {
        display: none !important;
    }
}

    @media screen {
    .no-print {
        display: flex;
        text-align: right;
        margin-bottom: 0px;
        margin-left: 870px;
    }
}
         @page {
            size: 6in 8.5in;
            margin: 0.2in;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.3;
            font-size: 10px;
            background-color: white;
        }
        .invoice {
            width: 5.6in;
            height: 8.1in;
            margin: 0 auto;
            padding: 0.1in;
            box-sizing: border-box;
            position: relative;
        }
       
        .header {
            text-align: center;
            margin-bottom: 0.1in;
        }
        .header h1 {
           
            color: white;
            background-color: #1abc9c;
            padding: 0.1in;
            font-size: 12px;
            border-radius: 3px;
        }
        .header h2 {
            margin: 0.05in 0;
            color: #000000;
            font-size: 10px;
        }
        .header div {
            font-size: 9px;
        }
        .contact-info {
            border-bottom: 1px solid #1abc9c;
            padding-bottom: 0.1in;
            margin-bottom: 0.1in;
            font-size: 9px;
        }
        .inline-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.05in;
            margin-top: 5px;
        }
        .customer-section {
            display: flex;
            justify-content: space-between;
            margin: 0.1in 0;
        }
        .customer-details {
            width: 65%;
            padding: 0.1in;
            background-color: #f0f9f7;
            border-left: 2px solid #1abc9c;
            font-size: 9px;
        }
        .customer-details div {
            margin: 0.05in 0;
        }
        .qr-code {
            width: 35%;
            padding: 0.1in;
            background-color: #f0f9f7;
            border: 1px solid #1abc9c;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 8px;
        }
        .qr-code img {
            width: 1in;
            height: 1in;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.1in 0;
            font-size: 8px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #1abc9c;
            color: white;
            padding: 0.05in;
            text-align: left;
        }
        td {
            padding: 0.05in;
            background-color: white;
        }
        tr:nth-child(even) td {
            background-color: #f0f9f7;
        }
        .final-bill-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.1in 0;
            font-size: 8px;
        }
        .final-bill-table th, 
        .final-bill-table td {
            padding: 0.03in;
        }
        .bank-details {
            margin-top: 0.3in;
            font-weight: bold;
            padding: 0.3in;
            background-color: #f0f9f7;
            border-left: 2px solid #1abc9c;
            font-size: 8px;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 0.7in;
            padding-top: 0.1in;
            font-size: 9px;
        }
        .footer div {
            padding: 0.1in;
            background-color: #f0f9f7;
            font-weight: bold;
            color: #000000;
        }
        .note {
            text-align: center;
            
            font-style: italic;
            color: #666;
            font-size: 8px;
            position: absolute;
            bottom: 0.6in;
            left: 0;
            right: 0;
        }
        .copyright {
            text-align: center;
            margin-top: 0.1in;
            color: #666;
            font-size: 7px;
            position: absolute;
            bottom: 0.5in;
            left: 0;
            right: 0;
        }
        .highlight {
            font-weight: bold;
        }
        .section-title {
            font-weight: bold;
            font-size: 9px;
            margin: 0.1in 0 0.05in 0;
        }
        .print-btn {
            background-color: #1abc9c;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            display: inline-block;
            margin: 10px auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .print-btn:hover {
            background-color: #159b87;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .button-container {
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="no-print">
    <div class="button-container">
        <button onclick="window.print()" class="print-btn">🖨️ Print Invoice</button>
    </div>
</div>

<div class="invoice">
    <div class="header">
        <h1>Tax Invoice</h1>
        <h2>ShivKrupa Computer & Laptop Repairing Center</h2>
        <div class="contact-info">
            <div>Address: Madhuprabha Complex, Near Hanuman temple, Gadge nagar, Amravati - 444603</div>
            <div class="inline-details">
                <div><strong>Mobile No.:</strong> 9270223242</div>
                <div><strong>Email id:</strong> shivkrupacomputer22@gmail.com</div>
                <div><strong>Date:</strong> <?= date('d-m-Y', strtotime($invoice['created_at'])) ?></div>
            </div>
        </div>
    </div>

    <div class="customer-section">
        <div class="customer-details">
            <div><strong>Bill Number:</strong> <?= $invoice_id ?></div>
            <div><strong>Customer Name:</strong> <?= htmlspecialchars($invoice['name']) ?></div>
            <div><strong>Mobile:</strong> <?= $invoice['phone'] ?></div>
            <div><strong>Email Id:</strong> <?= htmlspecialchars($invoice['email']) ?></div>
            <div><strong>Address:</strong> <?= htmlspecialchars($invoice['address']) ?></div>
        </div>
        <div class="qr-code">
            <img src="qr.png" alt="QR Code">
            <div>Scan to Pay</div>
        </div>
    </div>

    <div class="section-title">Products Purchased:</div>
    <table>
        <thead>
            <tr>
                <th>Sr.No</th>
                <th>Product</th>
                <th>HSN</th>
                <th>Qty</th>
                <th>Warranty</th>
                <th>Rate</th>                   
                <th>GST%</th>
                <th>Discount%</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        $subtotal = 0;
        $total_gst = 0;
        $total_discount = 0;
        while ($row = $items->fetch_assoc()):
            $price = $row['sale_price'];
            $qty = $row['quantity'];
            $gst = $row['gst'];
            $discount = isset($discounts[$i-1]) ? $discounts[$i-1] : 0;
            
            $base = $price * $qty;
            $discount_amt = ($base * $discount) / 100;
            $discounted_base = $base - $discount_amt;
            $gst_amt = ($discounted_base * $gst) / 100;
            $line_total = $discounted_base + $gst_amt;
            
            $subtotal += $base;
            $total_gst += $gst_amt;
            $total_discount += $discount_amt;
        ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= $row['hsn_code'] ?></td>
                <td><?= $qty ?></td>
                <td><?= $row['warranty'] ?> Months</td>
                <td>₹<?= number_format($price, 2) ?></td>               
                <td><?= $gst ?>%</td>
                <td><?= $discount ?>%</td>
                <td>₹<?= number_format($line_total, 2) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <div class="section-title">Final Bill Details:</div>
    <table class="final-bill-table">
        <tr>
            <th style="width: 40%;">Bank Details</th>
            <th style="width: 30%;">Description</th>
            <th style="width: 30%;">Amount (in Rs.)</th>
        </tr>
        <tr>
            <td rowspan="5" style="text-align: left; vertical-align: top;">
                <strong>Union Bank Of India</strong><br>
                ACCOUNT NUMBER : 621701010060480<br><br>
                BANK IFSC CODE : UBIN0562173
            </td>
            <td>Subtotal:</td>
            <td>₹<?= number_format($subtotal, 2) ?></td>
        </tr>
        <tr>
            <td>Discount:</td>
            <td>₹<?= number_format($total_discount, 2) ?></td>
        </tr>
        <tr>
            <td>SGST Amount:</td>
            <td>₹<?= number_format($total_gst/2, 2) ?></td>
        </tr>
        <tr>
            <td>CGST Amount:</td>
            <td>₹<?= number_format($total_gst/2, 2) ?></td>
        </tr>
        <tr>
            <td><strong>Total After Taxes:</strong></td>
            <td><strong>₹<?= number_format($subtotal - $total_discount + $total_gst, 2) ?></strong></td>
        </tr>
    </table>

    <div class="footer">
        <div>Customer Signature</div>
        <div>Shivkrupa Computer</div>
    </div>
    <div class="note">
        Note: This is a system-generated invoice. प्रॉडक्टची वॉरंटी ही कंपनीच्या नियमांनुसार राहील.
    </div>

    <div class="copyright">
        © <?= date('Y') ?> Shivkrupa Computer & Laptop Repairing Center. All rights reserved.
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.print-btn').addEventListener('click', function() {
            window.print();
        });
    });
</script>
</body>
</html>