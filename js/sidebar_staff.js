function loadStaffSidebar() {
    const sidebarContainer = document.getElementById('sidebar-container');
    if (!sidebarContainer) return;

    const currentPage = window.location.pathname.split("/").pop();

    const getLinkClass = (pageName) => {
        const baseClass = "flex items-center px-4 py-3 rounded-xl transition-all duration-200 mb-1";
        const activeClass = "bg-[#00796B] text-white font-semibold shadow-md";
        const inactiveClass = "text-teal-50 hover:bg-[#00796B]/20 hover:text-white";
        return `${baseClass} ${currentPage === pageName ? activeClass : inactiveClass}`;
    };

    sidebarContainer.classList.add('lg:w-64', 'lg:flex-none');
    sidebarContainer.innerHTML = `
    <button id="sidebar-toggle" type="button" aria-label="Open navigation" aria-expanded="false" class="lg:hidden fixed bottom-4 left-4 z-40 h-11 w-11 rounded-full bg-[#004D40] text-white shadow-lg"><i class="fa-solid fa-bars"></i></button>
    <div id="sidebar-overlay" class="lg:hidden fixed inset-0 z-40 hidden bg-slate-950/50"></div>
    <aside id="sidebar" class="bg-[#004D40] text-white w-64 h-screen flex flex-col shadow-2xl fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 z-50 transition-transform duration-300 ease-in-out border-r border-[#00332B]">
        
        <div class="px-4 pt-4">
            <button onclick="window.history.length > 1 ? history.back() : window.location.href = '../../login.html'" class="w-full flex items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm font-bold text-white hover:bg-white/10 transition">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
        </div>

        <div class="h-24 flex-shrink-0 flex items-center justify-center border-b border-[#00332B] bg-gradient-to-r from-[#004D40] to-[#00796B] mt-3">
            <div class="flex items-center gap-3 text-white">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/10">
                    <i class="fa-solid fa-desktop text-xl"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-wide">FRONT <span class="text-teal-300">DESK</span></h1>
                    <p class="text-[10px] uppercase opacity-70">City Veterinary Clinic</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <p class="text-[10px] uppercase font-bold text-teal-400 px-4 tracking-[0.2em] mb-4">Operations</p>
            
            <a href="dashboard_staff.html" class="${getLinkClass('dashboard_staff.html')}">
                <i class="fa-solid fa-list-check mr-3 w-6 text-center"></i> Live Queue
            </a>
            
            <a href="walkin_management.html" class="${getLinkClass('walkin_management.html')}">
                <i class="fa-solid fa-person-walking-arrow-right mr-3 w-6 text-center"></i> Walk-In Registry
            </a>

            <!-- ✨ NEW: PATIENT DATABASE LINK ✨ -->
            <a href="patient_records.html" class="${getLinkClass('patient_records.html')}">
                <i class="fa-solid fa-folder-open mr-3 w-6 text-center"></i> Patient Database
            </a>

            <a href="carousel_management.html" class="${getLinkClass('carousel_management.html')}">
                <i class="fa-solid fa-images mr-3 w-6 text-center"></i> Homepage Carousel
            </a>

            <div class="pt-6 mt-2 border-t border-[#00332B]">
                <p class="text-[10px] uppercase font-bold text-teal-400 px-4 tracking-[0.2em] mb-4">Settings</p>
                <a href="profile_settings.html" class="${getLinkClass('profile_settings.html')}">
                    <i class="fa-solid fa-user-gear mr-3 w-6 text-center"></i> Account Profile
                </a>
            </div>
        </nav>

        <div class="p-4 border-t border-[#00332B] flex-shrink-0 mb-2 bg-[#00332B]/30">
            <button onclick="logout()" class="w-full flex items-center justify-center px-4 py-3 text-red-300 hover:bg-red-900/30 rounded-xl font-bold transition-all border border-transparent hover:border-red-900/50">
                <i class="fa-solid fa-right-from-bracket mr-2"></i> Sign Out
            </button>
        </div>
    </aside>`;
    initializeResponsiveSidebar(sidebarContainer);
}

function initializeResponsiveSidebar(container) {
    const sidebar = container.querySelector('#sidebar'), toggle = container.querySelector('#sidebar-toggle'), overlay = container.querySelector('#sidebar-overlay');
    if (!sidebar || !toggle || !overlay) return;
    const syncLayout = () => { container.style.flex = window.innerWidth >= 1024 ? '0 0 16rem' : '0 0 0'; };
    syncLayout();
    const close = () => { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); toggle.setAttribute('aria-expanded', 'false'); };
    const open = () => { sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); toggle.setAttribute('aria-expanded', 'true'); };
    toggle.addEventListener('click', () => sidebar.classList.contains('-translate-x-full') ? open() : close()); overlay.addEventListener('click', close);
    sidebar.querySelectorAll('a').forEach(link => link.addEventListener('click', close));
    window.addEventListener('resize', () => { syncLayout(); if (window.innerWidth >= 1024) { overlay.classList.add('hidden'); toggle.setAttribute('aria-expanded', 'false'); } });
}
document.addEventListener('DOMContentLoaded', loadStaffSidebar);

function startSessionTimeoutWatcher() {
    if (window.cityVetSessionWatcherStarted) return;
    window.cityVetSessionWatcherStarted = true;

    const checkSessionTimeout = () => {
        fetch('../../api/auth/session_status.php?touch=0', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'logged_in') {
                    window.location.href = '../../login.html';
                }
            })
            .catch(() => {
                window.location.href = '../../login.html';
            });
    };

    let lastClientTouch = 0;
    const touchSession = () => {
        const now = Date.now();
        if (now - lastClientTouch < 30000) return;
        lastClientTouch = now;

        fetch('../../api/auth/session_status.php', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'logged_in') {
                    window.location.href = '../../login.html';
                }
            })
            .catch(() => {});
    };

    ['click', 'keydown', 'change', 'scroll'].forEach(eventName => {
        document.addEventListener(eventName, touchSession, { passive: true });
    });

    setInterval(checkSessionTimeout, 15000);
    checkSessionTimeout();
}
startSessionTimeoutWatcher();

// Global Logout Function
function logout() {
    Swal.fire({
        title: 'Logout Session?',
        text: "You will need to login again to access your account.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#00796B',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Logout',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../../api/auth/logout.php', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => { window.location.href = '../../login.html'; })
            .catch(err => { window.location.href = '../../login.html'; });
        }
    });
}
