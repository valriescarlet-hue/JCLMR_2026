<?php
// car.php
session_start();

// Database Connection
$host = '127.0.0.1';
$dbname = 'system_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}

// Fetch live car listings from database table `cars`
$stmt = $pdo->query("SELECT * FROM cars WHERE availability_status = 'available' ORDER BY vehicle_id ASC");
$db_cars = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JCLMR | Luxury Car Rentals</title>
    <link rel="stylesheet" href="car.css">
</head>
<body>

    <!-- Header Navbar -->
    <header>
        <div class="logo" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
            JCLMR <span>DRIVE</span>
        </div>
        <nav>
            <a href="#fleet">Fleet</a>
            <a href="#availability" class="nav-checker-btn" onclick="openAvailabilityModal(event)">Availability Checker</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
        </nav>
    </header>

    <!-- Back to Customer Home Button -->
    <div style="padding: 20px 5%;">
        <a href="customer.php" class="back-home-btn">← Back to Home</a>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-badge">Executive Fleet Services</div>
        <h1>Online Vehicle Booking</h1>
        <p>Browse our off-road, family, and city vehicles, view their specifications, and send a booking request.</p>
    </section>

    <!-- Filter and Search Controls -->
    <div class="controls" id="fleet">
        <div class="filter-buttons">
            <button class="filter-btn active" onclick="filterCategory('all', this)">All Fleet</button>
            <button class="filter-btn" onclick="filterCategory('offroad', this)">Off-Road</button>
            <button class="filter-btn" onclick="filterCategory('family', this)">Family</button>
            <button class="filter-btn" onclick="filterCategory('city', this)">City Cars</button>
        </div>

        <div class="search-box">
            <input type="text" id="search-input" onkeyup="searchCars()" placeholder="Search vehicle model...">
        </div>
    </div>

    <!-- Vehicles Grid (Loaded dynamically from PHP array) -->
    <main class="container">
        <div class="car-grid" id="car-grid">
            <?php foreach ($db_cars as $car): ?>
                <div class="car-card" 
                     data-category="<?php echo htmlspecialchars($car['category']); ?>" 
                     data-name="<?php echo htmlspecialchars(strtolower($car['vehicle_name'])); ?>"
                     onclick="openDetailsModal(<?php echo htmlspecialchars(json_encode($car)); ?>)">
                    
                    <div class="car-image-wrapper">
                        <img src="<?php echo htmlspecialchars($car['image_path']); ?>" alt="<?php echo htmlspecialchars($car['vehicle_name']); ?>">
                    </div>

                    <div class="car-info">
                        <div>
                            <div class="car-category"><?php echo htmlspecialchars(ucfirst($car['category'])); ?> • <?php echo htmlspecialchars(ucfirst($car['transmission'])); ?></div>
                            <div class="car-title"><?php echo htmlspecialchars($car['vehicle_name']); ?></div>
                            <div class="car-details">
                                <?php echo htmlspecialchars($car['horsepower']); ?> • <?php echo htmlspecialchars($car['seating_capacity']); ?> Seater • Plate: <?php echo htmlspecialchars($car['plate_number']); ?>
                            </div>
                        </div>

                        <div class="price-row">
                            <div class="price">
                                ₱<?php echo number_format($car['daily_rate'], 2); ?>
                                <span>per day</span>
                            </div>

                            <button class="book-btn" onclick="event.stopPropagation(); openBookingModal(<?php echo htmlspecialchars(json_encode($car)); ?>)">
                                Book Online
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Availability Checker Section -->
    <section id="availability" class="section-block">
        <h2 class="section-title">Availability Checker</h2>
        <p class="section-desc">Choose a vehicle category and rental dates to submit an availability inquiry. Final availability is confirmed by our team.</p>

        <div class="checker-box">
            <form onsubmit="checkAvailability(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>Select Hub Terminal</label>
                        <select id="check-hub">
                            <option value="Manila Flagship Hub">Manila Flagship Hub</option>
                            <option value="Clark International (CRK)">Clark International (CRK)</option>
                            <option value="BGC Taguig Terminal">BGC Taguig Terminal</option>
                            <option value="Cebu Mactan Airport">Cebu Mactan Airport</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Vehicle Category</label>
                        <select id="check-category">
                            <option value="all">All Categories</option>
                            <option value="offroad">Off-Road</option>
                            <option value="family">Family</option>
                            <option value="city">City Cars</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>From Date</label>
                        <input type="date" id="check-start" required>
                    </div>

                    <div class="form-group">
                        <label>To Date</label>
                        <input type="date" id="check-end" required>
                    </div>
                </div>

                <button type="submit" class="book-btn" style="width: 100%; margin-top: 0.5rem;">Check Availability</button>
            </form>

            <div class="checker-results" id="checker-results-box">
                <h4 style="color: #10b981; margin-bottom: 0.5rem;">Fleet Matches</h4>
                <p id="checker-output-text" style="color: var(--text-secondary); font-size: 0.9rem;"></p>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section id="about" class="section-block">
        <h2 class="section-title">About JCLMR Drive</h2>
        <p class="section-desc">We provide vehicle options for different journeys across the Philippines. Our fleet includes capable off-road pickups and SUVs, spacious family vehicles, and practical city cars.</p>
        <div class="info-cards">
            <div class="info-card">
                <h4>Chauffeur & Self-Drive</h4>
                <p>Choose between executive personal drivers or self-drive options tailored to your itinerary.</p>
            </div>
            <div class="info-card">
                <h4>VIP Airport Pickup</h4>
                <p>Direct terminal delivery and express handovers at Clark International (CRK) and Manila (NAIA).</p>
            </div>
            <div class="info-card">
                <h4>Full Coverage Insurance</h4>
                <p>Comprehensive protection and 24/7 roadside support on every reservation.</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section-block">
        <h2 class="section-title">Contact & Support</h2>
        <p class="section-desc">Have custom requirements or long-term lease inquiries? Get in touch with our concierge desk.</p>

        <div class="form-row">
            <div class="info-card">
                <h4>Concierge Desk</h4>
                <p><strong>Hotline:</strong> +63 (045) 892-3400</p>
                <p><strong>Mobile:</strong> 0917 123 4567</p>
                <p><strong>Email:</strong> concierge@jclmrdrive.ph</p>
                <p><strong>Hours:</strong> 24/7 Service Desk</p>
            </div>

            <div class="info-card">
                <h4>Inquiry Form</h4>
                <form onsubmit="submitInquiry(event)">
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" required placeholder="Name">
                    </div>
                    <div class="form-group">
                        <label>Message / Request</label>
                        <textarea rows="3" required placeholder="Type your message..."></textarea>
                    </div>
                    <button type="submit" class="book-btn" style="width: 100%;">Send Inquiry</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Modal 2: Vehicle Details -->
    <div class="modal" id="details-modal">
        <div class="modal-content">
            <button class="modal-close-icon" onclick="closeModal('details-modal')">&times;</button>
            <h3 id="details-car-name">Vehicle Details</h3>
            <img id="details-car-img" src="" alt="Car Image" class="car-details-img">

            <div class="car-specs-grid">
                <div class="spec-item">
                    <strong>Category</strong>
                    <span id="details-car-cat"></span>
                </div>
                <div class="spec-item">
                    <strong>Daily Rate</strong>
                    <span id="details-car-price"></span>
                </div>
                <div class="spec-item">
                    <strong>Performance / Specs</strong>
                    <span id="details-car-specs"></span>
                </div>
                <div class="spec-item">
                    <strong>Status</strong>
                    <span style="color:#10b981;">Available for Booking</span>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="book-btn" style="width: 50%;" id="details-book-now-btn">Book Online</button>
                <button type="button" class="cancel-btn" onclick="closeModal('details-modal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Modal 3: Online Booking -->
    <div class="modal" id="booking-modal">
        <div class="modal-content">
            <button class="modal-close-icon" onclick="closeModal('booking-modal')">&times;</button>
            <h3 id="modal-car-name">Online Reservation</h3>

            <form id="reservation-form" onsubmit="confirmReservation(event)">
                <input type="hidden" id="book-vehicle-id" name="vehicle_id">

                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="book-name" name="book-name" required placeholder="Juan Dela Cruz">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="tel" id="book-phone" name="book-phone" required placeholder="0917 123 4567">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="book-email" name="book-email" required placeholder="juan@example.com">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Pickup Date</label>
                        <input type="date" id="pickup-date" name="pickup-date" required onchange="calculateDaysAndTotal()">
                    </div>
                    <div class="form-group">
                        <label>Return Date</label>
                        <input type="date" id="return-date" name="return-date" required onchange="calculateDaysAndTotal()">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Pickup Location Hub</label>
                        <select id="book-hub" name="book-hub">
                            <option value="Manila Flagship Hub">Manila Flagship Hub</option>
                            <option value="Clark International (CRK)">Clark International (CRK)</option>
                            <option value="BGC Taguig Terminal">BGC Taguig Terminal</option>
                            <option value="Cebu Mactan Airport">Cebu Mactan Airport</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Rental Duration (Days)</label>
                        <input type="number" min="1" max="30" value="1" required id="modal-days" name="rental_days" onchange="calculateDaysAndTotal()" oninput="calculateDaysAndTotal()">
                    </div>
                </div>

                <!-- Price Summary -->
                <div class="summary-box">
                    <div>
                        <span>Estimated Total (Incl. 12% Tax & Fees)</span>
                        <p style="font-size:0.75rem; color:var(--text-secondary);" id="summary-breakdown">Calculated dynamically</p>
                    </div>
                    <h4 id="modal-total-price">₱0.00</h4>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="book-btn" style="width: 50%;">Confirm Booking</button>
                    <button type="button" class="cancel-btn" onclick="closeModal('booking-modal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 JCLMR Car Rental Services. All rights reserved.</p>
    </footer>

    <!-- JavaScript Client-Side Scripting -->
    <script>
        let currentCarRate = 0;

        window.addEventListener("DOMContentLoaded", () => {
            const today = new Date().toISOString().split("T")[0];
            const tomorrow = new Date(Date.now() + 86400000).toISOString().split("T")[0];

            ['check-start', 'pickup-date'].forEach(id => {
                const el = document.getElementById(id);
                if(el) { el.value = today; el.min = today; }
            });
            ['check-end', 'return-date'].forEach(id => {
                const el = document.getElementById(id);
                if(el) { el.value = tomorrow; el.min = tomorrow; }
            });
        });

        // Filter Grid Categories
        function filterCategory(category, button) {
            document.querySelectorAll(".filter-btn").forEach(btn => btn.classList.remove("active"));
            button.classList.add("active");

            const cards = document.querySelectorAll(".car-card");
            cards.forEach(card => {
                if (category === "all" || card.dataset.category === category) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            });
        }

        // Live Search Bar
        function searchCars() {
            const query = document.getElementById("search-input").value.toLowerCase();
            const cards = document.querySelectorAll(".car-card");
            cards.forEach(card => {
                const name = card.dataset.name;
                if (name.includes(query)) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            });
        }

        function openAvailabilityModal(e) {
            e.preventDefault();
            document.getElementById("availability-modal").style.display = "flex";
        }

        function openDetailsModal(car) {
            document.getElementById("details-car-name").innerText = car.vehicle_name;
            document.getElementById("details-car-img").src = car.image_path;
            document.getElementById("details-car-cat").innerText = car.category.toUpperCase() + " • " + car.transmission.toUpperCase();
            document.getElementById("details-car-price").innerText = "₱" + parseFloat(car.daily_rate).toLocaleString() + " / day";
            document.getElementById("details-car-specs").innerText = `${car.horsepower} • ${car.seating_capacity} Seater\n\n${car.description}`;

            document.getElementById("details-book-now-btn").onclick = function() {
                closeModal("details-modal");
                openBookingModal(car);
            };
            document.getElementById("details-modal").style.display = "flex";
        }

        function openBookingModal(car) {
            currentCarRate = parseFloat(car.daily_rate);
            document.getElementById("modal-car-name").innerText = `Book Online: ${car.vehicle_name}`;
            document.getElementById("book-vehicle-id").value = car.vehicle_id;
            document.getElementById("modal-days").value = 1;
            
            calculateDaysAndTotal();
            document.getElementById("booking-modal").style.display = "flex";
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        function calculateDaysAndTotal() {
            const pickup = document.getElementById("pickup-date").value;
            const returnDate = document.getElementById("return-date").value;
            const daysInput = document.getElementById("modal-days");

            if (pickup && returnDate) {
                const diffTime = new Date(returnDate) - new Date(pickup);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                if (diffDays > 0) {
                    daysInput.value = diffDays;
                }
            }

            const days = parseInt(daysInput.value, 10) || 1;
            const rentalFee = currentCarRate * days;
            const serviceFee = 500;
            const tax = (rentalFee + serviceFee) * 0.12;
            const total = rentalFee + serviceFee + tax;

            document.getElementById("summary-breakdown").innerText = `${days} day(s) @ ₱${currentCarRate.toLocaleString()} + ₱500 service fee + 12% VAT`;
            document.getElementById("modal-total-price").innerText = "₱" + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Send reservation request to back-end to reflect on staff dashboard
        function confirmReservation(e) {
            e.preventDefault();
            const formElement = document.getElementById("reservation-form");
            const formData = new FormData(formElement);

            fetch("process-booking.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Success! Your booking request has been sent to the staff. Reference: " + data.reference);
                    closeModal("booking-modal");
                    formElement.reset();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("An unexpected error occurred while communicating with the server.");
            });
        }

        function submitInquiry(e) {
            e.preventDefault();
            alert("Thank you for your inquiry! Our concierge team will reach out to you.");
            e.target.reset();
        }
    </script>
</body>
</html>