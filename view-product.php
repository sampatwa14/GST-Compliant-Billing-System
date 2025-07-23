<?php 
session_start();
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

$productNameFilter = $_GET['product_name'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

// Fetch all categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

// Build product query
$query = "SELECT p.*, c.name AS category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1";

if (!empty($productNameFilter)) {
    $safeName = mysqli_real_escape_string($conn, $productNameFilter);
    $query .= " AND p.name LIKE '%$safeName%'";
}
if (!empty($categoryFilter)) {
    $safeCat = mysqli_real_escape_string($conn, $categoryFilter);
    $query .= " AND p.category_id = '$safeCat'";
}
$query .= " ORDER BY p.name ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Products - ShivKrupa</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9fdfc;
        }

        .main-content {
            margin-left: 270px;
            padding: 80px 40px 40px;
            min-height: 100vh;
        }

        h2 {
            color: #1abc9c;
            margin-bottom: 20px;
        }

        .filter-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .filter-form div {
            display: flex;
            flex-direction: column;
        }

        .filter-form label {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .filter-form input[type="text"],
        .filter-form select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 220px;
        }

        .filter-form button {
            padding: 10px 15px;
            background-color: #1abc9c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #1abc9c;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f4f9f8;
        }

        .actions a {
            padding: 6px 10px;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            margin: 2px;
        }

.edit-btn {
            background-color: #1abc9c;
        }

        .edit-btn:hover {
            background-color: #16a085;
        }
        .delete-btn {
            background-color: #e74c3c;
        }

        .actions a:hover {
            opacity: 0.9;
        }

        .low-stock {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="main-content">
    <h2>All Products</h2>

    <form method="GET" class="filter-form">
        <div>
            <label for="product_name">Product Name</label>
            <input type="text" name="product_name" id="product_name" placeholder="Enter product name" value="<?= htmlspecialchars($productNameFilter) ?>">
        </div>

        <div>
            <label for="category">Category</label>
            <select name="category" id="category">
                <option value="">-- All Categories --</option>
                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $categoryFilter) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div style="align-self: flex-end;">
            <button type="submit">Filter</button>
        </div>
    </form>

    <table>
        <thead>
        <tr>
            <th>Sr No</th>
            <th>Name</th>
            <th>HSN Code</th>
            <th>Category</th>
            <th>Purchase Price (₹)</th>
            <th>Sale Price (₹)</th>
            <th>GST (%)</th>
            <th>Stock Quantity</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['hsn_code']) ?></td>
                    <td><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></td>
                    <td>₹<?= number_format($row['purchase_price'], 2) ?></td>
                    <td>₹<?= number_format($row['sale_price'], 2) ?></td>
                    <td><?= $row['gst'] ?>%</td>
                    <td class="<?= $row['quantity'] < 0 ? 'low-stock' : '' ?>">
                        <?= max(0, $row['quantity']) ?>
                    </td>
                    <td class="actions">
                        <a href="edit-product.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>
                        <a href="delete-product.php?id=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure to delete this product?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No products found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
