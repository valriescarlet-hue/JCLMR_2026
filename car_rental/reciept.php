<?php
// Database Connection Configuration
$host = '127.0.0.1';
$dbname = 'system_db';
$username = 'root'; // Change if your DB username is different
$password = '';     // Change if your DB password is set

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch receipt & booking data dynamically (defaulting to receipt_id = 2 or booking_id based on URL parameter)
$receipt_id = isset($_GET['receipt_id']) ? intval($_GET['receipt_id']) : 2;

$stmt = $pdo->prepare("
    SELECT r.*, p.payment_method, p.transaction_id, p.payment_status, p.payment_date,
           b.pickup_hub, b.daily_rate, b.rental_fee, b.service_fee, b.tax, c.home_address
    FROM receipts r
    JOIN payments p ON r.payment_id = p.payment_id
    JOIN bookings b ON p.booking_id = b.booking_id
    LEFT JOIN customers c ON b.customer_id = c.customer_id
    WHERE r.receipt_id = ?
");
$stmt->execute([$receipt_id]);
$receiptData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$receiptData) {
    die("Receipt not found.");
}

// Handle form submission / payment confirmation via AJAX or POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_action'])) {
    try {
        $pdo->beginTransaction();

        // 1. Update Payment status to 'paid' if pending
        $updatePayment = $pdo->prepare("UPDATE payments SET payment_status = 'paid' WHERE payment_id = ?");
        $updatePayment->execute([$receiptData['payment_id']]);

        // 2. Update Booking status to 'confirmed' / 'approved'
        $updateBooking = $pdo->prepare("UPDATE bookings SET booking_status = 'confirmed' WHERE booking_id = (SELECT booking_id FROM payments WHERE payment_id = ?)");
        $updateBooking->execute([$receiptData['payment_id']]);

        // 3. Log activity so it feeds into Admin & Head Admin dashboards (`activity_logs`)
        // Assuming a system user or admin performs this, or logged-in user ID
        $adminUserId = 1; // Fallback or session user ID for Head Admin/Admin tracking
        $logAction = "Payment Confirmed";
        $logDescription = "Receipt # " . $receiptData['receipt_number'] . " was successfully verified and confirmed.";
        
        $insertLog = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)");
        $insertLog->execute([$adminUserId, $logAction, $logDescription]);

        $pdo->commit();
        
        echo json_encode(['status' => 'success', 'message' => 'Payment successfully confirmed and recorded for Admin and Head Admin review!']);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental - Receipt</title>
    <link rel="stylesheet" href="reciept.css">
</head>

<body>

<div class="receipt">

    <div class="header">
        <div>
            <h1>CAR RENTAL</h1>
            <p>Payment Receipt</p>
        </div>
        <span class="paid"><?php echo htmlspecialchars(strtoupper($receiptData['payment_status'] ?? 'PAID')); ?></span>
    </div>

    <div class="receipt-number">
        <div>
            <p>Receipt No.</p>
            <strong><?php echo htmlspecialchars($receiptData['receipt_number']); ?></strong>
        </div>

        <div>
            <p>Date</p>
            <strong><?php echo htmlspecialchars(date("F d, Y", strtotime($receiptData['issued_at']))); ?></strong>
        </div>
    </div>

    <div class="section">
        <h3>Customer Information</h3>

        <div class="details">
            <div>
                <p>Customer Name</p>
                <strong><?php echo htmlspecialchars($receiptData['customer_name']); ?></strong>
            </div>

            <div>
                <p>Contact Number</p>
                <strong><?php echo htmlspecialchars($receiptData['contact_number']); ?></strong>
            </div>

            <div>
                <p>Email Address</p>
                <strong><?php echo htmlspecialchars($receiptData['email']); ?></strong>
            </div>

            <div>
                <p>Address</p>
                <strong><?php echo htmlspecialchars($receiptData['home_address'] ?? 'N/A'); ?></strong>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Rental Information</h3>

        <div class="details">
            <div>
                <p>Car Model</p>
                <strong><?php echo htmlspecialchars($receiptData['vehicle_name']); ?></strong>
            </div>

            <div>
                <p>Plate Number</p>
                <strong><?php echo htmlspecialchars($receiptData['plate_number']); ?></strong>
            </div>

            <div>
                <p>Pickup Date</p>
                <strong><?php echo htmlspecialchars($receiptData['pickup_date']); ?></strong>
            </div>

            <div>
                <p>Return Date</p>
                <strong><?php echo htmlspecialchars($receiptData['return_date']); ?></strong>
            </div>

            <div>
                <p>Rental Duration</p>
                <strong><?php echo htmlspecialchars($receiptData['rental_days']); ?> Days</strong>
            </div>

            <div>
                <p>Price Per Day</p>
                <strong>₱<?php echo number_format($receiptData['price_per_day'], 2); ?></strong>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Payment Details</h3>

        <table class="payment-table">
            <tr>
                <th>Description</th>
                <th>Amount</th>
            </tr>

            <tr>
                <td>Car Rental (<?php echo $receiptData['rental_days']; ?> Days)</td>
                <td>₱<?php echo number_format($receiptData['rental_days'] * $receiptData['price_per_day'], 2); ?></td>
            </tr>

            <tr>
                <td>Additional Charges</td>
                <td>₱<?php echo number_format($receiptData['additional_charges'], 2); ?></td>
            </tr>

            <tr>
                <td>Discount</td>
                <td>-₱<?php echo number_format($receiptData['discount'], 2); ?></td>
            </tr>
        </table>

        <div class="total">
            <span>Total Paid</span>
            <span>₱<?php echo number_format($receiptData['total_paid'], 2); ?></span>
        </div>
    </div>

    <div class="section">
        <h3>Payment Method</h3>

        <div class="details">
            <div>
                <p>Payment Method</p>
                <strong><?php echo htmlspecialchars($receiptData['payment_method']); ?></strong>
            </div>

            <div>
                <p>Payment Status</p>
                <strong style="color:green;"><?php echo ucfirst($receiptData['payment_status']); ?></strong>
            </div>

            <div>
                <p>Transaction ID</p>
                <strong><?php echo htmlspecialchars($receiptData['transaction_id']); ?></strong>
            </div>
        </div>
    </div>

    <div class="footer">
        <h3>Thank You for Choosing Us!</h3>
        <p>We appreciate your business.</p>
        <p>For inquiries, contact our Car Rental Service.</p>
        <p>© 2026 Car Rental System</p>
    </div>

   <div class="buttons">
    <button class="print" onclick="confirmPayment()">
        Confirm Payment
    </button>

    <button class="back" onclick="history.back()">
        Back
    </button>
</div>

</div>

<script>
function confirmPayment() {
    const confirmAction = confirm("Are you sure you want to confirm this payment and log it to admin dashboards?");

    if (confirmAction) {
        // Send AJAX POST request to update DB records and log entries for Admin/Head Admin views
        fetch('receipt.php?receipt_id=<?php echo $receipt_id; ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'confirm_action=1'
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if(data.status === 'success') {
                window.location.href = "customer.html";
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while confirming the payment.');
        });
    }
}
</script>
</body>
</html>