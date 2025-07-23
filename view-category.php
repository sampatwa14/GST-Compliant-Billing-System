<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

$search = $_GET['search'] ?? '';

// Fetch filtered categories
$query = "SELECT * FROM categories WHERE 1";
if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $query .= " AND name LIKE '%$safeSearch%'";
}
$query .= " ORDER BY name ASC";

$result = mysqli_query($conn, $query);

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM categories WHERE id = $delete_id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "Category deleted successfully.";
        $_SESSION['message_type'] = "success";
        header("Location: view-category.php");
        exit;
    } else {
        $_SESSION['message'] = "Error deleting category: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Categories - ShivKrupa</title>
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

        .add-btn {
            padding: 8px 14px;
            background-color: #1abc9c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 900px;
        
            
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

        .action-btn {
            padding: 5px 10px;
            margin: 0 3px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }

        .delete-btn {
            background-color: #e74c3c;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2>Category List</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?= $_SESSION['message_type'] ?>">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <form method="GET" class="search-form">
        <div>
            <label for="search">Search Category</label>
            <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Enter category name">
        </div>
        <div>
            <button type="submit">Search</button>
        </div>
    </form>

 

    <table>
        <thead>
            <tr>
                <th>Sr no</th>
                <th>Category Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td>
                            
                            <a href="view-category.php?delete_id=<?= $row['id'] ?>" 
                               class="action-btn delete-btn" 
                               onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No categories found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table><br>
       <a href="add-category.php" class="add-btn">+ Add New Category</a>
</div>

<script>
    // Confirm before deleting
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this category?')) {
                e.preventDefault();
            }
        });
    });
</script>
</body>
</html>