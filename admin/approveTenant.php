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
        <h1 class="text-3xl font-bold text-gray-800">Pending Tenant Requests</h1>
    </div>

    <div id="processTenantRequestModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl w-1/3">
            <h3 id="processTenantModalTitle" class="text-2xl font-['Palatino_Linotype'] tracking-[.01em] font-bold text-gray-900">Confirm Action</h3>
            <hr class="my-2 w-full">
            <p id="processTenantModalMessage" class="mt-3 text-[.99rem] font-['Sitka_Text'] text-gray-600">Are you sure?</p>
            <div class="mt-5 flex justify-end space-x-2">
                <button id="cancelProcessTenant" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                    Cancel
                </button>
                <button id="confirmProcessTenant" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>
    <!-- Pending Tenants Table Container -->
    <div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Property</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="pending-tenant-list" class="bg-white divide-y divide-gray-200">
                <!-- Rows will be loaded here by AJAX from pendingTenant.php -->
            </tbody>
        </table>
    </div>
</div>

<script>
    // This script will run when the content is loaded via AJAX
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    function loadPendingTenants() {
        const listContainer = document.getElementById('pending-tenant-list');
        if (!listContainer) return;

        listContainer.innerHTML = `<tr><td colspan="4" class="text-center py-12 px-6"><div class="mx-auto w-fit"><i data-lucide="loader-2" class="w-10 h-10 mx-auto text-blue-500 animate-spin"></i><h3 class="mt-2 text-lg font-semibold text-gray-700">Loading Requests...</h3></div></td></tr>`;
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        fetch('pendingTenant.php')
            .then(response => response.text())
            .then(data => {
                listContainer.innerHTML = data;
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            })
            .catch(error => {
                console.error('Error loading pending tenants:', error);
                listContainer.innerHTML = '<tr><td colspan="4" class="text-center py-12 px-6 text-red-500">Failed to load requests. Please try again.</td></tr>';
            });
    }

    function processTenantRequest(tenantId, action) {
        const formData = new FormData();
        formData.append('tenant_id', tenantId);
        formData.append('action', action);

        // Return the fetch promise to allow chaining .finally()
        return fetch('processRequest.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    loadPendingTenants(); // Refresh the list
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error processing request:', error);
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    // --- Modal Handling & Event Listeners ---
    // Use a self-invoking function to avoid polluting the global scope and to ensure
    // the script runs after the DOM elements are available.
    (function() {
        const modal = document.getElementById('processTenantRequestModal');
        const modalTitle = document.getElementById('processTenantModalTitle');
        const modalMessage = document.getElementById('processTenantModalMessage');
        const cancelBtn = document.getElementById('cancelProcessTenant');
        const confirmBtn = document.getElementById('confirmProcessTenant');
        const listContainer = document.getElementById('pending-tenant-list');

        if (!modal || !listContainer) return;

        function openModal(tenantId, action, tenantName) {
            modal.dataset.tenantId = tenantId;
            modal.dataset.action = action;

            modalTitle.textContent = action === 'accept' ? 'Confirm Acceptance' : 'Confirm Rejection';
            modalMessage.textContent = `Are you sure you want to ${action} the request for ${tenantName}?`;

            if (action === 'accept') {
                confirmBtn.className = 'px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700';
                confirmBtn.textContent = 'Accept';
            } else {
                confirmBtn.className = 'px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700';
                confirmBtn.textContent = 'Reject';
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Event delegation for action buttons on the tenant list
        listContainer.addEventListener('click', function(e) {
            const target = e.target.closest('.action-btn');
            if (target) {
                openModal(target.dataset.id, target.dataset.action, target.dataset.name);
            }
        });

        // Listeners for modal buttons
        confirmBtn.addEventListener('click', function() {
            const { tenantId, action } = modal.dataset;
            if (tenantId && action) {
                processTenantRequest(tenantId, action).finally(closeModal);
            }
        });

        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => e.target === modal && closeModal());
    })();

    // Initial load
    loadPendingTenants();
</script>