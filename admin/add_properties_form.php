<?php
include 'add_properties.php';

if (!isset($_SESSION['admin_id']) || $_SESSION['type_admin'] !== 'admin'){
    header('Location: ../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Property - Vasundhara Housing</title>
   <script src="../assets/js/tailwind.js"></script>
    <link href="../assets/css/tailwind.css" rel="stylesheet"/>
    <link href="../assets/css/all.min.css" rel="stylesheet"/>
    <link href="../assets/css/google_fonts.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/fonts/interFont.css">
    <link rel="stylesheet" href="./style.css">
    <script src="../assets/all.min.3.4.js"></script>
    <style>
        /* Base font for the entire document */
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Keyframe animations for a polished entry effect */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .animate-slideInUp {
            animation: slideInUp 0.6s ease-out forwards;
        }

        .file-input-button {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: #4f46e5; /* indigo-600 */
            color: white;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .file-input-button:hover {
            background-color: #4338ca; /* indigo-700 */
            transform: translateY(-2px);
        }

        .file-input-button i {
            margin-right: 0.5rem;
        }

        .custom-file-input {
            display: none;
        }

        #map-container {
            min-height: 300px;
            background-color: #e2e8f0;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748B;
            font-style: italic;
            text-align: center;
            padding: 1rem;
        }
    </style>
</head>

<body class="bg-slate-100">
    <main class="container mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h1 class="text-3xl font-extrabold text-gray-900">Add New Property</h1>
            <a href="./index.php" class="text-gray-600 hover:text-indigo-600 flex items-center transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <form id="add-property-form" method="post" enctype="multipart/form-data" class="space-y-8 bg-white p-8 rounded-2xl shadow-lg">

            <!-- Section: Basic Info & Amenities -->
            <section>
                <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="propertyName" class="block text-sm font-medium text-gray-700 mb-1">Property Name</label>
                        <input type="text" id="propertyName" name="propertyName" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="propertyType" class="block text-sm font-medium text-gray-700 mb-1">Type (e.g., 2 BHK, Villa)</label>
                        <select id="propertyType" name="propertyType"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="1BHK">1BHK</option>
                            <option value="2BHK">2BHK</option>
                            <option value="3BHK">3BHK</option>
                            <option value="Villas">Villas</option>
                            <option value="Bungalows">Bungalows</option>
                            <option value="Apartment">Apartment</option>
                        </select>
                    </div>
                    <div>
                        <label for="monthlyRent" class="block text-sm font-medium text-gray-700 mb-1">Monthly Rent (₹)</label>
                        <input type="number" id="monthlyRent" name="monthlyRent" required min="0" step="100"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="propertyStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="propertyStatus" name="propertyStatus"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="Available">Available</option>
                            <option value="Booked">Booked</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <label for="beds" class="block text-sm font-medium text-gray-700 mb-1">Bedrooms</label>
                        <input type="number" id="beds" name="beds" required min="0" step="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="baths" class="block text-sm font-medium text-gray-700 mb-1">Bathrooms</label>
                        <input type="number" id="baths" name="baths" required min="0" step="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="area_sq" class="block text-sm font-medium text-gray-700 mb-1">Area (sqft)</label>
                        <input type="number" id="area_sq" name="area_sq" required min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="mt-6">
                    <label for="propertyDescription" class="block text-sm font-medium text-gray-700 mb-1">Property Description</label>
                        <textarea id="propertyDescription" name="propertyDescription" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Enter a detailed description of the property..."></textarea>
                </div>
            </section>

            <!-- Agreement Details Section (Conditional) -->
            <section id="agreement-details-section" class="space-y-6 hidden animate-fadeIn">
                <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6">Agreement Details</h2>
                <p class="text-sm text-gray-500">This section is for properties that are already booked. Please provide the agreement details.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="starting_date" class="block text-sm font-medium text-gray-700 mb-1">Agreement Start Date</label>
                        <input type="date" id="starting_date" name="starting_date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="ending_date" class="block text-sm font-medium text-gray-700 mb-1">Agreement End Date</label>
                        <input type="date" id="ending_date" name="ending_date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="agreement_duration" class="block text-sm font-medium text-gray-700 mb-1">Agreement Duration (Months)</label>
                        <input type="text" id="agreement_duration" name="month_rent_no" readonly
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500 cursor-not-allowed">
                    </div>
                </div>
            </section>

            <!-- Section: Address -->
            <section>
                <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6">Property Address</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="addressHouseNo" class="block text-sm font-medium text-gray-700 mb-1">House No./Name</label>
                        <input type="text" id="addressHouseNo" name="addressHouseNo"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., 123, Green Villa">
                    </div>
                    <div>
                        <label for="street" class="block text-sm font-medium text-gray-700 mb-1">Street/Area</label>
                        <input type="text" id="street" name="street"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., Sardar Nagar">
                    </div>
                    <div>
                        <label for="addressTaluka" class="block text-sm font-medium text-gray-700 mb-1">Taluka</label>
                        <input type="text" id="addressTaluka" name="addressTaluka" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., Rajkot">
                    </div>
                    <div>
                        <label for="addressDistrict" class="block text-sm font-medium text-gray-700 mb-1">District</label>
                        <input type="text" id="addressDistrict" name="addressDistrict" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., Rajkot">
                    </div>
                    <div>
                        <label for="addressState" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                        <input type="text" id="addressState" name="addressState"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., Gujarat">
                    </div>
                    <div>
                        <label for="addressPincode" class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                        <input type="text" id="addressPincode" name="addressPincode" required pattern="[0-9]{6}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., 360001" maxlength="6">
                    </div>
                </div>
            </section>

            <!-- Section: Images -->
            <section>
                <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6">Property Images</h2>
                <p class="text-sm text-gray-500 mb-4">Upload images for the property. An outdoor image is recommended.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php
                    $imageFields = [
                        'outdoorImage' => ['label' => 'Outdoor'],
                        'hallImage' => ['label' => 'Hall'],
                        'bedroomImage' => ['label' => 'Bedroom'],
                        'kitchenImage' => ['label' => 'Kitchen'],
                    ];
                    foreach ($imageFields as $fieldId => $fieldData):
                    ?>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700"><?php echo $fieldData['label']; ?> Image</label>
                        <div class="w-full h-40 bg-slate-100 rounded-lg flex items-center justify-center border-2 border-dashed border-gray-300">
                            <img id="<?php echo $fieldId; ?>Preview" src="../assets/images/not_found.png" alt="<?php echo $fieldData['label']; ?> Preview" class="h-full w-full object-cover rounded-md">
                        </div>
                        <label for="<?php echo $fieldId; ?>" class="file-input-button w-full justify-center">
                            <i class="fas fa-upload"></i> <span>Choose Image</span>
                        </label>
                        <input type="file" id="<?php echo $fieldId; ?>" name="<?php echo $fieldId; ?>" accept="image/*" class="custom-file-input" data-preview-target="<?php echo $fieldId; ?>Preview">
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Section: Map -->
            <section>
                <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6">Location on Map</h2>
                <div id="map-container" class="w-full h-96 rounded-xl overflow-hidden shadow-md border bg-slate-200 flex items-center justify-center text-slate-500">
                    <p>Enter address details to see the location on the map.</p>
                </div>
            </section>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 pt-6 border-t mt-6">
                <button type="button" onclick="window.location.href='index.php'"
                    class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                    Cancel
                </button>
                <button type="submit" name="PropertyAdd"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg shadow-md hover:bg-indigo-700 transition-colors font-semibold">
                    Add Property
                </button>
            </div>
        </form>
    </main>

    <div id="toast-container" class="fixed bottom-5 right-5 z-50"></div>

    <script>
        /**
         * Displays a toast notification with an icon.
         * @param {string} message The message to display.
         * @param {string} type The type of message ('green' for success, 'red' for error).
         */
        function showToast(message, type) {
            const toastContainer = document.getElementById('toast-container');
            const bgColor = type === 'green' ? 'bg-green-600' : 'bg-red-600';
            const iconType = type === 'green' ? 'check-circle' : 'exclamation-triangle';
            const iconBg = type === 'green' ? 'bg-green-700' : 'bg-red-700';

            const toast = document.createElement('div');
            toast.className = `flex items-center w-full max-w-xs p-4 mb-4 text-white ${bgColor} rounded-lg shadow-lg animate-slideInRight`;
            toast.innerHTML = `
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-full ${iconBg} text-white">
                <i class="fas fa-${iconType}"></i>
            </div>
            <div class="ml-3 text-sm font-normal">${message}</div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-transparent text-white rounded-lg p-1.5 inline-flex h-8 w-8 hover:text-gray-200 focus:outline-none" onclick="this.parentElement.remove()">
                <span class="sr-only">Close</span>
                <i class="fas fa-xmark"></i>
            </button>
        `;
            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        /**
         * Calculates the number of months between two dates for an agreement.
         */
        function calculateAgreementMonths() {
            const startDateInput = document.getElementById('starting_date');
            const endDateInput = document.getElementById('ending_date');
            const durationInput = document.getElementById('agreement_duration');

            if (!startDateInput.value || !endDateInput.value) {
                durationInput.value = '';
                return;
            }

            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            if (endDate <= startDate) {
                durationInput.value = ''; // Clear if dates are invalid
                return;
            }

            let months;
            months = (endDate.getFullYear() - startDate.getFullYear()) * 12;
            months -= startDate.getMonth();
            months += endDate.getMonth();

            // This provides the difference in calendar months. E.g., Jan 15 to Feb 14 is 1 month.
            durationInput.value = months <= 0 ? 0 : months;
        }

        // Function to update the map dynamically based on district input
        function updateMap() {
            const houseNo = document.getElementById("addressHouseNo").value.trim();
            const street = document.getElementById("street").value.trim();
            const taluka = document.getElementById("addressTaluka").value.trim();
            const district = document.getElementById("addressDistrict").value.trim();
            const state = document.getElementById("addressState").value.trim();
            const pincode = document.getElementById("addressPincode").value.trim();
            const propertyName = document.getElementById("propertyName").value.trim();

            const mapContainer = document.getElementById("map-container");

            const addressParts = [houseNo, street, taluka, district, state, pincode].filter(Boolean);
            const fullAddress = addressParts.join(', ');

            if (fullAddress && mapContainer) {
                const mapUrl = `https://maps.google.com/maps?width=100%25&height=600&hl=en&q=${encodeURIComponent(fullAddress)}+(${encodeURIComponent(propertyName)})&t=&z=15&ie=UTF8&iwloc=B&output=embed`;
                mapContainer.innerHTML = `<iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="${mapUrl}"></iframe>`;
            } else if (mapContainer) {
                mapContainer.innerHTML = '<p>Enter address details to see the location on the map.</p>';
            }
        }

        function init() {
            // Attach event listeners to all address fields to update map
            const addressFieldIds = ['addressHouseNo', 'street', 'addressTaluka', 'addressDistrict', 'addressState', 'addressPincode', 'propertyName'];
            addressFieldIds.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.addEventListener('change', updateMap);
                }
            });
            // Toggle agreement details based on property status
            const propertyStatusSelect = document.getElementById('propertyStatus');
            const agreementSection = document.getElementById('agreement-details-section');

            propertyStatusSelect.addEventListener('change', (event) => {
                if (event.target.value === 'Booked') {
                    agreementSection.classList.remove('hidden');
                } else {
                    agreementSection.classList.add('hidden');
                    // Clear agreement fields when status is not 'Booked'
                    document.getElementById('starting_date').value = '';
                    document.getElementById('ending_date').value = '';
                    document.getElementById('agreement_duration').value = '';
                }
            });

            // Add event listeners to date inputs to calculate duration
            document.getElementById('starting_date').addEventListener('change', calculateAgreementMonths);
            document.getElementById('ending_date').addEventListener('change', calculateAgreementMonths);

            // File input preview handler
            document.querySelectorAll('.custom-file-input').forEach(input => {
                input.addEventListener('change', (event) => {
                    const file = event.target.files[0];
                    const previewTargetId = event.target.dataset.previewTarget;
                    const previewElement = document.getElementById(previewTargetId);

                    if (file && previewElement) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewElement.src = e.target.result;
                        }
                        reader.readAsDataURL(file);
                    }
                });
            });

            // Auto-fill bedrooms and bathrooms based on property type
            const propertyTypeSelect = document.getElementById('propertyType');
            const bedsInput = document.getElementById('beds');
            const bathsInput = document.getElementById('baths');

            const propertyTypeConfig = {
                '1BHK': {
                    beds: 1,
                    baths: 1
                },
                '2BHK': {
                    beds: 2,
                    baths: 2
                },
                '3BHK': {
                    beds: 3,
                    baths: 2
                } // 3BHKs often have 2 bathrooms
            };

            propertyTypeSelect.addEventListener('change', (event) => {
                const selectedType = event.target.value;
                const config = propertyTypeConfig[selectedType];

                if (config) {
                    bedsInput.value = config.beds;
                    bathsInput.value = config.baths;
                } else {
                    // Clear for types like Villas, Bungalows for manual entry
                    bedsInput.value = '';
                    bathsInput.value = '';
                }
            });

            // Show toast messages from PHP if any
            <?php if (!empty($message)): ?>
                showToast(<?php echo json_encode($message); ?>, <?php echo json_encode($messageType); ?>);
                <?php if ($messageType === 'green'): ?>
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 3000);
                <?php endif; ?>
            <?php endif; ?>
        }

        // Initialize after DOM is loaded
        document.addEventListener('DOMContentLoaded', init);
    </script>


</body>

</html>