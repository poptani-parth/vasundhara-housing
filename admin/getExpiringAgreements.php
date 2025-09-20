<?php
include './database.php';

$days = isset($_GET['days']) ? (int)$_GET['days'] : 5;

// The SQL query is modified to check for the existence of a newer agreement
// for the same tenant, which allows for conditional UI rendering.
$sql = "SELECT
            ra.agreement_id,
            t.tenant_id,
            t.tenant_name,
            p.pro_id,
            p.pro_nm,
            ra.ending_date,
            DATEDIFF(ra.ending_date, CURDATE()) as days_left,
            (EXISTS (
                SELECT 1
                FROM rental_agreements ra2
                WHERE ra2.tenant_id = ra.tenant_id
                AND ra2.starting_date > ra.starting_date
            )) as has_new_agreement
        FROM rental_agreements ra
        JOIN tenants t ON ra.tenant_id = t.tenant_id
        JOIN properties p ON ra.pro_id = p.pro_id
        WHERE
            ra.ending_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            AND t.status = 'Active'
            AND EXISTS (
                SELECT 1
                FROM rental_agreements ra2
                WHERE ra2.tenant_id = ra.tenant_id
                AND ra2.starting_date > ra.starting_date
            )
        ORDER BY
            ra.ending_date ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    // It's good practice to handle potential SQL errors.
    echo '<div class="text-center text-red-500 p-4">Error preparing the database query.</div>';
    exit();
}
$stmt->bind_param("i", $days);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tenantId = htmlspecialchars($row['tenant_id']);
        $tenantName = htmlspecialchars($row['tenant_name']);
        $propertyName = htmlspecialchars($row['pro_nm']);
        $propertyId = htmlspecialchars($row['pro_id']);
        $endingDate = date("d M, Y", strtotime($row['ending_date']));
        $daysLeft = (int)$row['days_left'];
        $hasNewAgreement = (int)$row['has_new_agreement'];

        if ($daysLeft < 0) continue; // Should not happen with the query, but as a safeguard.

        $daysText = $daysLeft === 0 ? 'today' : ($daysLeft === 1 ? 'in 1 day' : "in {$daysLeft} days");

        $rowClasses = "flex items-center justify-between p-3 rounded-lg border transition-colors";
        $iconHTML = '';
        $actionsHTML = '';

        if ($hasNewAgreement) {
            $rowClasses .= " bg-green-50 border-green-200";
            $iconHTML = '<i data-lucide="check-check" class="w-6 h-6 text-green-500"></i>';
            $actionsHTML = <<<HTML
            <div class="flex items-center gap-3">
                <span class="text-xs font-medium text-green-700 flex items-center gap-1">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Renewed
                </span>
            </div>
HTML;
        } else {
            $rowClasses .= " bg-yellow-50 border-yellow-200 hover:bg-yellow-100";
            $iconHTML = '<i data-lucide="file-clock" class="w-6 h-6 text-yellow-500"></i>';
            $actionsHTML = <<<HTML
            <div class="flex items-center gap-3">
                <button class="text-xs font-medium text-blue-600 hover:underline send-notice-btn" data-tenant-id="{$tenantId}" data-tenant-name="{$tenantName}">
                    Send Notice
                </button>
                <a href="../rentAgreement.php?name={$tenantName}&property_id={$propertyId}" target="_blank" class="text-xs font-medium text-green-600 hover:underline">
                    Renew
                </a>
            </div>
HTML;
        }

        echo <<<HTML
        <div class="{$rowClasses}">
            <div class="flex items-center gap-4">
                {$iconHTML}
                <div>
                    <p class="font-semibold text-gray-800">
                        <a href="#" class="hover:underline" onclick="loadPage('tenants.php'); return false;">{$tenantName}</a>'s agreement for <span class="font-bold">{$propertyName}</span>
                    </p>
                    <p class="text-sm text-gray-600">
                        Expires on {$endingDate} ({$daysText})
                    </p>
                </div>
            </div>
            {$actionsHTML}
        </div>
HTML;
    }
} else {
    echo '<div class="text-center text-gray-500 p-8 bg-gray-50 rounded-lg border-2 border-dashed">
            <i data-lucide="check-circle-2" class="w-12 h-12 mx-auto text-green-400"></i>
            <p class="mt-4">No agreements are expiring in the next ' . $days . ' days.</p>
          </div>';
}

echo '<script>if (typeof lucide !== "undefined") { lucide.createIcons(); }</script>';

$stmt->close();
$conn->close();
?>