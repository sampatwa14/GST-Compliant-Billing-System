<?php 
session_start();
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

$search = $_GET['search'] ?? '';

$query = "SELECT * FROM customers WHERE 1";
if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $query .= " AND (name LIKE '%$safeSearch%' OR email LIKE '%$safeSearch%' OR phone LIKE '%$safeSearch%')";
}
$query .= " ORDER BY name ASC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Customers - ShivKrupa</title>
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

        .search-form input[type="text"] {
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
    <h2>Customer Records</h2>

    <form method="GET" class="search-form">
        <div>
            <label for="search">Search Customer</label>
            <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Enter name, phone or email">
        </div>
        <div>
            <button type="submit">Search</button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td>
                        <a class="action-link edit" href="edit-customer.php?id=<?= $row['id'] ?>">Edit</a>
                        <a class="action-link delete" href="delete-customer.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this customer?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No customers found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
