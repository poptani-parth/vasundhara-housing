<?php
session_start();

// Ensure database connection is included (assuming it contains necessary credentials and PDO object)
include '../database.php';


function getImageUrl($path)
{
    if (!empty($path)) {
        $filename = basename($path); // get just the file name
        $relativePath = '../uploads/profile_photos/' . $filename;
        $absolutePath = '../uploads/profile_photos/' . $filename;

        if (file_exists($absolutePath)) {
            return $relativePath;
        }
    }
    return '../assets/images/not_found.png'; 
}
if (!isset($_SESSION['tenant_id']) || !isset($_SESSION['type_tenant']) || $_SESSION['type_tenant'] !== 'tenant') {
    header('Location: ../login.php');
    exit();
} 

// Handle Logout Action early to prevent any HTML output before redirection
if (isset($_GET['action']) && $_GET['action'] === 'logout') {

    $_SESSION['toast_message'] = 'You have been successfully logged out.';
    $_SESSION['toast_type'] = 'success';

    // Unset only tenant-specific session variables to avoid logging out the admin.
    unset($_SESSION['tenant_id']);
    unset($_SESSION['type_tenant']);
    unset($_SESSION['tenant_name']);
    header('Location: ../index.php');
    exit();
}

// Initialize user details from session with default values
$userName = $_SESSION['user_name'] ?? 'Tenant';
$userEmail = $_SESSION['user_email'] ?? 'user@example.com';
$userId = $_SESSION['tenant_id'] ?? 0;
$data = "select * from tenants where tenant_id = ?";
$stmt = mysqli_prepare($conn, $data);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tenantDetails = mysqli_fetch_assoc($result);

if (!$tenantDetails) {
    // This block executes if the tenant_id from the session does not exist in the database.
    // This can happen if the admin deletes the tenant while they are logged in.

    // Set a message for the user.
    $_SESSION['toast_message'] = 'Your account could not be found. Please log in again or contact support.';
    $_SESSION['toast_type'] = 'error';

    // Cleanly log the user out.
    session_unset();
    session_destroy();

    header('Location: ../login.php');
    exit();
}

// Handle toast message from previous page (e.g., login success, logout success)
$toastMessage = '';
$toastType = '';
if (isset($_SESSION['toast_message'])) {
    $toastMessage = $_SESSION['toast_message'];
    $toastType = $_SESSION['toast_type'] ?? 'success'; // Default toast type to 'success'
    unset($_SESSION['toast_message']); // Clear message after retrieving
    unset($_SESSION['toast_type']);    // Clear type after retrieving
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard - Vasundhara Housing</title>
    <link rel="stylesheet" href="../assets/fonts/Poppins.css">
    <script src="../assets/js/html2pdf.bundle.min.js"></script>
    <script src="../assets/js/tailwind.js"></script>
    <link href="../assets/css/tailwind.css" rel="stylesheet"/>
    <link href="../assets/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="./style.css">
    <link href="../assets/RemixIcon-master/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        /* Custom scrollbar for a cleaner look */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #64748b;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        body {
            font-family: 'Poppins', sans-serif;
        }

        @keyframes slide-up-fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-up-fade-in {
            animation: slide-up-fade-in 0.5s ease-out forwards;
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
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        .rotate-180 {
            transform: rotate(180deg);
        }

        .sidebar-item:hover {
            background-color: #e0e7ff; /* Light blue background on hover */
            color: #1d4ed8;            /* Dark blue text on hover */
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }

        /* <-- ADDED: active (clicked) sidebar item styles --> */
        .sidebar-item.active {
            background-color: #e0e7ff;
            color: #1d4ed8;
            font-weight: 600;
            transition: background 0.2s, color 0.2s;
            /* box-shadow: inset 4px 0 0 #1d4ed8;*/ /* left accent bar */
        }
        .sidebar-item.active svg {
            color: #1d4ed8 !important;
        }
        .sidebar-item.active span {
            color: inherit;
        }
    </style>
</head>

<body class="bg-gray-100 antialiased">

    <div class="flex h-screen bg-gray-100">
        <aside class="sidebar w-64 bg-white shadow-lg flex flex-col text-gray-700 transition-all duration-300">
            <div class="flex items-center justify-center h-20 border-b">
                <span class="text-xl font-extrabold text-blue-600">Vasundhara Housing</span>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="#" data-page="tenancy-details"
                    class="sidebar-item flex items-center p-3 rounded-lg transition-colors duration-200 active">
                    <svg class="w-6 h-6 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <span>Tenancy Details</span>
                </a>
                <a href="#" data-page="pay-rent"
                    class="sidebar-item flex items-center p-3 rounded-lg transition-colors duration-200">
                    <svg class="w-6 h-6 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <span>Pay Rent</span>
                </a>
                <a href="#" data-page="maintenance"
                    class="sidebar-item flex items-center p-3 rounded-lg transition-colors duration-200">
                    <svg class="w-6 h-6 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                        </path>
                    </svg>
                    <span>Maintenance</span>
                </a>
                <a href="#" data-page="payment_history"
                    class="sidebar-item flex items-center p-3 rounded-lg transition-colors duration-200">
                    <svg class="w-6 h-6 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z">
                        </path>
                    </svg>
                    <span>Payment History</span>
                </a>
                <a href="#" data-page="notices"
                    class="sidebar-item flex items-center p-3 rounded-lg transition-colors duration-200">
                    <svg class="w-6 h-6 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                    <span>Notices</span>
                </a>
                <a href="#" data-page="lease-documents"
                    class="sidebar-item flex items-center p-3 rounded-lg transition-colors duration-200">
                    <svg class="w-6 h-6 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span>Documents</span>
                </a>
                <a href="#" data-page="property-details"
                    class="sidebar-item flex items-center p-3 rounded-lg transition-colors duration-200">
                    <svg class="w-6 h-6 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    <span>Property Details</span>
                </a>
                <a href="#" data-page="update-profile"
                    class="sidebar-item flex items-center p-3 rounded-lg transition-colors duration-200">
                    <svg class="w-6 h-6 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Update Profile</span>
                </a>
            </nav>
            <div class="px-4 py-6 border-t">
                <a href="?action=logout"
                    class="sidebar-item flex items-center p-3 rounded-lg transition-colors duration-200">
                    <svg class="w-6 h-6 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center p-6 bg-white border-b">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
                    <p class="text-sm text-gray-500">Welcome back, <span id="header-user-name"><?= $tenantDetails['tenant_name'] ?? 'Tenant'; ?></span>!</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center overflow-hidden">

                        <img class="h-10 w-10 rounded-full object-cover" id="avatar-img"
                            src="<?php echo getImageUrl($tenantDetails['profile_photo']); ?>" alt="User avatar">
                        <div class="ml-3 hidden md:block">
                            <p class="text-sm font-medium text-gray-800" id="profile-display-name"><?= $tenantDetails['tenant_name'] ?? 'Tenant'; ?></p>
                            <p class="text-xs text-gray-500">Tenant</p>
                        </div>
                    </div>
                </div>
            </header>
            <main id="content-main" class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div id="loading-overlay" class="fixed inset-0 bg-white bg-opacity-70 flex items-center justify-center z-50 transition-opacity duration-500">
                    <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"></div>
                </div>
            </main>
        </div>
    </div>

    <!-- Annual Statement Modal -->
    <div id="annual-statement-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-3xl flex flex-col" style="height: 90vh;">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Annual Payment Statement</h3>
                <button id="annual-statement-close-btn" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <form id="annual-statement-form" class="flex items-end gap-4">
                    <div>
                        <label for="statement-year-select" class="block text-sm font-medium text-gray-700">Select Year</label>
                        <select id="statement-year-select" name="year" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"></select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700">Generate Preview</button>
                </form>
            </div>
            <div id="annual-statement-preview-area" class="flex-grow p-6 bg-gray-100 overflow-y-auto"><div class="text-center text-gray-500">Please select a year and generate a preview.</div></div>
            <div class="p-4 border-t flex justify-end gap-3">
                <button type="button" id="annual-statement-cancel-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Close</button>
                <button type="button" id="annual-statement-download-btn" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 hidden"><i class="ri-download-2-line mr-2"></i>Download PDF</button>
            </div>
        </div>
    </div>

    <!-- Re-apply Agreement Modal -->
    <div id="reapply-agreement-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-lg relative animate-slide-up-fade-in p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Re-apply for New Agreement</h3>
            <p class="text-sm text-gray-500 mb-4">Your new agreement will start on <strong id="new-agreement-start-date-display"></strong>. Please select a new end date.</p>
            <form id="reapply-agreement-form">
                <input type="hidden" name="old_agreement_id" id="reapply-old-agreement-id">
                <div class="space-y-4">
                    <div>
                        <label for="reapply-duration" class="block text-sm font-medium text-gray-700">Agreement Duration</label>
                        <select id="reapply-duration" name="duration_months" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                            <option value="11" selected>11 Months</option>
                            <option value="6">6 Months</option>
                            <option value="custom">Custom Date</option>
                        </select>
                    </div>
                    <div>
                        <label for="reapply-new-end-date" class="block text-sm font-medium text-gray-700">New End Date</label>
                        <input type="date" id="reapply-new-end-date" name="new_end_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" id="reapply-agreement-cancel-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Submit Re-application</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lease Termination Modal -->
    <div id="lease-termination-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-lg relative animate-slide-up-fade-in p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Request Lease Termination</h3>
            <p class="text-sm text-gray-500 mb-4">You are requesting to terminate your lease early. Please provide a reason. The admin will be notified and will contact you regarding the process and any applicable refund calculations.</p>
            <form id="lease-termination-form">
                <div class="space-y-4">
                    <div>
                        <label for="termination_reason" class="block text-sm font-medium text-gray-700">Reason for Leaving</label>
                        <textarea id="termination_reason" name="reason" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" placeholder="Please explain why you are requesting to terminate the lease..."></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" id="lease-termination-cancel-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Submit Termination Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lease Extension Modal -->
    <div id="lease-extension-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-lg relative animate-slide-up-fade-in p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Request Lease Modification</h3>
            <p class="text-sm text-gray-500 mb-4">Your current lease ends on <strong id="current-lease-end-date-display"></strong>. Select a new end date to request a change.</p>
            <form id="lease-extension-form">
                <div class="space-y-4">
                    <div>
                        <label for="new_end_date" class="block text-sm font-medium text-gray-700">New Lease End Date</label>
                        <input type="date" id="new_end_date" name="new_end_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                        <p id="lease-extension-error" class="text-red-500 text-xs mt-1 hidden"></p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" id="lease-extension-cancel-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col items-end gap-2">
    </div>

    <!-- Document Preview Modal -->
    <div id="document-preview-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-11/12 h-5/6 max-w-4xl flex flex-col">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Document Preview</h3>
                <div>
                    <button id="doc-close-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 ml-2">Close</button>
                </div>
            </div>
            <div class="flex-grow p-2 bg-gray-200">
                <iframe id="doc-preview-iframe" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="image-preview-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-3xl relative animate-slide-up-fade-in">
            <button id="image-close-btn" class="absolute -top-3 -right-3 bg-white rounded-full p-1 text-gray-600 hover:text-gray-900 shadow-lg z-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="p-4">
                <img id="image-preview-src" src="" alt="Document Preview" class="rounded-lg mx-auto max-w-full max-h-[85vh]">
            </div>
        </div>
    </div>

    <script>
        (function() {
            'use strict';

            // --- 1. STATE & ELEMENTS ---
            const state = {
                activePage: null,
                debounceTimer: null,
            };

            const elements = {
                // Main layout
                sidebar: document.querySelector('.sidebar'),
                sidebarItems: document.querySelectorAll('.sidebar-item'),
                contentMain: document.getElementById('content-main'),
                loadingOverlay: document.getElementById('loading-overlay'),

                // Header
                headerUserName: document.getElementById('header-user-name'),
                profileDisplayName: document.getElementById('profile-display-name'),
                avatarImg: document.getElementById('avatar-img'),

                // Modals
                toastContainer: document.getElementById('toast-container'),
                docPreviewModal: document.getElementById('document-preview-modal'),
                docPreviewIframe: document.getElementById('doc-preview-iframe'),
                docCloseBtn: document.getElementById('doc-close-btn'),
                imagePreviewModal: document.getElementById('image-preview-modal'),
                imagePreviewSrc: document.getElementById('image-preview-src'),
                imageCloseBtn: document.getElementById('image-close-btn'),
                leaseExtensionModal: document.getElementById('lease-extension-modal'),
                currentLeaseEndDateDisplay: document.getElementById('current-lease-end-date-display'),
                newEndDateInput: document.getElementById('new_end_date'),
                leaseExtensionForm: document.getElementById('lease-extension-form'),
                leaseExtensionCancelBtn: document.getElementById('lease-extension-cancel-btn'),
                leaseExtensionError: document.getElementById('lease-extension-error'),
                annualStatementModal: document.getElementById('annual-statement-modal'),
                annualStatementForm: document.getElementById('annual-statement-form'),
                annualStatementPreviewArea: document.getElementById('annual-statement-preview-area'),
                annualStatementDownloadBtn: document.getElementById('annual-statement-download-btn'),
                annualStatementCloseBtn: document.getElementById('annual-statement-close-btn'),
                annualStatementCancelBtn: document.getElementById('annual-statement-cancel-btn'),
                leaseTerminationModal: document.getElementById('lease-termination-modal'),
                leaseTerminationForm: document.getElementById('lease-termination-form'),
                leaseTerminationCancelBtn: document.getElementById('lease-termination-cancel-btn'),
                reapplyAgreementModal: document.getElementById('reapply-agreement-modal'),
                reapplyAgreementForm: document.getElementById('reapply-agreement-form'),
                reapplyAgreementCancelBtn: document.getElementById('reapply-agreement-cancel-btn'),
                newAgreementStartDateDisplay: document.getElementById('new-agreement-start-date-display'),
                reapplyDurationSelect: document.getElementById('reapply-duration'),
                reapplyNewEndDateInput: document.getElementById('reapply-new-end-date'),
                reapplyOldAgreementIdInput: document.getElementById('reapply-old-agreement-id'),
            };

            // --- 2. UTILITY & HELPER FUNCTIONS ---
            const showToast = (message, type = 'success') => {
                if (!elements.toastContainer) return;
                let iconSVG, bgColor, textColor, progressColor;
                switch (type) {
                    case 'error':
                        bgColor = 'bg-red-500';
                        textColor = 'text-white';
                        progressColor = 'bg-red-700';
                        iconSVG = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                        break;
                    case 'info':
                        bgColor = 'bg-blue-500';
                        textColor = 'text-white';
                        progressColor = 'bg-blue-700';
                        iconSVG = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                        break;
                    default:
                        bgColor = 'bg-green-500';
                        textColor = 'text-white';
                        progressColor = 'bg-green-700';
                        iconSVG = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                }
                const toast = document.createElement('div');
                toast.className = `relative flex items-center gap-4 p-4 pr-8 rounded-lg shadow-lg ${bgColor} ${textColor} w-full max-w-xs overflow-hidden animate-slide-up-fade-in`;
                toast.innerHTML = `
                    <div class="flex-shrink-0">${iconSVG}</div>
                    <div class="flex-1 text-sm font-medium">${message}</div>
                    <button class="absolute top-1 right-1 text-white/70 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    <div class="absolute bottom-0 left-0 h-1 ${progressColor}" style="animation: progress 5s linear forwards;"></div>`;
                elements.toastContainer.prepend(toast);
                const removeToast = () => {
                    toast.style.animation = 'fadeOut 0.5s ease-out forwards';
                    toast.addEventListener('animationend', () => toast.remove());
                };
                toast.querySelector('button').addEventListener('click', removeToast);
                setTimeout(removeToast, 5000);
            };

            const handleSlider = (target) => {
                const slider = document.getElementById('property-slider');
                if (!slider) return;

                const track = slider.querySelector('.slider-track');
                const slides = Array.from(track.children);
                const dots = Array.from(slider.querySelectorAll('.slider-dot'));
                const slideCount = slides.length;
                let currentIndex = parseInt(track.dataset.currentIndex || '0');

                if (target.matches('.slider-prev-btn')) {
                    currentIndex = (currentIndex - 1 + slideCount) % slideCount;
                } else if (target.matches('.slider-next-btn')) {
                    currentIndex = (currentIndex + 1) % slideCount;
                } else if (target.matches('.slider-dot')) {
                    currentIndex = parseInt(target.dataset.slideTo);
                }

                track.style.transform = `translateX(-${currentIndex * 100}%)`;
                track.dataset.currentIndex = currentIndex;

                dots.forEach((dot, index) => {
                    dot.classList.toggle('bg-white', index === currentIndex);
                    dot.classList.toggle('bg-white/50', index !== currentIndex);
                });
            };

            const showLoading = () => {
                elements.loadingOverlay.style.display = 'flex';
                setTimeout(() => elements.loadingOverlay.style.opacity = '1', 10);
            };

            const hideLoading = () => {
                elements.loadingOverlay.style.opacity = '0';
                setTimeout(() => elements.loadingOverlay.style.display = 'none', 500);
            };

            const showImageModal = (src) => {
                if (!elements.imagePreviewModal || !elements.imagePreviewSrc) return;
                elements.imagePreviewSrc.src = src;
                elements.imagePreviewModal.classList.remove('hidden');
                elements.imagePreviewModal.classList.add('flex');
            };

            const hideImageModal = () => {
                if (!elements.imagePreviewModal) return;
                elements.imagePreviewModal.classList.add('hidden');
                elements.imagePreviewModal.classList.remove('flex');
                elements.imagePreviewSrc.src = '';
            };

            const showDocModal = (blobUrl) => {
                if (!elements.docPreviewModal) return;
                elements.docPreviewIframe.src = blobUrl;
                elements.docPreviewModal.classList.remove('hidden');
                elements.docPreviewModal.classList.add('flex');
            };

            const hideDocModal = () => {
                if (!elements.docPreviewModal) return;
                elements.docPreviewIframe.src = 'about:blank';
                elements.docPreviewModal.classList.add('hidden');
                elements.docPreviewModal.classList.remove('flex');
            };

            const showLeaseExtensionModal = (currentEndDate, startDate) => {
                if (!elements.leaseExtensionModal) return;

                // To avoid timezone issues when displaying the date, parse it as UTC.
                const [year, month, day] = currentEndDate.split('-').map(Number);
                const dateObj = new Date(Date.UTC(year, month - 1, day));
                const formattedDate = dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });

                elements.currentLeaseEndDateDisplay.textContent = formattedDate;
                // The input type="date" expects a 'YYYY-MM-DD' string, which currentEndDate already is.
                elements.newEndDateInput.value = currentEndDate;

                // NEW: Set max date to 11 months from start date
                if (startDate) {
                    const startDateObj = new Date(startDate);
                    startDateObj.setMonth(startDateObj.getMonth() + 11);
                    const maxDate = startDateObj.toISOString().split('T')[0];
                    elements.newEndDateInput.max = maxDate;
                }

                elements.leaseExtensionModal.classList.remove('hidden');
                elements.leaseExtensionModal.classList.add('flex');
            };

            const hideLeaseExtensionModal = () => {
                if (!elements.leaseExtensionModal) return;
                elements.leaseExtensionModal.classList.add('hidden');
                elements.leaseExtensionModal.classList.remove('flex');
                elements.leaseExtensionForm.reset();
                elements.leaseExtensionError.classList.add('hidden');
            };

            const showAnnualStatementModal = () => {
                if (!elements.annualStatementModal) return;

                const yearSelect = document.getElementById('statement-year-select');
                yearSelect.innerHTML = ''; // Clear existing options
                const currentYear = new Date().getFullYear();
                // select year functionality first 10 year  use proper loop here
                for (let i = 0; i < 10; i++) {
                    const year = currentYear - i;
                    const option = document.createElement('option');
                    option.value = year;
                    option.textContent = year;
                    yearSelect.appendChild(option);
                }
                
                elements.annualStatementPreviewArea.innerHTML = '<div class="text-center text-gray-500">Please select a year and generate a preview.</div>';
                elements.annualStatementDownloadBtn.classList.add('hidden');
                elements.annualStatementModal.classList.remove('hidden');
                elements.annualStatementModal.classList.add('flex');
            };

            const hideAnnualStatementModal = () => {
                if (!elements.annualStatementModal) return;
                elements.annualStatementModal.classList.add('hidden');
                elements.annualStatementModal.classList.remove('flex');
            };

            const showLeaseTerminationModal = () => {
                if (!elements.leaseTerminationModal) return;
                elements.leaseTerminationModal.classList.remove('hidden');
                elements.leaseTerminationModal.classList.add('flex');
            };

            const hideLeaseTerminationModal = () => {
                if (!elements.leaseTerminationModal) return;
                elements.leaseTerminationModal.classList.add('hidden');
                elements.leaseTerminationModal.classList.remove('flex');
                elements.leaseTerminationForm.reset();
            };

            const showReapplyModal = (agreementId, oldEndDate) => {
                if (!elements.reapplyAgreementModal) return;

                const oldEndDateObj = new Date(oldEndDate);
                const newStartDateObj = new Date(oldEndDateObj.setDate(oldEndDateObj.getDate() + 1));
                
                const formattedStartDate = newStartDateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
                elements.newAgreementStartDateDisplay.textContent = formattedStartDate;
                elements.reapplyOldAgreementIdInput.value = agreementId;

                const updateEndDate = () => {
                    const duration = elements.reapplyDurationSelect.value;
                    if (duration === 'custom') {
                        elements.reapplyNewEndDateInput.readOnly = false;
                    } else {
                        elements.reapplyNewEndDateInput.readOnly = true;
                        const months = parseInt(duration, 10);
                        const newEndDateObj = new Date(newStartDateObj);
                        newEndDateObj.setMonth(newEndDateObj.getMonth() + months);
                        elements.reapplyNewEndDateInput.value = newEndDateObj.toISOString().split('T')[0];
                    }
                };

                elements.reapplyDurationSelect.addEventListener('change', updateEndDate);
                updateEndDate(); // Set initial value

                elements.reapplyAgreementModal.classList.remove('hidden');
                elements.reapplyAgreementModal.classList.add('flex');
            };

            const hideReapplyModal = () => {
                if (!elements.reapplyAgreementModal) return;
                elements.reapplyAgreementModal.classList.add('hidden');
                elements.reapplyAgreementModal.classList.remove('flex');
                elements.reapplyAgreementForm.reset();
            };


            // --- 3. CORE LOGIC & AJAX FUNCTIONS ---
            const loadPage = (page) => {
                showLoading();
                fetch(`${page}.php`, {
                    cache: 'no-cache' // Prevent browser from loading stale content
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.text();
                    })
                    .then(html => {
                        elements.contentMain.innerHTML = html;
                        state.activePage = page;
                        localStorage.setItem('lastTenantPage', page);
                    })
                    .catch(error => {
                        console.error('Error loading page:', error);
                        elements.contentMain.innerHTML = `<div class="p-6 text-center text-red-500">Error loading content. Please try again.</div>`;
                        showToast('Error loading page.', 'error');
                    })
                    .finally(hideLoading);
            };

            const loadNotices = (searchTerm = '') => {
                const container = document.getElementById('notice-list-container');
                if (!container) return;
                container.innerHTML = `<div class="flex justify-center items-center h-48"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-600"></div></div>`;
                const formData = new FormData();
                formData.append('is_ajax_request', '1');
                formData.append('search', searchTerm);
                fetch('notices.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.ok ? res.text() : Promise.reject('Failed to load notices'))
                    .then(html => container.innerHTML = html)
                    .catch(error => {
                        console.error('Error loading notices:', error);
                        container.innerHTML = `<div class="text-center text-red-500 p-8 bg-red-50 rounded-lg"><p>Error loading notices. Please try again.</p></div>`;
                    });
            };

            const loadPaymentHistory = (month = null) => {
                const container = document.getElementById('payment-list-container');
                if (!container) return;
                container.innerHTML = `<div class="flex justify-center items-center h-48"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-600"></div></div>`;
                let url = `payment_history.php?is_ajax=1${month ? '&month=' + month : ''}`;
                fetch(url)
                    .then(res => res.json())
                    .then(payments => {
                        let html = '';
                        if (payments.length === 0) {
                            html = `<div id="no-payments-message" class="text-center text-gray-500 p-8 bg-gray-50 rounded-lg border-2 border-dashed">
                                        <i class="ri-file-list-3-line text-4xl text-gray-400"></i>
                                        <p class="mt-4">No payments found for the selected period.</p>
                                    </div>`;
                        } else {
                            payments.forEach(payment => {
                                const paymentDate = new Date(payment.payment_date).toLocaleDateString('en-GB', {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric'
                                });
                                const amountFormatted = new Intl.NumberFormat('en-IN', {
                                    style: 'currency',
                                    currency: 'INR'
                                }).format(payment.amount);
                                const paymentPeriodDisplay = new Date(payment.payment_period).toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
                                html += `<div class="bg-white border border-gray-200 rounded-lg p-4 flex items-center justify-between gap-4">
                                            <div class="flex items-center gap-4">
                                                <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-green-100">
                                                    <i class="ri-secure-payment-line text-2xl text-green-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-800">Rent for ${payment.property_name} - ${paymentPeriodDisplay}</p>
                                                    <p class="text-sm text-gray-500">Paid on ${paymentDate}</p>
                                                    <p class="text-xs text-gray-400 mt-1">${payment.remark || ''}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-bold text-green-600">${amountFormatted}</p>
                                                <button data-id="${payment.payment_id}" class="print-invoice-btn mt-1 text-xs text-blue-600 hover:underline">View Invoice</button>
                                            </div>
                                        </div>`;
                            });
                        }
                        container.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error loading payment history:', error);
                        container.innerHTML = `<div class="text-center text-red-500 p-8 bg-red-50 rounded-lg"><p>Error loading payment history. Please try again.</p></div>`;
                    });
            };

            const printInvoice = (paymentId) => {
                showToast('Generating invoice...', 'info');
                fetch(`../admin/getInvoiceData.php?payment_id=${paymentId}`)
                    .then(res => res.json())
                    .then(response => {
                        if (!response.success) throw new Error(response.message || 'Failed to fetch invoice data.');
                        const invoiceData = response.data;
                        const formatCurrency = (num) => `₹${Number(num).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                        const fullHTML = `
                            <html>
                            <head>
                                <title>Invoice #${invoiceData.payment_id}</title>
                                <style> @page { size: A4; margin: 0.6in; } body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 10pt; color: #333; } table { width: 100%; border-collapse: collapse; } th, td { font-size: 9pt; } </style>
                            </head>
                            <body>
                                <table style=" border-collapse: collapse; margin-bottom: 2rem;">
                                    <tbody>
                                        <tr>
                                            <td style="vertical-align: top; width: 50%;">
                                                <img src="../admin/uploads/invoice-logo.png" alt="Logo" style="width: 10rem; height: auto;">
                                                <p style="font-size: 1rem; font-weight: bold; color: #1f2937; margin: 0;">Vasundhara Housing</p>
                                                <p style="font-size: 0.875rem; color: #4b5563; margin: 2px 0;">Vasundhara Housing, SG Highway, Ahmedabad, Gujarat, India</p>
                                                <p style="font-size: 0.875rem; color: #4b5563; margin: 2px 0;">info@vasundharahousing.com</p>
                                            </td>
                                            <td style="vertical-align: top; text-align: right; width: 5%;">
                                                <h1 style="font-size: 2.5rem; font-weight: bold; color: #1f2937; margin: 0 0 10px 0; padding-right: 10px;">INVOICE</h1>
                                                ${invoiceData.status !== 'Paid' ? `
                                                    <table style=" margin-bottom: 1rem;">
                                                        <thead style="color: white;">
                                                            <tr>
                                                                <th style="background-color: #dbeaff !important; font-size: 0.875rem; padding:12px 0px; color: #000 !important; -webkit-print-color-adjust: exact;">Amount Due</th>
                                                            </tr>
                                                            </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td style=" font-size: 1.2rem; font-weight: bold; padding: 12px 0; text-align: center; background-color: #f0fdf4;">${formatCurrency(invoiceData.amount)}</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>` : 
                                                            `<table style=" margin-bottom: 1rem;"><thead style="color: white;">
                                                                <tr>
                                                                    <th style="background-color: #dbeaff !important; font-size: 0.875rem; padding:12px 0px; color: #000 !important; -webkit-print-color-adjust: exact;">Amount Due</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td style=" font-size: 1.2rem; font-weight: bold; padding: 12px 0; text-align: center; background-color: #f0fdf4;">₹0</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>`}
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
                                            <td style=" font-size: 0.9rem;  padding: 12px 0rem; text-align: center; background-color: #f0fdf4;">${new Date().toLocaleDateString('en-GB')}</td>
                                        </tr>
                                    </tbody>
                                </table>
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
                                <table style="width: 100%; text-align: left; border-collapse: collapse; margin-bottom: 2rem;">
                                    <thead>
                                        <tr style="background-color: #dbeafe !important; color: #1e40af !important; -webkit-print-color-adjust: exact;">
                                        <th style="padding: 0.75rem;">Month</th>
                                        <th style="padding: 0.75rem;">Date Received</th>
                                        <th style="padding: 0.75rem;">Description</th>
                                        <th style="padding: 0.75rem; text-align: right;">Total</th>
                                        </tr>
                                    </thead><tbody>${invoiceData.items.map(item => `<tr style="border-bottom: 1px solid #e5e7eb;"><td style="padding: 0.75rem;">${new Date(item.payment_period).toLocaleDateString('en-GB', { month: 'long', year: 'numeric' })}</td><td style="padding: 0.75rem;">${new Date(item.payment_receive_date).toLocaleDateString('en-GB',  { day:'numeric',month: 'long', year: 'numeric' })}</td><td style="padding: 0.75rem;">${item.remark || `Rent for ${item.property_name}`}</td><td style="padding: 0.75rem; text-align: right;">${formatCurrency(item.amount)}</td></tr>`).join('')}</tbody>
                                </table>
                                <table style="width: 100%; margin-top: 2rem; border-top: 2px solid #e5e7eb; padding-top: 1rem;"><tbody><tr><td style="width: 50%; vertical-align: top; padding-top: 3.5rem; padding-right:1.5rem;"><p style="font-weight: 700; font-size: 1.1rem; color: #374151; margin-bottom: 0.5rem;">Payment Methods:</p><p style="font-size: 0.9rem; color: #6b7280; margin: 2px 0;">We Accept: UPI, Net Banking,<br> Credit card, etc..</p></td><td style="width: 50%; vertical-align: top; padding-top: 3.5rem;"><table style="width: 100%; border-collapse: collapse;"><tbody><tr><td style="padding: 4px 0; font-size: 0.875rem; color: #4b5563;">Subtotal:</td><td style="padding: 4px 0; font-size: 0.875rem; text-align: right;">${formatCurrency(invoiceData.amount)}</td></tr><tr><td style="padding: 4px 0; font-size: 0.875rem; color: #4b5563;">Tax (0%):</td><td style="padding: 4px 0; font-size: 0.875rem; text-align: right;">${formatCurrency(0)}</td></tr><tr><td style="padding-top: 1rem; font-weight: bold; font-size: 1.125rem; border-top: 2px solid #6b7280; margin-top: 1rem;">Total:</td><td style="padding: 1rem; text-align: right; justify-content: flex-end;  font-weight: bold; font-size: 1.125rem; border-top: 2px solid #6b7280; margin-top: 1rem;background-color: #dbeaff !important; -webkit-print-color-adjust: exact;">${formatCurrency(invoiceData.amount)}</td></tr></tbody></table></td></tr></tbody></table>
                            </body></html>`;
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
            };

            const generateAgreement = async (action = 'view', agreementId = null) => {
                const showFeedback = (message, type) => {
                    if (typeof showToast === 'function') {
                        showToast(message, type);
                    } else {
                        console.log(`[${type.toUpperCase()}]: ${message}`);
                    }
                };

                showFeedback('Generating your agreement...', 'info');

                const numberToWords = (num) => {
                    if (num === 0) return 'Zero';
                    if (!num || isNaN(num) || num < 0) return '';

                    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
                    const teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
                    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

                    const toWords = (n) => {
                        let str = '';
                        if (n >= 100) {
                            str += ones[Math.floor(n / 100)] + ' Hundred ';
                            n %= 100;
                        }
                        if (n >= 20) {
                            str += tens[Math.floor(n / 10)] + ' ';
                            n %= 10;
                        } else if (n >= 10) {
                            str += teens[n - 10] + ' ';
                            n = 0;
                        }
                        if (n > 0) {
                            str += ones[n] + ' ';
                        }
                        return str;
                    };

                    let numStr = String(num);
                    let result = '';
                    if (numStr.length > 7) { result += toWords(parseInt(numStr.slice(0, -7))) + 'Crore '; numStr = numStr.slice(-7); }
                    if (numStr.length > 5) { result += toWords(parseInt(numStr.slice(0, -5))) + 'Lakh '; numStr = numStr.slice(-5); }
                    if (numStr.length > 3) { result += toWords(parseInt(numStr.slice(0, -3))) + 'Thousand '; numStr = numStr.slice(-3); }
                    result += toWords(parseInt(numStr));

                    return (result.trim().replace(/\s+/g, ' '));
                };

                try {
                    const url = agreementId ? `getAgreementData.php?agreement_id=${agreementId}` : 'getAgreementData.php';
                    const res = await fetch(url);

                    if (!res.ok) {
                        throw new Error(`Network response was not ok, status: ${res.status}`);
                    }

                    const response = await res.json();

                    if (!response.success) {
                        throw new Error(response.message || 'Failed to retrieve agreement data.');
                    }

                    const data = response.data;
                    const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    }) : '__________';
                    const filename = `Rental_Agreement_${data.tenant_name.replace(/\s/g, '_')}.pdf`;

                    // Calculate agreement duration in months
                    let agreementMonths = 11; // Default to 11
                    let agreementDurationText = 'Eleven (11)';
                    if (data.starting_date && data.ending_date) {
                        const startDate = new Date(data.starting_date);
                        const endDate = new Date(data.ending_date);
                        if (endDate > startDate) {
                            let calculatedMonths = (endDate.getFullYear() - startDate.getFullYear()) * 12;
                            calculatedMonths -= startDate.getMonth();
                            calculatedMonths += endDate.getMonth();
                            // A lease within the same month is still for a period of 'One' month.
                            agreementMonths = calculatedMonths <= 0 ? 1 : calculatedMonths;
                            agreementDurationText = `${numberToWords(agreementMonths)} (${agreementMonths})`;
                        }
                    }

                    // --- NEW HEADER STRUCTURE ---
                    const headerHTML = `
                       <div style="text-align: left; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
                            <div style="display: flex; flex-direction: row; gap: 40px; align-items: center;">
                                <img src="../admin/uploads/invoice-logo.png" alt="Vasundhara Housing Logo" style="width: 15rem; height: auto; margin-bottom: 10px;">
                                <div style="margin-left: 20px;">
                                    <h2 style="font-size: 18pt; margin: 0; padding: 0;">Vasundhara Housing</h2>
                                    <p style="margin: 0; font-size: 10pt;">Vasundhara Housing, SG Highway, Ahmedabad, Gujarat, India</p>
                                    <p style="margin: 0; font-size: 10pt;">Email: info@vasundharahousing.com | Contact: 7201918502</p>
                                </div>
                            </div>
                        </div>
                    `;
                    // --- END OF NEW HEADER ---

                    const agreementContentHTML = `
                       <h1 style="text-align: center; font-size: 16pt; font-weight: bold; margin-bottom: 30px; text-decoration: underline;">RENT AGREEMENT</h1>

                        <p>This Rent Agreement is made on this <strong>${formatDate(data.starting_date)}</strong> by <strong>Vasundhara Housing</strong>, Add: <strong>Vasundhara Housing, SG Highway, Ahmedabad, Gujarat, India</strong>. Herein after called the Lessor / Owner, Party Of the first part </p>

                        <p style="text-align: center; font-weight: bold; margin: 15px 0;">AND</p>

                        <p><strong>${data.tenant_name || '_____________________________'}</strong>, S/o <strong>${data.fatherName || '__________'}</strong> called Lessee/Tenant, Party of the Second Part

                        That the expression of the term, Lessor/Owner and the Lessee/Tenant Shall mean and include their legal heirs successors, assigns, representative etc. Whereas the Lessor/Owner is the owner and in possession of the property Name: <strong>${data.property_name || '_________________________________________________________________________________'}</strong> and has agreed to let out the ${data.pro_type || '_____________________'}, Badrooms : <strong>${data.bed || '______'}</strong>,Bathrooms : <strong>${data.bath || '______'}</strong>, Kitchens : <strong>1</strong> Set on said property, to the Lessee/Tenant and the Lessee/Tenant has agreed to take the same on rent of Rs. <strong>${Number(data.month_rent_no).toLocaleString('en-IN') || '______'}/-</strong> (<strong>${data.month_rent_word || 'In words'}</strong>) per month.</p>

                        <h3 style="text-align: center; font-weight: bold; margin-top: 20px; text-decoration: underline;">NOW THIS RENT AGREEMENT WITNESSETH AS UNDER:-</h3>

                        <ol style="list-style-type: decimal; padding-left: 25px; margin-top: 15px;">
                            <li style="margin-bottom: 10px;">That the Tenant/Lessee shall pay as the monthly rent of RS. <strong>${Number(data.month_rent_no).toLocaleString('en-IN') || '_________'}</strong>/- (<strong>${data.month_rent_word || 'In words'}</strong>) per month, excluding electricity and water charge.</li>
                            <li style="margin-bottom: 10px;">That the Tenant /Lessee shall not sub-let any part of the above said demised premised premises to anyone else under any circumstances without the consent of Owner.</li>
                            <li style="margin-bottom: 10px;">That the Tenant / Lessee shall abide by all the bye - laws, rules and regulation, of the local authorities in respect of the demised premises and shall not do any illegal activities in the said demised premises.</li>
                            <li style="margin-bottom: 10px;">That this Lease is granted for a period of ${agreementDurationText} months only commencing from <strong>${formatDate(data.starting_date)}</strong> and ending on <strong>${formatDate(data.ending_date)}</strong>. This lease can be extended further by both the parties with their mutual consent on the basis of prevailing rental value in the market.</li>
                            <li style="margin-bottom: 10px;">That the Lessee shall pay Electricity & Water charge as per the proportionate consumption of the meter to the Lessor /Owner.</li>
                            <li style="margin-bottom: 10px;">That the Tenant/Lessee shall not be entitled to make structure in the rented premises except the installation of temporary decoration, wooden partition/ cabin, air - conditioners etc. without the prior consent of the owner.</li>
                            <li style="margin-bottom: 10px;">That the Tenant/lessee can neither make addition/alteration in the said premises without the written consent of the owner, nor the lessee can sublet part or entire premises to any person(s)/firm(s)/company(s).</li>
                        </ol>

                        <ol start="8" style="list-style-type: decimal; padding-left: 25px; margin-top: 15px;">
                            <li style="margin-bottom: 10px;">That the Tenant/Lessee shall permit the Lessor/Owner or his Authorized agent to enter in to the said tenanted premises for inspection/general checking or to carry out the repair work, at any reasonable time.</li>
                            <li style="margin-bottom: 10px;">That the Tenant/Lessee shall keep the said premises in clean & hygienic condition and shall not do or causes to be done any act which may be a nuisance to other.</li>
                            <li style="margin-bottom: 10px;">That the Tenant/Lessees shall carry on all day to day minor repairs at his/her own cost.</li>
                            <li style="margin-bottom: 10px;">That this Agreement may be terminated before the expiry of this tenancy period by serving One month prior notice by either party for this intention.</li>
                            <li style="margin-bottom: 10px;">That the Lessee shall use the above said premises for Residential Purpose Only.</li>
                            <li style="margin-bottom: 10px;">That the Lessee/Tenant Shall not store/Keep any offensive, dangerous, explosive or highly Inflammable articles in the said premises and shall not use the same for any unlawful activities.</li>
                            <li style="margin-bottom: 10px;">That the Lessee shall pay the one monthâ€™s advance rent to the Lessor the same shall be adjusted in monthly rent.</li>
                            <li style="margin-bottom: 10px;">That both the parties have read over and understood all the contents of this agreement and have signed the same without any force or pressure from any side.</li>
                        </ol>
                        
                        <h3 style="font-weight: bold; margin-top: 30px;">WITNESSES:-</h3>
                        <ol style="list-style-type: decimal; padding-left: 25px; margin-top: 15px;">
                            <li style="margin-bottom: 10px;"><strong>${data.witness1_name || '_________________'}</strong></li>
                            <li style="margin-bottom: 10px;"><strong>${data.witness2_name || '_________________'}</strong></li>
                        </ol>
                        
                        <div style="margin: 60px 0; display: grid; grid-template-columns: 1fr 1fr; gap: 50px; text-align: center;">
                            <div><p><strong>Vasundhara Housing</strong><p style="margin-bottom:40px;">(name of the proposed Company)</p></p></div>
                            <div><p><strong>${data.tenant_name || '_________________'}</strong></p><p style="margin-bottom:5px;">(name of the landlord)</p></div>
                        </div>
                    `;

                    const agreementHTML = `<div style="padding: 0px; font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6;">${headerHTML}${agreementContentHTML}</div>`;

                    const opt = {
                        margin: 0.75,
                        filename: filename,
                        image: {
                            type: 'jpeg',
                            quality: 0.98
                        },
                        html2canvas: {
                            scale: 2,
                            useCORS: true
                        },
                        jsPDF: {
                            unit: 'in',
                            format: 'a4',
                            orientation: 'portrait'
                        }
                    };

                    const worker = html2pdf().from(agreementHTML).set(opt);

                    if (action === 'view') {
                        const bloburl = await worker.output('bloburl');
                        showDocModal(bloburl);
                        showFeedback('Agreement is ready to view.', 'success');
                    } else {
                        await worker.save();
                        showFeedback('Agreement has been downloaded.', 'success');
                    }

                } catch (error) {
                    console.error('Error generating agreement:', error);
                    showFeedback(error.message || 'An unexpected error occurred.', 'error');
                }
            };

            const downloadImage = (url, filename) => {
                showToast('Starting download...', 'info');
                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok.');
                        return response.blob();
                    })
                    .then(blob => {
                        const objectUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = objectUrl;
                        a.download = filename || 'downloaded-image.jpg';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(objectUrl);
                        document.body.removeChild(a);
                    })
                    .catch(error => {
                        console.error('Download error:', error);
                        showToast('Failed to download image.', 'error');
                    });
            };

            const handleProfileUpdate = (form) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;
                submitBtn.textContent = 'Updating...';
                submitBtn.disabled = true;

                const formData = new FormData(form);
                fetch('process-profile-update.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            if (data.profile_photo_url) {
                                elements.avatarImg.src = data.profile_photo_url;
                                const smallPreview = document.getElementById('profile-photo-preview-small');
                                if (smallPreview) smallPreview.src = data.profile_photo_url;
                            }
                            const newName = formData.get('tenant_name');
                            elements.headerUserName.textContent = newName;
                            elements.profileDisplayName.textContent = newName;
                        } else {
                            showToast(data.message || 'An unknown error occurred.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Profile update error:', error);
                        showToast('An error occurred. Please try again.', 'error');
                    })
                    .finally(() => {
                        submitBtn.textContent = originalBtnText;
                        submitBtn.disabled = false;
                    });
            };

            const generateAnnualStatementPreview = (year) => {
                if (!year) {
                    showToast('Please select a year.', 'error');
                    return;
                }
                const previewArea = elements.annualStatementPreviewArea;
                previewArea.innerHTML = '<div class="text-center text-gray-500">Generating preview...</div>';
                elements.annualStatementDownloadBtn.classList.add('hidden');

                fetch(`getAnnualStatementData.php?year=${year}`)
                    .then(res => res.json())
                    .then(response => {
                        if (!response.success) {
                            throw new Error(response.message || 'Failed to fetch statement data.');
                        }
                        const { tenantDetails, payments, period } = response.data;
                        if (payments.length === 0) {
                            previewArea.innerHTML = `<div class="text-center text-gray-500">No payments found for ${period}.</div>`;
                            return;
                        }

                        const formatCurrency = (num) => `₹${Number(num).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                        let totalAmount = payments.reduce((sum, item) => sum + parseFloat(item.amount), 0);

                        const headerHTML = `
                            <table style="width: 100%; border-collapse: collapse; margin-bottom: 2rem;">
                                <tbody>
                                    <tr>
                                        <td style="vertical-align: top; width: 50%;">
                                            <img src="../admin/uploads/invoice-logo.png" alt="Logo" style="width: 10rem; height: auto;">
                                            <p style="font-size: 1rem; font-weight: bold; color: #1f2937; margin: 0;">Vasundhara Housing</p>
                                            <p style="font-size: 0.875rem; color: #4b5563; margin: 2px 0;">Vasundhara Housing, SG Highway, Ahmedabad, Gujarat, India</p>
                                            <p style="font-size: 0.875rem; color: #4b5563; margin: 2px 0;">info@vasundharahousing.com</p>
                                        </td>
                                        <td style="vertical-align: top; text-align: right; width: 50%;">
                                            <h1 style="font-size: 2.5rem; font-weight: bold; color: #1f2937; margin: 0 0 10px 0;">ANNUAL STATEMENT</h1>
                                            <p style="font-size: 1.2rem; font-weight: bold; color: #4b5563;">For the Year ${period}</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr style="border: 1px solid gray; border-top: 2px solid #e5e7eb; margin-bottom: 1.5rem;">
                        `;

                        const partiesHTML = `
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                                <div>
                                    <p style="font-weight: 600; font-size: 0.875rem; color: #000;">Statement From:</p>
                                    <p style="color: #4b5563;">Vasundhara Housing</p>
                                    <p style="font-size: 0.875rem; color: #6b7280;">Vasundhara Housing, SG Highway, Ahmedabad, Gujarat, India</p>
                                </div>
                                <div>
                                    <p style="font-weight: 600; font-size: 0.875rem; color: #000;">Statement For:</p>
                                    <p style="color: #4b5563;">${tenantDetails.tenant_name}</p>
                                    <p style="font-size: 0.875rem; color: #6b7280;">${tenantDetails.tenant_address || 'Address not available'}</p>
                                </div>
                            </div>
                        `;

                        const itemsHTML = payments.map(item => {
                            return `
                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                    <td style="padding: 0.75rem;">${new Date(item.payment_period).toLocaleDateString('en-GB', { month: 'long', year: 'numeric' })}</td>
                                    <td style="padding: 0.75rem;">${new Date(item.payment_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })}</td>
                                    <td style="padding: 0.75rem;">${item.remark || `Rent for ${item.property_name}`}</td>
                                    <td style="padding: 0.75rem; text-align: right;">${formatCurrency(item.amount)}</td>
                                </tr>
                            `;
                        }).join('');

                        const tableHTML = `<table style="width: 100%; text-align: left; border-collapse: collapse; font-size: 0.875rem;"><thead><tr style="background-color: #f3f4f6;"><th style="padding: 0.75rem;">Payment For</th><th style="padding: 0.75rem;">Date Paid</th><th style="padding: 0.75rem;">Description</th><th style="padding: 0.75rem; text-align: right;">Amount</th></tr></thead><tbody>${itemsHTML}</tbody><tfoot><tr style="font-weight: bold; border-top: 2px solid #ccc;"><td colspan="3" style="padding: 0.75rem; text-align: right;">Total Paid:</td><td style="padding: 0.75rem; text-align: right;">${formatCurrency(totalAmount)}</td></tr></tfoot></table>`;

                        const previewHTML = `
                            <div id="annual-statement-printable" style="background-color: #fff; padding: 1.5rem; border-radius: 0.5rem;">
                                ${headerHTML}
                                ${partiesHTML}
                                <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 2rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div>
                                        <p style="font-size: 0.875rem; color: #6b7280;">Total Payments Made</p>
                                        <p style="font-size: 1.5rem; font-weight: bold; color: #111827;">${payments.length}</p>
                                    </div>
                                    <div style="text-align: right;">
                                        <p style="font-size: 0.875rem; color: #6b7280;">Total Amount Paid</p>
                                        <p style="font-size: 1.5rem; font-weight: bold; color: #16a34a;">${formatCurrency(totalAmount)}</p>
                                    </div>
                                </div>
                                ${tableHTML}
                            </div>
                        `;
                        previewArea.innerHTML = previewHTML;
                        elements.annualStatementDownloadBtn.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error generating statement preview:', error);
                        previewArea.innerHTML = `<div class="text-center text-red-500">${error.message}</div>`;
                        showToast(error.message, 'error');
                    });
            };

            const downloadAnnualStatementPDF = () => {
                const previewContent = document.getElementById('annual-statement-printable');
                if (!previewContent) {
                    showToast('No preview content to download.', 'error');
                    return;
                }
                
                const year = document.getElementById('statement-year-select').value;
                const filename = `Payment_Statement_${year}.pdf`;
                
                const opt = {
                    margin: 0.75,
                    filename: filename,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true },
                    jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
                };

                html2pdf().from(previewContent).set(opt).save().then(() => {
                    showToast('Statement downloaded successfully.', 'success');
                });
            };

            const handleLeaseTerminationRequest = (form) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;
                submitBtn.textContent = 'Submitting...';
                submitBtn.disabled = true;

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                fetch('request-lease-termination.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(res => res.json().then(data => ({ ok: res.ok, data })))
                .then(({ ok, data }) => {
                    showToast(data.message, ok ? 'success' : 'error');
                    if (ok) {
                        hideLeaseTerminationModal();
                    }
                })
                .catch(error => {
                    console.error('Lease termination error:', error);
                    showToast('A network error occurred. Please try again.', 'error');
                })
                .finally(() => { submitBtn.textContent = originalBtnText; submitBtn.disabled = false; });
            };

            const handleLeaseExtensionRequest = (form) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;
                submitBtn.textContent = 'Submitting...';
                submitBtn.disabled = true;
                elements.leaseExtensionError.classList.add('hidden');

                const formData = new FormData(form);
                const newEndDate = formData.get('new_end_date');

                fetch('request-lease-extension.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ new_end_date: newEndDate })
                })
                .then(res => {
                    // res.json() parses the body. We check res.ok to see if it was a success or error status code.
                    return res.json().then(data => ({ ok: res.ok, data }));
                })
                .then(({ ok, data }) => {
                    if (ok) {
                        // Server returned 2xx status
                        showToast(data.message, 'success');
                        hideLeaseExtensionModal();
                        loadPage('pay-rent');
                    } else {
                        // Server returned 4xx/5xx status, `data` contains the error JSON
                        throw new Error(data.message || 'An unknown server error occurred.');
                    }
                })
                .catch(error => {
                    // Catches network errors, JSON parsing errors, and errors thrown from the .then block.
                    console.error('Lease modification error:', error);
                    showToast(error.message, 'error');
                })
                .finally(() => { submitBtn.textContent = originalBtnText; submitBtn.disabled = false; });
            };

            const handleReapplyAgreement = (form) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;
                submitBtn.textContent = 'Submitting...';
                submitBtn.disabled = true;

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                fetch('reapply-agreement.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(res => res.json().then(data => ({ ok: res.ok, data })))
                .then(({ ok, data }) => {
                    showToast(data.message, ok ? 'success' : 'error');
                    if (ok) {
                        hideReapplyModal();
                        loadPage('pay-rent'); // Reload the rent page to show new details
                    }
                })
                .catch(error => {
                    console.error('Re-application error:', error);
                    showToast('A network error occurred. Please try again.', 'error');
                })
                .finally(() => { submitBtn.textContent = originalBtnText; submitBtn.disabled = false; });
            };

            const handleMaintenanceRequest = (form) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;
                submitBtn.textContent = 'Submitting...';
                submitBtn.disabled = true;

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                fetch('submit-maintenance.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(res => res.json())
                    .then(result => {
                        if (result.success) {
                            showToast(result.message, 'success');
                            form.reset();
                            const listContainer = document.getElementById('request-history-list');
                            if (listContainer) {
                                fetch('maintenance.php?is_ajax=1')
                                    .then(res => res.text())
                                    .then(html => listContainer.innerHTML = html);
                            }
                        } else {
                            showToast(result.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Maintenance request error:', err);
                        showToast('An error occurred. Please try again.', 'error');
                    })
                    .finally(() => {
                        submitBtn.textContent = originalBtnText;
                        submitBtn.disabled = false;
                    });
            };

            const handlePasswordChange = (form) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;
                submitBtn.textContent = 'Changing...';
                submitBtn.disabled = true;

                const formData = new FormData(form);
                fetch('process-password-change.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    showToast(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        form.reset();
                    }
                })
                .catch(error => {
                    console.error('Password change error:', error);
                    showToast('An error occurred. Please try again.', 'error');
                })
                .finally(() => {
                    submitBtn.textContent = originalBtnText;
                    submitBtn.disabled = false;
                });
            };

            // --- 4. EVENT HANDLERS ---
            const handleSidebarClick = (e) => {
                const item = e.target.closest('.sidebar-item');
                if (!item) return;
                e.preventDefault();
                const page = item.dataset.page;
                if (page) {
                    loadPage(page);
                    elements.sidebarItems.forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                } else if (item.href.includes('?action=logout')) {
                    window.location.href = item.href;
                }
            };

            const handleContentClick = (e) => {
                const target = e.target;
                const printInvoiceBtn = target.closest('.print-invoice-btn');
                const viewImageBtn = target.closest('.view-image-btn');
                const downloadImageBtn = target.closest('.download-image-btn');
                const thumbnail = target.closest('.thumbnail-image');
                const navButton = target.closest('.sidebar-item-nav');
                const sliderButton = target.closest('.slider-prev-btn, .slider-next-btn, .slider-dot');
                const noticeToggleBtn = target.closest('.notice-toggle');

                if (noticeToggleBtn) {
                    const noticeBody = noticeToggleBtn.nextElementSibling;
                    const noticeChevron = noticeToggleBtn.querySelector('i');
                    if (noticeBody && noticeChevron) {
                        noticeBody.classList.toggle('hidden');
                        noticeChevron.classList.toggle('rotate-180');
                    }
                }


                if (sliderButton) handleSlider(sliderButton);

                const passwordToggleBtn = target.closest('.password-toggle');
                if (passwordToggleBtn) {
                    const parent = passwordToggleBtn.parentElement;
                    const input = parent.querySelector('input');
                    const icon = passwordToggleBtn.querySelector('i');
                    if (input && icon) {
                        if (input.type === 'password') {
                            input.type = 'text';
                            icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
                        } else {
                            input.type = 'password';
                            icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
                        }
                    }
                }

                if (printInvoiceBtn) printInvoice(printInvoiceBtn.dataset.id);
                if (viewImageBtn) showImageModal(viewImageBtn.dataset.src);
                if (downloadImageBtn) downloadImage(downloadImageBtn.dataset.src, downloadImageBtn.dataset.filename); 
                if (target.matches('.view-agreement-btn, .download-agreement-btn')) {
                    const agreementId = target.dataset.agreementId;
                    const action = target.classList.contains('view-agreement-btn') ? 'view' : 'download';
                    generateAgreement(action, agreementId);
                }
                if (target.id === 'search-button') loadPaymentHistory(document.getElementById('search-month').value);
                if (target.id === 'download-statement-btn') showAnnualStatementModal();
                if (target.id === 'request-lease-modification-btn') {
                    const currentEndDate = target.dataset.currentEndDate;
                    const startDate = target.dataset.startDate;
                    showLeaseExtensionModal(currentEndDate, startDate);
                }
                if (target.id === 'request-lease-termination-btn') {
                    showLeaseTerminationModal();
                }
                if (target.id === 're-apply-agreement-btn') {
                    const agreementId = target.dataset.agreementId;
                    const endingDate = target.dataset.endingDate;
                    showReapplyModal(agreementId, endingDate);
                }

                if (target.id === 'reset-button') {
                    const monthInput = document.getElementById('search-month');
                    if (monthInput) monthInput.value = new Date().toISOString().slice(0, 7);
                    loadPaymentHistory();
                }
                if (thumbnail) {
                    const mainImage = document.getElementById('main-property-image');
                    if (mainImage) {
                        mainImage.style.opacity = '0';
                        setTimeout(() => {
                            mainImage.src = thumbnail.src;
                            mainImage.style.opacity = '1';
                        }, 200);
                    }
                }
                if (navButton) {
                    const pageToLoad = navButton.dataset.page;
                    const sidebarLink = document.querySelector(`.sidebar-item[data-page='${pageToLoad}']`);
                    if (sidebarLink) sidebarLink.click();
                }
            };

            const handleFormSubmit = (e) => {
                e.preventDefault();
                if (e.target.id === 'maintenance-form') handleMaintenanceRequest(e.target);
                if (e.target.id === 'profile-update-form') handleProfileUpdate(e.target);
                if (e.target.id === 'password-change-form') handlePasswordChange(e.target);
                if (e.target.id === 'annual-statement-form') {
                    const year = e.target.elements.year.value;
                    generateAnnualStatementPreview(year);
                }
                if (e.target.id === 'lease-extension-form') handleLeaseExtensionRequest(e.target);
                if (e.target.id === 'lease-termination-form') handleLeaseTerminationRequest(e.target);
                if (e.target.id === 'reapply-agreement-form') handleReapplyAgreement(e.target);
            };

            const handleDebouncedInput = (e) => {
                if (e.target.id === 'notice-search-input') {
                    clearTimeout(state.debounceTimer);
                    state.debounceTimer = setTimeout(() => loadNotices(e.target.value), 350);
                }
            };

            const handleImagePreview = (e) => {
                if (e.target.id === 'profile_photo' && e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const imageUrl = event.target.result;
                        const mainPreview = document.getElementById('profile-photo-preview');
                        const smallPreview = document.getElementById('profile-photo-preview-small');
                        if (mainPreview) mainPreview.src = imageUrl;
                        if (smallPreview) smallPreview.src = imageUrl;
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            };

            // --- 5. INITIALIZATION ---
            const init = () => {
                window.history.replaceState({}, document.title, window.location.href);

                const lastPage = localStorage.getItem('lastTenantPage') || 'tenancy-details';
                loadPage(lastPage);
                elements.sidebarItems.forEach(item => {
                    item.classList.toggle('active', item.dataset.page === lastPage);
                });

                elements.sidebar.addEventListener('click', handleSidebarClick);
                elements.contentMain.addEventListener('click', handleContentClick);
                document.body.addEventListener('submit', handleFormSubmit); // Listen on body to catch modal form submits
                elements.contentMain.addEventListener('input', handleDebouncedInput);
                elements.contentMain.addEventListener('change', handleImagePreview);

                elements.docCloseBtn.addEventListener('click', hideDocModal);
                elements.docPreviewModal.addEventListener('click', (e) => {
                    if (e.target === elements.docPreviewModal) hideDocModal();
                });
                elements.imageCloseBtn.addEventListener('click', hideImageModal);
                elements.imagePreviewModal.addEventListener('click', (e) => {
                    if (e.target === elements.imagePreviewModal) hideImageModal();
                });
                elements.leaseExtensionCancelBtn.addEventListener('click', hideLeaseExtensionModal);
                elements.leaseExtensionModal.addEventListener('click', (e) => {
                    if (e.target === elements.leaseExtensionModal) hideLeaseExtensionModal();
                });
                elements.leaseTerminationCancelBtn.addEventListener('click', hideLeaseTerminationModal);
                elements.leaseTerminationModal.addEventListener('click', (e) => {
                    if (e.target === elements.leaseTerminationModal) hideLeaseTerminationModal();
                });
                elements.reapplyAgreementCancelBtn.addEventListener('click', hideReapplyModal);
                elements.reapplyAgreementModal.addEventListener('click', (e) => { if (e.target === elements.reapplyAgreementModal) hideReapplyModal(); });
                elements.annualStatementCloseBtn.addEventListener('click', hideAnnualStatementModal);
                elements.annualStatementCancelBtn.addEventListener('click', hideAnnualStatementModal);
                elements.annualStatementDownloadBtn.addEventListener('click', downloadAnnualStatementPDF);
                

                const phpToastMessage = <?php echo json_encode($toastMessage); ?>;
                if (phpToastMessage) {
                    showToast(phpToastMessage, <?php echo json_encode($toastType); ?>);
                }

                hideLoading(); // Hide initial loading overlay
            };

            // Run the app on DOMContentLoaded
            document.addEventListener('DOMContentLoaded', init);
        })();
    </script>
</body>

</html>