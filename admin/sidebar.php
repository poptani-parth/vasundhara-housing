<aside class="sidebar w-64 bg-gray-800 text-gray-200 flex flex-col shadow-lg">
            <div class="flex items-center justify-center h-20 border-b py-5  border-gray-700">
                <h1 class="text-xl font-bold items-center text-white">Vasundhara Housing</h1>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="index.php" data-page="dashboard.php"
                    class="sidebar-item cursor-pointer flex items-center p-3 rounded-lg transition-colors duration-200 hover:bg-gray-700">
                    <i class="fas fa-tachometer-alt w-6 text-center"></i><span class="ml-3">Dashboard</span>
                </a>
                <a href="tenants.php" data-page="tenants.php"
                    class="sidebar-item cursor-pointer flex items-center p-3 rounded-lg transition-colors duration-200 hover:bg-gray-700">
                    <i class="fas fa-users w-6 text-center"></i><span class="ml-3">Tenants</span>
                </a>
                <a href="properties.php" data-page="properties.php"
                    class="sidebar-item cursor-pointer flex items-center p-3 rounded-lg transition-colors duration-200 hover:bg-gray-700">
                    <i class="fas fa-home w-6 text-center"></i><span class="ml-3">Properties</span>
                </a>
                <a href="payments.php" data-page="payments.php"
                    class="sidebar-item cursor-pointer flex items-center p-3 rounded-lg transition-colors duration-200 hover:bg-gray-700">
                    <i class="fas fa-file-invoice-dollar w-6 text-center"></i><span class="ml-3">Payments & Rent</span>
                </a>
                <a href="maintenance.php" data-page="maintenance.php"
                    class="sidebar-item cursor-pointer flex items-center p-3 rounded-lg transition-colors duration-200 hover:bg-gray-700">
                    <i class="fas fa-tools w-6 text-center"></i><span class="ml-3">Maintenance</span>
                </a>
                <a href="reports.php" data-page="reports.php"
                    class="sidebar-item cursor-pointer flex items-center p-3 rounded-lg transition-colors duration-200 hover:bg-gray-700">
                    <i class="fas fa-chart-pie w-6 text-center"></i><span class="ml-3">Reports</span>
                </a>
                <a href="notices.php" data-page="notices.php"
                    class="sidebar-item cursor-pointer flex items-center p-3 rounded-lg transition-colors duration-200 hover:bg-gray-700">
                    <i class="fas fa-bullhorn w-6 text-center"></i><span class="ml-3">Send Notice</span>
                </a>
                <a href="messages.php" data-page="messages.php"
                    class="sidebar-item cursor-pointer flex items-center p-3 rounded-lg transition-colors duration-200 hover:bg-gray-700">
                    <i class="fas fa-comments w-6 text-center"></i><span class="ml-3">Messages</span>
                </a>
                <a href="approveTenant.php" data-page="approveTenant.php"
                    class="sidebar-item cursor-pointer flex items-center p-3 rounded-lg transition-colors duration-200 hover:bg-gray-700">
                    <i class="fas fa-user-plus w-6 text-center"></i><span class="ml-3">Pending Tenants Requests</span>
                </a>
                <a href="#" data-page="termination_requests.php"
                    class="sidebar-item cursor-pointer flex items-center p-3 rounded-lg transition-colors duration-200 hover:bg-gray-700">
                    <i class="fas fa-file-contract w-6 text-center"></i><span class="ml-3">Termination Requests</span>
                </a>
            </nav>
           
</aside>
<div class="flex-1 flex flex-col overflow-hidden">
    <header class="flex justify-between items-center p-6 bg-white border-b">
        <h2 id="page-title" class="text-2xl font-semibold text-gray-800">Dashboard</h2>
        <div class="flex items-center space-x-4">
            <p class="text-sm text-gray-600">Welcome, Parth!</p>
            <img class="h-10 w-10 rounded-full object-cover" src="uploads/logo.png" alt="Admin avatar">
            <button id="header-logout-btn" class="text-gray-500 hover:text-red-600 transition-colors" title="Logout">
                <i class="fas fa-sign-out-alt text-xl"></i>
            </button>
        </div>
    </header>
    <main id="main-content" class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6"></main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = window.location.pathname.split('/').pop();
        const sidebarItems = document.querySelectorAll('.sidebar-item');

        sidebarItems.forEach(item => {
            const page = item.getAttribute('data-page');
            if (page == currentPage) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    });
</script>