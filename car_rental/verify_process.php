<?php
// verify_process.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_otp = trim($_POST['otp']);

    if (isset($_SESSION['otp_code']) && time() <= $_SESSION['otp_expires']) {
        if ($user_otp == $_SESSION['otp_code']) {
            // OTP is correct! Clear temp OTP session and set permanent login session
            $_SESSION['user_id'] = $_SESSION['temp_user_id'];
            $_SESSION['role'] = $_SESSION['temp_role'];

            $role = $_SESSION['role'];

            // Clean up temp session variables
            unset($_SESSION['temp_user_id'], $_SESSION['temp_email'], $_SESSION['temp_role'], $_SESSION['otp_code'], $_SESSION['otp_expires']);

            // Redirect based on role
            switch ($role) {
                case 'customer':
                    header("Location: customer.php");
                    break;
                case 'staff':
                    header("Location: staff.php");
                    break;
                case 'admin':
                    header("Location: admin.php");
                    break;
                case 'head_admin':
                    header("Location: headadmin.php");
                    break;
                default:
                    header("Location: login.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid verification code.'); window.location.href='verify.php';</script>";
        }
    } else {
        echo "<script>alert('Verification code expired. Please log in again.'); window.location.href='login.html';</script>";
    }
}
?>