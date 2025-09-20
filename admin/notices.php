<?php
include "./database.php"; // This should provide a $conn mysqli object.
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
function renderNoticesList($conn, $search = '') {
    $sql = "SELECT notice_id, subject, message, recipient, created_at FROM tenantnotice";

    $params = [];
    $types = '';
    if (!empty($search)) {
        // Updated to search in a case-insensitive manner
        $sql .= " WHERE LOWER(subject) LIKE ? OR LOWER(message) LIKE ? OR LOWER(recipient) LIKE ?";
        $searchTerm = "%" . strtolower($search) . "%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
        $types = 'sss';
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($search)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $notices = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (count($notices) > 0) {
        echo '<div class="space-y-4">';
        foreach ($notices as $notice) {
            $recipient_icon = ($notice['recipient'] === 'All Tenants') ? 'users' : 'user';
            $recipient_color = ($notice['recipient'] === 'All Tenants') ? 'text-blue-500' : 'text-indigo-500';

            echo '<div class="view-notice-btn bg-slate-50 border border-slate-200 rounded-lg p-4 transition-all hover:shadow-md hover:border-blue-300 cursor-pointer" 
                    data-id="' . $notice['notice_id'] . '"
                    data-subject="' . htmlspecialchars($notice['subject']) . '"
                    data-recipient="' . htmlspecialchars($notice['recipient']) . '"
                    data-date="' . date('M d, Y', strtotime($notice['created_at'])) . '"
                    data-message="' . htmlspecialchars($notice['message']) . '"
                    data-recipient-icon="' . $recipient_icon . '"
                    data-recipient-color="' . $recipient_color . '">';
            echo '  <div class="flex justify-between items-center pointer-events-none">';
            echo '    <div>';
            echo '      <h4 class="font-bold text-gray-800">' . htmlspecialchars($notice['subject']) . '</h4>';
            echo '      <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">';
            echo '        <i data-lucide="' . $recipient_icon . '" class="w-3 h-3 ' . $recipient_color . '"></i>';
            echo '        <span>To: ' . htmlspecialchars($notice['recipient']) . '</span>';
            echo '        <span class="mx-1">•</span>';
            echo '        <span>' . date('M d, Y', strtotime($notice['created_at'])) . '</span>';
            echo '      </div>';
            echo '    </div>';
            echo '    <i data-lucide="eye" class="w-5 h-5 text-gray-400"></i>';
            echo '  </div>';
            echo '</div>';
        }
        echo '</div>';
        // Re-initialize Lucide icons after AJAX load
        echo '<script>if (typeof lucide !== "undefined") { lucide.createIcons(); }</script>';
    } else {
        echo '<div class="text-center py-16 px-6 bg-slate-50 rounded-lg border-2 border-dashed"><div class="mx-auto w-fit"><i data-lucide="bell-off" class="w-16 h-16 mx-auto text-gray-400"></i><h3 class="mt-2 text-xl font-semibold text-gray-800">No Notices Found</h3><p class="mt-1 text-gray-500">Try adjusting your search or send a new notice.</p></div></div>';
        echo '<script>if (typeof lucide !== "undefined") { lucide.createIcons(); }</script>';
    }
}

if (isset($_POST['is_ajax_request'])) {
    $search = $_POST['search'] ?? '';
    renderNoticesList($conn, $search);
    exit; // Stop script execution for AJAX requests
}

// --- Page Load Data ---
// Fetch tenants for the dropdown
$tenants = [];
$sql = "SELECT tenant_id, tenant_name FROM tenants WHERE status = 'Active' ORDER BY tenant_name ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $tenants = $result->fetch_all(MYSQLI_ASSOC);
}

$message = '';
$messageType = '';
?>

<div class="content-section">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Notices & Alerts</h1>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <!-- Left Column: Send Notice Form -->
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-2xl shadow-lg border h-full">
                <h3 class="text-2xl font-bold text-gray-800 mb-1">Send a New Notice</h3>
                <p class="text-sm text-gray-500 mb-6">Compose and send a notice to all or a specific tenant.</p>
                <form id="send-notice-form" method="POST" class="space-y-5">
                    <div>
                        <label for="noticeTitle" class="block text-sm font-medium text-gray-700">Notice Title</label>
                        <input type="text" id="noticeTitle" name="noticeTitle" required
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="e.g., Rent Payment Due">
                    </div>
                    <div>
                        <label for="noticeMessage" class="block text-sm font-medium text-gray-700">Notice Message</label>
                        <textarea id="noticeMessage" name="noticeMessage" rows="5" required
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Enter the details of the notice..."></textarea>
                    </div>
                    <div>
                        <label for="recipientType" class="block text-sm font-medium text-gray-700">Recipient</label>
                        <select id="recipientType" name="recipientType" required
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="All Tenants">All Tenants</option>
                            <?php foreach ($tenants as $tenant): ?>
                                <option value="<?php echo htmlspecialchars($tenant['tenant_id']); ?>">
                                    <?php echo htmlspecialchars($tenant['tenant_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pt-2">
                        <button type="submit" name="sendNotice"
                            class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-md flex items-center justify-center">
                            <i data-lucide="send" class="w-5 h-5 mr-2"></i>Send Notice
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Sent Notices List -->
        <div class="lg:col-span-3">
            <!-- This container is now a flex column with a max height, creating a scroll context -->
            <div class="bg-white rounded-2xl shadow-lg border flex flex-col" style="max-height: calc(100vh - 10rem);">
                <!-- Header (no longer sticky, but fixed by flex layout) -->
                <div class="p-6 border-b border-gray-200 flex-shrink-0">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Sent Notices</h3>
                    <div class="relative">
                        <input type="text" id="notice-search-input" placeholder="Search by subject, message, or recipient..."
                            class="w-full pl-4 pr-10 py-3 text-base border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition outline-none" />
                        <i data-lucide="search" class="absolute top-1/2 right-4 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                    </div>
                </div>
                <!-- This div will now scroll independently -->
                <div id="notice-list" class="flex-grow overflow-y-auto p-6">
                    <?php renderNoticesList($conn); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // This script runs when the content is loaded.
    // All event handlers are delegated in index.php for robustness.
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
