<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    echo "❌ Invoice ID is required.";
    exit;
}

$invoice_id = (int)$_GET['id'];

$invoice_sql = "SELECT invoices.*, customers.name, customers.address, customers.phone, customers.email
                FROM invoices 
                JOIN customers ON invoices.customer_id = customers.id 
                WHERE invoices.id = $invoice_id";
$invoice_result = $conn->query($invoice_sql);

if ($invoice_result->num_rows === 0) {
    echo "❌ Invoice not found.";
    exit;
}
$invoice = $invoice_result->fetch_assoc();

$items_sql = "SELECT ii.quantity, ii.sale_price, ii.gst, ii.warranty, ii.hsn_code, p.name
              FROM invoice_items ii 
              JOIN products p ON ii.product_id = p.id 
              WHERE ii.invoice_id = $invoice_id";
$items_result = $conn->query($items_sql);

// Calculate totals with GST validation
$subtotal = 0;
$total_gst = 0;
$items = [];
while ($row = $items_result->fetch_assoc()) {
    $base = $row['sale_price'] * $row['quantity'];
    $gst_percent = max(0, $row['gst']); // Ensure GST is never negative
    $gst_amt = ($base * $gst_percent) / 100;
    $line_total = $base + $gst_amt;
    
    $items[] = [
        'name' => $row['name'],
        'hsn_code' => $row['hsn_code'],
        'quantity' => $row['quantity'],
        'sale_price' => $row['sale_price'],
        'warranty' => $row['warranty'],
        'gst' => $gst_percent,
        'total' => max(0, $line_total)
    ];
    
    $subtotal += $base;
    $total_gst += $gst_amt;
}

$sgst = max(0, $total_gst / 2);
$cgst = max(0, $total_gst / 2);
$grand_total = max(0, $subtotal + $total_gst);

if (isset($invoice['total_amount'])) {
    $grand_total = max(0, $invoice['total_amount']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $invoice_id ?></title>
    <style>
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
        .print-controls {
            text-align: center;
            margin-bottom: 10px;
        }
        .print-btn {
            background-color: #1abc9c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        @media print {
            .print-controls {
                display: none;
            }
            body {
                background-color: white;
            }
            .invoice {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
            }
        }
        .header {
            text-align: center;
            margin-bottom: 0.1in;
        }
        .header h1 {
            margin: 0.03in 0;
            color: white;
            background-color: #1abc9c;
            padding: 0.05in;
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
            width: 1.2in;
            height: 1.2in;
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
            margin-top: 0.2in;
            font-style: italic;
            color: #666;
            font-size: 8px;
            position: absolute;
            bottom: 0.3in;
            left: 0;
            right: 0;
        }
        .copyright {
            text-align: center;
            margin-top: 0.1in;
            color: #666;
            font-size: 7px;
            position: absolute;
            bottom: 0.1in;
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
    </style>
</head>
<body>
    <div class="print-controls">
        <button onclick="window.print()" class="print-btn">🖨️ Print Invoice</button>
    </div>

    <div class="invoice">
        <div class="header">
            <h1>Tax Invoice</h1>
            <h2>ShivKrupa Computer & Laptop Repairing Center</h2>
            <div>Address: Madhuprabha Complex, Near Hanuman temple, Gadge nagar, Amravati - 444603</div>
        </div>

        <div class="contact-info">
            <div class="inline-details">
                <div><strong>Mobile No.:</strong> 9270223242</div>
                <div><strong>Email id:</strong> shivkrupacomputer22@gmail.com</div>
                <div><strong>Date:</strong> <?= date('d-m-Y', strtotime($invoice['created_at'])) ?></div>
            </div>
        </div>

        <div class="customer-section">
            <div class="customer-details">
                <div><strong>Bill Number:</strong> <?= $invoice_id ?></div>
                <div><strong>Customer Name:</strong> <?= htmlspecialchars($invoice['name']) ?></div>
                <div><strong>Mobile:</strong> <?= $invoice['phone'] ?></div>
                <div><strong>Email:</strong> <?= htmlspecialchars($invoice['email']) ?></div>
                <div><strong>Address:</strong> <?= htmlspecialchars($invoice['address']) ?></div>
            </div>
            <div class="qr-code">
                <img src="qr.png" alt="Payment QR Code">
                <div>Scan to pay ₹<?= number_format($grand_total, 2) ?></div>
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
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['hsn_code'] ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= $item['warranty'] ?>M</td>
                    <td>₹<?= number_format($item['sale_price'], 2) ?></td>
                    <td><?= $item['gst'] ?>%</td>
                    <td class="highlight">₹<?= number_format($item['total'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="section-title">Final Bill Details:</div>
        <table class="final-bill-table">
            <tr>
                <td rowspan="5" style="width: 50%; vertical-align: top; background-color: #f0f9f7; font-size: 7px;">
                    <strong>Bank Details</strong><br>
                    Union Bank Of India<br>
                    A/C: 621701010050480<br>
                    IFSC: UBIN0562173
                </td>
                <td>Total Amount:</td>
                <td>₹<?= number_format($subtotal, 2) ?></td>
            </tr>
            <tr>
                <td>GST Amount:</td>
                <td>₹<?= number_format($total_gst, 2) ?></td>
            </tr>
            <tr>
                <td>SGST Amount:</td>
                <td>₹<?= number_format($sgst, 2) ?></td>
            </tr>
            <tr>
                <td>CGST Amount:</td>
                <td>₹<?= number_format($cgst, 2) ?></td>
            </tr>
            <tr>
                <td class="highlight">Total:</td>
                <td class="highlight">₹<?= number_format($grand_total, 2) ?></td>
            </tr>
        </table>

        <div class="footer">
            <div><strong>Customer Signature</strong></div>
            <div><strong>Shivkrupa Computer</strong></div>
        </div>

        <div class="note">
            Note: This is a system-generated invoice. प्रॉडक्टची वॉरंटी ही कंपनीच्या नियमांनुसार राहील.
        </div>

        <div class="copyright">
            © <?= date('Y') ?> Shivkrupa Computer & Laptop Repairing Center. All rights reserved.
        </div>
    </div>
</body>
</html>