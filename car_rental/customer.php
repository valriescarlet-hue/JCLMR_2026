<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JCLMR DRIVE | Customer Dashboard</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#060913] text-slate-50 font-sans min-h-screen flex flex-col antialiased selection:bg-blue-500/30 selection:text-blue-200 overflow-x-hidden" style="background-image: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.08) 0%, transparent 55%), linear-gradient(to bottom, #060913, #020408);">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-40 bg-[#0f172a]/90 backdrop-blur-xl border-b border-[#263554] px-4 sm:px-8 lg:px-12 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between gap-4">
            
            <!-- Brand & Portal Status -->
            <div class="flex items-center gap-4">
                <a href="#" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-blue-600/15 border border-blue-500/35 flex items-center justify-center text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-all duration-200">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                            <circle cx="7" cy="17" r="2"/>
                            <path d="M9 17h6"/>
                            <circle cx="17" cy="17" r="2"/>
                        </svg>
                    </div>
                    <span class="text-lg sm:text-xl font-extrabold tracking-[0.16em] text-white">
                        JCLMR <span class="text-blue-500">DRIVE</span>
                    </span>
                </a>

                <div class="hidden md:flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/30 px-3 py-1 rounded-full text-xs font-semibold text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Customer Portal Active
                </div>
            </div>

            <!-- User Badge & Log Out Action -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Student Member Verified Badge -->
                <div class="hidden sm:flex items-center gap-3 px-3.5 py-2 rounded-xl bg-[#060913]/80 border border-[#263554]">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/20 border border-blue-500/30 flex items-center justify-center text-blue-400 font-bold text-xs">
                        SM
                    </div>
                    <div class="leading-tight">
                        <div class="text-xs font-semibold text-slate-100 flex items-center gap-1.5">
                            <span>Rental Member • Verified ID</span>
                        </div>
                        <div class="text-[11px] font-semibold text-emerald-400 mt-0.5">
                            
                        </div>
                    </div>
                </div>

                <!-- Log Out Button -->
                <button onclick="openLogoutModal()" class="px-4 py-2.5 rounded-xl bg-red-500/10 hover:bg-red-600 text-red-300 hover:text-white border border-red-500/30 hover:border-red-600 text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Log Out</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Dashboard Content -->
    <main class="max-w-5xl w-full mx-auto px-4 sm:px-8 py-12 sm:py-16 flex-1 flex flex-col justify-center">
        
        <!-- Dashboard Welcome Header -->
        <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-12 space-y-3">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                Rental Member Dashboard
            </h1>
            <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                Choose what you would like to do today. Book a reliable ride at student-friendly rates or list your own car safely with JCLMR.
            </p>
        </div>

        <!-- Primary 2-Column Operational Action Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
            
            <!-- Card 1: Rent a Vehicle -->
            <div class="group rounded-2xl bg-[#0f172a] border border-[#263554] hover:border-blue-500 p-7 sm:p-9 flex flex-col justify-between transition-all duration-200 hover:-translate-y-1 shadow-xl">
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-blue-500/15 border border-blue-500/30 flex items-center justify-center text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <!-- Crisp Car Vector Icon -->
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                                <circle cx="7" cy="17" r="2"/>
                                <path d="M9 17h6"/>
                                <circle cx="17" cy="17" r="2"/>
                            </svg>
                        </div>
                        <span class="px-3 py-1 rounded-lg bg-[#060913] border border-[#263554] text-xs font-semibold text-blue-400">
                            Variety
                        </span>
                    </div>

                    <h2 class="text-2xl font-bold text-white mb-3">Rent a Vehicle</h2>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed mb-8">
                        Browse clean, well-maintained cars for daily campus commutes, errands, or weekend trips—with an automatic 15% verified student discount and easy online booking.
                    </p>
                </div>

                <a href="car.php" class="w-full flex items-center justify-between px-6 py-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm sm:text-base font-semibold transition-all shadow-lg shadow-blue-600/25">
                    <span>Go to Car Listing</span>
                    <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <!-- Card 2: Lend Your Car -->
            <div class="group rounded-2xl bg-[#0f172a] border border-[#263554] hover:border-blue-500 p-7 sm:p-9 flex flex-col justify-between transition-all duration-200 hover:-translate-y-1 shadow-xl">
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-blue-500/15 border border-blue-500/30 flex items-center justify-center text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <!-- Crisp Key Vector Icon -->
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                            </svg>
                        </div>
                        <span class="px-3 py-1 rounded-lg bg-[#060913] border border-[#263554] text-xs font-semibold text-emerald-400">
                            Protected Sharing
                        </span>
                    </div>

                    <h2 class="text-2xl font-bold text-white mb-3">Lend Your Car</h2>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed mb-8">
                        Share and list your personal vehicle safely through JCLMR's verified network. Set your own availability schedule while every trip stays covered by full protection.
                    </p>
                </div>

                <a href="lend.php" class="w-full flex items-center justify-between px-6 py-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm sm:text-base font-semibold transition-all shadow-lg shadow-blue-600/25">
                    <span>Go to Car Lending</span>
                    <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-[#263554] bg-[#060913] py-6 px-4 text-center">
        <p class="text-xs sm:text-sm text-slate-400">
            &copy; 2026 JCLMR Car Rental Services.  Client Portal.
        </p>
    </footer>

    <!-- Custom Logout Confirmation Modal -->
    <div id="logoutModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-2xl bg-[#0f172a] border border-[#263554] shadow-2xl p-6 space-y-5">
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-red-500/15 border border-red-500/30 flex items-center justify-center text-red-400 shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Log Out of Portal?</h3>
                    <p class="text-sm text-slate-300 mt-1">Are you sure you want to end your current session?</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button onclick="closeLogoutModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-[#263554] text-sm font-semibold transition-all">
                    Cancel
                </button>
                <a href="logout.php" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white text-sm font-semibold transition-all shadow-lg shadow-red-600/25">
                    Yes, Log Out
                </a>
            </div>
        </div>
    </div>

    <script>
        // Open custom logout confirmation modal
        function openLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
        }

        // Close custom logout confirmation modal
        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }

        // Close modal when clicking outside the dialog card
        document.getElementById('logoutModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });
    </script>
</body>
</html>