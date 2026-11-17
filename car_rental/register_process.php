<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name     = trim($_POST['first_name']);
    $last_name      = trim($_POST['last_name']);
    $email          = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $home_address   = trim($_POST['home_address']);
    $username       = trim($_POST['username']);
    $password       = $_POST['password'];
    $id_type        = $_POST['id_type'];
    $facial_data    = $_POST['facial_scan_data'] ?? '';

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password)) {
        $_SESSION['error'] = "All required fields must be filled out.";
        header("Location: customer_reg.php");
        exit();
    }

    try {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Username or Email already exists.";
            header("Location: customer_reg.php");
            exit();
        }

        // Handle Valid ID File Upload
        if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/ids/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileExtension = pathinfo($_FILES['valid_id']['name'], PATHINFO_EXTENSION);
            $idFileName = uniqid('id_', true) . '.' . $fileExtension;
            move_uploaded_file($_FILES['valid_id']['tmp_name'], $uploadDir . $idFileName);
        } else {
            $_SESSION['error'] = "Valid ID upload is required.";
            header("Location: customer_reg.php");
            exit();
        }

        // Handle Facial Biometric Snapshot Storage
        if (!empty($facial_data)) {
            $uploadDirFace = 'uploads/biometrics/';
            if (!is_dir($uploadDirFace)) {
                mkdir($uploadDirFace, 0755, true);
            }
            $imageParts = explode(";base64,", $facial_data);
            $imageTypeAux = explode("image/", $imageParts[0]);
            $imageExtension = $imageTypeAux[1] ?? 'png';
            $imageDecoded = base64_decode($imageParts[1]);
            $faceFileName = uniqid('face_', true) . '.' . $imageExtension;
            file_put_contents($uploadDirFace . $faceFileName, $imageDecoded);
        } else {
            $_SESSION['error'] = "Facial biometric scan is required.";
            header("Location: customer_reg.php");
            exit();
        }

        // Hash password securely
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Begin Database Transaction
        $pdo->beginTransaction();

        // 1. Insert into `users` table (role = 'customer')
        $userStmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, account_status) VALUES (?, ?, ?, 'customer', 'active')");
        $userStmt->execute([$username, $email, $password_hash]);
        $userId = $pdo->lastInsertId();

        // 2. Insert into `customers` table
        $custStmt = $pdo->prepare("INSERT INTO customers (user_id, first_name, last_name, contact_number, home_address) VALUES (?, ?, ?, ?, ?)");
        $custStmt->execute([$userId, $first_name, $last_name, $contact_number, $home_address]);

        // Commit Transaction
        $pdo->commit();

        // Redirect to login page upon success
        header("Location: login.html");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        header("Location: customer_reg.php");
        exit();
    }
} else {
    header("Location: customer_reg.php");
    exit();
}
?>