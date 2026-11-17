<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    // 1. Vehicles
    $stmt = $pdo->query("SELECT vehicle_id AS id, vehicle_name AS name, plate_number AS plate, category AS type, availability_status AS status FROM cars");
    $vehicles = $stmt->fetchAll();
    foreach ($vehicles as &$v) {
        $v['status'] = ucfirst(strtolower($v['status']));
    }
    unset($v);

    // 2. Payments
    $stmt = $pdo->query("
        SELECT p.payment_id AS id, COALESCE(b.customer_name, 'Walk-in Customer') AS customer, p.amount, p.payment_method AS method, p.payment_status AS status 
        FROM payments p 
        LEFT JOIN bookings b ON p.booking_id = b.booking_id
    ");
    $payments = $stmt->fetchAll();
    foreach ($payments as &$p) {
        $p['status'] = ucfirst(strtolower($p['status']));
    }
    unset($p);

    // 3. Maintenance
    $stmt = $pdo->query("
        SELECT m.maintenance_id AS id, c.vehicle_name AS vehicle, m.maintenance_type AS issue, m.maintenance_date AS date, m.maintenance_status AS status 
        FROM maintenance m
        JOIN cars c ON m.vehicle_id = c.vehicle_id
    ");
    $maintenance = $stmt->fetchAll();
    foreach ($maintenance as &$m) {
        $m['status'] = ($m['status'] === 'completed') ? 'Completed' : 'Pending';
    }
    unset($m);

    // 4. Rentals & Returns
    $stmt = $pdo->query("
        SELECT b.booking_id AS id, b.customer_name AS customer, c.vehicle_name AS vehicle, b.pickup_date AS date, b.booking_status AS status 
        FROM bookings b
        JOIN cars c ON b.vehicle_id = c.vehicle_id
    ");
    $rentals = $stmt->fetchAll();
    foreach ($rentals as &$r) {
        if ($r['status'] === 'completed' || $r['status'] === 'confirmed') {
            $r['status'] = 'Upcoming';
        } elseif ($r['status'] === 'ongoing') {
            $r['status'] = 'Rented';
        } else {
            $r['status'] = ucfirst($r['status']);
        }
    }
    unset($r);

    // 5. Staff Requests
    $stmt = $pdo->query("
        SELECT s.staff_id AS id, CONCAT(s.first_name, ' ', s.last_name) AS name, u.email, s.position, s.registration_status AS status 
        FROM staff s
        JOIN users u ON s.user_id = u.user_id
    ");
    $staffRequests = $stmt->fetchAll();
    foreach ($staffRequests as &$sr) {
        $sr['status'] = ucfirst(strtolower($sr['status']));
    }
    unset($sr);

    if (empty($staffRequests)) {
        $staffRequests = [];
    }

    // 6. Admin Requests
    $stmt = $pdo->query("
        SELECT user_id AS id, username AS name, email, 'Admin' AS position, 
               CASE WHEN account_status = 'active' THEN 'Approved' ELSE 'Pending' END AS status 
        FROM users WHERE role IN ('admin', 'head_admin')
    ");
    $adminRequests = $stmt->fetchAll();

    echo json_encode([
        'vehicles' => $vehicles,
        'payments' => $payments,
        'maintenance' => $maintenance,
        'rentals' => $rentals,
        'staffRequests' => $staffRequests,
        'adminRequests' => $adminRequests
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>