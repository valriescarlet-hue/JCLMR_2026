
let data = {
    vehicles: [],
    payments: [],
    maintenance: [],
    rentals: [],
    staffRequests: [],
    adminRequests: []
};

// Fetch live database records via api.php
async function loadDataFromDB() {
    try {
        const response = await fetch('api.php?action=get_all');

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            data = {
                vehicles: result.vehicles || [],
                payments: result.payments || [],
                maintenance: result.maintenance || [],
                rentals: result.rentals || [],
                staffRequests: result.staffRequests || [],
                adminRequests: result.adminRequests || [],
                errors: result.errors || {}
            };

            renderPage();

            const failedSections = Object.keys(data.errors);

            if (failedSections.length) {
                const warning = document.createElement("p");
                warning.className = "api-warning";
                warning.textContent =
                    "Some dashboard sections could not be loaded: " +
                    failedSections.join(", ") +
                    ". Check the PHP error log and database table/column names.";

                content.prepend(warning);
            }

        } else {
            console.error("Database error:", result.error);

            content.innerHTML = `
                <div class="empty-message">
                    Error loading database records:
                    ${escapeHTML(result.error)}
                </div>
            `;
        }

    } catch (error) {
        console.error("Failed to connect to backend API:", error);

        content.innerHTML = `
            <div class="empty-message">
                Failed to connect to api.php.
                Please check your server environment.
                <br>
                ${escapeHTML(error.message)}
            </div>
        `;
    }
}


// ELEMENTS
const content = document.getElementById("content");
const pageTitle = document.getElementById("page-title");
const navButtons = document.querySelectorAll(".nav-btn");

let currentPage = "dashboard";


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


// GENERAL HELPERS
function money(amount) {
    return "₱" + Number(amount || 0).toLocaleString("en-PH", {
        minimumFractionDigits: 2
    });
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
            <h2>${escapeHTML(title)}</h2>
            <p>${escapeHTML(description)}</p>
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
                    <tr>
                        ${headers.map(h => `<th>${escapeHTML(h)}</th>`).join("")}
                    </tr>
                </thead>
                <tbody>
                    ${rows.join("")}
                </tbody>
            </table>
        </div>
    `;
}


// NAVIGATION
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
        rentals: "Rentals & Returns",
        staff: "Staff Requests",
        admins: "Admin Requests"
    };

    pageTitle.textContent = titles[currentPage] || "Dashboard";

    const pages = {
        dashboard: renderDashboard,
        payments: renderPayments,
        reports: renderReports,
        vehicles: renderVehicles,
        maintenance: renderMaintenance,
        rentals: renderRentals,
        staff: renderStaffRequests,
        admins: renderAdminRequests
    };

    if (pages[currentPage]) {
        pages[currentPage]();
    }
}


// ==========================================
// RENDER VIEWS
// ==========================================

function renderDashboard() {

    const totalVehicles = data.vehicles.length;

    const availableVehicles = data.vehicles.filter(
        v => String(v.status).toLowerCase() === "available"
    ).length;

    const rentedVehicles = data.vehicles.filter(
        v => String(v.status).toLowerCase() === "rented"
    ).length;

    const pendingPayments = data.payments.filter(
        p => String(p.status).toLowerCase() === "pending"
    ).length;

    const totalRevenue = data.payments
        .filter(p => String(p.status).toLowerCase() === "paid")
        .reduce((sum, p) => sum + Number(p.amount), 0);

    const pendingStaff = data.staffRequests.filter(
        r => String(r.status).toLowerCase() === "pending"
    ).length;

    const pendingAdmins = data.adminRequests.filter(
        r => String(r.status).toLowerCase() === "pending"
    ).length;

    content.innerHTML = `
        ${sectionHeading(
            "Dashboard Overview",
            "Reflecting live data from your system_db MySQL tables."
        )}

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

            <div class="stat-card">
                <div class="stat-icon">♙</div>
                <p>Pending Requests</p>
                <h2>${pendingStaff + pendingAdmins}</h2>
                <small>
                    ${pendingStaff} Staff / ${pendingAdmins} Admin
                </small>
            </div>

        </div>

        <div class="panel">

            <div class="panel-heading">
                <h3>Recent Payments</h3>
                <span class="muted">
                    ${pendingPayments} Pending Payments
                </span>
            </div>

            ${table(
                ["Payment ID", "Customer", "Amount", "Method", "Status"],

                data.payments.slice(-5).reverse().map(p => `
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


// PAYMENTS
function renderPayments() {

    content.innerHTML = `
        ${sectionHeading(
            "Payments Management",
            "Live records from payments table."
        )}

        <div class="panel">

            ${table(
                ["ID", "Customer", "Amount", "Method", "Status"],

                data.payments.map(p => `
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


// REPORTS
function renderReports() {
    renderDashboard();
}


// VEHICLES
function renderVehicles() {

    content.innerHTML = `
        ${sectionHeading(
            "Vehicle Management",
            "Live records from cars table."
        )}

        <div class="panel">

            ${table(
                ["ID", "Vehicle Name", "Plate Number", "Category", "Status"],

                data.vehicles.map(v => `
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


// MAINTENANCE
function renderMaintenance() {

    content.innerHTML = `
        ${sectionHeading(
            "Maintenance Management",
            "Live records from maintenance table."
        )}

        <div class="panel">

            ${table(
                ["ID", "Vehicle", "Issue / Type", "Date", "Status"],

                data.maintenance.map(m => `
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


// RENTALS & RETURNS
function renderRentals() {

    content.innerHTML = `
        ${sectionHeading(
            "Rentals & Returns",
            "Live records from rental_returns table."
        )}

        <div class="panel">

            ${table(
                ["ID", "Customer", "Vehicle", "Return Date", "Status"],

                data.rentals.map(r => `
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


// STAFF REQUESTS
function renderStaffRequests() {

    content.innerHTML = `
        ${sectionHeading(
            "Staff Requests",
            "Live records from staff table."
        )}

        <div class="panel">

            ${table(
                ["ID", "Name", "Employee ID", "Position", "Status"],

                data.staffRequests.map(s => `
                    <tr>
                        <td>#${escapeHTML(s.id)}</td>
                        <td>${escapeHTML(s.name)}</td>
                        <td>${escapeHTML(s.email)}</td>
                        <td>${escapeHTML(s.position)}</td>
                        <td>${statusBadge(s.status)}</td>
                    </tr>
                `)
            )}

        </div>
    `;
}


// ADMIN REQUESTS
function renderAdminRequests() {

    content.innerHTML = `
        ${sectionHeading(
            "Admin Requests",
            "Live records from users table."
        )}

        <div class="panel">

            ${table(
                ["ID", "Username", "Email", "Role", "Status"],

                data.adminRequests.map(a => `
                    <tr>
                        <td>#${escapeHTML(a.id)}</td>
                        <td>${escapeHTML(a.name)}</td>
                        <td>${escapeHTML(a.email)}</td>
                        <td>${escapeHTML(a.position)}</td>
                        <td>${statusBadge(a.status)}</td>
                    </tr>
                `)
            )}

        </div>
    `;
}


// INITIALIZE DASHBOARD
loadDataFromDB();
