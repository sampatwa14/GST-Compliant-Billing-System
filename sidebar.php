<!-- sidebar.php -->
<div class="sidebar">
  <div class="sidebar-inner">
    <a href="dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a>
    <a href="create-invoice.php" class="sidebar-link"><i class="fas fa-plus"></i> New Bill</a>
    <a href="add-supplier.php" class="sidebar-link"><i class="fas fa-money-bill"></i> Add Supplier</a>
    <a href="add-category.php" class="sidebar-link"><i class="fas fa-plus-square"></i> Add Category</a>
    <a href="add-product.php" class="sidebar-link"><i class="fas fa-box"></i> Add Product</a>
    <a href="add-purchase.php" class="sidebar-link"><i class="fas fa-cart-plus"></i> Add Purchase</a>
    <a href="sale-report.php" class="sidebar-link"><i class="fas fa-chart-line"></i> Sales Report</a>
  </div>
</div>

<style>
  .sidebar {
    font-family: 'Segoe UI', sans-serif;
}
.sidebar {
  width: 250px;
  background-color: #2e293b;
  padding: 10px;
  border-top-right-radius: 25px;
  display: flex;
  justify-content: center;
  position: fixed;
  top: 60px;
  bottom: 0;
  left: 0;
  z-index: 999;
}

.sidebar-inner {
  background-color: #1f1b2e;
  width: 100%;
  border-radius: 25px;
  padding-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #fff;
  padding: 12px 20px;
  border-radius: 0 25px 25px 0;
  transition: background-color 0.3s;
  font-size: 15px;
  text-decoration: none;
}

.sidebar-link:hover,
.sidebar-link.active {
  background-color: #1abc9c;
  color: white;
}
</style>
