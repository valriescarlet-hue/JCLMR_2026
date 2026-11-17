<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'head_admin', 'dmin'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please sign in as an administrator.']);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$host = 'localhost';
$db = 'system_db';
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('Admin API database connection error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

$action = $_GET['action'] ?? '';
if ($action !== 'get_all') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action. Use ?action=get_all']);
    exit;
}

$data = [
    'success' => true,
    'vehicles' => [],
    'payments' => [],
    'maintenance' => [],
    'rentals' => [],
    'errors' => []
];

$queries = [
    'vehicles' => "SELECT vehicle_id AS id, vehicle_name AS name, plate_number AS plate, category AS type, availability_status AS status FROM cars",
    'payments' => "SELECT p.payment_id AS id, COALESCE(b.customer_name, 'Guest') AS customer, p.amount, p.payment_method AS method, p.payment_status AS status FROM payments p LEFT JOIN bookings b ON p.booking_id = b.booking_id",
    'maintenance' => "SELECT m.maintenance_id AS id, COALESCE(c.vehicle_name, 'Unknown Vehicle') AS vehicle, m.maintenance_type AS issue, m.maintenance_date AS date, m.maintenance_status AS status FROM maintenance m LEFT JOIN cars c ON m.vehicle_id = c.vehicle_id",
    'rentals' => "SELECT r.return_id AS id, COALESCE(b.customer_name, 'Guest') AS customer, COALESCE(c.vehicle_name, 'Unknown Vehicle') AS vehicle, r.actual_return_date AS date, r.return_status AS status FROM rental_returns r LEFT JOIN bookings b ON r.booking_id = b.booking_id LEFT JOIN cars c ON b.vehicle_id = c.vehicle_id",
];

foreach ($queries as $key => $sql) {
    try {
        $stmt = $pdo->query($sql);
        $res = $stmt->fetchAll();
        $data[$key] = is_array($res) ? $res : [];
    } catch (PDOException $e) {
        $data[$key] = [];
        $data['errors'][$key] = 'Query failed';
        error_log("Dashboard query error ($key): " . $e->getMessage());
    }
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);