<?php
include "./database.php";
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

function renderMessagesList($conn, $search = '') {
    // Assuming the table is named 'message' and has a 'created_at' column for sorting.
    // If not, you can change 'created_at' to 'id'.
    $sql = "SELECT id, name, email, number, message FROM messages";

    $params = [];
    $types = '';
    if (!empty($search)) {
        $sql .= " WHERE LOWER(name) LIKE ? OR LOWER(email) LIKE ? OR LOWER(message) LIKE ?";
        $searchTerm = "%" . strtolower($search) . "%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
        $types = 'sss';
    }

    $stmt = $conn->prepare($sql);
    if (!empty($search)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (count($messages) > 0) {
        echo '<div class="space-y-4">';
        foreach ($messages as $msg) {
            echo '<div class="bg-white border border-gray-200 rounded-lg transition-shadow hover:shadow-md overflow-hidden">';
            // Accordion Header
            echo '  <button class="message-toggle w-full flex justify-between items-center p-4 text-left">';
            echo '      <div>';
            echo '          <h4 class="font-bold text-gray-800">Inquiry from: ' . htmlspecialchars($msg['name']) . '</h4>';
            echo '          <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">';
            echo '              <i data-lucide="mail" class="w-3 h-3 text-indigo-500"></i>';
            echo '              <span>' . htmlspecialchars($msg['email']) . '</span>';
            echo '              <span class="mx-1">•</span>';
            echo '          </div>';
            echo '      </div>';
            echo '      <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 transition-transform message-chevron"></i>';
            echo '  </button>';
            // Accordion Body (hidden by default)
            echo '  <div class="message-body hidden px-4 pb-4">';
            echo '      <div class="border-t border-gray-200 pt-3">';
            echo '          <p class="text-sm text-gray-600 mb-2"><strong>Phone:</strong> ' . htmlspecialchars($msg['number'] ?: 'N/A') . '</p>';
            echo '          <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-md">' . nl2br(htmlspecialchars($msg['message'])) . '</p>';
            echo '      </div>';
            echo '      <div class="text-right mt-4"><button data-id="' . $msg['id'] . '" class="delete-message-btn text-xs text-red-500 hover:text-red-700 font-semibold flex items-center gap-1"><i data-lucide="trash-2" class="w-3 h-3"></i>Delete</button></div>';
            echo '  </div>';
            echo '</div>';
        }
        echo '</div>';
        echo '<script>if (typeof lucide !== "undefined") { lucide.createIcons(); }</script>';
    } else {
        echo '<div class="text-center py-16 px-6 bg-slate-50 rounded-lg border-2 border-dashed"><div class="mx-auto w-fit"><i data-lucide="message-square-off" class="w-16 h-16 mx-auto text-gray-400"></i><h3 class="mt-2 text-xl font-semibold text-gray-800">No Messages Found</h3><p class="mt-1 text-gray-500">There are no inquiries in the system.</p></div></div>';
        echo '<script>if (typeof lucide !== "undefined") { lucide.createIcons(); }</script>';
    }
}

// Handle AJAX requests for searching
if (isset($_POST['is_ajax_request'])) {
    $search = $_POST['search'] ?? '';
    renderMessagesList($conn, $search);
    exit;
}
?>

<div class="content-section">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Inquiries & Messages</h1>
    <div class="bg-white p-6 rounded-2xl shadow-lg border">
        <div class="relative mb-4">
            <input type="text" id="message-search-input" placeholder="Search by name, email, or message content..." class="w-full pl-4 pr-10 py-3 text-base border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition outline-none" />
            <i data-lucide="search" class="absolute top-1/2 right-4 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
        </div>
        <div id="message-list" class="mt-6 transition-all duration-300 ease-in-out">
            <?php renderMessagesList($conn); ?>
        </div>
    </div>
</div>

<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>