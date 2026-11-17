<?php
// staff_reg_process.php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $position = $_POST['position']; // Manager, Rental Staff, Maintenance Staff, Administrator, Head Administrator
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Map position to users table role
    $user_role = 'staff';
    if ($position === 'Administrator') {
        $user_role = 'admin';
    } elseif ($position === 'Head Administrator') {
        $user_role = 'head_admin';
    }

    try {
        $pdo->beginTransaction();

        // 1. Insert into users table
        $stmtUser = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, account_status) VALUES (?, ?, ?, ?, 'active')");
        $stmtUser->execute([$username, $email, $password, $user_role]);
        $user_id = $pdo->lastInsertId();

        // 2. Insert into staff table
        $stmtStaff = $pdo->prepare("INSERT INTO staff (user_id, employee_id, first_name, last_name, contact_number, position, registration_status) VALUES (?, ?, ?, ?, ?, ?, 'approved')");
        $stmtStaff->execute([$user_id, $employee_id, $first_name, $last_name, $contact_number, $position]);

        $pdo->commit();
        echo "<script>alert('Staff registered successfully!'); window.location.href='login.html';</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Registration failed: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JCLMR - Staff Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="car-icon">👨‍💼</div>
    <h1>JCLMR</h1>
    <h2>STAFF REGISTRATION</h2>
    
    <?php if (!empty($error)): ?>
        <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p style="color: green; text-align: center;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form action="staff_reg.php" method="post">
        <label>Staff ID / Employee ID</label>
        <input type="text" name="employee_id" placeholder="Enter staff ID" required>

        <label>First Name</label>
        <input type="text" name="first_name" placeholder="Enter first name" required>

        <label>Last Name</label>
        <input type="text" name="last_name" placeholder="Enter last name" required>

        <label>Email Address</label>
        <input type="email" name="email" placeholder="Enter email address" required>

        <label>Contact Number</label>
        <input type="tel" name="contact_number" placeholder="Enter contact number" required>

        <label>Position</label>
        <select name="position" required>
            <option value="">Select Position</option>
            <option value="Manager">Manager</option>
            <option value="Rental Staff">Rental Staff</option>
            <option value="Maintenance Staff">Maintenance Staff</option>
            <option value="Administrator">Administrator</option>
        </select>

        <label>Username</label>
        <input type="text" name="username" placeholder="Create username" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Create password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" placeholder="Confirm password" required>

        <button type="submit">Register Staff Account</button>
    </form>
      <p class="bottom-text" style="margin-top: 20px;">
        Already have an account?
        <a href="login.php">Login here</a>
    </p>
</div>
</body>
</html>