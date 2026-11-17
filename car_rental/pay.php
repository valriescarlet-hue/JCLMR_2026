<!-- Inside pay.php -->
<form id="paymentForm" action="process_payment.php" method="POST">
    <!-- Keep your existing booking_id hidden input so the system knows what booking is being paid for -->
    <input type="hidden" name="booking_id" value="7"> 
    
    <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
    </div>

    <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>
    </div>

    <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" placeholder="09XXXXXXXXX" pattern="[0-9]{11}" required>
    </div>

    <h2 class="payment-heading">Payment Method</h2>

    <div class="payment-options">
        <label class="payment-option">
            <input type="radio" name="payment_method" value="GCash" checked>
            <span>GCash</span>
        </label>
        <label class="payment-option">
            <input type="radio" name="payment_method" value="Credit/Debit Card">
            <span>Credit/Debit Card</span>
        </label>
    </div>
    
    <!-- Rest of your card details and submit button -->
    <button type="submit" class="submit-btn">Submit Payment</button>
</form>