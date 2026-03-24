function loadVetSidebar() {
    const sidebarContainer = document.getElementById('sidebar-container');
    if (!sidebarContainer) return;

    // Get current filename to highlight the active link
    const currentPage = window.location.pathname.split("/").pop();

    const getLinkClass = (pageName) => {
        const baseClass = "flex items-center px-4 py-3 rounded-lg transition-colors";
        const activeClass = "bg-[#E0F2F1] text-[#00796B] font-semibold"; // Match Owner Active Color
        const inactiveClass = "text-gray-600 hover:bg-gray-100 hover:text-[#00796B]";
        return `${baseClass} ${currentPage === pageName ? activeClass : inactiveClass}`;
    };

    sidebarContainer.innerHTML = `
    <aside id="sidebar" class="bg-white text-gray-700 w-64 h-screen flex flex-col shadow-xl fixed lg:static transform -translate-x-full lg:translate-x-0 z-30 sidebar-transition">
        <div class="h-24 flex-shrink-0 flex items-center justify-center border-b border-gray-200 bg-blue-600">
            <div class="flex items-center gap-3 text-white">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-user-md text-xl"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-wide">VET PORTAL</h1>
                    <p class="text-[10px] uppercase opacity-80">City Veterinary Clinic</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="dashboard.html" class="${getLinkClass('dashboard.html')}">
                <i class="fa-solid fa-clipboard-list mr-3 w-6 text-center"></i> Pending Requests
            </a>
            <a href="history.html" class="${getLinkClass('history.html')}">
                <i class="fa-solid fa-history mr-3 w-6 text-center"></i> Appointment History
            </a>
            </nav>

        <div class="p-4 border-t border-gray-200 flex-shrink-0">
            <button onclick="logout()" class="w-full flex items-center justify-center px-4 py-2 text-red-500 hover:bg-red-50 rounded-lg font-bold transition">
                <i class="fa-solid fa-right-from-bracket mr-2"></i> Sign Out
            </button>
        </div>
    </aside>`;
}

// Auto-run if the container exists
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
            .then(data => {
                window.location.href = '../../login.html'; 
            })
            .catch(err => {
                window.location.href = '../../login.html'; 
            });
        }
    });
}