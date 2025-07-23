<?php
session_start();
include 'db.php';

$result = $conn->query("SELECT * FROM customers ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer List</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    
    <h2>All Customers</h2>
    <a href="add-customer.php">+ Add New Customer</a> | 
    <a href="dashboard.php">← Back to Dashboard</a>
    <br><br>

    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row["id"]; ?></td>
                    <td><?= htmlspecialchars($row["name"]); ?></td>
                    <td><?= htmlspecialchars($row["phone"]); ?></td>
                    <td><?= htmlspecialchars($row["email"]); ?></td>
                    <td><?= nl2br(htmlspecialchars($row["address"])); ?></td>
                    <td>
                        <a href="edit-customer.php?id=<?= $row['id']; ?>">Edit</a> | 
                        <a href="delete-customer.php?id=<?= $row['id']; ?>" onclick="return confirm('Are you sure to delete this customer?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No customers found.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
