<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

$message = "";

// Delete supplier
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $delete = mysqli_query($conn, "DELETE FROM suppliers WHERE id = $id");
    $message = $delete ? "✅ Supplier deleted successfully." : "❌ Failed to delete supplier.";
}

// Handle search
$search = $_GET['search'] ?? '';
$filter_query = "SELECT * FROM suppliers WHERE 1";
if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $filter_query .= " AND (name LIKE '%$safeSearch%' OR contact LIKE '%$safeSearch%' OR email LIKE '%$safeSearch%' OR address LIKE '%$safeSearch%')";
}
$filter_query .= " ORDER BY name ASC";
$result = mysqli_query($conn, $filter_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Suppliers - ShivKrupa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            text-align: left;
        }

        .search-box {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            align-items: flex-end;
            gap: 15px;
            flex-wrap: wrap;
        }

        .search-box div {
            display: flex;
            flex-direction: column;
        }

        .search-box input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 250px;
        }

        .search-box button {
            padding: 10px 16px;
            background-color: #1abc9c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        

        .message {
            text-align: center;
            font-weight: bold;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .message.success {
            background: #eafaf1;
            color: #27ae60;
        }

        .message.error {
            background: #fdecea;
            color: #e74c3c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
           
        }

        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #1abc9c;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .action-btn {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            margin-right: 5px;
            transition: all 0.3s;
        }

        .edit-btn {
            background-color: #3498db;
            color: white;
        }

        .edit-btn:hover {
            background-color: #2980b9;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }
            
        
    </style>
</head>
<body>
<div class="main-content">
    <h2>All Suppliers</h2>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="GET" class="search-box">
        <div>
            <label for="search">Search Supplier</label>
            <input type="text" id="search" name="search" placeholder="Name, Contact, Email, Address" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div>
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['contact']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                        <td>
                            <a href="edit-supplier.php?id=<?= $row['id'] ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="view-supplier.php?delete=<?= $row['id'] ?>" 
                               class="action-btn delete-btn"
                               onclick="return confirm('Are you sure you want to delete this supplier?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No suppliers found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
</div>

</body>
</html>