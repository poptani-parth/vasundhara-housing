<?php
session_start();
include '../database.php'; // Go up one directory to find database.php

// This function will render the list of notices. It's the core logic.
function terrminationRequestNoticesList($conn, $tenantId) {
    $sql = "SELECT admin_remark, status, request_date 
            FROM termination_requests 
            WHERE tenant_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo '<div class="text-center text-red-500 p-4">Database query error.</div>';
        return;
    }
    $stmt->bind_param("i", $tenantId);
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo '<div class="mt-6">';
    echo '<h2 class="text-2xl font-bold text-gray-800 mb-4">Lease Termination Requests</h2>';
    if (count($requests) > 0) {
        echo '<div class="space-y-4">';
        foreach ($requests as $request) {
            $status = htmlspecialchars($request['status']);
            $statusConfig = match ($status) {
                'Approved' => ['badge' => 'bg-blue-100 text-blue-800'],
                'Rejected' => ['badge' => 'bg-red-100 text-red-800'],
                'Completed' => ['badge' => 'bg-green-100 text-green-800'],
                default => ['badge' => 'bg-yellow-100 text-yellow-800'],
            };

            echo '<div class="bg-white border border-gray-200 rounded-lg p-4">';
            echo '  <div class="flex items-center gap-4">';
            echo '      <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center bg-gray-100"><i class="ri-file-text-line text-gray-500 text-xl"></i></div>';
            echo '      <div>';
            echo '          <h4 class="font-semibold text-gray-800">Lease Termination Request</h4>';
            echo '          <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">';
            echo '              <span>Requested: ' . date('M d, Y', strtotime($request['request_date'])) . '</span>';
            echo '              <span class="mx-1">•</span>';
            echo '              <span class="px-2 py-0.5 font-semibold rounded-full ' . $statusConfig['badge'] . '">' . $status . '</span>';
            echo '          </div>';
            echo '      </div>';
            echo '  </div>';
            if (!empty($request['admin_remark'])) {
                echo '  <div class="border-t border-gray-200 pt-3 mt-4">';
                echo '      <p class="text-sm text-gray-500 mb-1 font-medium">Admin Remark:</p>';
                echo '      <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-md">' . nl2br(htmlspecialchars($request['admin_remark'])) . '</p>';
                echo '  </div>';
            }
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="text-center py-16 px-6 bg-gray-50 rounded-lg border-2 border-dashed">';
        echo '  <div class="mx-auto w-fit">';
        echo '      <i class="ri-file-list-line text-5xl text-gray-400"></i>';
        echo '      <h3 class="text-lg font-semibold text-gray-800 mt-2">No termination requests found.</h3>';
        echo '  </div>';
        echo '</div>';
    }
}
function renderTenantNoticesList($conn, $tenantId, $search = '') {
    // The crucial part: select notices for this tenant OR notices for ALL tenants (where tenant_id is NULL)
    $sql = "SELECT notice_id, subject, message, recipient, created_at 
            FROM tenantnotice 
            WHERE (tenant_id = ? OR tenant_id IS NULL)";

    $params = [$tenantId];
    $types = 'i';

    if (!empty($search)) {
        $sql .= " AND (LOWER(subject) LIKE ? OR LOWER(message) LIKE ?)";
        $searchTerm = "%" . strtolower($search) . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo '<div class="text-center text-red-500 p-4">Database query error.</div>';
        return;
    }
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $notices = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();


    if (count($notices) > 0) {
        echo '<div class="space-y-4">';
        foreach ($notices as $notice) {
            $isBroadcast = ($notice['recipient'] === 'All Tenants');
            $icon = $isBroadcast ? 'ri-broadcast-fill' : 'ri-user-fill';
            $color = $isBroadcast ? 'text-blue-500' : 'text-indigo-500';
            $recipientText = $isBroadcast ? 'All Tenants' : 'For You';

            echo '<div class="bg-white border border-gray-200 rounded-lg transition-shadow hover:shadow-md overflow-hidden">';
            // Accordion Header
            echo '  <button class="notice-toggle w-full flex justify-between items-center p-4 text-left">';
            echo '      <div class="flex items-center gap-3">';
            echo '          <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center ' . ($isBroadcast ? 'bg-blue-100' : 'bg-indigo-100') . '">';
            echo '              <i class="' . $icon . ' ' . $color . ' text-xl"></i>';
            echo '          </div>';
            echo '          <div>';
            echo '              <h4 class="font-semibold text-gray-800">' . htmlspecialchars($notice['subject']) . '</h4>';
            echo '              <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">';
            echo '                  <span>Sent: ' . date('M d, Y', strtotime($notice['created_at'])) . '</span>';
            echo '                  <span class="mx-1">•</span>';
            echo '                  <span>To: ' . $recipientText . '</span>';
            echo '              </div>';
            echo '          </div>';
            echo '      </div>';
            echo '      <i class="ri-arrow-down-s-line text-gray-400 transition-transform text-xl"></i>';
            echo '  </button>';
            // Accordion Body (hidden by default)
            echo '  <div class="notice-body hidden px-4 pb-4">';
            echo '      <div class="border-t border-gray-200 pt-4 mt-2">';
            echo '          <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-4 rounded-md">' . nl2br(htmlspecialchars($notice['message'])) . '</p>';
            echo '      </div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="text-center py-16 px-6 bg-gray-50 rounded-lg border-2 border-dashed">';
        echo '  <div class="mx-auto w-fit">';
        echo '      <i class="ri-mail-forbid-line text-5xl text-gray-400"></i>';
        echo '      <h3 class="mt-2 text-xl font-semibold text-gray-800">No Notices Found</h3>';
        echo '      <p class="mt-1 text-gray-500">You have no new notices at this time.</p>';
        echo '  </div>';
        echo '</div>';
    }
}

// --- AJAX Request Handling ---
if (isset($_POST['is_ajax_request'])) {
    if (!isset($_SESSION['tenant_id'])) {
        http_response_code(401);
        echo 'Session expired. Please log in again.';
        exit;
    }
    $tenantId = $_SESSION['tenant_id'];
    $search = $_POST['search'] ?? '';
    renderTenantNoticesList($conn, $tenantId, $search);
    terrminationRequestNoticesList($conn, $tenantId);
    exit;
}

// --- Initial Page Load ---
if (!isset($_SESSION['tenant_id'])) {
    echo 'Unauthorized access.';
    exit;
}
$tenantId = $_SESSION['tenant_id'];
?>

<div class="animate-slide-up-fade-in">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Notices & Alerts</h1>
    <div class="bg-white p-6 rounded-xl shadow-md border">
        <div class="relative mb-6">
            <input type="text" id="notice-search-input" placeholder="Search notices by subject or content..." class="w-full pl-10 pr-4 py-3 text-base border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition outline-none" />
            <i class="ri-search-line absolute top-1/2 left-3 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
        </div>
        <div id="notice-list-container" class="transition-all duration-300 ease-in-out">
            <?php renderTenantNoticesList($conn, $tenantId); ?>
        </div>
        <div id="termination-request-list-container" class="mt-10 transition-all duration-300 ease-in-out">
            <?php terrminationRequestNoticesList($conn, $tenantId); ?>
        </div>
    </div>
</div>