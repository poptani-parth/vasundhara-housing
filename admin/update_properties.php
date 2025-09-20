<?php
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
    <title>Update Property - Vasundhara Housing</title>
   <link rel="stylesheet" href="style.css">
     <script src="../assets/js/tailwind.js"></script>
    <link href="../assets/css/tailwind.css" rel="stylesheet"/>
    <link href="../assets/css/all.min.css" rel="stylesheet"/>
    <link href="../assets/css/google_fonts.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/fonts/interFont.css">
    <link rel="stylesheet" href="./style.css">
    <script src="../assets/all.min.3.4.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            /* Light gray background */
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4 animate-fadeIn">
    <div class="w-full max-w-4xl bg-white p-8 rounded-xl shadow-lg border border-gray-200 animate-slideInUp">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h1 class="text-3xl font-extrabold text-gray-900">Update Property</h1>
            <a href="index.php"
                class="text-blue-600 hover:text-blue-800 flex items-center transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <form id="update-property-form" method="post" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" id="propertyId" name="propertyId">

            <div class="form-field-animation" style="animation-delay: 0.1s;">
                <label for="propertyName" class="block text-lg font-medium text-gray-700 mb-2">Property
                    Name/Address</label>
                <input type="text" id="propertyName" name="propertyName" 
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 transition-all duration-200">
            </div>

            <div class="form-field-animation" style="animation-delay: 0.2s;">
                <label for="propertyType" class="block text-lg font-medium text-gray-700 mb-2">Type (e.g., 2 BHK,
                    Villa)</label>
                <input type="text" id="propertyType" name="propertyType" 
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 transition-all duration-200">
            </div>

            <div class="form-field-animation" style="animation-delay: 0.3s;">
                <label for="monthlyRent" class="block text-lg font-medium text-gray-700 mb-2">Monthly Rent (₹)</label>
                <input type="number" id="monthlyRent" name="monthlyRent"  min="0" step="100"
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 transition-all duration-200">
            </div>

            <div class="form-field-animation" style="animation-delay: 0.4s;">
                <label for="propertyStatus" class="block text-lg font-medium text-gray-700 mb-2">Status</label>
                <select id="propertyStatus" name="propertyStatus"
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 transition-all duration-200">
                    <option value="Available">Available</option>
                    <option value="Booked">Booked</option>
                </select>
            </div>

            <div class="form-field-animation" style="animation-delay: 0.5s;">
                <label for="propertyDescription" class="block text-lg font-medium text-gray-700 mb-2">Property
                    Description</label>
                <textarea id="propertyDescription" name="propertyDescription" rows="4"
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200"
                    placeholder="Enter a detailed description of the property..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-field-animation" style="animation-delay: 0.6s;">
                    <label for="addressHouseNo" class="block text-lg font-medium text-gray-700 mb-2">House
                        No./Name</label>
                    <input type="text" id="addressHouseNo" name="addressHouseNo"
                        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200"
                        placeholder="e.g., 123, Green Villa">
                </div>
                <div class="form-field-animation" style="animation-delay: 0.7s;">
                    <label for="addressStreet" class="block text-lg font-medium text-gray-700 mb-2">Street/Area</label>
                    <input type="text" id="addressStreet" name="addressStreet"
                        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200"
                        placeholder="e.g., MG Road, Anand Nagar">
                </div>
                <div class="form-field-animation" style="animation-delay: 0.8s;">
                    <label for="city" class="block text-lg font-medium text-gray-700 mb-2">City/Rural</label>
                    <input type="text" id="city" name="city"
                        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200"
                        placeholder="e.g., Rajkot">
                </div>
                <div class="form-field-animation" style="animation-delay: 0.9s;">
                    <label for="addressTaluka" class="block text-lg font-medium text-gray-700 mb-2">Taluka</label>
                    <input type="text" id="addressTaluka" name="addressTaluka" 
                        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200"
                        placeholder="e.g., Rajkot">
                </div>
                <div class="form-field-animation" style="animation-delay: 1.0s;">
                    <label for="addressDistrict" class="block text-lg font-medium text-gray-700 mb-2">District</label>
                    <input type="text" id="addressDistrict" name="addressDistrict" 
                        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200"
                        placeholder="e.g., Rajkot">
                </div>
                <div class="form-field-animation" style="animation-delay: 1.1s;">
                    <label for="addressState" class="block text-lg font-medium text-gray-700 mb-2">State</label>
                    <input type="text" id="addressState" name="addressState" 
                        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200"
                        placeholder="e.g., Gujarat">
                </div>
                <div class="md:col-span-2 form-field-animation" style="animation-delay: 1.2s;">
                    <label for="addressPincode" class="block text-lg font-medium text-gray-700 mb-2">Pincode</label>
                    <input type="text" id="addressPincode" name="addressPincode"  pattern="[0-9]{6}"
                        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 transition-all duration-200"
                        placeholder="e.g., 360001" maxlength="6">
                </div>
            </div>

            <div class="form-field-animation" style="animation-delay: 1.3s;">
                <label class="block text-lg font-medium text-gray-700 mb-2">Property Location on Map</label>
                <div id="map-container" class="transition-all duration-300 ease-in-out">
                    Map will be displayed here based on the address.
                    <br>
                    (Enter address details above)
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-field-animation" style="animation-delay: 1.4s;">
                    <label class="block text-lg font-medium text-gray-700 mb-2">Outdoor Image</label>
                    <img id="currentOutdoorImage" src="../assets/images/notFoundImage.png" alt="Outdoor Image"
                        class="w-36 h-24 object-cover rounded-lg mb-2 border border-gray-300">
                    <label for="outdoorImage" class="file-input-label">
                        <i class="fas fa-upload"></i> Change File
                    </label>
                    <input type="file" id="outdoorImage" name="outdoorImage" accept="image/*" class="custom-file-input">
                    <p id="outdoorFileName" class="mt-2 text-sm text-gray-500"></p>
                </div>
                <div class="form-field-animation" style="animation-delay: 1.5s;">
                    <label class="block text-lg font-medium text-gray-700 mb-2">Hall Image</label>
                    <img id="currentHallImage" src="../assets/images/notFoundImage.png" alt="Hall Image"
                        class="w-36 h-24 object-cover rounded-lg mb-2 border border-gray-300">
                    <label for="hallImage" class="file-input-label">
                        <i class="fas fa-upload"></i> Change File
                    </label>
                    <input type="file" id="hallImage" name="hallImage" accept="image/*" class="custom-file-input">
                    <p id="hallFileName" class="mt-2 text-sm text-gray-500"></p>
                </div>
                <div class="form-field-animation" style="animation-delay: 1.6s;">
                    <label class="block text-lg font-medium text-gray-700 mb-2">Bedroom Image</label>
                    <img id="currentBedroomImage" src="../assets/images/notFoundImage.png" alt="Bedroom Image"
                        class="w-36 h-24 object-cover rounded-lg mb-2 border border-gray-300">
                    <label for="bedroomImage" class="file-input-label">
                        <i class="fas fa-upload"></i> Change File
                    </label>
                    <input type="file" id="bedroomImage" name="bedroomImage" accept="image/*" class="custom-file-input">
                    <p id="bedroomFileName" class="mt-2 text-sm text-gray-500"></p>
                </div>
                <div class="form-field-animation" style="animation-delay: 1.7s;">
                    <label class="block text-lg font-medium text-gray-700 mb-2">Kitchen Image</label>
                    <img id="currentKitchenImage" src="../assets/images/notFoundImage.png" alt="Kitchen Image"
                        class="w-36 h-24 object-cover rounded-lg mb-2 border border-gray-300">
                    <label for="kitchenImage" class="file-input-label">
                        <i class="fas fa-upload"></i> Change File
                    </label>
                    <input type="file" id="kitchenImage" name="kitchenImage" accept="image/*" class="custom-file-input">
                    <p id="kitchenFileName" class="mt-2 text-sm text-gray-500"></p>
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-6 border-t mt-6 form-field-animation"
                style="animation-delay: 1.8s;">
                <button type="button" onclick="window.location.href='index.php'"
                    class="px-6 py-3 bg-gray-300 text-gray-800 rounded-lg shadow-md hover:bg-gray-400 transition-colors duration-200 font-semibold transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                    Cancel
                </button>
                <button type="submit"
                    name="updateProperty"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition-colors duration-200 font-semibold transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

        <?php include 'mng_properties.php';?>

    <div id="toast-notification"
        class="fixed bottom-5 right-5 bg-green-500 text-white py-3 px-6 rounded-lg shadow-lg opacity-0 transition-all duration-300 ease-out z-50 transform translate-y-5">
        Toast message
    </div>

    <script>
        let map; // Declare map variable globally
        let geocoder; // Declare geocoder variable globally
        let marker; // Declare marker variable globally

        // This function is called by the Google Maps API when it's fully loaded
        function initMap() {
            geocoder = new google.maps.Geocoder();
            // Default map center (e.g., Rajkot, Gujarat, India)
            const defaultLatLng = { lat: 22.2989, lng: 70.7958 }; // Coordinates for Rajkot

            map = new google.maps.Map(document.getElementById('map-container'), {
                zoom: 10,
                center: defaultLatLng,
            });

            marker = new google.maps.Marker({
                map: map,
                position: defaultLatLng,
                title: "Property Location"
            });

            // After map is initialized, check if there's any pre-filled address to update the map
            updateMapLocation();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const toastNotification = document.getElementById('toast-notification');

            function showToast(message, type = 'green', duration = 3000) {
                toastNotification.textContent = message;
                // Reset classes and apply new ones for type and animation
                toastNotification.className = `fixed bottom-5 right-5 text-white py-3 px-6 rounded-lg shadow-lg opacity-0 transition-all duration-300 ease-out z-50 transform translate-y-5`;

                if (type === 'green') {
                    toastNotification.classList.add('bg-green-500');
                } else if (type === 'red') {
                    toastNotification.classList.add('bg-red-500');
                } else if (type === 'blue') {
                    toastNotification.classList.add('bg-blue-500');
                }

                // Animate in
                toastNotification.classList.remove('opacity-0', 'translate-y-5');
                toastNotification.classList.add('opacity-100', 'translate-y-0');

                setTimeout(() => {
                    // Animate out
                    toastNotification.classList.remove('opacity-100', 'translate-y-0');
                    toastNotification.classList.add('opacity-0', 'translate-y-5');
                }, duration);
            }

            const addressFields = [
                'addressHouseNo', 'addressStreet', 'city',
                'addressTaluka', 'addressDistrict', 'addressState', 'addressPincode'
            ];
            const mapContainer = document.getElementById('map-container');

            window.updateMapLocation = function () { // Make it global for initMap to call
                if (!geocoder || !map) {
                    // Google Maps API not yet loaded or initialized
                    mapContainer.textContent = 'Loading map... Please wait.';
                    return;
                }

                const fullAddress = addressFields.map(id => document.getElementById(id).value).filter(Boolean).join(', ');

                if (fullAddress.trim()) {
                    geocoder.geocode({ 'address': fullAddress }, function (results, status) {
                        if (status === 'OK' && results[0]) {
                            map.setCenter(results[0].geometry.location);
                            map.setZoom(15); // Adjust zoom level as needed
                            marker.setPosition(results[0].geometry.location);
                            marker.setMap(map); // Ensure marker is visible
                            mapContainer.innerHTML = ''; // Clear "Map will show location..." text
                        } else {
                            mapContainer.innerHTML = `<p class="text-red-500">Geocoding failed for: <strong>${fullAddress}</strong></p><p>Status: ${status}</p><p>Please refine the address.</p>`;
                            marker.setMap(null); // Hide marker if geocoding fails
                        }
                    });
                } else {
                    mapContainer.innerHTML = '<p>Enter address details to see location on map.</p>';
                    // Optionally set map to a default location and hide marker if address is empty
                    if (map) {
                        map.setCenter({ lat: 22.2989, lng: 70.7958 }); // Default Rajkot
                        map.setZoom(10);
                        marker.setMap(null);
                    }
                }
            };

            // Update map whenever address fields change (can be debounced for performance)
            addressFields.forEach(id => {
                document.getElementById(id).addEventListener('input', window.updateMapLocation);
            });
            <?php if (!empty($message)): ?>
                showToast(<?php echo json_encode($message); ?>, <?php echo json_encode($messageType); ?>);
            <?php endif; ?>
        });
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_Maps_API_KEY&callback=initMap" async defer></script>
</body>

</html>