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
        <h1 class="text-3xl font-bold text-gray-800">Properties</h1>
        <a href="add_properties_form.php" class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <i data-lucide="plus-circle" class="w-5 h-5"></i>
            Add Property
        </a>
    </div>

    <!-- Search, Dropdown, and Filter Controls -->
    <div class="flex flex-col md:flex-row items-center gap-4 mb-6 bg-white p-4 rounded-xl shadow-sm border">
        <!-- Search Bar -->
        <div class="relative flex-grow w-full md:w-auto">
            <input type="text" id="property-search-input" placeholder="Search by name or city..."
                class="w-full pl-4 pr-10 py-3 text-base border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition outline-none" />
            <i data-lucide="search" class="absolute top-1/2 right-4 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
        </div>

        <!-- Property Type Dropdown -->
        <div class="flex items-center gap-2">
            <label for="property-type-select" class="text-sm font-medium text-gray-600">Type:</label>
            <select id="property-type-select" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="All">All Types</option>
                <option value="1 BHK">1 BHK</option>
                <option value="2 BHK">2 BHK</option>
                <option value="3 BHK">3 BHK</option>
                <option value="villa">Villa</option>
                <option value="flat">Flat</option>
            </select>
        </div>

        <!-- Status Filter Buttons -->
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-sm font-medium text-gray-600 mr-2">Status:</span>
            <button data-status="All" class="filter-status-btn px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white shadow-md">All</button>
            <button data-status="Available" class="filter-status-btn px-4 py-2 text-sm font-medium rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300">Available</button>
            <button data-status="Booked" class="filter-status-btn px-4 py-2 text-sm font-medium rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300">Booked</button>
        </div>
    </div>

    <!-- Properties Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rent</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="property-list" class="bg-white divide-y divide-gray-200">
                <!-- Property rows will be loaded here by AJAX -->
            </tbody>
        </table>
    </div>
</div>
<script>lucide.createIcons();</script>