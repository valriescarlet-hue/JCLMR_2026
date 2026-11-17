<?php
// Start session if you need customer tracking (Optional, depending on your login system)
session_start();

// Database configuration
$host = '127.0.0.1';
$db   = 'system_db';
$user = 'root'; // Change if you have a different database user
$pass = '';     // Change if you have a database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$successMessage = "";
$errorMessage = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);

        // Sanitize and collect form inputs
        $owner_full_name   = trim($_POST['owner_full_name']);
        $contact_number    = trim($_POST['contact_number']);
        $car_brand_model   = trim($_POST['car_brand_model']);
        $vehicle_type      = $_POST['vehicle_type'];
        $transmission      = $_POST['transmission'];
        $daily_rental_rate = $_POST['daily_rental_rate'];
        
        // Optional: If you track logged-in customers via session
        $customer_id       = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null;

        // Handle Image Upload
        $car_image_path = null;
        if (isset($_FILES['carImage']) && $_FILES['carImage']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath   = $_FILES['carImage']['tmp_name'];
            $fileName      = $_FILES['carImage']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadFileDir = 'uploads/';
                
                // Create uploads directory if it doesn't exist
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;

                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $car_image_path = $dest_path;
                } else {
                    $errorMessage = "There was an error moving the uploaded file.";
                }
            } else {
                $errorMessage = "Invalid file type. Only JPG, JPEG, PNG, and WEBP files are allowed.";
            }
        }

        if (empty($errorMessage)) {
            // Insert into car_lending table (lending_status defaults to 'pending' for staff review)
            $sql = "INSERT INTO car_lending (customer_id, owner_full_name, contact_number, car_brand_model, vehicle_type, transmission, daily_rental_rate, car_image, lending_status) 
                    VALUES (:customer_id, :owner_full_name, :contact_number, :car_brand_model, :vehicle_type, :transmission, :daily_rental_rate, :car_image, 'pending')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'customer_id'       => $customer_id,
                'owner_full_name'   => $owner_full_name,
                'contact_number'    => $contact_number,
                'car_brand_model'   => $car_brand_model,
                'vehicle_type'      => $vehicle_type,
                'transmission'      => $transmission,
                'daily_rental_rate' => $daily_rental_rate,
                'car_image'         => $car_image_path
            ]);

            $successMessage = "Thank you! Your car details have been submitted successfully and are now pending staff review.";
        }

    } catch (\PDOException $e) {
        $errorMessage = "Database Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JCLMR Car Rental - Lend Your Car</title>
    <link rel="stylesheet" href="lend.css">
    <style>
        .alert-success { background: #065f46; color: #d1fae5; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .alert-error { background: #991b1b; color: #fee2e2; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 14px; }
    </style>
</head>

<body>

    <!-- NAVIGATION BAR -->
    <header>
        <div class="logo">
            JCLMR <span>DRIVE</span>
        </div>
        <nav>
            <a href="customer.php">Home</a>
            <a href="lend.php" class="active">Lend Your Car</a>
        </nav>
    </header>

    <!-- FORM CONTAINER -->
    <div class="container">

        <div class="title-section">
            <h2>Lend Your Car With Us</h2>
            <p>
                Want to earn extra income? Register your vehicle
                to JCLMR Car Rental today.
            </p>
        </div>

        <!-- Feedback Messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <!-- CAR OWNER FORM -->
        <form id="lendCarForm" action="lend.php" method="POST" enctype="multipart/form-data">

            <div class="row">
                <div class="col form-group">
                    <label>Owner Full Name</label>
                    <input
                        type="text"
                        name="owner_full_name"
                        placeholder="e.g. Juan Dela Cruz"
                        required
                    >
                </div>

                <div class="col form-group">
                    <label>Contact Number</label>
                    <input
                        type="tel"
                        name="contact_number"
                        placeholder="e.g. 09123456789"
                        required
                    >
                </div>
            </div>

            <div class="row">
                <div class="col form-group">
                    <label>Car Brand & Model</label>
                    <input
                        type="text"
                        name="car_brand_model"
                        placeholder="e.g. Toyota Vios 2022"
                        required
                    >
                </div>

                <div class="col form-group">
                    <label>Vehicle Type</label>
                    <select name="vehicle_type" required>
                        <option value="" disabled selected>
                            Select type
                        </option>
                        <option value="sedan">Sedan</option>
                        <option value="suv">SUV</option>
                        <option value="van">Van / MPV</option>
                        <option value="pickup">Pickup Truck</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col form-group">
                    <label>Transmission</label>
                    <select name="transmission" required>
                        <option value="" disabled selected>
                            Select transmission
                        </option>
                        <option value="automatic">Automatic</option>
                        <option value="manual">Manual</option>
                    </select>
                </div>

                <div class="col form-group">
                    <label>Daily Rental Rate (₱)</label>
                    <input
                        type="number"
                        name="daily_rental_rate"
                        min="1"
                        step="0.01"
                        placeholder="e.g. 2000"
                        required
                    >
                </div>
            </div>

            <!-- CAR IMAGE UPLOAD -->
            <div class="form-group">
                <label for="carImage">Upload Car Image</label>
                <input
                    type="file"
                    id="carImage"
                    name="carImage"
                    class="image-upload"
                    accept="image/*"
                    required
                >
                <!-- IMAGE PREVIEW -->
                <img
                    id="imagePreview"
                    alt="Car Image Preview"
                >
            </div>

            <button type="submit" class="btn-submit">
                Submit Car Listing
            </button>

        </form>

    </div>

    <!-- JAVASCRIPT -->
    <script>
        // IMAGE UPLOAD PREVIEW
        const carImage = document.getElementById("carImage");
        const imagePreview = document.getElementById("imagePreview");

        carImage.addEventListener("change", function() {
            const file = this.files[0];
            if (file) {
                if (!file.type.startsWith("image/")) {
                    alert("Please select a valid image file.");
                    this.value = "";
                    imagePreview.style.display = "none";
                    return;
                }
                const imageURL = URL.createObjectURL(file);
                imagePreview.src = imageURL;
                imagePreview.style.display = "block";
            } else {
                imagePreview.style.display = "none";
            }
        });
    </script>

</body>
</html>