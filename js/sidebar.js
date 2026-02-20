// Function to generate the sidebar and highlight the active link
function loadSidebar() {
    const sidebarContainer = document.getElementById('sidebar-container');
    if (!sidebarContainer) return;

    // Get the current page name from the URL (e.g., "owners.html")
    const currentPage = window.location.pathname.split("/").pop();

    // Helper function to determine classes
    const getLinkClass = (pageName) => {
        const baseClass = "flex items-center px-4 py-3 rounded-lg transition-colors";
        const activeClass = "bg-[#E0F2F1] text-[#00796B] font-semibold";
        const inactiveClass = "text-gray-600 hover:bg-gray-100 hover:text-[#00796B]";
        
        return `${baseClass} ${currentPage === pageName ? activeClass : inactiveClass}`;
    };

    sidebarContainer.innerHTML = `
    <aside id="sidebar" class="bg-white text-gray-700 w-64 min-h-screen flex flex-col shadow-xl fixed lg:static transform -translate-x-full lg:translate-x-0 z-30 sidebar-transition">
        <div class="h-24 flex items-center justify-center border-b border-gray-200 bg-[#00796B]">
            <div class="flex items-center gap-3 text-white">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center"><i class="fa-solid fa-paw text-xl"></i></div>
                <h1 class="text-lg font-bold tracking-wide">CITY VET ADMIN</h1>
            </div>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="dashboard.html" class="${getLinkClass('dashboard.html')}"><i class="fa-solid fa-gauge-high mr-3 w-6 text-center"></i> Dashboard</a>
            
            <p class="px-4 text-xs font-bold text-gray-400 uppercase mt-4 mb-2">Records</p>
            <a href="owners.html" class="${getLinkClass('owners.html')}"><i class="fa-solid fa-users mr-3 w-6 text-center"></i> Pet Owners & Pets</a>
            <a href="staff.html" class="${getLinkClass('staff.html')}"><i class="fa-solid fa-user-doctor mr-3 w-6 text-center"></i> Employee Records</a>
            
            <p class="px-4 text-xs font-bold text-gray-400 uppercase mt-4 mb-2">Clinic Ops</p>
            <a href="appointments.html" class="${getLinkClass('appointments.html')}"><i class="fa-regular fa-calendar-check mr-3 w-6 text-center"></i> Appointments</a>
            <a href="consultations.html" class="${getLinkClass('consultations.html')}"><i class="fa-solid fa-stethoscope mr-3 w-6 text-center"></i> Consultations</a>
            <a href="species_breeds.html" class="${getLinkClass('species_breeds.html')}"><i class="fa-solid fa-dog mr-3 w-6 text-center"></i> Species & Breeds</a>
            
            <p class="px-4 text-xs font-bold text-gray-400 uppercase mt-4 mb-2">System</p>
            <a href="reports.html" class="${getLinkClass('reports.html')}"><i class="fa-solid fa-file-invoice mr-3 w-6 text-center"></i> Reports</a>
            <a href="user_management.html" class="${getLinkClass('user_management.html')}"><i class="fa-solid fa-users-gear mr-3 w-6 text-center"></i> User Management</a>
            <a href="master_files.html" class="${getLinkClass('master_files.html')}"><i class="fa-solid fa-sliders mr-3 w-6 text-center"></i> Clinic Settings</a>
        </nav>
        <div class="p-4 border-t border-gray-200">
            <button onclick="logout()" class="w-full flex items-center justify-center px-4 py-2 text-red-500 hover:bg-red-50 rounded-lg font-bold transition"><i class="fa-solid fa-right-from-bracket mr-2"></i> Sign Out</button>
        </div>
    </aside>`;
}

// Call the function once the script loads
loadSidebar();