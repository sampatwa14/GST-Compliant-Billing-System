<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

// Handle filters
$supplierFilter = $_GET['supplier'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

// Build main query
$query = "SELECT purchases.*, suppliers.name AS supplier_name 
          FROM purchases 
          JOIN suppliers ON purchases.supplier_id = suppliers.id 
          WHERE 1";

if (!empty($supplierFilter)) {
    $query .= " AND supplier_name LIKE '%" . mysqli_real_escape_string($conn, $supplierFilter) . "%'";
}
if (!empty($fromDate)) {
    $query .= " AND purchase_date >= '" . mysqli_real_escape_string($conn, $fromDate) . "'";
}
if (!empty($toDate)) {
    $query .= " AND purchase_date <= '" . mysqli_real_escape_string($conn, $toDate) . "'";
}
$query .= " ORDER BY purchase_date DESC";
$result = mysqli_query($conn, $query);

// Total purchase amount calculation
$total_query = "SELECT SUM(purchase_price * quantity) AS total 
                FROM purchases 
                JOIN suppliers ON purchases.supplier_id = suppliers.id 
                WHERE 1";

if (!empty($supplierFilter)) {
    $total_query .= " AND supplier_name LIKE '%" . mysqli_real_escape_string($conn, $supplierFilter) . "%'";
}
if (!empty($fromDate)) {
    $total_query .= " AND purchase_date >= '" . mysqli_real_escape_string($conn, $fromDate) . "'";
}
if (!empty($toDate)) {
    $total_query .= " AND purchase_date <= '" . mysqli_real_escape_string($conn, $toDate) . "'";
}
$total_result = mysqli_query($conn, $total_query);
$total_purchase = mysqli_fetch_assoc($total_result)['total'] ?? 0;

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Fetch quantity and product_id before delete to restore stock
    $purchase = $conn->query("SELECT item_name, quantity FROM purchases WHERE id = $id")->fetch_assoc();
    if ($purchase) {
        // Restore quantity (optional depending on your stock tracking)
        $conn->query("UPDATE products SET quantity = quantity - {$purchase['quantity']} WHERE name = '{$purchase['item_name']}'");

        // Delete purchase record
        $conn->query("DELETE FROM purchases WHERE id = $id");
    }

    header("Location: view-purchase.php?success=" . urlencode("Purchase deleted successfully."));
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Purchases - ShivKrupa</title>
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

        .summary {
            font-size: 18px;
            font-weight: 600;
            color: #333;
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

        .search-form label {
            font-weight: 600;
            margin-bottom: 4px;
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
    <h2>Purchase Records</h2>

    <div class="summary">
        Total Purchases: ₹<?= number_format($total_purchase, 2) ?>
    </div>

    <form method="GET" class="search-form">
        <div>
            <label for="supplier">Supplier Name</label>
            <input type="text" id="supplier" name="supplier" value="<?= htmlspecialchars($supplierFilter) ?>">
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
                <th>Supplier</th>
                <th>Item</th>
                <th>HSN</th>
                <th>Qty</th>
                <th>Purchase Price</th>
                <th>Sale Price</th>
                <th>Total Purchase</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): 
                    $totalPurchase = $row['purchase_price'] * $row['quantity'];
                ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= htmlspecialchars($row['hsn_code']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td>₹<?= number_format($row['purchase_price'], 2) ?></td>
                        <td>₹<?= number_format($row['sale_price'], 2) ?></td>
                        <td>₹<?= number_format($totalPurchase, 2) ?></td>
                        <td><?= date('d-m-Y', strtotime($row['purchase_date'])) ?></td>
                        <td>
                            <a class="action-link edit" href="edit-purchase.php?id=<?= $row['id'] ?>">Edit</a>
                            <a class="action-link delete" href="view-purchase.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this purchase?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10">No purchases found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>