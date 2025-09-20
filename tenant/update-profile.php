<?php
session_start();
include '../database.php';

// Check if the user is logged in
if (!isset($_SESSION['tenant_id']) || $_SESSION['type_tenant'] !== 'tenant') {
    echo '<div class="text-center text-red-500 p-8">Please log in to view this page.</div>';
    exit();
}
function getImageUrl($path)
{
    if (!empty($path)) {
        $filename = basename($path);
        $relativePath = '../uploads/profile_photos/' . $filename;
        $absolutePath = '../uploads/profile_photos/' . $filename;
        if (file_exists($absolutePath)) {
            return $relativePath;
        }
    }
    return '../assets/images/not_found.png'; // Placeholder for missing image
}

$userId = $_SESSION['tenant_id'];
$tenantDetails = null;

// Fetch existing tenant details and the profile photo path from the database
try {
    $con = mysqli_connect("localhost", "root", "", "vasundharahousing");
    if (!$con) {
        throw new Exception("Database connection failed.");
    }

    $sql = "SELECT tenant_name, email, contact_number, profile_photo FROM tenants WHERE tenant_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tenantDetails = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($con);
} catch (Exception $e) {
    echo '<div class="text-center text-red-500 p-8">' . htmlspecialchars($e->getMessage()) . '</div>';
    exit();
}

// Check if tenant data was found
if (!$tenantDetails) {
    echo '<div class="text-center text-red-500 p-8">Tenant details not found.</div>';
    exit();
}
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-slide-up-fade-in">
    <!-- Left Column: Profile Card -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl shadow-lg text-center">
            <div class="relative w-32 h-32 mx-auto rounded-full overflow-hidden border-4 border-blue-200 shadow-lg">
                <img id="profile-photo-preview" src="<?php echo getImageUrl($tenantDetails['profile_photo']); ?>" alt="Profile Photo" class="w-full h-full object-cover">
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mt-4"><?php echo htmlspecialchars($tenantDetails['tenant_name']); ?></h3>
            <p class="text-gray-500"><?php echo htmlspecialchars($tenantDetails['email']); ?></p>
            <p class="text-xs text-gray-400 mt-4">Update your photo and personal details using the form on the right.</p>
        </div>
    </div>

    <!-- Right Column: Update Form -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Profile Update Form -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-lg">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Profile Information</h2>
            <form id="profile-update-form" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Profile Photo</label>
                    <div class="mt-1 flex items-center gap-4">
                        <span class="inline-block h-12 w-12 rounded-full overflow-hidden bg-gray-100">
                            <img id="profile-photo-preview-small" src="<?php echo getImageUrl($tenantDetails['profile_photo']); ?>" class="h-full w-full object-cover text-gray-300" />
                        </span>
                        <label for="profile_photo" class="cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Change
                        </label>
                        <input type="file" id="profile_photo" name="profile_photo" class="hidden" accept="image/*">
                    </div>
                </div>
                <div>
                    <label for="tenant_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" id="tenant_name" name="tenant_name" value="<?php echo htmlspecialchars($tenantDetails['tenant_name']); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($tenantDetails['email']); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                </div>
                <div>
                    <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                    <input type="tel" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($tenantDetails['contact_number']); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full py-3 px-4 rounded-lg text-white font-semibold bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300 ease-in-out">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password Section -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-lg">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Security Settings</h2>
            <p class="text-sm text-gray-500 mb-6">Update your password to keep your account secure.</p>
            <form id="password-change-form" class="space-y-6">
                <div>
                    <label for="old_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="ri-lock-password-line text-gray-400"></i></span>
                        <input type="password" id="old_password" name="old_password" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 pl-10">
                        <button type="button" class="password-toggle absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                            <i class="ri-eye-line"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="ri-key-2-line text-gray-400"></i></span>
                        <input type="password" id="new_password" name="new_password" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 pl-10">
                        <button type="button" class="password-toggle absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                            <i class="ri-eye-line"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="confirm_new_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="ri-key-2-line text-gray-400"></i></span>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 pl-10">
                        <button type="button" class="password-toggle absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                            <i class="ri-eye-line"></i>
                        </button>
                    </div>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full py-3 px-4 rounded-lg text-white font-semibold bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-300 ease-in-out">
                        <i class="ri-shield-keyhole-line mr-2"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
