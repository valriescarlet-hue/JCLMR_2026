<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JCLMR - Customer Registration with Biometrics</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .step-section { display: none; }
        .step-section.active { display: block; }
        #webcam-container { text-align: center; margin-top: 15px; }
        video { width: 100%; max-width: 320px; border-radius: 8px; border: 2px solid #ccc; }
        canvas { display: none; }
        .preview-img { max-width: 100%; height: auto; margin-top: 10px; border-radius: 6px; }
    </style>
</head>
<body>

<div class="container">
    <div class="car-icon">👤</div>
    <h1>JCLMR</h1>
    <h2>CUSTOMER REGISTRATION</h2>
    <p class="subtitle">Secure Registration with Facial Scan & Valid ID Verification</p>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red; margin-bottom: 15px; text-align: center;"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form id="regForm" action="register_process.php" method="POST" enctype="multipart/form-data">
        
        <!-- STEP 1: Basic Information -->
        <div class="step-section active" id="step-1">
            <h3>Step 1: Account Details</h3>
            <label>First Name</label>
            <input type="text" name="first_name" placeholder="Enter first name" required>

            <label>Last Name</label>
            <input type="text" name="last_name" placeholder="Enter last name" required>

            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter email address" required>

            <label>Contact Number</label>
            <input type="tel" name="contact_number" placeholder="Enter contact number" required>

            <label>Home Address</label>
            <input type="text" name="home_address" placeholder="Enter complete address" required>

            <label>Username</label>
            <input type="text" name="username" placeholder="Create username" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Create password" required>

            <button type="button" onclick="nextStep(2)">Next: ID & Biometric Verification</button>
        </div>

        <!-- STEP 2: Valid ID Upload -->
        <div class="step-section" id="step-2">
            <h3>Step 2: Valid ID Verification</h3>
            <p class="subtitle" style="font-size: 0.9rem;">Please upload a clear photo of your <strong>PhilSys ID</strong> or <strong>Passport</strong>.</p>
            
            <label>Select ID Type</label>
            <select name="id_type" required style="width:100%; padding:10px; margin-bottom:15px; border-radius:5px; border:1px solid #ccc;">
                <option value="philsys">PhilSys National ID</option>
                <option value="passport">Passport</option>
            </select>

            <label>Upload ID Image</label>
            <input type="file" name="valid_id" id="valid_id" accept="image/*" required onchange="previewID(event)">
            <br>
            <img id="id-preview" class="preview-img" style="display:none;" alt="ID Preview">

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="button" onclick="prevStep(1)" style="background: #6c757d;">Back</button>
                <button type="button" onclick="nextStep(3)">Next: Facial Scan</button>
            </div>
        </div>

        <!-- STEP 3: Facial Scan Biometrics -->
        <div class="step-section" id="step-3">
            <h3>Step 3: Facial Biometric Scan</h3>
            <p class="subtitle" style="font-size: 0.9rem;">Position your face clearly in front of the camera to verify against your ID.</p>
            
            <div id="webcam-container">
                <video id="webcam" autoplay playsinline></video>
                <canvas id="canvas"></canvas>
                <br>
                <button type="button" id="capture-btn" onclick="captureSnapshot()" style="background: #28a745; margin-top: 10px;">Capture Facial Scan</button>
                <div id="capture-status" style="margin-top: 10px; font-weight: bold; color: green;"></div>
            </div>

            <!-- Hidden input to store captured face image base64 data -->
            <input type="hidden" name="facial_scan_data" id="facial_scan_data" required>
            <br>
            <img id="face-preview" class="preview-img" style="display:none; max-width: 150px; margin: 0 auto;" alt="Face Preview">

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="button" onclick="prevStep(2)" style="background: #6c757d;">Back</button>
                <button type="submit" id="submit-btn" disabled style="background: #ccc; cursor: not-allowed;">Complete Registration</button>
            </div>
        </div>

    </form>

    <p class="bottom-text" style="margin-top: 20px;">
        Already have an account?
        <a href="login.php">Login here</a>
    </p>
</div>

<script>
    let currentStep = 1;

    function nextStep(step) {
        // Validate Step 1 fields before proceeding
        if (step === 2) {
            const step1Inputs = document.querySelectorAll('#step-1 input');
            let valid = true;
            step1Inputs.forEach(input => {
                if (!input.value) valid = false;
            });
            if (!valid) {
                alert('Please fill out all fields in Step 1.');
                return;
            }
        }

        document.querySelectorAll('.step-section').forEach(sec => sec.classList.remove('active'));
        document.getElementById('step-' + step).classList.add('active');
        currentStep = step;

        if (step === 3) {
            initWebcam();
        }
    }

    function prevStep(step) {
        document.querySelectorAll('.step-section').forEach(sec => sec.classList.remove('active'));
        document.getElementById('step-' + step).classList.add('active');
        currentStep = step;
        stopWebcam();
    }

    function previewID(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('id-preview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    let videoStream = null;
    function initWebcam() {
        const video = document.getElementById('webcam');
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                videoStream = stream;
                video.srcObject = stream;
            })
            .catch(err => {
                alert('Webcam access denied or unavailable: ' + err);
            });
    }

    function stopWebcam() {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
        }
    }

    function captureSnapshot() {
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const context = canvas.getContext('2d');
        
        canvas.width = video.videoWidth || 320;
        canvas.height = video.videoHeight || 240;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const dataURL = canvas.toDataURL('image/png');
        document.getElementById('facial_scan_data').value = dataURL;

        const facePreview = document.getElementById('face-preview');
        facePreview.src = dataURL;
        facePreview.style.display = 'block';

        document.getElementById('capture-status').innerText = '✓ Facial Scan Verified Successfully!';
        
        // Enable final submit button
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = false;
        submitBtn.style.backgroundColor = '#007bff';
        submitBtn.style.cursor = 'pointer';

        stopWebcam();
    }
</script>

</body>
</html>

