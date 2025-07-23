<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

// Fetch data for cards
$total_sales = 0;
$total_purchases = 0;
$total_customers = 0;
$total_products = 0;
$total_categories = 0;
$total_invoices = 0;
$total_suppliers = 0;

// Total Sales
$sales_result = $conn->query("SELECT SUM(total_amount) AS total_sales FROM invoices");
if ($sales_result && $row = $sales_result->fetch_assoc()) {
    $total_sales = $row['total_sales'] ?? 0;
}

// Total Purchases
$purchase_result = $conn->query("SELECT SUM(quantity * purchase_price) AS total_purchases FROM purchases");
if ($purchase_result && $row = $purchase_result->fetch_assoc()) {
    $total_purchases = $row['total_purchases'] ?? 0;
}

// Count other entities
$total_customers = $conn->query("SELECT COUNT(*) FROM customers")->fetch_row()[0];
$total_products = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$total_categories = $conn->query("SELECT COUNT(*) FROM categories")->fetch_row()[0];
$total_invoices = $conn->query("SELECT COUNT(*) FROM invoices")->fetch_row()[0];
$total_suppliers = $conn->query("SELECT COUNT(*) FROM suppliers")->fetch_row()[0];

// Product stock data


  $stock_items = [];
      $stock_result = $conn->query("SELECT name, quantity FROM products ORDER BY quantity ASC LIMIT 4");
if ($stock_result && $stock_result->num_rows > 0) {
    while ($row = $stock_result->fetch_assoc()) {
        $stock_items[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard - ShivKrupa</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      background-color: #f4f4f4;
    }
    .dashboard-container {
      margin-left: 260px;
      padding: 80px 40px;
    }
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }
    .dashboard-card {
      background-color: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      position: relative;
      transition: 0.3s;
    }
    .dashboard-card:hover {
      transform: translateY(-5px);
    }
    .card-icon {
      font-size: 30px;
      margin-bottom: 10px;
      color: white;
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
    }
    .card-details h3 {
      margin: 5px 0;
      font-size: 18px;
      color: #333;
    }
    .card-details p {
      font-size: 22px;
      font-weight: bold;
      color: #2c3e50;
    }
    .card-link {
      position: absolute;
      bottom: 15px;
      right: 20px;
      color: #3498db;
      text-decoration: none;
      font-weight: bold;
    }
    .card-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <h2>Welcome to ShivKrupa Dashboard</h2>
    <div class="dashboard-grid">

      <!-- Total Sales -->
      <div class="dashboard-card">
        <div class="card-icon" style="background-color: #27ae60;">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="card-details">
          <h3>Total Sales</h3>
          <p>₹<?= number_format($total_sales, 2) ?></p>
        </div>
        <a href="view-invoice.php" class="card-link">View</a>
      </div>

      <!-- Total Purchases -->
      <div class="dashboard-card">
        <div class="card-icon" style="background-color:#8e44ad;">
          <i class="fas fa-tags"></i>
        </div>
        <div class="card-details">
          <h3>Total Purchases</h3>
          <p>₹<?= number_format($total_purchases, 2) ?></p>
        </div>
        <a href="view-purchase.php" class="card-link">View</a>
      </div>

      <!-- Total Customers -->
      <div class="dashboard-card">
        <div class="card-icon" style="background-color: #3498db;">
          <i class="fas fa-users"></i>
        </div>
        <div class="card-details">
          <h3>Total Customers</h3>
          <p><?= $total_customers ?></p>
        </div>
        <a href="view-customer.php" class="card-link">View</a>
      </div>

      <!-- Total Products -->
      <div class="dashboard-card">
        <div class="card-icon" style="background-color: #8e44ad;">
          <i class="fas fa-box-open"></i>
        </div>
        <div class="card-details">
          <h3>Total Products</h3>
          <p><?= $total_products ?></p>
        </div>
        <a href="view-product.php" class="card-link">View</a>
      </div>

      <!-- Total Categories -->
      <div class="dashboard-card">
        <div class="card-icon" style="background-color: #16a085;">
          <i class="fas fa-list-alt"></i>
        </div>
        <div class="card-details">
          <h3>Total Categories</h3>
          <p><?= $total_categories ?></p>
        </div>
        <a href="view-category.php" class="card-link">View</a>
      </div>

      <!-- Total Invoices -->
      <div class="dashboard-card">
        <div class="card-icon" style="background-color: #c0392b;">
          <i class="fas fa-file-invoice"></i>
        </div>
        <div class="card-details">
          <h3>Total Invoices</h3>
          <p><?= $total_invoices ?></p>
        </div>
        <a href="view-invoice.php" class="card-link">View</a>
      </div>

      <!-- Total Suppliers -->
      <div class="dashboard-card">
        <div class="card-icon" style="background-color: #2c3e50;">
          <i class="fa fa-truck"></i>
        </div>
        <div class="card-details">
          <h3>Total Suppliers</h3>
          <p><?= $total_suppliers ?></p>
        </div>
        <a href="view-supplier.php" class="card-link">View</a>
      </div>



<div class="dashboard-card">
  <div class="card-icon" style="background-color: #2980b9;">
    <i class="fas fa-warehouse"></i>
  </div>
  <div class="card-details">
    <h3>Available Stock</h3>
    <small>Click below to see all product's stock</small>
  </div>
  <a href="view-stock.php" class="card-link">View All</a>
</div>


    </div>
  </div>
</body>
</html>
