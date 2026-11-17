<?php
session_start();
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification - JCLMR Drive</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="car-icon">🔒</div>
    <h1>JCLMR</h1>
    <h2>EMAIL VERIFICATION</h2>
    <p class="subtitle">Enter the 6-digit code sent to your Gmail.</p>

    <form action="verify_process.php" method="post">
        <label>6-Digit Verification Code</label>
        <input type="text" name="otp" maxlength="6" placeholder="123456" required>
        <button type="submit">Verify & Proceed</button>
    </form>
</div>
</body>
</html>