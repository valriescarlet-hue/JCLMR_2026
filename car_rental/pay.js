// SUBMIT PAYMENT VIA AJAX
document.getElementById("paymentForm").addEventListener(
    "submit",
    async function(event) {

        event.preventDefault();

        const name = document.getElementById("name").value.trim();
        const email = document.getElementById("email").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const payment = document.querySelector('input[name="payment"]:checked').value;
        const message = document.getElementById("message");

        // CARD VALIDATION
        if (payment === "Credit/Debit Card") {
            const cardName = document.getElementById("cardName").value.trim();
            const cardNumber = document.getElementById("cardNumber").value.replace(/\s/g, "");
            const expiry = document.getElementById("expiry").value;
            const cvv = document.getElementById("cvv").value;

            if (!cardName || !/^\d{16}$/.test(cardNumber) || !/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry) || !/^\d{3,4}$/.test(cvv)) {
                message.style.color = "#ef4444";
                message.textContent = "Please check your card information.";
                return;
            }
        }

        // Prepare Form Data for Backend
        const formData = new FormData();
        formData.append("name", name);
        formData.append("email", email);
        formData.append("phone", phone);
        formData.append("payment", payment);

        try {
            const response = await fetch("process_payment.php", {
                method: "POST",
                body: formData
            });

            const result = await response.json();

            if (result.status === "success") {
                const receipt = {
                    name: name,
                    email: email,
                    phone: phone,
                    payment: payment,
                    receiptNumber: result.receipt_number,
                    transactionId: result.transaction_id,
                    car: "Toyota Sports Car",
                    transmission: "Automatic Transmission",
                    pickup: "Nov 15, 2026",
                    returnDate: "Nov 18, 2026",
                    duration: "3 Days",
                    rentalFee: 3000,
                    serviceFee: 300,
                    tax: 200,
                    total: 3500,
                    date: new Date().toLocaleDateString()
                };

                sessionStorage.setItem("paymentReceipt", JSON.stringify(receipt));
                window.location.href = "reciept.html";
            } else {
                message.style.color = "#ef4444";
                message.textContent = result.message;
            }
        } catch (error) {
            message.style.color = "#ef4444";
            message.textContent = "A network error occurred. Please try again.";
        }
    }
);