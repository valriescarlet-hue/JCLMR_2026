
let approvedCount = 0;
let rejectedCount = 0;

function updateRequest(button, decision) {

    const row = button.closest("tr");
    const cells = row.querySelectorAll("td");

    const requestId = cells[0].textContent;
    const customer = cells[1].textContent;

    const tableId = row.closest("tbody").id;

    const requestType =
        tableId === "rentalTable"
            ? "Rental"
            : "Car Lending";

    const confirmAction = confirm(
        `Are you sure you want to ${decision} this request?`
    );

    if (!confirmAction) return;

    const statusClass =
        decision === "approved"
            ? "approved-status"
            : "rejected-status";

    const statusText =
        decision === "approved"
            ? "Approved"
            : "Rejected";

    // Update status
    cells[5].innerHTML =
        `<span class="status ${statusClass}">${statusText}</span>`;

    // Update statistics
    if (decision === "approved") {
        approvedCount++;
    } else {
        rejectedCount++;
    }

    document.getElementById("approvedCount").textContent =
        approvedCount;

    document.getElementById("rejectedCount").textContent =
        rejectedCount;

    // Add to history
    const historyTable =
        document.getElementById("historyTable");

    if (historyTable.querySelector(".empty")) {
        historyTable.innerHTML = "";
    }

    const newRow = document.createElement("tr");

    [requestId, customer, requestType, statusText]
        .forEach((value, index) => {
            const td = document.createElement("td");

            if (index === 3) {
                const span = document.createElement("span");
                span.className = `status ${statusClass}`;
                span.textContent = value;
                td.appendChild(span);
            } else {
                td.textContent = value;
            }

            newRow.appendChild(td);
        });

    historyTable.appendChild(newRow);

    // Remove action buttons
    cells[cells.length - 1].innerHTML =
        `<span class="status ${statusClass}">${statusText}</span>`;

    updatePendingCounts();
}

// Update pending request statistics
function updatePendingCounts() {

    const rentalCount = document.querySelectorAll(
        "#rentalTable .status.pending"
    ).length;

    const carCount = document.querySelectorAll(
        "#carTable .status.pending"
    ).length;

    document.getElementById("pendingRentals").textContent =
        rentalCount;

    document.getElementById("pendingCars").textContent =
        carCount;
}

// Logout
function logout() {

    const confirmLogout = confirm(
        "Are you sure you want to logout?"
    );

    if (confirmLogout) {
        window.location.href = "login.html";
    }
}

updatePendingCounts();
