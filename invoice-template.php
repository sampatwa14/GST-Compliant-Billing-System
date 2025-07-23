<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $invoice['id'] ?></title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            padding: 20px;
        }
        .header, .footer {
            text-align: center;
        }
        .company-details {
            margin-bottom: 20px;
        }
        h2 {
            color: #1abc9c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
        }
        th {
            background-color: #1abc9c;
            color: #fff;
        }
        .totals td {
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="header">
    <h2>ShivKrupa Traders</h2>
    <div class="company-details">
        <strong>GSTIN:</strong> 27ABCDE1234F1Z5<br>
        <strong>Address:</strong> Nagpur, MH, India
    </div>
</div>

<p><strong>Invoice ID:</strong> <?= $invoice['id'] ?></p>
<p><strong>Date:</strong> <?= date('d-m-Y', strtotime($invoice['created_at'])) ?></p>

<p>
    <strong>Customer:</strong> <?= $invoice['name'] ?><br>
    <strong>Phone:</strong> <?= $invoice['phone'] ?><br>
    <strong>Email:</strong> <?= $invoice['email'] ?><br>
    <strong>Address:</strong> <?= $invoice['address'] ?>
</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Rate</th>
            <th>GST (%)</th>
            <th>Tax Amt</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $i = 1;
        $grand_total = 0;
        $total_gst = 0;
        foreach ($items as $item):
            $price = $item['price'];
            $qty = $item['quantity'];
            $gst = $item['gst'];
            $tax_amount = ($price * $qty * $gst) / 100;
            $total = $price * $qty + $tax_amount;
            $total_gst += $tax_amount;
            $grand_total += $total;
        ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= $item['product_name'] ?></td>
            <td><?= $qty ?></td>
            <td>₹<?= number_format($price, 2) ?></td>
            <td><?= $gst ?>%</td>
            <td>₹<?= number_format($tax_amount, 2) ?></td>
            <td>₹<?= number_format($total, 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr class="totals">
            <td colspan="5">Total GST</td>
            <td colspan="2">₹<?= number_format($total_gst, 2) ?></td>
        </tr>
        <tr class="totals">
            <td colspan="5">Grand Total</td>
            <td colspan="2">₹<?= number_format($grand_total, 2) ?></td>
        </tr>
    </tfoot>
</table>

<div class="footer" style="margin-top: 40px;">
    <p>Thank you for your business!</p>
</div>

</body>
</html>
