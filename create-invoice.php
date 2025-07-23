<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_POST['customer_id'];
    $products = $_POST['product'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['price'];
    $gst_values = $_POST['gst'];
    $warranties = $_POST['warranty'];
    $hsns = $_POST['hsn_code'];
    $discounts = $_POST['discount']; 

    // Calculate totals
    $subtotal = 0;
    $gst_amount = 0;
    $total_discount = 0;
    
    foreach ($products as $i => $product_id) {
        $qty = $quantities[$i];
        $price = $prices[$i];
        $gst = $gst_values[$i];
        $discount = $discounts[$i]; 
        
        $line_total = $price * $qty;
        $line_discount = ($line_total * $discount) / 100;
        $subtotal += $line_total;
        $total_discount += $line_discount;
        $gst_amount += (($line_total - $line_discount) * $gst) / 100;
    }

    $total_amount = $subtotal - $total_discount + $gst_amount;

    // Insert into invoices table
    $stmt = $conn->prepare("INSERT INTO invoices (customer_id, total_amount, discount_amount, gst_amount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iddd", $customer_id, $total_amount, $total_discount, $gst_amount);
    $stmt->execute();
    $invoice_id = $stmt->insert_id;
    $stmt->close();

    // Store discounts in session for print page
    $_SESSION['invoice_discounts'][$invoice_id] = $discounts;

    // Insert invoice items
    $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_id, quantity, sale_price, gst, warranty, hsn_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
    for ($i = 0; $i < count($products); $i++) {
        $stmt->bind_param("iiiddis", $invoice_id, $products[$i], $quantities[$i], $prices[$i], $gst_values[$i], $warranties[$i], $hsns[$i]);
        $stmt->execute();

    // Update product stock
        $update = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
        $update->bind_param("ii", $quantities[$i], $products[$i]);
        $update->execute();
        $update->close();
    }
    $stmt->close();

    header("Location: print-invoice.php?id=" . $invoice_id);
    exit;
}

$customers = $conn->query("SELECT * FROM customers ORDER BY name ASC");
$products = $conn->query("SELECT * FROM products ORDER BY name ASC");
?>
<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Invoice</title>
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
            max-width: 800px;
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
            margin-bottom: 5px;
        }

        input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }

        button {
            background-color: #1abc9c;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #17a589;
        }

        .customer-container {
            margin-bottom: 20px;
        }

        .customer-select-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .customer-select {
            flex: 1;
        }

        .add-customer-btn {
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 14px;
            height: 40px;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .add-customer-btn:hover {
            background-color: #2980b9;
        }

        .product-row {
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .product-grid .form-group {
            margin-bottom: 0;
        }

        .summary-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .discount-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .discount-input input {
            width: 80px;
            text-align: right;
        }

        .button-row {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .button-row button {
            flex: 1;
        }

        .add-more-btn {
            background-color: #3498db;
        }
        
        .add-more-btn:hover {
            background-color: #2980b9;
        }
       
    </style>
    <script>
       
        const productPrices = {
            <?php 
            $products->data_seek(0); 
            while($prod = $products->fetch_assoc()): 
                echo "'{$prod['id']}': {$prod['sale_price']},";
            endwhile; 
            ?>
        };

        function addProductRow() {
            const container = document.getElementById('products-container');
            const row = document.querySelector('.product-row').cloneNode(true);
            row.querySelectorAll('input').forEach(input => input.value = '');
            
            const productSelect = row.querySelector('select[name="product[]"]');
            productSelect.addEventListener('change', function() {
                updateProductPrice(this);
            });
            
            container.appendChild(row);
        }

        function updateProductPrice(selectElement) {
            const productId = selectElement.value;
            const row = selectElement.closest('.product-row');
            const priceInput = row.querySelector('input[name="price[]"]');
            
            if (productId && productPrices[productId]) {
                priceInput.value = productPrices[productId];
            }
        }

        // existing product selects
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('select[name="product[]"]').forEach(select => {
                select.addEventListener('change', function() {
                    updateProductPrice(this);
                });
                
                // Trigger change event if a product is already selected
                if (select.value) {
                    select.dispatchEvent(new Event('change'));
                }
            });
        });
    </script>
</head>
<body>

<div class="main-content">
    <div class="form-box">
        <h2>Create Invoice</h2>

        <form method="POST" action="">
            <div class="customer-container">
                <label>Select Customer:</label>
                <div class="customer-select-container">
                    <div class="customer-select">
                        <select name="customer_id" required>
                            <option value="">-- Select --</option>
                            <?php $customers->data_seek(0); while($cust = $customers->fetch_assoc()): ?>
                                <option value="<?= $cust['id'] ?>"><?= $cust['name'] ?> - <?= $cust['phone'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <a href="add-customer.php" class="add-customer-btn">Add New Customer</a>
                </div>
            </div>

            <div id="products-container">
                <div class="product-row">
                    <div class="product-grid">
                        <div class="form-group">
                            <label>Product:</label>
                            <select name="product[]" required>
                                <option value="">-- Select --</option>
                                <?php $products->data_seek(0); while($prod = $products->fetch_assoc()): ?>
                                    <option value="<?= $prod['id'] ?>"><?= $prod['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Quantity:</label>
                            <input type="number" name="quantity[]" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Sale Price:</label>
                            <input type="number" step="1" name="price[]" required>
                        </div>
                        
                        <div class="form-group">
                            <label>GST (%):</label>
                            <input type="number" step="1" name="gst[]" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Warranty (Months):</label>
                            <input type="number" name="warranty[]" required>
                        </div>
                        
                        <div class="form-group">
                            <label>HSN Code:</label>
                            <input type="text" name="hsn_code[]" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Discount (%):</label>
                            <input type="number" step="1" min="0" max="100" name="discount[]" value="0" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="button-row">
                <button type="button" class="add-more-btn" onclick="addProductRow()">+ Add More Products</button>
                <button type="submit">Generate Invoice</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>