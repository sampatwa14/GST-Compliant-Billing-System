<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

// Filters
$search = $_GET['search'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

// Query
$sql = "SELECT invoices.*, customers.name AS customer_name FROM invoices 
        JOIN customers ON invoices.customer_id = customers.id WHERE 1=1";

if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $sql .= " AND customers.name LIKE '%$safeSearch%'";
}
if (!empty($fromDate)) {
    $sql .= " AND invoices.created_at >= '" . mysqli_real_escape_string($conn, $fromDate) . " 00:00:00'";
}
if (!empty($toDate)) {
    $sql .= " AND invoices.created_at <= '" . mysqli_real_escape_string($conn, $toDate) . " 23:59:59'";
}

$sql .= " ORDER BY invoices.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Invoices - ShivKrupa</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9fdfc;
        }

        .main-content {
            margin-left: 270px;
            padding: 80px 40px;
        }

        h2 {
            color: #1abc9c;
            margin-bottom: 20px;
        }

        .search-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            display: flex;
            align-items: flex-end;
            gap: 15px;
            flex-wrap: wrap;
        }

        .search-form div {
            display: flex;
            flex-direction: column;
        }

        .search-form input[type="text"],
        .search-form input[type="date"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 250px;
        }

        .search-form button {
            padding: 10px 16px;
            background-color: #1abc9c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ccc;
        }

        th {
            background-color: #1abc9c;
            color: white;
        }

        .action-link {
            padding: 6px 12px;
            text-decoration: none;
            color: white;
            border-radius: 4px;
            margin: 2px;
            display: inline-block;
        }

        .view {
            background-color: #3498db;
        }

        .view:hover {
            background-color: #2980b9;
        }

        .edit {
            background-color: #1abc9c;
        }

        .edit:hover {
            background-color: #16a085;
        }

        .delete {
            background-color: #e74c3c;
        }

        .delete:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2>Invoice Records</h2>

    <form class="search-form" method="GET">
        <div>
            <label for="search">Customer Name</label>
            <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div>
            <label for="from_date">From Date</label>
            <input type="date" id="from_date" name="from_date" value="<?= htmlspecialchars($fromDate) ?>">
        </div>
        <div>
            <label for="to_date">To Date</label>
            <input type="date" id="to_date" name="to_date" value="<?= htmlspecialchars($toDate) ?>">
        </div>
        <div>
            <button type="submit">Filter</button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Discount</th>
                <th>GST</th>
                <th>Net Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): $i = 1; ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td><?= date('d-m-Y H:i', strtotime($row['created_at'])) ?></td>
                    <td>₹<?= number_format($row['total_amount'] + $row['discount_amount'], 2) ?></td>
                    <td>₹<?= number_format($row['discount_amount'], 2) ?></td>
                    <td>₹<?= number_format($row['gst_amount'], 2) ?></td>
                    <td>₹<?= number_format($row['total_amount'], 2) ?></td>
                    <td>
                        <a class="action-link view" href="print-invoice.php?id=<?= $row['id'] ?>" target="_blank">View</a>
                        <a class="action-link edit" href="edit-invoice.php?id=<?= $row['id'] ?>">Edit</a>
                        <a class="action-link delete" href="delete-invoice.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure to delete this invoice?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No invoices found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>