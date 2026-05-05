function loadSidebar() {
    const sidebarContainer = document.getElementById('sidebar-container');
    if (!sidebarContainer) return;

    const currentPage = window.location.pathname.split("/").pop();

    const getLinkClass = (pageName) => {
        const baseClass = "flex items-center px-4 py-3 rounded-xl transition-all duration-200 mb-1";
        const activeClass = "bg-[#00796B] text-white font-semibold shadow-md";
        const inactiveClass = "text-teal-50 hover:bg-[#00796B]/20 hover:text-white";
        return `${baseClass} ${currentPage === pageName ? activeClass : inactiveClass}`;
    };

    sidebarContainer.innerHTML = `
    <aside id="sidebar" class="bg-[#004D40] text-white w-64 h-screen flex flex-col shadow-2xl fixed lg:static transform -translate-x-full lg:translate-x-0 z-30 sidebar-transition border-r border-[#00332B]">
        
        <div class="h-24 flex-shrink-0 flex items-center justify-center border-b border-[#00332B] bg-gradient-to-r from-[#004D40] to-[#00796B]">
            <div class="flex items-center gap-3 text-white">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/10">
                    <i class="fa-solid fa-paw text-xl"></i>
                </div>
                <h1 class="text-lg font-bold tracking-wide">CITY VET <span class="text-teal-300">ADMIN</span></h1>
            </div>
        </div>
        
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <a href="dashboard.html" class="${getLinkClass('dashboard.html')}">
                <i class="fa-solid fa-gauge-high mr-3 w-6 text-center"></i> Dashboard
            </a>
            
            <p class="px-4 text-[10px] font-bold text-teal-400 uppercase mt-6 mb-2 tracking-[0.2em]">Records</p>
            <a href="owners.html" class="${getLinkClass('owners.html')}">
                <i class="fa-solid fa-users mr-3 w-6 text-center"></i> Pet Owners & Pets
            </a>
            <a href="staff.html" class="${getLinkClass('staff.html')}">
                <i class="fa-solid fa-user-doctor mr-3 w-6 text-center"></i> Employee Records
            </a>
            
            <p class="px-4 text-[10px] font-bold text-teal-400 uppercase mt-6 mb-2 tracking-[0.2em]">Clinic Ops</p>
            <a href="appointments.html" class="${getLinkClass('appointments.html')}">
                <i class="fa-regular fa-calendar-check mr-3 w-6 text-center"></i> Appointments
            </a>
            <a href="consultations.html" class="${getLinkClass('consultations.html')}">
                <i class="fa-solid fa-stethoscope mr-3 w-6 text-center"></i> Consultations
            </a>
            <a href="species_breeds.html" class="${getLinkClass('species_breeds.html')}">
                <i class="fa-solid fa-dog mr-3 w-6 text-center"></i> Species & Breeds
            </a>
            
            <p class="px-4 text-[10px] font-bold text-teal-400 uppercase mt-6 mb-2 tracking-[0.2em]">System Control</p>
            <a href="reports.html" class="${getLinkClass('reports.html')}">
                <i class="fa-solid fa-file-invoice mr-3 w-6 text-center"></i> Reports & Analytics
            </a>
            <a href="audit_logs.html" class="${getLinkClass('audit_logs.html')}">
                <i class="fa-solid fa-clock-rotate-left mr-3 w-6 text-center"></i> System Audit Logs
            </a>
            <a href="user_management.html" class="${getLinkClass('user_management.html')}">
                <i class="fa-solid fa-users-gear mr-3 w-6 text-center"></i> User Management
            </a>
            <a href="profile_settings.html" class="${getLinkClass('profile_settings.html')}">
                <i class="fa-solid fa-user-shield mr-3 w-6 text-center"></i> Admin Profile
            </a>
        </nav>

        <div class="p-4 border-t border-[#00332B] flex-shrink-0 mb-2 bg-[#00332B]/30">
            <button onclick="logout()" class="w-full flex items-center justify-center px-4 py-3 text-red-300 hover:bg-red-900/30 rounded-xl font-bold transition-all border border-transparent hover:border-red-900/50">
                <i class="fa-solid fa-right-from-bracket mr-2"></i> Sign Out
            </button>
        </div>
    </aside>`;
}
loadSidebar();

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