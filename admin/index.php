<?php
// index.php
include './database.php';

session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Vasundhara Housing</title>

    <link rel="stylesheet" href="../assets/fonts/Poppins.css">
    <link rel="stylesheet" href="./style.css">
    <script src="../assets/js/tailwind.js"></script>
    <link href="../assets/css/tailwind.css" rel="stylesheet"/>
    <link href="../assets/css/all.min.css" rel="stylesheet"/>
    <script src="../assets/all.min.3.4.js"></script>
    <style>
        /* Custom scrollbar styles (from index.php) */
        ::-webkit-scrollbar {
            width: 0px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 10px;
        }

        /* Basic body font and background (from index.php) */
        body {
            padding: 0%;
            margin: 0%;
            font-family: 'Poppins', sans-serif;
            font-size: 0.875rem;
            background-color: #f1f5f9;
        }

        /* Animations (ensure these are consistent with your global style.css) */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .animate-slideInUp {
            animation: slideInUp 0.6s ease-out forwards;
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }
        @keyframes progress {
            from { width: 100%; }
            to { width: 0%; }
        }

        .animate-fadeOut {
            animation: fadeOut 0.5s ease-out forwards;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-500 {
            animation-delay: 0.5s;
        }

        .delay-600 {
            animation-delay: 0.6s;
        }

        /* Updated active state for the sidebar design */
        .sidebar-item.active,
        .sidebar-item.active:hover {
            background-color: #2563eb;
            color: white;
        }

        .sidebar-item.active i,
        .sidebar-item.active:hover i {
            color: white;
        }

        .expiring-filter-btn {
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            background-color: #e5e7eb; /* bg-gray-200 */
            color: #374151; /* text-gray-700 */
            transition: all 0.2s ease-in-out;
        }
        .expiring-filter-btn:hover {
            background-color: #d1d5db; /* bg-gray-300 */
        }
        .expiring-filter-btn.active {
            background-color: #2563eb; /* bg-blue-600 */
            color: white;
        }

        .content-section {
            animation: fadeIn 0.5s ease-in-out;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="flex w-full h-screen">

        <?php include 'sidebar.php'; ?>
        <div id="logoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-xl w-100">
                <h3 class="text-2xl font-['Palatino_Linotype'] tracking-[.01em] font-bold text-gray-900">Confirm Logout</h3>
                <hr class="my-2 w-full">
                <p class="mt-3 text-[.99rem] font-['Sitka_Text'] text-gray-600">Are you sure you want to log out of your account?</p>
                <div class="mt-5 flex justify-end space-x-2">
                    <button id="cancelLogout" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="confirmLogout" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        Log Out
                    </button>
                </div>
            </div>
        </div>

        <div id="deleteMessageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl w-100">
                <h3 class="text-2xl font-['Palatino_Linotype'] tracking-[.01em] font-bold text-gray-900">Confirm Deletion</h3>
                <hr class="my-2 w-full">
                <p class="mt-3 text-[.99rem] font-['Sitka_Text'] text-gray-600">Are you sure you want to delete this message? This action cannot be undone.</p>
                <div class="mt-5 flex justify-end space-x-2">
                    <button id="cancelDeleteMessage" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="confirmDeleteMessage" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <div id="deleteTenantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-xl w-100">
                <h3 class="text-2xl font-['Palatino_Linotype'] tracking-[.01em] font-bold text-gray-900">Confirm Deletion</h3>
                <hr class="my-2 w-full">
                <p class="mt-3 text-[.99rem] font-['Sitka_Text'] text-gray-600">Are you sure you want to delete this tenant? This action cannot be undone.</p>
                <div class="mt-5 flex justify-end space-x-2">
                    <button id="cancelDeleteTenant" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="confirmDeleteTenant" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>
        <div id="deletePropertyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-xl w-100">
                <h3 class="text-2xl font-['Palatino_Linotype'] tracking-[.01em] font-bold text-gray-900">Confirm Deletion</h3>
                <hr class="my-2 w-full">
                <p class="mt-3 text-[.99rem] font-['Sitka_Text'] text-gray-600">Are you sure you want to delete this Property?</p>
                <div class="mt-5 flex justify-end space-x-2">
                    <button id="cancelDeleteProperty" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="confirmDeleteProperty" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <div id="sendExpiryNoticeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl w-100">
                <h3 class="text-2xl font-['Palatino_Linotype'] tracking-[.01em] font-bold text-gray-900">Confirm Notice</h3>
                <hr class="my-2 w-full">
                <p id="sendExpiryNoticeMessage" class="mt-3 text-[.99rem] font-['Sitka_Text'] text-gray-600">Are you sure you want to send an expiry notice?</p>
                <div class="mt-5 flex justify-end space-x-2">
                    <button id="cancelSendExpiryNotice" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="confirmSendExpiryNotice" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Send Notice
                    </button>
                </div>
            </div>
        </div>

        <div id="sendDueNoticeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl w-100">
                <h3 class="text-2xl font-['Palatino_Linotype'] tracking-[.01em] font-bold text-gray-900">Confirm Due Notice</h3>
                <hr class="my-2 w-full">
                <p id="sendDueNoticeMessage" class="mt-3 text-[.99rem] font-['Sitka_Text'] text-gray-600">Are you sure?</p>
                <div class="mt-5 flex justify-end space-x-2">
                    <button id="cancelSendDueNotice" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="confirmSendDueNotice" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Send Notice
                    </button>
                </div>
            </div>
        </div>

        <!-- Notice Detail Modal -->
        <div id="noticeDetailModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden items-center justify-center z-50 p-4">
            <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-2xl animate-slideInUp">
                <div class="flex justify-between items-start mb-4 pb-4 border-b">
                    <div>
                        <h3 id="noticeModalSubject" class="text-2xl font-bold text-gray-800"></h3>
                        <div id="noticeModalMeta" class="flex items-center gap-2 text-sm text-gray-500 mt-2">
                            <!-- Meta info will be injected here -->
                        </div>
                    </div>
                    <button id="closeNoticeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div id="noticeModalMessage" class="prose prose-sm max-w-none text-gray-700 leading-relaxed max-h-[60vh] overflow-y-auto pr-2">
                    <!-- Message will be injected here -->
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button id="deleteNoticeFromModalBtn" data-id="" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"><i data-lucide="trash-2" class="w-4 h-4 inline-block -mt-1 mr-1"></i>Delete Notice</button>
                    <button id="cancelNoticeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Close</button>
                </div>
            </div>
        </div>

    <!-- Process Termination Modal -->
    <div id="processTerminationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg animate-slideInUp">
            <h3 id="processTerminationModalTitle" class="text-2xl font-bold text-gray-900">Process Request</h3>
            <hr class="my-3 w-full">
            <form id="process-termination-form">
                <input type="hidden" name="request_id" id="termination-request-id">
                <input type="hidden" name="action" id="termination-action">
                <p id="processTerminationModalMessage" class="text-gray-600">Are you sure?</p>
                <div class="mt-4">
                    <label for="admin-remark" class="block text-sm font-medium text-gray-700">Admin Remark (Optional)</label>
                    <textarea id="admin-remark" name="admin_remark" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Provide a reason for rejection or notes for approval..."></textarea>
                </div>
                <div class="mt-5 flex justify-end space-x-2">
                    <button type="button" id="cancelProcessTermination" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Cancel</button>
                    <button type="submit" id="confirmProcessTermination" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Confirm</button>
                </div>
            </form>
        </div>
    </div>
    </div>


    <div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col items-end gap-2">
    </div>

    <script>
        // --- Utility & Modal Functions ---
        function showLogoutModal() {
            document.getElementById('logoutModal')?.classList.add('flex');
            document.getElementById('logoutModal')?.classList.remove('hidden');
        }

        function hideLogoutModal() {
            document.getElementById('logoutModal')?.classList.add('hidden');
            document.getElementById('logoutModal')?.classList.remove('flex');
        }

       function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            if (!toastContainer) return;

            let iconHTML, borderColor, progressColor;

            switch (type) {
                case 'error':
                    borderColor = 'border-red-500';
                    progressColor = 'bg-red-500';
                    iconHTML = '<i data-lucide="alert-circle" class="w-6 h-6 text-red-500"></i>';
                    break;
                case 'info':
                    borderColor = 'border-blue-500';
                    progressColor = 'bg-blue-500';
                    iconHTML = '<i data-lucide="info" class="w-6 h-6 text-blue-500"></i>';
                    break;
                case 'success':
                default:
                    borderColor = 'border-green-500';
                    progressColor = 'bg-green-500';
                    iconHTML = '<i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>';
                    break;
            }

            const toast = document.createElement('div');
            toast.className = `relative flex items-center gap-4 p-4 pr-8 bg-white rounded-lg shadow-lg border-l-4 ${borderColor} w-full max-w-sm overflow-hidden animate-fadeIn`;
            
            toast.innerHTML = `
                <div class="flex-shrink-0">${iconHTML}</div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">${type.charAt(0).toUpperCase() + type.slice(1)}</p>
                    <p class="text-sm text-gray-600">${message}</p>
                </div>
                <button class="absolute top-2 right-2 text-gray-400 hover:text-gray-900">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
                <div class="absolute bottom-0 left-0 h-1 ${progressColor}" style="animation: progress 5s linear forwards;"></div>
            `;

            toastContainer.prepend(toast);
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const closeButton = toast.querySelector('button');
            const removeToast = () => {
                toast.style.animation = 'fadeOut 0.5s ease-out forwards';
                toast.addEventListener('animationend', () => toast.remove());
            };

            closeButton.addEventListener('click', removeToast);
            setTimeout(removeToast, 5000);
        }

        // --- Data Fetching Functions ---
        function logout() {
            fetch('logout.php', {
                    method: 'POST'
                })
                .then(res => res.ok ? res.json() : Promise.reject(res))
                .then( data => {
                    if (data.status === 'success') window.location.href = '../login.php';
                    else showToast('Logout failed: ' + data.message, 'error');
                })
                .catch(() => showToast('An error occurred during logout.', 'error'))
                .finally(hideLogoutModal);
        }

        function loadPage(page, data = null) {
            const mainContent = document.getElementById("main-content");
            if (!mainContent) return;
            mainContent.innerHTML = `<p class="p-4 text-center text-gray-500">Loading...</p>`;

            fetch(page)
                .then(res => res.ok ? res.text() : Promise.reject(res))
                .then(html => {
                    mainContent.innerHTML = html;

                    // This ensures that scripts inside the loaded content are executed reliably.
                    // Some pages (like maintenance, notices) have their own self-initializing scripts.
                    const scripts = Array.from(mainContent.querySelectorAll("script"));
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement("script");
                        Array.from(oldScript.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });
                        newScript.text = oldScript.innerHTML; // Use innerHTML for script content
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });

                    if (page !== 'logout') localStorage.setItem('lastAdminPage', page);

                    // After content is loaded, trigger initial data load for pages whose
                    // initialization logic resides in this main index.php file.
                    if (page === 'dashboard.php') loadExpiringAgreements();
                    if (page === 'notices.php' && data && data.action === 'prefillNotice') {
                        prefillNoticeForm(data.tenantId, data.tenantName);
                    }


                    if (page === 'properties.php') loadProperties();
                    if (page === 'tenants.php') renderTenantList();
                    if (page === 'payments.php') loadPayments();
                    if (page === 'messages.php') loadMessages();

                    // Handle any toast messages passed in the URL from redirects
                    const urlParams = new URLSearchParams(window.location.search);
                    const message = urlParams.get('message');
                    if (message) {
                        showToast(message, urlParams.get('type') || 'success');
                        // Clean the URL to prevent the toast from showing on refresh
                        history.replaceState(null, null, window.location.pathname);
                    }
                })
                .catch((err) => {
                    console.error('Failed to load page:', page, err);
                    mainContent.innerHTML = `<p class="p-4 text-center text-red-500">Failed to load content. Please try again.</p>`;
                });
        }

        function loadProperties(status = 'All', search = '', type = 'All') {
            const propertyList = document.getElementById('property-list');
            if (!propertyList) return;
            propertyList.innerHTML = `<tr><td colspan="5" class="text-center p-6 text-gray-500">Loading properties...</td></tr>`;
            const formData = new FormData();
            formData.append('status', status);
            formData.append('search', search);
            formData.append('type', type);

            fetch('displayProperties.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    propertyList.innerHTML = data;
                })
                .catch(() => propertyList.innerHTML = `<p class="p-3 text-center text-red-500">Failed to load properties.</p>`);
        }

        function renderTenantList(status = 'All', search = '') {
            const tenantList = document.getElementById('tenant-list');
            if (!tenantList) return;
            tenantList.innerHTML = `<tr><td colspan="5" class="text-center p-6 text-gray-500">Loading tenants...</td></tr>`;
            const formData = new FormData();
            formData.append('status', status);
            formData.append('search', search);

            fetch('displayTenants.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => tenantList.innerHTML = data)
                .catch(() => tenantList.innerHTML = `<p class="p-3 text-center text-red-500">Failed to load tenants.</p>`);
        }

        function loadPayments(status = 'All', search = '') {
            const paymentList = document.getElementById('payment-list');
            if (!paymentList) return;
            paymentList.innerHTML = `<tr><td colspan="6" class="text-center p-6 text-gray-500">Loading payments...</td></tr>`;
            const formData = new FormData();
            formData.append('status', status);
            formData.append('search', search);

            fetch('displayPayments.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => paymentList.innerHTML = data)
                .catch(() => paymentList.innerHTML = `<p class="p-3 text-center text-red-500">Failed to load payments.</p>`);
        }

        function updatePropertyStatus(propertyId, newStatus) {
            fetch(`./updateStatus.php?id=${propertyId}&status=${newStatus}`)
                .then(res => {
                    if (res.ok) {
                        showToast('Property status updated successfully!', 'success');
                        loadProperties(); // Reload properties to reflect the change
                    } else {
                        showToast('Failed to update property status.', 'error');
                    }
                })
                .catch(() => showToast('Failed to update property status.', 'error'));
        }

        function loadExpiringAgreements(days = 5) {
            const listContainer = document.getElementById('expiring-agreements-list');
            if (!listContainer) return;

            listContainer.innerHTML = `<p class="text-gray-500 text-center py-4">Loading expiring agreements...</p>`;

            fetch(`getExpiringAgreements.php?days=${days}`)
                .then(res => res.text())
                .then(html => {
                    listContainer.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading expiring agreements:', error);
                    listContainer.innerHTML = `<p class="text-red-500 text-center py-4">Failed to load data.</p>`;
                });
        }

        function showSendExpiryNoticeModal(tenantId, tenantName) {
            const modal = document.getElementById('sendExpiryNoticeModal');
            if (modal) {
                modal.dataset.tenantId = tenantId;
                modal.dataset.tenantName = tenantName;

                const messageEl = document.getElementById('sendExpiryNoticeMessage');
                if (messageEl) {
                    messageEl.textContent = `Are you sure you want to send an expiry notice to ${tenantName}?`;
                }
                modal.classList.add('flex');
                modal.classList.remove('hidden');
            }
        }

        function hideSendExpiryNoticeModal() {
            const modal = document.getElementById('sendExpiryNoticeModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function executeSendExpiryNotice() {
            const modal = document.getElementById('sendExpiryNoticeModal');
            if (!modal) return;

            const tenantId = modal.dataset.tenantId;
            const tenantName = modal.dataset.tenantName;
            const buttonElement = document.querySelector(`.send-notice-btn[data-tenant-id="${tenantId}"]`);

            if (!tenantId || !tenantName || !buttonElement) {
                showToast('An error occurred. Could not find notice details or button.', 'error');
                hideSendExpiryNoticeModal();
                return;
            }

            const originalButtonText = buttonElement.innerHTML;
            buttonElement.disabled = true;
            buttonElement.innerHTML = 'Sending...';

            hideSendExpiryNoticeModal();
            showToast(`Sending expiry notice to ${tenantName}...`, 'info');

            const formData = new FormData();
            formData.append('tenant_id', tenantId);
            formData.append('tenant_name', tenantName);

            fetch('send_expiry_notice.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => showToast(data.message, data.success ? 'success' : 'error'))
                .catch(() => showToast('A network error occurred while sending the notice.', 'error'))
                .finally(() => {
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = originalButtonText;
                });
        }

        function sendExpiryNotice(tenantId, tenantName) {
            showSendExpiryNoticeModal(tenantId, tenantName);
        }

        function prefillNoticeForm(tenantId, tenantName) {
            // This function is called by loadPage after notices.php is loaded
            const recipientSelect = document.getElementById('recipient-select'); // Assuming this ID in notices.php
            const subjectInput = document.getElementById('notice-subject');
            const messageTextarea = document.getElementById('notice-message');

            if (recipientSelect && subjectInput && messageTextarea) {
                const optionToSelect = Array.from(recipientSelect.options).find(opt => opt.value == tenantId);
                if (optionToSelect) {
                    optionToSelect.selected = true;
                }
                
                subjectInput.value = `Lease Agreement Expiry Reminder`;
                messageTextarea.value = `Dear ${tenantName},\n\nThis is a friendly reminder that your lease agreement is due to expire soon. Please contact us to discuss renewal options.\n\nThank you,\nVasundhara Housing Management`;

                showToast(`Notice form pre-filled for ${tenantName}.`, 'info');
                subjectInput.focus();
            }
        }

        function deleteProperty() {
            const modal = document.getElementById('deletePropertyModal');
            const propertyId = modal ? modal.dataset.propertyId : null;
            if (!propertyId) return;

            fetch('./deleteproperties.php?id=' + propertyId, { method: 'GET' })
                .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
                .then(({ status, ok, data }) => {
                    if (ok && data.success) {
                        showToast(data.message, 'success');
                        loadProperties(); // Reload properties to reflect the change
                    } else {
                        // If it's a conflict (409), show an info toast, otherwise an error toast.
                        const toastType = status === 409 ? 'info' : 'error';
                        showToast(data.message || 'Failed to delete property.', toastType);
                    }
                })
                .catch(() => showToast('A network error occurred while deleting the property.', 'error'))
                .finally(hideDeletePropertyModal);
        }

        function showDeleteTenantModal(tenantId) {
            const modal = document.getElementById('deleteTenantModal');
            if (modal) {
                modal.dataset.tenantId = tenantId; // Store tenantId in a data attribute
                modal.classList.add('flex');
                modal.classList.remove('hidden');
            }
        }

        function hideDeleteTenantModal() {
            const modal = document.getElementById('deleteTenantModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function deleteTenant() {
            const modal = document.getElementById('deleteTenantModal');
            const tenantId = modal ? modal.dataset.tenantId : null;
            if (!tenantId) return;

            fetch('./deleteTenant.php?id=' + tenantId, { method: 'GET' })
                .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
                .then(({ status, ok, data }) => {
                    if (ok && data.success) {
                        showToast(data.message, 'success');
                        renderTenantList(); // Reload tenants to reflect the change
                    } else {
                        // If it's a conflict (409), show an info toast, otherwise an error toast.
                        const toastType = status === 409 ? 'info' : 'error';
                        showToast(data.message || 'Failed to delete tenant.', toastType);
                    }
                })
                .catch(() => showToast('A network error occurred while deleting the tenant.', 'error'))
                .finally(hideDeleteTenantModal);
        }

        function showDeletePropertyModal(propertyId) {
            const modal = document.getElementById('deletePropertyModal');
            if (modal) {
                modal.dataset.propertyId = propertyId; // Store propertyId in a data attribute
                modal.classList.add('flex');
                modal.classList.remove('hidden');
            }
        }

        function hideDeletePropertyModal() {
            const modal = document.getElementById('deletePropertyModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function showDeleteMessageModal(messageId) {
            const modal = document.getElementById('deleteMessageModal');
            if (modal) {
                modal.dataset.messageId = messageId;
                modal.classList.add('flex');
                modal.classList.remove('hidden');
            }
        }

        function hideDeleteMessageModal() {
            const modal = document.getElementById('deleteMessageModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function reportFetch() {
            const monthYearInput = document.getElementById('report-month-year');
            if (!monthYearInput) {
                showToast('Report month/year selector not found.', 'error');
                return;
            }
            const monthYear = monthYearInput.value; // e.g., "2024-07"
            const [year, month] = monthYear.split('-');

            const reportDisplayArea = document.getElementById('report-display-area');
            const reportPlaceholder = document.getElementById('report-placeholder');
            const reportTableBody = document.getElementById('report-table-body');

            if (!monthYear || !reportDisplayArea || !reportPlaceholder || !reportTableBody) {
                showToast('Report elements not found on the page.', 'error');
                return;
            }

            // Show loading state
            // showToast('Generating report...', 'info');
            reportPlaceholder.innerHTML = `<div class="text-center text-gray-500"><i data-lucide="loader-2" class="w-12 h-12 mx-auto text-blue-500 animate-spin"></i><p class="mt-2">Generating report...</p></div>`;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            reportPlaceholder.classList.remove('hidden');
            reportDisplayArea.classList.add('hidden');

            fetch(`./generateMonthlyReport.php?month=${month}&year=${year}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    // Update Title
                    document.getElementById('report-title').textContent = data.reportTitle;

                    // Update Summary Cards
                    const formatCurrency = (num) => `₹${Number(num).toLocaleString('en-IN')}`;
                    document.getElementById('summary-revenue').textContent = formatCurrency(data.summary.totalRevenue);
                    document.getElementById('summary-due').textContent = formatCurrency(data.summary.totalDue);
                    document.getElementById('summary-paid-count').textContent = data.summary.paidCount;
                    document.getElementById('summary-due-count').textContent = data.summary.dueCount;

                    // Populate Table
                    reportTableBody.innerHTML = ''; // Clear previous data
                    if (data.payments.length > 0) {
                        data.payments.forEach(p => {
                            const status_classes = p.status === 'Paid' ? 'bg-green-100 text-green-800' : (p.status === 'Due' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                            const paymentPeriodDisplay = p.payment_period ? new Date(p.payment_period).toLocaleDateString('en-GB', { month: 'long', year: 'numeric' }) : 'N/A';

                            const row = `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900">${p.tenant_name}</div></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${p.pro_nm}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold">${formatCurrency(p.month_rent)}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold">${p.status === 'Paid' ? formatCurrency(p.amount) : '₹0'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${paymentPeriodDisplay}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${status_classes}">${p.status}</span></td>
                                </tr>
                            `;
                            reportTableBody.innerHTML += row;
                        });
                    } else {
                        reportTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-gray-500">No payment records found for this period.</td></tr>`;
                    }

                    // Show report, hide placeholder
                    reportDisplayArea.classList.remove('hidden');
                    reportPlaceholder.classList.add('hidden');
                    showToast('Report generated successfully!', 'success');

                })
                .catch(error => {
                    console.error('Error fetching report data:', error);
                    showToast('Error fetching report data: ' + error.message, 'error');
                    reportPlaceholder.innerHTML = `<div class="text-center text-red-500"><i data-lucide="alert-triangle" class="w-12 h-12 mx-auto"></i><p class="mt-2">Failed to generate report.</p></div>`;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
        }

        function printReport() {
            const reportContent = document.getElementById('report-content-container');
            const reportTitleElement = document.getElementById('report-title');

            if (!reportContent || !reportTitleElement) {
                showToast('Could not find report content to print.', 'error');
                return;
            }

            const reportTitle = reportTitleElement.textContent.trim();

            // 1. Extract data from the current view to build a clean print version
            const summary = {
                revenue: document.getElementById('summary-revenue').textContent,
                due: document.getElementById('summary-due').textContent,
                paidCount: document.getElementById('summary-paid-count').textContent,
                dueCount: document.getElementById('summary-due-count').textContent
            };

            const tableRows = Array.from(document.querySelectorAll('#report-table-body tr')).map(tr => {
                const cells = Array.from(tr.querySelectorAll('td'));
                if (cells.length < 6) return null; // Skip placeholder/empty rows
                return {
                    tenant: cells[0].innerText,
                    property: cells[1].innerText,
                    expected: cells[2].innerText,
                    paid: cells[3].innerText,
                    period: cells[4].innerText,
                    status: cells[5].innerText
                };
            }).filter(row => row !== null);
            showToast('Preparing report for printing...', 'info');
            // 2. Build a dedicated HTML string for printing
            const headerHTML = `
                <div style="text-align: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 20px;">
                    <h1 style="font-size: 22pt; font-weight: bold; color: #000; font-family: 'Georgia', serif;">Vasundhara Housing</h1>
                    <p style="font-size: 10pt; color: #555; margin: 2px 0;">Vasundhara Housing, SG Highway, Ahmedabad, Gujarat, India</p>
                    <p style="font-size: 10pt; color: #555; margin: 2px 0;">info@vasundharahousing.com | +91 7201918502</p>
                </div>
                <h2 style="font-size: 16pt; font-weight: bold; text-align: center; margin-bottom: 20px;">${reportTitle}</h2>
            `;

            const summaryHTML = `
                <table style="width: 100%; border-collapse: separate; border-spacing: 10px 0; margin-bottom: 1.5rem; page-break-inside: avoid;">
                    <tbody>
                        <tr>
                            <td style="border-left: 5px solid #22c55e; padding: 12px ;background-color: #f0fdf4; -webkit-print-color-adjust: exact; width: 25%; border-radius: 8px;">
                                <p style="font-size: 10pt; margin: 0 0 5px 0; color: #22c55e; font-weight: 400;">Total Revenue</p>
                                <p style="font-size: 16pt; margin: 0; font-weight: bold; color: #14532d;">${summary.revenue}</p>
                            </td>
                            <td style="border-left: 5px solid #eab308; padding: 12px; background-color: #fefce8; -webkit-print-color-adjust: exact; width: 25%; border-radius: 8px;">
                                <p style="font-size: 10pt; margin: 0 0 5px 0; color: #eab308; font-weight: 400;">Total Due</p>
                                <p style="font-size: 16pt; margin: 0; font-weight: bold; color: #703e12;">${summary.due}</p>
                            </td>
                            <td style="border-left: 5px solid #3b82f6; padding: 12px; background-color: #eff6ff; -webkit-print-color-adjust: exact; width: 25%; border-radius: 8px;">
                                <p style="font-size: 10pt; margin: 0 0 5px 0; color: #3b82f6; font-weight: 400;">Paid Transactions</p>
                                <p style="font-size: 16pt; margin: 0; font-weight: bold; color: ##1e3a8a;">${summary.paidCount}</p>
                            </td>
                            <td style="border-left: 5px solid #ef4444; padding: 12px; background-color: #fef2f2; -webkit-print-color-adjust: exact; width: 25%; border-radius: 8px;">
                                <p style="font-size: 10pt; margin: 0 0 5px 0; color: #ef4444; font-weight: 400;">Due Transactions</p>
                                <p style="font-size: 16pt; margin: 0; font-weight: bold; color: ##7f1d1d;">${summary.dueCount}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            `;

            const tableHTML = `
                <h3 style="font-size: 14pt; font-weight: 600; color: #000; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 20px; margin-bottom: 15px;">Detailed Transactions</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact;">
                            <th style="padding: 8px; text-align: left; border-bottom: 2px solid #333;">Tenant</th>
                            <th style="padding: 8px; text-align: left; border-bottom: 2px solid #333;">Property</th>
                            <th style="padding: 8px; text-align: left; border-bottom: 2px solid #333;">Expected Rent</th>
                            <th style="padding: 8px; text-align: left; border-bottom: 2px solid #333;">Paid Amount</th>
                            <th style="padding: 8px; text-align: left; border-bottom: 2px solid #333;">Payment Date</th>
                            <th style="padding: 8px; text-align: center; border-bottom: 2px solid #333;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableRows.map(row => {
                            let statusStyle = 'color: #333;'; // Default color
                            if (row.status === 'Paid') statusStyle = 'color: #155724; font-weight: bold;';
                            else if (row.status === 'Due') statusStyle = 'color: #856404;';
                            else if (row.status === 'Overdue') statusStyle = 'color: #721c24; font-weight: bold;';
                            
                            return `
                                <tr style="page-break-inside: avoid;">
                                    <td style="padding: 8px 4px; border-bottom: 1px solid #ccc;">${row.tenant}</td>
                                    <td style="padding: 8px 4px; border-bottom: 1px solid #ccc;">${row.property}</td>
                                    <td style="padding: 8px 4px; border-bottom: 1px solid #ccc;">${row.expected}</td>
                                    <td style="padding: 8px 4px; border-bottom: 1px solid #ccc;">${row.paid}</td>
                                    <td style="padding: 8px 4px; border-bottom: 1px solid #ccc;">${row.period}</td>
                                    <td style="padding: 8px 4px; border-bottom: 1px solid #ccc; text-align: center; ${statusStyle}">${row.status}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            `;

            const fullHTML = `
                <html>
                <head>
                    <title>${reportTitle}</title>
                    <style> @page { size: A4; margin: 0.6in; } body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 10pt; color: #333; -webkit-print-color-adjust: exact; } table { width: 100%; border-collapse: collapse; } th, td { font-size: 9pt; } </style>
                    
                </head>
                <body>
                    ${headerHTML}
                    ${summaryHTML}
                    ${tableHTML}
                </body>
                </html>
            `;

            // 3. Write to iframe and print
            const iframe = document.createElement('iframe');
            iframe.style.position = 'absolute';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            document.body.appendChild(iframe);

            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(fullHTML);
            doc.close();

            // A timeout is needed for some browsers to ensure content is fully loaded before printing
            setTimeout(() => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                // Remove the iframe after printing is initiated
                document.body.removeChild(iframe);
            }, 500);
        }

        function showSendDueNoticeModal(tenantId, tenantName, period, amount) {
            const modal = document.getElementById('sendDueNoticeModal');
            if (modal) {
                modal.dataset.tenantId = tenantId;
                modal.dataset.tenantName = tenantName;
                modal.dataset.period = period;
                modal.dataset.amount = amount;

                const messageEl = document.getElementById('sendDueNoticeMessage');
                if (messageEl) {
                    messageEl.textContent = `Are you sure you want to send a payment due notice to ${tenantName} for the period of ${period}?`;
                }
                modal.classList.add('flex');
                modal.classList.remove('hidden');
            }
        }

        function hideSendDueNoticeModal() {
            const modal = document.getElementById('sendDueNoticeModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function executeSendDueNotice() {
            const modal = document.getElementById('sendDueNoticeModal');
            if (!modal) return;

            const { tenantId, tenantName, period, amount } = modal.dataset;

            if (!tenantId || !tenantName || !period || !amount) {
                showToast('An error occurred. Could not find notice details.', 'error');
                hideSendDueNoticeModal();
                return;
            }

            hideSendDueNoticeModal();
            showToast('Sending notice...', 'info');

            const formData = new FormData();
            formData.append('tenant_id', tenantId);
            formData.append('tenant_name', tenantName);
            formData.append('period', period);
            formData.append('amount', amount);

            fetch('send_payment_notice.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => showToast(data.message, data.success ? 'success' : 'error'))
                .catch(() => showToast('A network error occurred while sending the notice.', 'error'));
        }

        function sendDueNotice(tenantId, tenantName, period, amount) {
            showSendDueNoticeModal(tenantId, tenantName, period, amount);
        }

        function showNoticeModal(data) {
            const modal = document.getElementById('noticeDetailModal');
            if (!modal) return;

            document.getElementById('noticeModalSubject').textContent = data.subject;
            // Use innerHTML to render line breaks from \n
            document.getElementById('noticeModalMessage').innerHTML = data.message.replace(/\n/g, '<br>');
            
            const metaHTML = `
                <i data-lucide="${data.recipientIcon}" class="w-4 h-4 ${data.recipientColor}"></i>
                <span class="font-medium">To: ${data.recipient}</span>
                <span class="mx-1 text-gray-300">•</span>
                <span>${data.date}</span>
            `;
            document.getElementById('noticeModalMeta').innerHTML = metaHTML;
            
            // Store the notice ID for the delete button
            document.getElementById('deleteNoticeFromModalBtn').dataset.id = data.id;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function hideNoticeModal() {
            const modal = document.getElementById('noticeDetailModal');
            if (modal) modal.classList.add('hidden');
        }

        function printInvoice(paymentId) {
            showToast('Generating invoice...', 'info');

            fetch(`getInvoiceData.php?payment_id=${paymentId}`)
                .then(res => res.json())
                .then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to fetch invoice data.');
                    }
                    const invoiceData = response.data;
                    const formatCurrency = (num) => `₹${Number(num).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                    const headerAndDetailsHTML = `
                        <table style=" border-collapse: collapse; margin-bottom: 2rem;">
                            <tbody>
                                <tr>
                                    <td style="vertical-align: top; width: 50%;">
                                        <img src="uploads/invoice-logo.png" alt="Logo" style="width: 10rem; height: auto;">
                                        <p style="font-size: 1rem; font-weight: bold; color: #1f2937; margin: 0;">Vasundhara Housing</p>
                                        <p style="font-size: 0.875rem; color: #4b5563; margin: 2px 0;">Vasundhara Housing, SG Highway, Ahmedabad, Gujarat, India</p>
                                        <p style="font-size: 0.875rem; color: #4b5563; margin: 2px 0;">info@vasundharahousing.com</p>
                                    </td>
                                    <td style="vertical-align: top; text-align: right; width: 5%;">
                                        <h1 style="font-size: 2.5rem; font-weight: bold; color: #1f2937; margin: 0 0 10px 0; padding-right: 10px;">INVOICE</h1>
                                        ${invoiceData.status !== 'Paid' ? `
                                            <table style=" margin-bottom: 1rem;">
                                                <thead style="color: white;">
                                                    <tr><th style="background-color: #dbeaff !important; font-size: 0.875rem; padding:12px 0px; color: #000 !important; -webkit-print-color-adjust: exact;">Amount Due</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr><td style=" font-size: 1.2rem; font-weight: bold; padding: 12px 0; text-align: center; background-color: #f0fdf4;">${formatCurrency(invoiceData.amount)}</td></tr>
                                                </tbody>
                                            </table>
                                        ` : `
                                            <table style=" margin-bottom: 1rem;">
                                                <thead style="color: white;">
                                                    <tr><th style="background-color: #dbeaff !important; font-size: 0.875rem; padding:12px 0px; color: #000 !important; -webkit-print-color-adjust: exact;">Amount Due</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr><td style=" font-size: 1.2rem; font-weight: bold; padding: 12px 0; text-align: center; background-color: #f0fdf4;">₹0</td></tr>
                                                </tbody>
                                            </table>
                                        `}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table style="margin-left:55%; border: 2px solid #fff!important; width: 45%; margin-bottom: 1rem;">
                            <thead style="color: white;margin-left: 10px;">
                                <tr>
                                    <th style="background-color: #dbeaff !important;padding: 0 1.5rem; font-size: 0.875rem; padding:12px 0px; color: #000 !important; -webkit-print-color-adjust: exact;">Invoice no:</th>
                                    <th style="background-color: #dbeaff !important; padding: 0 1.5rem;font-size: 0.875rem; padding:12px 0px; color: #000 !important; -webkit-print-color-adjust: exact;">Invoice Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style=" font-size: 0.9rem;  padding: 12px 0rem; text-align: center; background-color: #f0fdf4;">${invoiceData.payment_id}</td>
                                    //use current date here
                                    <td style=" font-size: 0.9rem;  padding: 12px 0rem; text-align: center; background-color: #f0fdf4;">${new Date().toLocaleDateString('en-GB')}</td>
                                </tr>
                            </tbody>
                        </table>
                    `;

                    const partiesHTML = `
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                            <div>
                                <p style="font-weight: 600;font-size: 0.875rem; color: #000;">Bill From:</p>
                                <p style="color: #4b5563;">Vasundhara Housing</p>
                                <p style="font-size: 0.875rem; color: #6b7280;">Vasundhara Housing, SG Highway, Ahmedabad, Gujarat, India</p>
                                <p style="font-size: 0.875rem; color: #6b7280;">info@vasundharahousing.com</p>
                            </div>
                            <div>
                                <p style="font-weight: 600;font-size: 0.875rem; color: #000;">Bill To:</p>
                                <p style="color: #4b5563;">${invoiceData.tenant_name}</p>
                                <p style="font-size: 0.875rem; color: #6b7280;">${invoiceData.tenant_address || 'Address not available'}</p>
                                <p style="font-size: 0.875rem; color: #6b7280;">${invoiceData.tenant_phone || 'Phone not available'}</p>
                                <p style="font-size: 0.875rem; color: #6b7280;">${invoiceData.tenant_email}</p>
                            </div>
                        </div>
                    `;

                    const tableHTML = `
                        <table style="width: 100%; text-align: left; border-collapse: collapse; margin-bottom: 2rem;">
                            <thead>
                                <tr style="background-color: #dbeafe !important; color: #1e40af !important; -webkit-print-color-adjust: exact;">
                                    <th style="padding: 0.75rem;">Month</th>
                                    <th style="padding: 0.75rem;">Date Received</th>
                                    <th style="padding: 0.75rem;">Description</th>
                                    <th style="padding: 0.75rem; text-align: right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${invoiceData.items.map(item => `
                                    <tr style="border-bottom: 1px solid #e5e7eb;">
                                        <td style="padding: 0.75rem;">${item.remark}</td>
                                        <td style="padding: 0.75rem;">${new Date(item.payment_period).toLocaleDateString('en-GB')}</td>
                                        <td style="padding: 0.75rem;">Rent for ${item.property_name}</td>
                                        <td style="padding: 0.75rem; text-align: right;">${formatCurrency(item.amount)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;

                    const totalHTML = `
                        <table style="width: 100%; margin-top: 2rem; border-top: 2px solid #e5e7eb; padding-top: 1rem;">
                            <tbody>
                                <tr>
                                    <td style="width: 50%; vertical-align: top; padding-top: 3.5rem; padding-right:1.5rem;">
                                        <p style="font-weight: 700; font-size: 1.1rem; color: #374151; margin-bottom: 0.5rem;">Payment Methods:</p>
                                        <p style="font-size: 0.9rem; color: #6b7280; margin: 2px 0;">We Accept: UPI, Net Banking,<br> Credit card, etc..</p>
                                    </td>
                                    <td style="width: 50%; vertical-align: top; padding-top: 3.5rem;">
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tbody>
                                                <tr>
                                                    <td style="padding: 4px 0; font-size: 0.875rem; color: #4b5563;">Subtotal:</td>
                                                    <td style="padding: 4px 0; font-size: 0.875rem; text-align: right;">${formatCurrency(invoiceData.amount)}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 4px 0; font-size: 0.875rem; color: #4b5563;">Tax (0%):</td>
                                                    <td style="padding: 4px 0; font-size: 0.875rem; text-align: right;">${formatCurrency(0)}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-top: 1rem; font-weight: bold; font-size: 1.125rem; border-top: 2px solid #6b7280; margin-top: 1rem;">Total:</td>
                                                    <td style="padding: 1rem; text-align: right; justify-content: flex-end;  font-weight: bold; font-size: 1.125rem; border-top: 2px solid #6b7280; margin-top: 1rem;background-color: #dbeaff !important; -webkit-print-color-adjust: exact;">${formatCurrency(invoiceData.amount)}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    `;

                    const fullHTML = `
                        <html>
                        <head>
                            <title>Invoice #${invoiceData.payment_id}</title>
                            <style> @page { size: A4; margin: 0.6in; } body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 10pt; color: #333; } table { width: 100%; border-collapse: collapse; } th, td { font-size: 9pt; } </style>
                        </head>
                        <body>
                            ${headerAndDetailsHTML}
                            ${partiesHTML}
                            ${tableHTML}
                            ${totalHTML}
                        </body>
                        </html>
                    `;

                    const iframe = document.createElement('iframe');
                    iframe.style.position = 'absolute';
                    iframe.style.width = '0';
                    iframe.style.height = '0';
                    iframe.style.border = '0';
                    document.body.appendChild(iframe);

                    const doc = iframe.contentWindow.document;
                    doc.open();
                    doc.write(fullHTML);
                    doc.close();

                    setTimeout(() => {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                        document.body.removeChild(iframe);
                    }, 500);
                })
                .catch(error => {
                    console.error('Error generating invoice:', error);
                    showToast(error.message, 'error');
                });
        }

        function loadNotices(searchTerm = '') {
            const noticeList = document.getElementById('notice-list');
            if (!noticeList) return; // Only run if the notices list element is on the page
            noticeList.innerHTML = '<p class="text-center text-gray-500 py-4">Loading notices...</p>';

            const formData = new FormData();
            formData.append('is_ajax_request', '1');
            formData.append('search', searchTerm);

            fetch('notices.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                noticeList.innerHTML = data;
                // The response from notices.php already includes the script to create icons.
            })
            .catch(error => {
                console.error('Error loading notices:', error);
                noticeList.innerHTML = '<p class="text-center text-red-500 py-4">Failed to load notices.</p>';
            });
        }

        function loadMessages(searchTerm = '') {
            const messageList = document.getElementById('message-list');
            if (!messageList) return;
            messageList.innerHTML = '<p class="text-center text-gray-500 py-4">Loading messages...</p>';

            const formData = new FormData();
            formData.append('is_ajax_request', '1');
            formData.append('search', searchTerm);

            fetch('messages.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                messageList.innerHTML = data;
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                messageList.innerHTML = '<p class="text-center text-red-500 py-4">Failed to load messages.</p>';
            });
        }

        function deleteNotice(noticeId) {
            // The confirmation is now handled by the button's event listener
                const formData = new FormData();
                formData.append('notice_id', noticeId);

                fetch('deleteNotice.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        // Refresh the list with the current search term
                        const searchTerm = document.getElementById('notice-search-input')?.value || '';
                        loadNotices(searchTerm);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error deleting notice:', error);
                    showToast('An error occurred while deleting the notice.', 'error');
                });
        }

        function executeDeleteMessage() {
            const modal = document.getElementById('deleteMessageModal');
            const messageId = modal ? modal.dataset.messageId : null;
            if (!messageId) return;

            const formData = new FormData();
            formData.append('message_id', messageId);

            fetch('delete_message.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    const searchTerm = document.getElementById('message-search-input')?.value || '';
                    loadMessages(searchTerm);
                } else { showToast(data.message, 'error'); }
            })
            .catch(() => showToast('An error occurred while deleting the message.', 'error'))
            .finally(hideDeleteMessageModal);
        }

        // --- Main Application Logic ---
        // --- Main Application Logic ---
        document.addEventListener("DOMContentLoaded", function() {
            let debounceTimer;

            // 1. CLICK Event Delegation
            document.addEventListener("click", function(e) {
                const sidebarItem = e.target.closest('.sidebar-item');
                const filterStatusBtn = e.target.closest('.filter-status-btn');
                const filterTenantBtn = e.target.closest('.filter-tenant-btn');
                const filterPaymentBtn = e.target.closest('.filter-payment-btn');
                const deleteTenantBtn = e.target.closest('.delete-tenant-btn');
                const deletePropertyBtn = e.target.closest('.delete-property-btn');
                const generateReportBtn = e.target.closest('#generate-report-btn');
                const printReportBtn = e.target.closest('#download-report-btn');
                const printInvoiceBtn = e.target.closest('.print-invoice-btn');
                const deleteNoticeBtn = e.target.closest('.delete-notice-btn');
                const expiringFilterBtn = e.target.closest('.expiring-filter-btn');
                const sendNoticeBtn = e.target.closest('.send-notice-btn');
                const sendDueNoticeBtn = e.target.closest('.send-due-notice-btn');
                const viewNoticeBtn = e.target.closest('.view-notice-btn');
                const processTerminationBtn = e.target.closest('.process-termination-btn');
                const headerLogoutBtn = e.target.closest('#header-logout-btn');
                const messageToggleBtn = e.target.closest('.message-toggle');

                // Sidebar navigation
                if (sidebarItem) {
                    e.preventDefault();
                    const page = sidebarItem.getAttribute("data-page");
                    if (page === 'logout') return showLogoutModal();

                    loadPage(page);
                    // Explicitly set the active state for only the clicked item
                    document.querySelectorAll('.sidebar-item').forEach(item => {
                        item.classList.toggle('active', item === sidebarItem);
                    });
                }

                // Header Logout Button
                if (headerLogoutBtn) {
                    showLogoutModal();
                }

                // Logout modal
                if (e.target.id === 'confirmLogout') logout();
                if (e.target.id === 'cancelLogout') hideLogoutModal();

                // Delete Tenant Modal
                if (e.target.id === 'confirmDeleteTenant') deleteTenant();
                if (e.target.id === 'cancelDeleteTenant') hideDeleteTenantModal();
                if (e.target.id === 'confirmDeleteProperty') deleteProperty();
                if (e.target.id === 'cancelDeleteProperty') hideDeletePropertyModal(); 

                // Send Expiry Notice Modal
                if (e.target.id === 'confirmSendExpiryNotice') executeSendExpiryNotice();
                if (e.target.id === 'cancelSendExpiryNotice') hideSendExpiryNoticeModal();

                // Send Due Notice Modal
                if (e.target.id === 'confirmSendDueNotice') executeSendDueNotice();
                if (e.target.id === 'cancelSendDueNotice') hideSendDueNoticeModal();

                // Delete Message Modal
                if (e.target.id === 'confirmDeleteMessage') executeDeleteMessage();
                if (e.target.id === 'cancelDeleteMessage') hideDeleteMessageModal();

                // Property Status Filter Buttons
                if (filterStatusBtn) {
                    const status = filterStatusBtn.dataset.status;
                    const search = document.getElementById('property-search-input')?.value || '';
                    const type = document.getElementById('property-type-select')?.value || 'All';

                    document.querySelectorAll('.filter-status-btn').forEach(btn => {
                        btn.classList.remove('bg-blue-600', 'text-white', 'shadow-md');
                        btn.classList.add('bg-gray-200', 'text-gray-700');
                    });
                    filterStatusBtn.classList.add('bg-blue-600', 'text-white', 'shadow-md');
                    filterStatusBtn.classList.remove('bg-gray-200', 'text-gray-700');

                    loadProperties(status, search, type);
                }

                // Tenant Filter Buttons
                if (filterTenantBtn) {
                    const status = filterTenantBtn.dataset.status;
                    document.querySelectorAll('.filter-tenant-btn').forEach(btn => {
                        btn.classList.remove('bg-blue-600', 'text-white', 'shadow-md');
                        btn.classList.add('bg-gray-200', 'text-gray-700');
                    });
                    filterTenantBtn.classList.add('bg-blue-600', 'text-white', 'shadow-md');
                    filterTenantBtn.classList.remove('bg-gray-200', 'text-gray-700');

                    const searchTerm = document.getElementById('tenant-search-input')?.value || '';
                    renderTenantList(status, searchTerm);
                }

                // Payment Status Filter Buttons
                if (filterPaymentBtn) {
                    const status = filterPaymentBtn.dataset.status;
                    const search = document.getElementById('payment-search-input')?.value || '';

                    document.querySelectorAll('.filter-payment-btn').forEach(btn => {
                        btn.classList.remove('bg-blue-600', 'text-black', 'shadow-md', 'hover:bg-blue-600');
                        btn.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
                    });
                    filterPaymentBtn.classList.add('bg-blue-600', 'text-white', 'shadow-md', 'hover:bg-blue-600');
                    filterPaymentBtn.classList.remove('bg-gray-200', 'text-gray-700');

                    loadPayments(status, search);
                }

                // Delete Tenant Button
                if (deleteTenantBtn) {
                    const tenantId = deleteTenantBtn.dataset.id;
                    if (tenantId) showDeleteTenantModal(tenantId);
                }
                if (deletePropertyBtn) {
                    const propertyId = deletePropertyBtn.dataset.id;
                    if (propertyId) {
                        const modal = document.getElementById('deletePropertyModal');
                        if (modal) {
                            modal.dataset.propertyId = propertyId; // Store propertyId in a data attribute
                            modal.classList.add('flex');
                            modal.classList.remove('hidden');
                        }
                    }
                }

                // Generate Report Button
                if (generateReportBtn) {
                    reportFetch();
                }

                // Print Report Button
                if (printReportBtn) {
                    printReport();
                }

                // Print Invoice Button
                if (printInvoiceBtn) {
                    const paymentId = printInvoiceBtn.dataset.id;
                    if (paymentId) {
                        printInvoice(paymentId);
                    }
                }

                // Delete Notice Button (from notices.php)
                if (deleteNoticeBtn) {
                    const noticeId = deleteNoticeBtn.dataset.id;
                    if (noticeId) {
                        deleteNotice(noticeId);
                    }
                }

                // Expiring Agreements Filter
                if (expiringFilterBtn) {
                    const days = expiringFilterBtn.dataset.days;
                    document.querySelectorAll('.expiring-filter-btn').forEach(btn => btn.classList.remove('active'));
                    expiringFilterBtn.classList.add('active');
                    loadExpiringAgreements(days);
                }

                // Send Expiry Notice Button
                if (sendNoticeBtn) {
                    const tenantId = sendNoticeBtn.dataset.tenantId;
                    const tenantName = sendNoticeBtn.dataset.tenantName;
                    sendExpiryNotice(tenantId, tenantName);
                }

                // Send Payment Due Notice Button
                if (sendDueNoticeBtn) {
                    const tenantId = sendDueNoticeBtn.dataset.tenantId;
                    const tenantName = sendDueNoticeBtn.dataset.tenantName;
                    const period = sendDueNoticeBtn.dataset.period;
                    const amount = sendDueNoticeBtn.dataset.amount;
                    sendDueNotice(tenantId, tenantName, period, amount);
                }

                // View Notice Modal Trigger
                if (viewNoticeBtn) {
                    const noticeData = {
                        id: viewNoticeBtn.dataset.id,
                        subject: viewNoticeBtn.dataset.subject,
                        recipient: viewNoticeBtn.dataset.recipient,
                        date: viewNoticeBtn.dataset.date,
                        message: viewNoticeBtn.dataset.message,
                        recipientIcon: viewNoticeBtn.dataset.recipientIcon,
                        recipientColor: viewNoticeBtn.dataset.recipientColor
                    };
                    showNoticeModal(noticeData);
                }

                // Process Termination Request Button
                if (processTerminationBtn) {
                    const requestId = processTerminationBtn.dataset.id;
                    const action = processTerminationBtn.dataset.action;
                    const tenantName = processTerminationBtn.dataset.name;
                    showProcessTerminationModal(requestId, action, tenantName);
                }

                // Message Accordion Toggle
                if (messageToggleBtn) {
                    const messageBody = messageToggleBtn.nextElementSibling;
                    const messageChevron = messageToggleBtn.querySelector('.message-chevron');
                    if (messageBody && messageChevron) {
                        messageBody.classList.toggle('hidden');
                        messageChevron.classList.toggle('rotate-180');
                    }
                }

                // Delete Message Button
                const deleteMessageBtn = e.target.closest('.delete-message-btn');
                if (deleteMessageBtn) {
                    showDeleteMessageModal(deleteMessageBtn.dataset.id);
                }
            });

            // 2. INPUT Event Delegation (with Debounce)
            document.addEventListener('input', function(e) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    if (e.target.id === 'property-search-input') {
                        const search = e.target.value;
                        const status = document.querySelector('.filter-status-btn.bg-blue-600')?.dataset.status || 'All';
                        const type = document.getElementById('property-type-select')?.value || 'All';
                        loadProperties(status, search, type);
                    }
                    if (e.target.id === 'tenant-search-input') {
                        const searchTerm = e.target.value;
                        const status = document.querySelector('.filter-tenant-btn.bg-blue-500')?.dataset.status || 'All';
                        renderTenantList(status, searchTerm);
                    }
                    if (e.target.id === 'payment-search-input') {
                        const search = e.target.value;
                        const status = document.querySelector('.filter-payment-btn.bg-blue-600')?.dataset.status || 'All';
                        loadPayments(status, search);
                    }
                    if (e.target.id === 'notice-search-input') {
                        const searchTerm = e.target.value;
                        loadNotices(searchTerm);
                    }
                    if (e.target.id === 'message-search-input') {
                        const searchTerm = e.target.value;
                        loadMessages(searchTerm);
                    }
                }, 350);
            });

            // 3. CHANGE Event Delegation
            document.addEventListener('change', function(e) {
                if (e.target.id === 'property-type-select') {
                    const type = e.target.value;
                    const search = document.getElementById('property-search-input')?.value || '';
                    const status = document.querySelector('.filter-status-btn.bg-blue-600')?.dataset.status || 'All';
                    loadProperties(status, search, type);
                }
                if (e.target.classList.contains('update-status-dropdown')) {
                    const propertyId = e.target.closest('.property-card')?.querySelector('input[name="pro_id"]')?.value;
                    if (propertyId) updatePropertyStatus(propertyId, e.target.value);
                }
                // In index.php, inside the document.addEventListener('change', function(e) { ... });

                if (e.target.classList.contains('property-status-select')) {
                    const propertyId = e.target.dataset.id;
                    const newStatus = e.target.value;

                    // Disable the dropdown to prevent multiple rapid changes
                    e.target.disabled = true;

                    fetch(`updateStatus.php?id=${propertyId}&status=${newStatus}`)
                        .then(res => {
                            if (!res.ok) {
                                // If the server returns an error (e.g., 400, 403, 500), try to parse the JSON body for a message
                                return res.json().then(err => Promise.reject(err));
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (data.status === 'success') {
                                showToast(data.message, 'success');
                            } else {
                                // This case handles non-error HTTP codes but with a logical error from the app
                                showToast(data.message || 'An unknown error occurred.', 'error');
                            }
                        })
                        .catch(error => {
                            // This handles network errors or errors from the .json() parsing
                            console.error('Error updating status:', error);
                            showToast(error.message || 'Failed to update status. Check console for details.', 'error');
                            // You could add logic here to revert the dropdown to its original value if the update fails
                        })
                        .finally(() => {
                            // Always re-enable the dropdown, whether the request succeeded or failed
                            e.target.disabled = false;
                        });
                }

            });
            
            // 4. SUBMIT Event Delegation
            document.addEventListener('submit', function(e) {
                // Handle Send Notice Form from notices.php
                if (e.target.id === 'send-notice-form') {
                    e.preventDefault();
                    const form = e.target;
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalButtonContent = submitButton.innerHTML;
                    submitButton.disabled = true;
                    submitButton.innerHTML = `<i data-lucide="loader-2" class="w-5 h-5 mr-2 animate-spin"></i>Sending...`;
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    const formData = new FormData(form);

                    fetch('storeNotice.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            form.reset();
                            loadNotices(); // Refresh the list of notices
                        } else {
                            showToast(data.message || 'An error occurred.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error sending notice:', error);
                        showToast('A network error occurred. Please try again.', 'error');
                    }).finally(() => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonContent;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            });

            // --- Notice Modal Specific Listeners ---
            const noticeModal = document.getElementById('noticeDetailModal');
            if (noticeModal) {
                document.getElementById('closeNoticeModal').addEventListener('click', hideNoticeModal);
                document.getElementById('cancelNoticeModal').addEventListener('click', hideNoticeModal);
                document.getElementById('deleteNoticeFromModalBtn').addEventListener('click', function() {
                    const noticeId = this.dataset.id;
                    if (noticeId && confirm('Are you sure you want to delete this notice? This action cannot be undone.')) {
                        deleteNotice(noticeId);
                        hideNoticeModal();
                    }
                });
                noticeModal.addEventListener('click', (e) => { if (e.target === noticeModal) hideNoticeModal(); });
            }

            // --- Process Termination Modal Listeners ---
            const processTerminationModal = document.getElementById('processTerminationModal');
            if (processTerminationModal) {
                document.getElementById('cancelProcessTermination').addEventListener('click', hideProcessTerminationModal);
                processTerminationModal.addEventListener('click', (e) => {
                    if (e.target === processTerminationModal) hideProcessTerminationModal();
                });
            }

            // --- Initial Page Load ---
            // Determine the page to load, with a fallback to the dashboard
            const lastPage = localStorage.getItem('lastAdminPage') || 'dashboard.php';
            let pageToActivate = lastPage;

            // Verify the page exists in the sidebar, otherwise default to dashboard
            if (!document.querySelector(`.sidebar-item[data-page="${pageToActivate}"]`)) {
                pageToActivate = 'dashboard.php';
            }

            // Set the active class on the correct sidebar item
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.classList.toggle('active', item.getAttribute('data-page') === pageToActivate);
            });
            loadPage(pageToActivate);
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        function showProcessTerminationModal(requestId, action, tenantName) {
            const modal = document.getElementById('processTerminationModal');
            if (!modal) return;

            // Populate form fields
            document.getElementById('termination-request-id').value = requestId;
            document.getElementById('termination-action').value = action;

            // Customize modal text
            const title = document.getElementById('processTerminationModalTitle');
            const message = document.getElementById('processTerminationModalMessage');
            const confirmBtn = document.getElementById('confirmProcessTermination');
            const remarkField = document.getElementById('admin-remark');
            const remarkLabel = document.querySelector('label[for="admin-remark"]');
            remarkField.value = ''; // Clear previous remarks

            if (action === 'approve') {
                title.textContent = 'Approve Termination';
                message.textContent = `Are you sure you want to approve the termination request for ${tenantName}? This will send them a 24-hour notice to vacate.`;
                confirmBtn.className = 'px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700';
                confirmBtn.textContent = 'Approve';
                if (remarkLabel) remarkLabel.textContent = 'Reason for Approval (Optional)';
                remarkField.placeholder = 'e.g., Tenant has cleared all dues and is eligible for early termination.';
            } else if (action === 'reject') {
                title.textContent = 'Reject Termination';
                message.textContent = `Are you sure you want to reject the termination request for ${tenantName}? You can provide a reason below.`;
                confirmBtn.className = 'px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700';
                confirmBtn.textContent = 'Reject';
                if (remarkLabel) remarkLabel.textContent = 'Reason for Rejection (Optional)';
                remarkField.placeholder = 'e.g., Lease period not completed, outstanding dues found.';
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hideProcessTerminationModal() {
            const modal = document.getElementById('processTerminationModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function handleProcessTermination(form) {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Processing...';

            const formData = new FormData(form);

            fetch('process_termination.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    hideProcessTerminationModal();
                    loadPage('termination_requests.php');
                }
            })
            .catch(error => {
                console.error('Error processing termination:', error);
                showToast('A network error occurred. Please try again.', 'error');
            })
            .finally(() => { submitBtn.disabled = false; submitBtn.textContent = originalBtnText; });
        }

        document.addEventListener('submit', function(e) {
            if (e.target.id === 'process-termination-form') {
                e.preventDefault();
                handleProcessTermination(e.target);
            }
        });
    </script>

</body>

</html>