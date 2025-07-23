<?php
session_start();
include 'db.php';

$msg = "";

// Fetch categories
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $hsn_code = $_POST['hsn_code'];
    $purchase_price = $_POST['purchase_price'];
    $sale_price = $_POST['sale_price'];
    $gst = $_POST['gst'];
    $category_id = $_POST['category_id'];

    $sql = "INSERT INTO products (name, hsn_code, purchase_price, sale_price, gst, category_id)
            VALUES ('$name', '$hsn_code', '$purchase_price', '$sale_price', '$gst', '$category_id')";

    if (mysqli_query($conn, $sql)) {
        $msg = "✅ Product added successfully!";
    } else {
        $msg = "❌ Error: " . mysqli_error($conn);
    }
}
?>

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product - ShivKrupa</title>
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

        input, select {
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

        .msg {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 6px;
        }
        
        .msg.success {
            background: #e9f9f2;
            color: #27ae60;
        }

        .msg.error {
            background: #fdecea;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="form-box">
            <h2>Add New Product</h2>

            <?php if ($msg): ?>
                <div class="msg <?= strpos($msg, 'successfully') !== false ? 'success' : 'error' ?>">
                    <?= $msg ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label for="name">Product Name</label>
                <input type="text" name="name" required>

                <label>HSN Code:</label>
                <input type="text" name="hsn_code" required>

                <label for="purchase_price">Purchase Price</label>
                <input type="number" step="1" name="purchase_price" required>

                <label for="sale_price">Sale Price</label>
                <input type="number" step="1" name="sale_price" required>

                <label for="gst">GST (%)</label>
                <input type="number" step="1" name="gst" required>

                <label for="category_id">Category</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Add Product</button>
            </form>
        </div>
    </div>
</body>
</html>
