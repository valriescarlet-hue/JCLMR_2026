
<?php
session_start();

// Only authenticated head administrators may view this page.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'head_admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JCLMR Drive | Head Admin</title>
    <link rel="stylesheet" href="admin.css">
    <script src="head%20admin.js" defer></script>
</head>
<body data-role="head">
<div class="layout">
    <aside class="sidebar">
        <h2 class="logo">JCLMR <span>DRIVE</span></h2>
        <p class="side-label">HEAD ADMIN PANEL</p>
        <nav>
            <button class="nav-btn active" data-page="dashboard">▦ Dashboard</button>
            <button class="nav-btn" data-page="payments">₱ Payments</button>
            <button class="nav-btn" data-page="reports">▤ Reports &amp; Analysis</button>
            <button class="nav-btn" data-page="vehicles">🚘 Vehicles</button>
            <button class="nav-btn" data-page="maintenance">🔧 Maintenance</button>
            <button class="nav-btn" data-page="rentals">📋 Rentals &amp; Returns</button>
            <button class="nav-btn" data-page="staff">♙ Staff Requests</button>
            <button class="nav-btn" data-page="admins">♙ Admin Requests</button>
        </nav>
        <div class="sidebar-bottom">
            <p>Logged in as</p>
            <strong>Head Administrator</strong>
            <a href="logout.php" class="switch-link">Log Out →</a>
        </div>
    </aside>
    <main class="main">
        <header class="topbar">
            <div>
                <h2 id="page-title">Dashboard Overview</h2>
                <p>Welcome back, Head Administrator.</p>
            </div>
            <div class="profile">
                <span class="status-dot"></span>
                <strong>Head Admin</strong>
                <div class="avatar">HA</div>
            </div>
        </header>
        <section id="content" class="content" aria-live="polite">
            <p class="empty-message">Loading dashboard data...</p>
        </section>
    </main>
</div>
</body>
</html>
