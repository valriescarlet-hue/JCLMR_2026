<?php

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.html");
    exit();
}

// Database Connection Configuration
$host = 'localhost';
$db   = 'system_db';
$user = 'root';
$pass = ''; // Update with your MySQL password if configured

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Pending Rental Requests from the 'bookings' table
$rental_query = "SELECT booking_id, customer_name, vehicle_id, pickup_date, total_amount, booking_status FROM bookings WHERE booking_status = 'pending'";
$rental_result = $conn->query($rental_query);

// Fetch Pending Car Lending Requests from the 'car_lending' table
$lending_query = "SELECT lending_id, owner_full_name, car_brand_model, submitted_at, vehicle_type, lending_status FROM car_lending WHERE lending_status = 'pending'";
$lending_result = $conn->query($lending_query);

// Calculate initial statistics counts directly from the database
$pending_rentals_count = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE booking_status = 'pending'")->fetch_assoc()['total'];
$pending_cars_count = $conn->query("SELECT COUNT(*) AS total FROM car_lending WHERE lending_status = 'pending'")->fetch_assoc()['total'];
$approved_count = $conn->query("SELECT (SELECT COUNT(*) FROM bookings WHERE booking_status = 'approved') + (SELECT COUNT(*) FROM car_lending WHERE lending_status = 'approved') AS total")->fetch_assoc()['total'];
$rejected_count = $conn->query("SELECT (SELECT COUNT(*) FROM bookings WHERE booking_status = 'rejected') + (SELECT COUNT(*) FROM car_lending WHERE lending_status = 'rejected') AS total")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="staff.css">
</head>
<body>

<div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">
        <h2>Staff Panel</h2>

        <nav>
            <a href="#dashboard" class="active">Dashboard</a>
            <a href="#rentals">Rental Requests</a>
            <a href="#cars">Car Lending</a>
            <a href="#history">Request History</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <!-- Top Navigation -->
        <header class="topbar">
            <div>
                <h1>Staff Dashboard</h1>
                <p>Welcome! Manage customer requests here.</p>
            </div>

            <div class="topbar-right">
                <span class="staff-name">Staff Administrator</span>

                <a href="logout.php"class="logout-btn" onclick="logout()">
                    Logout
                </a>
            </div>
        </header>

        <!-- Dashboard Statistics -->
        <section id="dashboard">
            <div class="stats">
                <div class="stat-card">
                    <h3>Pending Rentals</h3>
                    <h2 id="pendingRentals"><?php echo $pending_rentals_count; ?></h2>
                </div>

                <div class="stat-card">
                    <h3>Pending Car Lending</h3>
                    <h2 id="pendingCars"><?php echo $pending_cars_count; ?></h2>
                </div>

                <div class="stat-card">
                    <h3>Approved</h3>
                    <h2 id="approvedCount"><?php echo $approved_count; ?></h2>
                </div>

                <div class="stat-card">
                    <h3>Rejected</h3>
                    <h2 id="rejectedCount"><?php echo $rejected_count; ?></h2>
                </div>
            </div>
        </section>

        <!-- Rental Requests -->
        <section class="content-card" id="rentals">
            <div class="section-title">
                <h2>Pending Rental Requests</h2>
                <span class="pending-label">Pending</span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Customer Name</th>
                            <th>Vehicle ID</th>
                            <th>Pickup Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="rentalTable">
                        <?php if ($rental_result && $rental_result->num_rows > 0): ?>
                            <?php while($row = $rental_result->fetch_assoc()): ?>
                                <tr data-id="<?php echo $row['booking_id']; ?>" data-type="rental">
                                    <td>#BK-<?php echo str_pad($row['booking_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td>Vehicle #<?php echo $row['vehicle_id']; ?></td>
                                    <td><?php echo $row['pickup_date']; ?></td>
                                    <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td><span class="status pending">Pending</span></td>
                                    <td>
                                        <button class="approve" onclick="updateRequest(this, 'approved')">Approve</button>
                                        <button class="reject" onclick="updateRequest(this, 'rejected')">Reject</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty">No pending rental requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Car Lending Requests -->
        <section class="content-card" id="cars">
            <div class="section-title">
                <h2>Pending Car Lending Requests</h2>
                <span class="pending-label">Pending</span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Owner Name</th>
                            <th>Car Model</th>
                            <th>Vehicle Type</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="carTable">
                        <?php if ($lending_result && $lending_result->num_rows > 0): ?>
                            <?php while($row = $lending_result->fetch_assoc()): ?>
                                <tr data-id="<?php echo $row['lending_id']; ?>" data-type="lending">
                                    <td>#CL-<?php echo str_pad($row['lending_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($row['owner_full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['car_brand_model']); ?></td>
                                    <td><?php echo ucfirst($row['vehicle_type']); ?></td>
                                    <td><?php echo $row['submitted_at']; ?></td>
                                    <td><span class="status pending">Pending</span></td>
                                    <td>
                                        <button class="approve" onclick="updateRequest(this, 'approved')">Approve</button>
                                        <button class="reject" onclick="updateRequest(this, 'rejected')">Reject</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty">No pending car lending requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Request History -->
        <section class="content-card" id="history">
            <h2>Request History</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Customer / Owner</th>
                            <th>Request Type</th>
                            <th>Decision</th>
                        </tr>
                    </thead>
                    <tbody id="historyTable">
                        <tr>
                            <td colspan="4" class="empty">
                                No processed requests yet in this session.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

<script src="staff.js"></script>
</body>
</html>