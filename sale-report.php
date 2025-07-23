<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

// Get filters
$search = $_GET['search'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

// Build query
$query = "SELECT invoices.id, invoices.created_at, customers.name AS customer_name, invoices.total_amount 
          FROM invoices 
          JOIN customers ON invoices.customer_id = customers.id 
          WHERE 1";

if (!empty($search)) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $query .= " AND (customers.name LIKE '%$safe_search%' 
               OR invoices.id IN (
                    SELECT invoice_id FROM invoice_items 
                    JOIN products ON invoice_items.product_id = products.id 
                    WHERE products.name LIKE '%$safe_search%'
               ))";
}

if (!empty($fromDate)) {
    $query .= " AND DATE(invoices.created_at) >= '$fromDate'";
}
if (!empty($toDate)) {
    $query .= " AND DATE(invoices.created_at) <= '$toDate'";
}

$query .= " ORDER BY invoices.created_at DESC";
$result = mysqli_query($conn, $query);

// Total sales
$total_query = "SELECT SUM(total_amount) AS total FROM invoices WHERE 1";
if (!empty($fromDate)) {
    $total_query .= " AND DATE(created_at) >= '$fromDate'";
}
if (!empty($toDate)) {
    $total_query .= " AND DATE(created_at) <= '$toDate'";
}
$total_result = mysqli_query($conn, $total_query);
$total_sales = mysqli_fetch_assoc($total_result)['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9fdfc;
        }

        .main-content {
            margin-left: 260px;
            padding: 80px 40px;
        }

        h2 {
            color: #1abc9c;
            margin-bottom: 20px;
        }

        .summary {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .filter-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-form input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 200px;
        }

        .filter-form label {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .filter-form div {
            display: flex;
            flex-direction: column;
        }

        .filter-form button {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #1abc9c;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .no-results {
            text-align: center;
            color: #888;
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2>Sales Report</h2>

    <div class="summary">
        Total Sales: ₹<?= number_format($total_sales, 2) ?>
    </div>

    <form method="GET" class="filter-form">
        <div>
            <label>Customer Name</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search">
        </div>
        <div>
            <label>From Date</label>
            <input type="date" name="from_date" value="<?= htmlspecialchars($fromDate) ?>">
        </div>
        <div>
            <label>To Date</label>
            <input type="date" name="to_date" value="<?= htmlspecialchars($toDate) ?>">
        </div>
        <div style="align-self: flex-end;">
            <button type="submit">Filter</button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Invoice ID</th>
                <th>Customer</th>
                <th>Date and time</th>
                <th>Total Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td><?= date('d-m-Y H:i:s', strtotime($row['created_at'])) ?></td>
                    <td>₹<?= number_format($row['total_amount'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4" class="no-results">No invoices found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
