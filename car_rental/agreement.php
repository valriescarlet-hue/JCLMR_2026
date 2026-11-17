<?php
require_once 'db.php';

$successMessage = "";
$errorMessage = "";

// Handle Form Submission via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize inputs
    $booking_id = 7; // Example booking ID linked to bookings table (adjust dynamically via session/URL as needed)
    $agreement_number = 'JCLMR-AGR-' . date('Y') . '-' . rand(1000, 9999);
    $selected_vehicle = 'Toyota Raize (CVT)';
    $rental_start = '2026-11-14';
    $rental_end = '2026-11-17';
    $daily_rate = 2500.00;
    $security_deposit = 5000.00;
    $renter_signature = trim($_POST['sigInput'] ?? '');
    $agreed_to_terms = isset($_POST['agreeCheck']) ? 1 : 0;
    $agreement_status = 'signed';
    $signed_at = date('Y-m-d H:i:s');

    if (!empty($renter_signature) && $agreed_to_terms) {
        try {
            // INSERT INTO query for rental_agreements table
            $sql = "INSERT INTO rental_agreements 
                    (booking_id, agreement_number, selected_vehicle, rental_start, rental_end, daily_rate, security_deposit, renter_signature, agreed_to_terms, agreement_status, signed_at) 
                    VALUES (:booking_id, :agreement_number, :selected_vehicle, :rental_start, :rental_end, :daily_rate, :security_deposit, :renter_signature, :agreed_to_terms, :agreement_status, :signed_at)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':booking_id' => $booking_id,
                ':agreement_number' => $agreement_number,
                ':selected_vehicle' => $selected_vehicle,
                ':rental_start' => $rental_start,
                ':rental_end' => $rental_end,
                ':daily_rate' => $daily_rate,
                ':security_deposit' => $security_deposit,
                ':renter_signature' => $renter_signature,
                ':agreed_to_terms' => $agreed_to_terms,
                ':agreement_status' => $agreement_status,
                ':signed_at' => $signed_at
            ]);

            // Redirect to payment page upon success
            header("Location: payment.html");
            exit();

        } catch (PDOException $e) {
            $errorMessage = "Error saving agreement: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please accept the terms and provide your digital signature.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JCLMR | Digital Rental Agreement</title>
    <link rel="stylesheet" href="agreement.css">
</head>
<body>

    <div class="agreement-container">
        <div class="agreement-header">
            <div class="brand-title">JCLMR <span>DRIVE</span></div>
            <h2>Vehicle Lease & Rental Agreement</h2>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; text-align: center;">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Agreement Metadata -->
        <div class="meta-grid">
            <div class="meta-item">
                <span>Agreement ID</span>
                <strong>JCLMR-AGR-2026-9042</strong>
            </div>
            <div class="meta-item">
                <span>Selected Vehicle</span>
                <strong>Toyota Raize (CVT)</strong>
            </div>
            <div class="meta-item">
                <span>Rental Period</span>
                <strong>3 Days (Nov 14 - Nov 17, 2026)</strong>
            </div>
            <div class="meta-item">
                <span>Rate & Deposit</span>
                <strong>₱2,500 / day + ₱5,000 Deposit</strong>
            </div>
        </div>

        <!-- Scrollable Terms & Conditions -->
        <div class="terms-box">
            <h4>1. Vehicle Use & Operator Eligibility</h4>
            <p>The Renter certifies that they possess a valid, unexpired Land Transportation Office (LTO) driver's license...</p>

            <h4>2. Fuel & Mileage Policy</h4>
            <p>The vehicle is provided with a full tank of fuel and must be returned at the same level...</p>

            <h4>3. Prohibited Operations</h4>
            <p>The vehicle shall not be used for illegal activities, speed racing, towing unauthorized trailers...</p>

            <h4>4. Insurance & Liability</h4>
            <p>Comprehensive insurance is included during the active lease period...</p>

            <h4>5. Return & Late Penalties</h4>
            <p>The vehicle must be returned to the designated JCLMR drop-off station at the agreed-upon date...</p>
        </div>

        <!-- E-Signature & Submission Form -->
        <form class="signature-section" id="agreementForm" method="POST" action="">
            <label class="checkbox-group">
                <input type="checkbox" id="agreeCheck" name="agreeCheck" onchange="toggleSubmitButton()">
                <span>I have read, understood, and agree to all terms and conditions stated in this digital lease agreement.</span>
            </label>

            <div class="signature-input-group">
                <label for="sigInput">Digital Signature (Type Full Legal Name)</label>
                <input type="text" id="sigInput" name="sigInput" placeholder="e.g., Jiro Sanchez" required oninput="toggleSubmitButton()">
            </div>

            <button type="submit" class="btn-submit" id="submitBtn" disabled>Sign & Confirm Agreement</button>
        </form>

        <div class="success-banner" id="successBanner" style="<?php echo !empty($successMessage) ? 'display:block;' : ''; ?>">
            ✅ Agreement successfully signed and encrypted! Generating Digital Key Pass...
        </div>
    </div>

    <script>
        function toggleSubmitButton() {
            const isChecked = document.getElementById('agreeCheck').checked;
            const signatureText = document.getElementById('sigInput').value.trim();
            const submitBtn = document.getElementById('submitBtn');

            if (isChecked && signatureText.length > 0) {
                submitBtn.removeAttribute('disabled');
            } else {
                submitBtn.setAttribute('disabled', 'true');
            }
        }
    </script>
</body>
</html>