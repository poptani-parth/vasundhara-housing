<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>
<div class="content-section">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Maintenance Requests</h1>
    </div>

    <!-- Search and Filter Controls -->
    <div class="bg-white p-4 rounded-xl shadow-sm border mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
            <!-- Search Input -->
            <div>
                <input type="text" id="maintenance-search-input" placeholder="Search by Tenant, Property, or Description..." class="w-full p-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" />
            </div>
            <!-- Date Filter -->
            <div>
                <input type="date" id="maintenance-date-filter"
                    class="w-full sm:w-auto p-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" />
            </div>
        </div>
        <!-- Status Filters -->
        <div class="mt-4 flex flex-wrap gap-2">
            <button data-status="All" class="filter-maintenance-btn px-4 py-2 text-sm rounded-lg bg-blue-600 text-white shadow-md">All</button>
            <button data-status="Pending" class="filter-maintenance-btn px-4 py-2 text-sm rounded-lg bg-gray-200 text-gray-700">Pending</button>
            <button data-status="In Progress" class="filter-maintenance-btn px-4 py-2 text-sm rounded-lg bg-gray-200 text-gray-700">In Progress</button>
            <button data-status="Completed" class="filter-maintenance-btn px-4 py-2 text-sm rounded-lg bg-gray-200 text-gray-700">Completed</button>
            <button data-status="Cancelled" class="filter-maintenance-btn px-4 py-2 text-sm rounded-lg bg-gray-200 text-gray-700">Cancelled</button>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="maintenance-list" class="divide-y divide-gray-200">
                <!-- Maintenance requests will be loaded here via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Maintenance Details Modal -->
<div id="maintenance-modal" class="fixed inset-0 bg-gray-800 bg-opacity-60 hidden items-center justify-center z-50 animate-fadeIn">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-lg mx-4 animate-slideInUp">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-xl font-semibold text-gray-800">Maintenance Request Details</h3>
            <button id="close-maintenance-modal-btn" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>
        <div id="maintenance-modal-body" class="p-6">
            <!-- Details will be loaded here via AJAX -->
        </div>
    </div>
</div>
<script>
    // Self-invoking function to scope variables and run immediately
    (function() {
        const searchInput = document.getElementById('maintenance-search-input');
        const dateFilter = document.getElementById('maintenance-date-filter');
        const filterButtons = document.querySelectorAll('.filter-maintenance-btn');
        const maintenanceList = document.getElementById('maintenance-list');
        const modal = document.getElementById('maintenance-modal');
        const modalBody = document.getElementById('maintenance-modal-body');
        const closeModalBtn = document.getElementById('close-maintenance-modal-btn');

        let debounceTimer;

        function loadMaintenance() {
            const statusBtn = document.querySelector('.filter-maintenance-btn.bg-blue-600');
            const status = statusBtn ? statusBtn.dataset.status : 'All';
            const search = searchInput.value;
            const date = dateFilter.value;

            maintenanceList.innerHTML = `<tr><td colspan="6" class="text-center p-6 text-gray-500">Loading maintenance requests...</td></tr>`;

            const formData = new FormData();
            formData.append('status', status);
            formData.append('search', search);
            formData.append('date', date);

            fetch('displayMaintenance.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.ok ? res.text() : Promise.reject('Failed to load data'))
                .then(data => {
                    if (data.trim() === '') {
                        maintenanceList.innerHTML = `<tr><td colspan="6" class="text-center p-6 text-gray-500">No maintenance requests found.</td></tr>`;
                    } else {
                        maintenanceList.innerHTML = data;
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    maintenanceList.innerHTML = `<tr><td colspan="6" class="text-center p-6 text-red-500">Error loading maintenance requests.</td></tr>`;
                });
        }

        function showMaintenanceModal(maintenanceId) {
            modalBody.innerHTML = `<p class="text-center text-gray-500">Loading details for request #${maintenanceId}...</p>`;
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            fetch(`getMaintenanceRequest.php?id=${maintenanceId}`)
                .then(res => res.ok ? res.text() : Promise.reject('Failed to load details'))
                .then(data => {
                    modalBody.innerHTML = data;
                })
                .catch(error => {
                    console.error('Modal fetch error:', error);
                    modalBody.innerHTML = `<p class="text-center text-red-500">Could not load request details. Please try again.</p>`;
                });
        }

        function hideMaintenanceModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function handleStatusUpdate(e) {
            const updateBtn = e.target.closest('.update-status-btn');
            if (!updateBtn) return;

            const maintenanceId = updateBtn.dataset.id;
            const newStatus = updateBtn.dataset.status;

            const formData = new FormData();
            formData.append('id', maintenanceId);
            formData.append('status', newStatus);

            // Add a visual cue that it's processing
            updateBtn.textContent = 'Updating...';
            updateBtn.disabled = true;

            fetch('updateMaintenanceStatus.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        hideMaintenanceModal();
                        loadMaintenance(); // Reload the main table
                    } else {
                        showToast(data.message || 'Failed to update status.', 'error');
                        updateBtn.textContent = newStatus; // Reset button text on failure
                        updateBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Update status error:', error);
                    showToast('An error occurred while updating status.', 'error');
                    updateBtn.textContent = newStatus;
                    updateBtn.disabled = false;
                });
        }

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => {
                    btn.classList.remove('bg-blue-600', 'text-white', 'shadow-md');
                    btn.classList.add('bg-gray-200', 'text-gray-700');
                });
                button.classList.add('bg-blue-600', 'text-white', 'shadow-md');
                button.classList.remove('bg-gray-200', 'text-gray-700');
                loadMaintenance();
            });
        });

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(loadMaintenance, 350);
        });

        dateFilter.addEventListener('change', loadMaintenance);
        closeModalBtn.addEventListener('click', hideMaintenanceModal);
        modalBody.addEventListener('click', handleStatusUpdate);

        // Event delegation to handle clicks on dynamically loaded "View" buttons
        maintenanceList.addEventListener('click', (e) => {
            const viewBtn = e.target.closest('.view-maintenance-btn');
            if (viewBtn) {
                const maintenanceId = viewBtn.dataset.id;
                showMaintenanceModal(maintenanceId);
            }
        });
        // Initial data load
        loadMaintenance();
    })();
</script>