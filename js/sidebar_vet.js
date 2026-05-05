function loadVetSidebar() {
    const sidebarContainer = document.getElementById('sidebar-container');
    if (!sidebarContainer) return;

    const currentPage = window.location.pathname.split("/").pop();

    const getLinkClass = (pageName) => {
        const baseClass = "flex items-center px-4 py-3 rounded-xl transition-all duration-200 mb-1";
        const activeClass = "bg-[#00796B] text-white font-semibold shadow-md";
        const inactiveClass = "text-teal-50 hover:bg-[#00796B]/20 hover:text-white";
        // Check if current page matches exactly or is the pet details page
        return `${baseClass} ${currentPage === pageName ? activeClass : inactiveClass}`;
    };

    sidebarContainer.innerHTML = `
    <aside id="sidebar" class="bg-[#004D40] text-white w-64 h-screen flex flex-col shadow-2xl fixed lg:static transform -translate-x-full lg:translate-x-0 z-30 sidebar-transition border-r border-[#00332B]">
        
        <div class="h-24 flex-shrink-0 flex items-center justify-center border-b border-[#00332B] bg-gradient-to-r from-[#004D40] to-[#00796B]">
            <div class="flex items-center gap-3 text-white">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/10">
                    <i class="fa-solid fa-user-md text-xl"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-wide">VET <span class="text-teal-300">PORTAL</span></h1>
                    <p class="text-[10px] uppercase opacity-70">City Veterinary Clinic</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <p class="text-[10px] uppercase font-bold text-teal-400 px-4 tracking-[0.2em] mb-4">Medical Menu</p>
            
            <a href="dashboard.html" class="${getLinkClass('dashboard.html')}">
                <i class="fa-solid fa-clipboard-list mr-3 w-6 text-center"></i> Pending Requests
            </a>

            <!-- ✨ NEW: PATIENT RECORDS LINK -->
            <a href="pet_details.html" class="${getLinkClass('pet_details.html')}">
                <i class="fa-solid fa-file-medical mr-3 w-6 text-center"></i> Patient Records
            </a>

            <a href="history.html" class="${getLinkClass('history.html')}">
                <i class="fa-solid fa-history mr-3 w-6 text-center"></i> Appointment History
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
}
document.addEventListener('DOMContentLoaded', loadVetSidebar);

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
            .then(data => { window.location.href = '../../login.html'; })
            .catch(err => { window.location.href = '../../login.html'; });
        }
    });
}