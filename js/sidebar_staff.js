function loadStaffSidebar() {
    const sidebarContainer = document.getElementById('sidebar-container');
    if (!sidebarContainer) return;

    const currentPage = window.location.pathname.split("/").pop();

    const getLinkClass = (pageName) => {
        const baseClass = "flex items-center px-4 py-3 rounded-xl transition-colors mb-1";
        const activeClass = "bg-teal-700 text-white font-semibold shadow-inner";
        const inactiveClass = "text-teal-100 hover:bg-teal-700/50 hover:text-white";
        return `${baseClass} ${currentPage === pageName ? activeClass : inactiveClass}`;
    };

    sidebarContainer.innerHTML = `
    <aside id="sidebar" class="bg-[#004D40] text-white w-64 h-screen flex flex-col shadow-2xl fixed lg:static transform -translate-x-full lg:translate-x-0 z-30 sidebar-transition">
        <div class="h-24 flex-shrink-0 flex items-center justify-center border-b border-teal-800 bg-[#00332B]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-teal-400/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-user-shield text-teal-400 text-xl"></i>
                </div>
                <h1 class="text-lg font-bold tracking-wider uppercase">Staff <span class="text-teal-400">Portal</span></h1>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <a href="dashboard_staff.html" class="${getLinkClass('dashboard_staff.html')}">
                <i class="fa-solid fa-chart-line mr-3 w-6 text-center"></i> Dashboard
            </a>
            <a href="walkin_management.html" class="${getLinkClass('walkin_management.html')}">
                <i class="fa-solid fa-person-walking mr-3 w-6 text-center"></i> Walk-in
            </a>
            <a href="consultation.html" class="${getLinkClass('consultation.html')}">
                <i class="fa-solid fa-notes-medical mr-3 w-6 text-center"></i> Consultations
            </a>
            
            <div class="pt-4 pb-2">
                <p class="text-[10px] uppercase font-bold text-teal-500 px-4 tracking-[0.2em]">Patient Records</p>
            </div>

            <a href="pets_records.html" class="${getLinkClass('pets_records.html')}">
                <i class="fa-solid fa-paw mr-3 w-6 text-center"></i> Pet Profiles
            </a>
        </nav>

        <div class="p-4 border-t border-teal-800 flex-shrink-0 mb-2">
            <button onclick="logout()" class="w-full flex items-center justify-center px-4 py-3 text-red-300 hover:bg-red-900/30 rounded-xl font-bold transition">
                <i class="fa-solid fa-right-from-bracket mr-2"></i> Sign Out
            </button>
        </div>
    </aside>`;
}
loadStaffSidebar();

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
            fetch('../../api/auth/logout.php')
            .then(res => res.json())
            .then(data => {
                window.location.href = '../../login.html'; 
            })
            .catch(err => {
                window.location.href = '../../login.html'; 
            });
        }
    });
}