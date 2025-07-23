<?php
$userName = $_SESSION['user_name'] ?? 'User';
?>

<style>
    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 60px;
        background-color: #1abc9c;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 25px;
        color: white;
        z-index: 1000;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        font-family: 'Segoe UI', sans-serif;
    }

    .navbar .logo {
        font-size: 20px;
        font-weight: bold;
        color: white;
        text-decoration: none;
    }

    .navbar .right-side {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .navbar .username {
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .navbar .logout-btn {
        background-color: white;
        color: #1abc9c;
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
        text-decoration: none;
    }

    .navbar .logout-btn:hover {
        background-color: #16a085;
        color: white;
    }

    body {
        padding-top: 60px;
    }
</style>

<!-- Font Awesome Icon CDN (optional if already included elsewhere) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="navbar">
    <a href="dashboard.php" class="logo">🧾 ShivKrupa Billing</a>
    <div class="right-side">
        <span class="username"><i class="fas fa-user"></i> <?= htmlspecialchars($userName) ?></span>
        <a href="logout.php"><button class="logout-btn">Logout</button></a>
    </div>
</div>
