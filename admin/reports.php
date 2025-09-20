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
        <h1 class="text-3xl font-bold text-gray-800">Reports</h1>
    </div>

    <!-- Filter/Selector Controls -->
    <div class="flex flex-col md:flex-row items-center mb-6 bg-white p-4 rounded-xl shadow-sm border">
        <!-- Month and Year Selector -->
        <div class="flex-grow w-full md:w-auto">
            <label for="report-month-year" class="text-lg mr-2 font-medium text-gray-600">Select Month:</label>
            <input type="month" id="report-month-year" name="report_month_year"
                   value="<?php echo date('Y-m'); ?>"
                   class="mt-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <!-- Generate Button -->
        <div class="w-full md:w-auto">
            <button id="generate-report-btn"
                class="w-full px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center shadow-md">
                <i data-lucide="file-text" class="w-5 h-5 mr-2"></i>
                Generate Report
            </button>
        </div>
    </div>

    <!-- Report Display Area -->
    <div id="report-display-area" class="bg-white rounded-xl shadow-sm border overflow-hidden p-4 md:p-6 hidden">
        
        <!-- Report Header and Download Button -->
        <div class="flex justify-between items-center mb-6">
            <h2 id="report-title" class="text-2xl font-bold text-gray-800">Monthly Report</h2>
            <button id="download-report-btn"
                class="inline-flex items-center gap-2 bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors shadow-md">
                 Print Report
            </button>
        </div>

        <!-- Report Content to be Printed -->
        <div id="report-content-container">
            <!-- Summary Cards -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                    <p class="text-sm text-green-700 font-medium">Total Revenue</p>
                    <p id="summary-revenue" class="text-2xl font-bold text-green-900">₹0</p>
                </div>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                    <p class="text-sm text-yellow-700 font-medium">Total Due</p>
                    <p id="summary-due" class="text-2xl font-bold text-yellow-900">₹0</p>
                </div>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                    <p class="text-sm text-blue-700 font-medium">Paid Transactions</p>
                    <p id="summary-paid-count" class="text-2xl font-bold text-blue-900">0</p>
                </div>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <p class="text-sm text-red-700 font-medium">Due Transactions</p>
                    <p id="summary-due-count" class="text-2xl font-bold text-red-900">0</p>
                </div>
            </div>

            <!-- Detailed Payments Table -->
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Detailed Transactions</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Rent</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Period</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody id="report-table-body" class="bg-white divide-y divide-gray-200">
                        <!-- Data rows will be inserted here by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
    
    <!-- Placeholder for when no report is generated -->
    <div id="report-placeholder" class="bg-white rounded-xl shadow-sm border p-6 min-h-[400px] flex items-center justify-center">
        <div class="text-center text-gray-500">
            <i data-lucide="clipboard-list" class="w-16 h-16 mx-auto text-gray-400"></i>
            <h3 class="mt-2 text-xl font-semibold text-gray-800">No Report Generated</h3>
            <p class="mt-1">Select a month and year, then click "Generate Report" to view data.</p>
        </div>
    </div>
</div>
<script>
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>
