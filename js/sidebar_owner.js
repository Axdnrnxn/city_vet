function loadSidebar() {
    const sidebarContainer = document.getElementById('sidebar-container');
    if (!sidebarContainer) return;

    const currentPage = window.location.pathname.split("/").pop();

    const getLinkClass = (pageName) => {
        const baseClass = "flex items-center px-4 py-3 rounded-lg transition-colors";
        // ACTIVE: Light teal background with dark teal text
        const activeClass = "bg-[#E0F2F1] text-[#00796B] font-semibold shadow-sm";
        // INACTIVE: Gray text, hovering changes to brand teal
        const inactiveClass = "text-gray-600 hover:bg-teal-50 hover:text-[#00796B]";
        
        return `${baseClass} ${currentPage === pageName ? activeClass : inactiveClass}`;
    };

    sidebarContainer.innerHTML = `
    <aside id="sidebar" class="bg-white text-gray-700 w-64 h-screen flex flex-col shadow-xl fixed lg:static transform -translate-x-full lg:translate-x-0 z-30 sidebar-transition">
        
        <div class="h-24 flex-shrink-0 flex items-center justify-center border-b border-gray-200 bg-gradient-to-br from-[#004D40] to-[#00796B]">
            <div class="flex items-center gap-3 text-white">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/10">
                    <i class="fa-solid fa-paw text-xl"></i>
                </div>
                <h1 class="text-lg font-bold tracking-wide">CITY VET</h1>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Owner Menu</p>
            
            <a href="dashboard.html" class="${getLinkClass('dashboard.html')}">
                <i class="fa-solid fa-calendar-check mr-3 w-6 text-center text-lg"></i> 
                Book Appointment
            </a>
            
            <a href="mypets.html" class="${getLinkClass('mypets.html')}">
                <i class="fa-solid fa-dog mr-3 w-6 text-center text-lg"></i> 
                My Pet Records
            </a>

            <div class="pt-4 mt-4 border-t border-gray-100">
                <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Settings</p>
                
                <a href="profile_settings.html" class="${getLinkClass('profile_settings.html')}">
                    <i class="fa-solid fa-user-gear mr-3 w-6 text-center text-lg"></i> 
                    Account Profile
                </a>
            </div>
        </nav>

        <div class="p-4 border-t border-gray-200 flex-shrink-0 bg-gray-50/50">
            <button onclick="logout()" class="w-full flex items-center justify-center px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl font-bold transition-all border border-transparent hover:border-red-100">
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
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Yes, Logout',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'rounded-lg px-6 py-2',
            cancelButton: 'rounded-lg px-6 py-2'
        }
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