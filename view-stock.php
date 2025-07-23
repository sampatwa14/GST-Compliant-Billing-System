<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'sidebar.php';

$search = $_GET['search'] ?? '';

$query = "SELECT name, quantity FROM products WHERE 1";
if (!empty($search)) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $query .= " AND name LIKE '%$safe_search%'";
}
$query .= " ORDER BY name ASC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
  <title>All Product Stock - ShivKrupa</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9fdfc;
    }

    .main-content {
      margin-left: 260px;
      padding: 80px 40px;
      min-height: 100vh;
    }

    h2 {
      color: #1abc9c;
      margin-bottom: 20px;
    }

    .search-form {
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .search-form input[type="text"] {
      padding: 10px;
      width: 250px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .search-form button {
      padding: 10px 18px;
      background-color: #1abc9c;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }

    .search-form button:hover {
      background-color: #16a085;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    th, td {
      padding: 12px;
      border: 1px solid #ccc;
      text-align: left;
    }

    th {
      background-color: #1abc9c;
      color: white;
    }

    .low-stock {
      color: #e74c3c;
      font-weight: bold;
    }

    .no-data {
      text-align: center;
      padding: 20px;
      color: #999;
      font-style: italic;
    }
  </style>
</head>
<body>

<div class="main-content">
  <h2> All Product Stock</h2>

  <form method="GET" class="search-form">
    <input type="text" name="search" placeholder="Search by product name" value="<?= htmlspecialchars($search) ?>">
    <button type="submit"><i class="fas fa-search"></i> Search</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>Product Name</th>
        <th>Available Stock</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td class="<?= $row['quantity'] < 5 ? 'low-stock' : '' ?>">
              <?= $row['quantity'] ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="2" class="no-data">No products found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</body>
</html>
