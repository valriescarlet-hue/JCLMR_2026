<?php
// login.php
session_start();
require_once 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Fetch user logging in from the database users table
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['account_status'] !== 'active') {
            die("Account is inactive or pending approval.");
        }

        $role = $user['role']; // customer, staff, admin, head_admin

        // Check staff positions from staff table
        if ($role === 'staff' || $role === 'admin') {
            $staffStmt = $pdo->prepare("SELECT position FROM staff WHERE user_id = ?");
            $staffStmt->execute([$user['user_id']]);
            $staffInfo = $staffStmt->fetch();

            if ($staffInfo) {
                $pos = $staffInfo['position'];
                if ($pos === 'Administrator') {
                    $role = 'admin';
                } elseif ($pos === 'Head Administrator') {
                    $role = 'head_admin';
                } elseif (in_array($pos, ['Manager', 'Rental Staff', 'Maintenance Staff'])) {
                    $role = 'staff';
                }
            }
        }

        // 2. Automatically fetch an active sender account from mail_config table
        $configStmt = $pdo->query("SELECT * FROM mail_config WHERE is_active = 1 ORDER BY RAND() LIMIT 1");
        $mailConfig = $configStmt->fetch();

        if (!$mailConfig) {
            die("Error: No active system mail configuration found in database.");
        }

        // Generate 6-digit verification code
        $otp = rand(100000, 999999);

        // Store temporary session data for verification
        $_SESSION['temp_user_id'] = $user['user_id'];
        $_SESSION['temp_email'] = $user['email'];
        $_SESSION['temp_role'] = $role;
        $_SESSION['otp_code'] = $otp;
        $_SESSION['otp_expires'] = time() + 300; // valid for 5 minutes

        // 3. Initialize PHPMailer using automatically selected sender credentials from database
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            $mail->Username   = $mailConfig['smtp_email'];
            $mail->Password   = $mailConfig['smtp_app_password'];
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($mailConfig['smtp_email'], $mailConfig['sender_name']);
            $mail->addAddress($user['email'], $user['username']);

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = 'Your JCLMR Drive Verification Code';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                    <h2>Hello, " . htmlspecialchars($user['username']) . "</h2>
                    <p>You requested to log into your JCLMR Drive account.</p>
                    <p>Your 6-digit verification code is:</p>
                    <h1 style='color: #2563eb; letter-spacing: 2px;'>$otp</h1>
                    <p>This code will expire in 5 minutes.</p>
                    <hr style='border: none; border-top: 1px solid #eee;' />
                    <p style='font-size: 12px; color: #777;'>If you did not request this, please ignore this email.</p>
                </div>
            ";

            $mail->send();
            
            // Redirect to OTP verification input page
            header("Location: verify.php");
            exit();
        } catch (Exception $e) {
            die("Message could not be sent to {$user['email']}. Mailer Error: {$mail->ErrorInfo}");
        }

    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JCLMR - Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .back-home-btn {
            display: inline-block;
            margin-bottom: 1.5rem;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        .back-home-btn:hover {
            color: #3b82f6;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php" class="back-home-btn">← Back to Home</a>
    
    <div class="car-icon">🚘</div>
    <h1>JCLMR</h1>
    <h2>CAR RENTAL SYSTEM</h2>
   
    <?php if (!empty($error)): ?>
        <p style="color: red; text-align: center; margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="Enter your email" required>
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required>
        <button type="submit">Login to Account</button>
    </form>

    <div class="separator">
        <span>New to JCLMR?</span>
    </div>

    <a href="customer_reg.php" class="outline-button">
        Register as Customer
    </a>

    <a href="staff_reg.php" class="outline-button">
        Staff Registration
    </a>
</div>
</body>
</html>