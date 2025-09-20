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
        <h1 class="text-3xl font-bold text-gray-800">Lease Termination Requests</h1>
        <p class="text-gray-600">Review and process tenant requests for early lease termination.</p>
    </div>

    <!-- Termination Requests Table Container -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div id="termination-request-list" class="divide-y divide-gray-200">
            <!-- Rows will be loaded here by AJAX -->
        </div>
    </div>
</div>

<script>
    function loadTerminationRequests() {
        const listContainer = document.getElementById('termination-request-list');
        if (!listContainer) return;

        listContainer.innerHTML = `<div class="p-12 text-center text-gray-500"><i data-lucide="loader-2" class="w-10 h-10 mx-auto text-blue-500 animate-spin"></i><p class="mt-2">Loading requests...</p></div>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        fetch('get_termination_requests.php')
            .then(response => response.text())
            .then(data => {
                listContainer.innerHTML = data;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(error => {
                listContainer.innerHTML = '<div class="p-6 text-center text-red-500">Failed to load requests. Please try again.</div>';
            });
    }
    loadTerminationRequests();
</script>