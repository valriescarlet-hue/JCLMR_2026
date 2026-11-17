const content = document.getElementById("content");
const pageTitle = document.getElementById("page-title");
const navButtons = document.querySelectorAll(".nav-btn");

let currentPage = "dashboard";
let data = {
    vehicles: [],
    payments: [],
    maintenance: [],
    rentals: []
};

async function loadDataFromDB() {
    try {
        const response = await fetch('api2.php?action=get_all');
        const result = await response.json();
        
        if (result && result.success) {
            data = {
                vehicles: result.vehicles || [],
                payments: result.payments || [],
                maintenance: result.maintenance || [],
                rentals: result.rentals || []
            };
            renderPage();
        } else {
            console.error("Database error:", result?.error);
            content.innerHTML = `<div class="empty-message">Error loading database records: ${escapeHTML(result?.error || 'Unknown error')}</div>`;
        }
    } catch (error) {
        console.error("Failed to connect to backend API:", error);
        content.innerHTML = `<div class="empty-message">Failed to connect to api2.php. Please check your server environment.</div>`;
    }
}

// SECURITY HELPER
function escapeHTML(value) {
    return String(value ?? "").replace(/[&<>"']/g, char => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    })[char]);
}

function money(amount) {
    return "₱" + Number(amount || 0).toLocaleString("en-PH", {minimumFractionDigits: 2});
}

function statusBadge(status) {
    const cleanStatus = String(status || 'pending').toLowerCase();
    return `<span class="status ${escapeHTML(cleanStatus)}">
        ${escapeHTML(status)}
    </span>`;
}

function emptyMessage(message) {
    return `<div class="empty-message">${escapeHTML(message)}</div>`;
}

function sectionHeading(title, description) {
    return `
        <div class="section-heading">
            <h2>${title}</h2>
            <p>${description}</p>
        </div>
    `;
}

function table(headers, rows) {
    if (!rows || !rows.length) {
        return emptyMessage("No records found in this database table.");
    }

    return `
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>${headers.map(h => `<th>${h}</th>`).join("")}</tr>
                </thead>
                <tbody>${rows.join("")}</tbody>
            </table>
        </div>
    `;
}

navButtons.forEach(button => {
    button.addEventListener("click", () => {
        navButtons.forEach(btn => btn.classList.remove("active"));
        button.classList.add("active");
        currentPage = button.dataset.page;
        renderPage();
    });
});

function renderPage() {
    const titles = {
        dashboard: "Dashboard Overview",
        payments: "Payments Management",
        reports: "Reports & Analysis",
        vehicles: "Vehicle Management",
        maintenance: "Maintenance Management",
        rentals: "Rentals & Returns"
    };

    pageTitle.textContent = titles[currentPage] || "Dashboard";

    const pages = {
        dashboard: renderDashboard,
        payments: renderPayments,
        reports: renderReports,
        vehicles: renderVehicles,
        maintenance: renderMaintenance,
        rentals: renderRentals
    };

    if (pages[currentPage]) {
        pages[currentPage]();
    }
}

function renderDashboard() {
    const vehicles = data.vehicles || [];
    const payments = data.payments || [];

    const totalVehicles = vehicles.length;
    const availableVehicles = vehicles.filter(v => String(v.status || '').toLowerCase() === "available").length;
    const rentedVehicles = vehicles.filter(v => String(v.status || '').toLowerCase() === "rented").length;
    const pendingPayments = payments.filter(p => String(p.status || '').toLowerCase() === "pending").length;

    const totalRevenue = payments
        .filter(p => String(p.status || '').toLowerCase() === "paid")
        .reduce((sum, p) => sum + Number(p.amount || 0), 0);

    content.innerHTML = `
        ${sectionHeading("Dashboard Overview", "Reflecting live data from your system_db MySQL tables.")}

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🚘</div>
                <p>Total Vehicles</p>
                <h2>${totalVehicles}</h2>
                <small>${availableVehicles} Available</small>
            </div>
            <div class="stat-card">
                <div class="stat-icon">₱</div>
                <p>Total Revenue</p>
                <h2>${money(totalRevenue)}</h2>
                <small>Paid transactions</small>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <p>Active Rentals</p>
                <h2>${rentedVehicles}</h2>
                <small>Currently rented</small>
            </div>
        </div>

        <div class="panel">
            <div class="panel-heading">
                <h3>Recent Payments</h3>
                <span class="muted">${pendingPayments} Pending Payments</span>
            </div>
            ${table(
                ["Payment ID", "Customer", "Amount", "Method", "Status"],
                payments.slice(-5).reverse().map(p => `
                    <tr>
                        <td>#${escapeHTML(p.id)}</td>
                        <td>${escapeHTML(p.customer)}</td>
                        <td>${money(p.amount)}</td>
                        <td>${escapeHTML(p.method)}</td>
                        <td>${statusBadge(p.status)}</td>
                    </tr>
                `)
            )}
        </div>
    `;
}

function renderPayments() {
    const payments = data.payments || [];
    content.innerHTML = `
        ${sectionHeading("Payments Management", "Live records from payments table.")}
        <div class="panel">
            ${table(
                ["ID", "Customer", "Amount", "Method", "Status"],
                payments.map(p => `
                    <tr>
                        <td>#${escapeHTML(p.id)}</td>
                        <td>${escapeHTML(p.customer)}</td>
                        <td>${money(p.amount)}</td>
                        <td>${escapeHTML(p.method)}</td>
                        <td>${statusBadge(p.status)}</td>
                    </tr>
                `)
            )}
        </div>
    `;
}

function renderReports() {
    renderDashboard();
}

function renderVehicles() {
    const vehicles = data.vehicles || [];
    content.innerHTML = `
        ${sectionHeading("Vehicle Management", "Live records from cars table.")}
        <div class="panel">
            ${table(
                ["ID", "Vehicle Name", "Plate Number", "Category", "Status"],
                vehicles.map(v => `
                    <tr>
                        <td>#${escapeHTML(v.id)}</td>
                        <td>${escapeHTML(v.name)}</td>
                        <td>${escapeHTML(v.plate)}</td>
                        <td>${escapeHTML(v.type)}</td>
                        <td>${statusBadge(v.status)}</td>
                    </tr>
                `)
            )}
        </div>
    `;
}

function renderMaintenance() {
    const maintenance = data.maintenance || [];
    content.innerHTML = `
        ${sectionHeading("Maintenance Management", "Live records from maintenance table.")}
        <div class="panel">
            ${table(
                ["ID", "Vehicle", "Issue / Type", "Date", "Status"],
                maintenance.map(m => `
                    <tr>
                        <td>#${escapeHTML(m.id)}</td>
                        <td>${escapeHTML(m.vehicle)}</td>
                        <td>${escapeHTML(m.issue)}</td>
                        <td>${escapeHTML(m.date)}</td>
                        <td>${statusBadge(m.status)}</td>
                    </tr>
                `)
            )}
        </div>
    `;
}

function renderRentals() {
    const rentals = data.rentals || [];
    content.innerHTML = `
        ${sectionHeading("Rentals & Returns", "Live records from rental_returns table.")}
        <div class="panel">
            ${table(
                ["ID", "Customer", "Vehicle", "Return Date", "Status"],
                rentals.map(r => `
                    <tr>
                        <td>#${escapeHTML(r.id)}</td>
                        <td>${escapeHTML(r.customer)}</td>
                        <td>${escapeHTML(r.vehicle)}</td>
                        <td>${escapeHTML(r.date)}</td>
                        <td>${statusBadge(r.status)}</td>
                    </tr>
                `)
            )}
        </div>
    `;
}

// Initialize loading
loadDataFromDB();