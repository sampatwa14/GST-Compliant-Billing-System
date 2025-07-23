<?php
session_start();
include 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if (!empty($name)) {
        $query = "INSERT INTO categories (name) VALUES ('$name')";
        if (mysqli_query($conn, $query)) {
            $message = "✅ Category added successfully!";
        } else {
            $message = "❌ Failed to add category: " . mysqli_error($conn);
        }
    } else {
        $message = "❗ Category name cannot be empty.";
    }
}
?>
<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Category - ShivKrupa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9fdfc;
            margin: 0;
        }

        .main-content {
            margin-left: 270px;
            padding: 80px 20px 20px 20px;
            min-height: 100vh;
        }

        .form-box {
            background: #fff;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #1abc9c;
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-top: 15px;
        }

        input {
            width: 96%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        button {
            width: 100%;
            background-color: #1abc9c;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #17a589;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 6px;
        }

        .message.success {
            background: #e9f9f2;
            color: #27ae60;
        }

        .message.error {
            background: #fdecea;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="form-box">
            <h2>Add New Category</h2>

            <?php if ($message): ?>
                <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" placeholder="Enter category name" required>

                <button type="submit">Add Category</button>
            </form>
        </div>
    </div>
</body>
</html>
