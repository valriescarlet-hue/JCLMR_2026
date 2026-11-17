<?php
// Database configuration
$host = '127.0.0.1';
$dbname = 'system_db';
$username = 'root'; // Update with your DB username if different
$password = '';     // Update with your DB password if different

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve form inputs safely
        $booking_id     = $_POST['booking_id'] ?? 7; // Default or fetched dynamically
        $customer_name  = trim($_POST['name']);
        $email          = trim($_POST['email']);
        $phone          = trim($_POST['phone']);
        $payment_method = $_POST['payment_method'] ?? 'GCash';
        
        // Define amount (this should ideally be fetched securely from your 'bookings' table based on booking_id)
        $stmt_booking = $pdo->prepare("SELECT total_amount, vehicle_id FROM bookings WHERE booking_id = ?");
        $stmt_booking->execute([$booking_id]);
        $booking = $stmt_booking->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            die("Invalid booking reference.");
        }

        $amount = $booking['total_amount'];
        $transaction_id = 'TRX-' . strtoupper(uniqid());
        $payment_status = 'paid';

        // Begin transaction to ensure data integrity
        $pdo->beginTransaction();

        // 1. Insert into 'payments' table
        $sql_payment = "INSERT INTO payments (booking_id, payment_method, transaction_id, amount, payment_status, payment_date) 
                        VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt_payment = $pdo->prepare($sql_payment);
        $stmt_payment->execute([$booking_id, $payment_method, $transaction_id, $amount, $payment_status]);
        $payment_id = $pdo->lastInsertId();

        // 2. Insert into 'receipts' table for reporting/printing
        $receipt_number = 'CR-' . date('Y') . '-' . str_pad($payment_id, 3, '0', STR_PAD_LEFT);
        
        // Fetch vehicle name & plate for receipt details
        $stmt_car = $pdo->prepare("SELECT vehicle_name, plate_number, daily_rate FROM cars WHERE vehicle_id = ?");
        $stmt_car->execute([$booking['vehicle_id']]);
        $car = $stmt_car->fetch(PDO::FETCH_ASSOC);

        $sql_receipt = "INSERT INTO receipts (payment_id, receipt_number, customer_name, contact_number, email, vehicle_name, plate_number, total_paid, issued_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt_receipt = $pdo->prepare($sql_receipt);
        $stmt_receipt->execute([
            $payment_id, 
            $receipt_number, 
            $customer_name, 
            $phone, 
            $email, 
            $car['vehicle_name'], 
            $car['plate_number'], 
            $amount
        ]);

        // 3. Log the activity so Admin and Head Admin can track it in reports/activity logs
        $sql_log = "INSERT INTO activity_logs (user_id, action, description, created_at) VALUES (NULL, ?, ?, NOW())";
        $stmt_log = $pdo->prepare($sql_log);
        $stmt_log->execute([
            'Payment Received', 
            "Payment of ₱{$amount} received via {$payment_method} for Booking ID: {$booking_id} (Receipt: {$receipt_number})"
        ]);

        // Commit transaction
        $pdo->commit();

        // Redirect to success/receipt page
        header("Location: reciept.html?receipt=" . $receipt_number);
        exit();

    }
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Database Error: " . $e->getMessage());
}
?>