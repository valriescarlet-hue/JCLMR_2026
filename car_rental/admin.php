<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JCLMR Drive | Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body data-role="admin">

<div class="layout">
    <aside class="sidebar">
        <h2 class="logo">JCLMR <span>DRIVE</span></h2>
        <p class="side-label">ADMIN PANEL</p>

        <nav>
            <button class="nav-btn active" data-page="dashboard">▦ Dashboard</button>
            <button class="nav-btn" data-page="payments">₱ Payments</button>
            <button class="nav-btn" data-page="reports">▤ Reports & Analysis</button>
            <button class="nav-btn" data-page="vehicles">🚘 Vehicles</button>
            <button class="nav-btn" data-page="maintenance">🔧 Maintenance</button>
            <button class="nav-btn" data-page="rentals">📋 Rentals & Returns</button>
        </nav>

        <div class="sidebar-bottom">
            <p>Logged in as</p>
            <strong>Administrator</strong>
            <a href="logout.php" class="switch-link">Log out→</a>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <h2 id="page-title">Dashboard Overview</h2>
                <p>Welcome back, Administrator.</p>
            </div>
            <div class="profile">
                <span class="status-dot"></span>
                <strong>Administrator</strong>
                <div class="avatar">AD</div>
            </div>
        </header>

        <section id="content" class="content"></section>
    </main>
</div>
<script src="admin.js"></script>
</body>
</html>