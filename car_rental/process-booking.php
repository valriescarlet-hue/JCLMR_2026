<?php
// process-booking.php
session_start();
header('Content-Type: application/json');

// Database Connection Configuration
$host = '127.0.0.1';
$dbname = 'system_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $vehicle_id = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);
    $customer_name = trim($_POST['book-name'] ?? '');
    $contact_number = trim($_POST['book-phone'] ?? '');
    $email = trim($_POST['book-email'] ?? '');
    $pickup_hub = trim($_POST['book-hub'] ?? '');
    $pickup_date = trim($_POST['pickup-date'] ?? '');
    $return_date = trim($_POST['return-date'] ?? '');
    $rental_days = filter_input(INPUT_POST, 'rental_days', FILTER_VALIDATE_INT);

    if (!$vehicle_id || empty($customer_name) || empty($contact_number) || empty($email) || empty($pickup_date) || empty($return_date)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled out properly.']);
        exit;
    }

    // 1. Fetch Vehicle and its true database daily_rate
    $stmt = $pdo->prepare("SELECT vehicle_id, vehicle_name, daily_rate FROM cars WHERE vehicle_id = ?");
    $stmt->execute([$vehicle_id]);
    $car = $stmt->fetch();

    if (!$car) {
        echo json_encode(['success' => false, 'message' => 'Selected vehicle does not exist in the database.']);
        exit;
    }

    $daily_rate = floatval($car['daily_rate']);
    $rental_fee = $daily_rate * max(1, $rental_days);
    $service_fee = 500.00; // Standard flat service fee
    $tax = ($rental_fee + $service_fee) * 0.12; // 12% tax computation
    $total_amount = $rental_fee + $service_fee + $tax;

    // 2. Resolve or create customer profile (Defaulting to customer_id = 1 if unauthenticated guest)
    $customer_id = $_SESSION['customer_id'] ?? 1; 

    // 3. Generate Unique Booking Reference
    $booking_reference = 'BK-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // 4. Insert Booking Request into DB (defaults to 'pending' for staff review)
    $insertQuery = "INSERT INTO bookings (
        booking_reference, customer_id, vehicle_id, customer_name, contact_number, 
        email, pickup_hub, pickup_date, return_date, rental_days, daily_rate, 
        rental_fee, service_fee, tax, total_amount, booking_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmtInsert = $pdo->prepare($insertQuery);
    $success = $stmtInsert->execute([
        $booking_reference,
        $customer_id,
        $vehicle_id,
        $customer_name,
        $contact_number,
        $email,
        $pickup_hub,
        $pickup_date,
        $return_date,
        $rental_days,
        $daily_rate,
        $rental_fee,
        $service_fee,
        $tax,
        $total_amount
    ]);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Booking request submitted successfully!',
            'reference' => $booking_reference
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save booking request to database.']);
    }
    exit;
}