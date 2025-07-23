<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

// Fetch suppliers and products
$suppliers = $conn->query("SELECT id, name FROM suppliers ORDER BY name");
$products = $conn->query("SELECT id, name FROM products ORDER BY name");

// Handle form submission
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'];
    $purchase_date = $_POST['purchase_date'] ?? date('Y-m-d');
    $items = $_POST['items'] ?? [];

    foreach ($items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $purchase_price = $item['purchase_price'];
        $sale_price = $item['sale_price'];

        if ($product_id && $quantity > 0) {
            // Get product info
            $stmt = $conn->prepare("SELECT name, hsn_code FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stmt->bind_result($item_name, $hsn_code);
            $stmt->fetch();
            $stmt->close();

            // Insert into purchases table
            $insert = $conn->prepare("INSERT INTO purchases (supplier_id, item_name, hsn_code, quantity, purchase_price, sale_price, purchase_date, product_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("issiddsi", $supplier_id, $item_name, $hsn_code, $quantity, $purchase_price, $sale_price, $purchase_date, $product_id);
            $insert->execute();
            $insert->close();

            // Update product stock
            $update = $conn->prepare("UPDATE products SET quantity = quantity + ?, purchase_price = ?, sale_price = ? WHERE id = ?");
            $update->bind_param("iddi", $quantity, $purchase_price, $sale_price, $product_id);
            $update->execute();
            $update->close();
        }
    }

    $msg = "✅ Purchase recorded successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Purchase - ShivKrupa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9fdfc;
            margin: 0;
        }

        .main-content {
            margin-left: 270px;
            padding: 80px 20px 20px;
        }

        .form-box {
            background: white;
            padding: 30px;
            max-width: 800px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
        }

        h2 {
            color: #1abc9c;
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-top: 15px;
        }

        select, input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 15px;
            box-sizing: border-box;
            font-size: 15px;
        }

        .item-block {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #fefefe;
        }

        .msg {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            color: green;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
        }

        .button-group button {
            width: 48%;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
        }

        .add-btn {
            background-color: #3498db;
        }

        .submit-btn {
            background-color: #1abc9c;
        }

        .add-btn:hover {
            background-color: #2980b9;
        }

        .submit-btn:hover {
            background-color: #16a085;
        }

        .supplier-row {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;
}

.add-supplier-btn {
    display: inline-block;
    background-color: #3498db;
    color: white;
    padding: 10px 16px;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    white-space: nowrap;
    transition: background-color 0.3s ease;
}

.add-supplier-btn:hover {
    background-color: #2980b9;
}

    </style>

    <script>
        function addItemBlock() {
            const container = document.getElementById('items-container');
            const count = container.children.length;
            const item = document.querySelector('.item-block').cloneNode(true);
            item.querySelectorAll('select, input').forEach(el => {
                const name = el.getAttribute('name');
                if (name) el.setAttribute('name', name.replace(/\[\d+\]/, `[${count}]`));
                el.value = '';
            });
            container.appendChild(item);
        }
    </script>
</head>
<body>
<div class="main-content">
    <div class="form-box">
        <h2>Add Purchase</h2>

        <?php if ($msg): ?>
            <div class="msg"><?= $msg ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="supplier-row">
            <div style="flex: 1;">
                <label for="supplier_id">Supplier</label>
                <select name="supplier_id" required>
                    <option value="">-- Select Supplier --</option>
                    <?php while ($s = $suppliers->fetch_assoc()): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="padding-left: 10px; margin-top: 41px;">
                <a href="add-supplier.php" class="add-supplier-btn">+ Add New Supplier</a>
            </div>
        </div>


            <label for="purchase_date">Purchase Date</label>
            <input type="date" name="purchase_date" value="<?= date('Y-m-d') ?>" required>

            <div id="items-container">
                <div class="item-block">
                    <label>Product</label>
                    <select name="items[0][product_id]" required>
                        <option value="">-- Choose Product --</option>
                        <?php
                        $products2 = $conn->query("SELECT id, name FROM products ORDER BY name");
                        while ($p = $products2->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label>Quantity</label>
                    <input type="number" name="items[0][quantity]" min="1" required>

                    <label>Purchase Price</label>
                    <input type="number" step="1" name="items[0][purchase_price]" required>

                    <label>Sale Price</label>
                    <input type="number" step="1" name="items[0][sale_price]" required>
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="add-btn" onclick="addItemBlock()">+ Add Another Item</button>
                <button type="submit" class="submit-btn">Save Purchase</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
