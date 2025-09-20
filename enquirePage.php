<?php

include './database.php';
$propertyId = filter_var($_GET['id'] ?? null, FILTER_SANITIZE_NUMBER_INT);

$property = null; // Initialize property variable

if ($propertyId && is_numeric($propertyId)) {
    // Prepare and execute the SQL query to fetch property details
    $stmt = $conn->prepare("SELECT * FROM properties WHERE pro_id = ?");
    $stmt->bind_param("i", $propertyId); // 'i' for integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $property = $result->fetch_assoc();
    }
    $stmt->close();
    $district = $property['district'] ?? '';
    $state = $property['state'] ?? '';
    $taluka = $property['taluka'] ?? '';
    $pincode = $property['pincode'] ?? '';
    
    $_SESSION['property_id'] = $propertyId;
}
function getImageUrl($path)
{
    if (!empty($path)) {
        $filename = basename($path);
        $relativePath = './admin/uploads/property_images/' . $filename;
        $absolutePath = './admin/uploads/property_images/' . $filename;
        if (file_exists($absolutePath)) {
            return $relativePath;
        }
    }
    return './assets/images/notFoundImage.png'; // Placeholder for missing image
}

// Collect all image paths for the slider
$imagePaths = [];
if ($property) {
    if (!empty($property['outdoor_img'])) $imagePaths[] = getImageUrl($property['outdoor_img']);
    if (!empty($property['hall_img'])) $imagePaths[] = getImageUrl($property['hall_img']);
    if (!empty($property['bedroom_img'])) $imagePaths[] = getImageUrl($property['bedroom_img']);
    if (!empty($property['kitchen_img'])) $imagePaths[] = getImageUrl($property['kitchen_img']);
}

// If no images are found, add a default placeholder
if (empty($imagePaths)) {
    $imagePaths[] = './assets/images/notFoundImage.png';
}



$conn->close(); // Close database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vasundhara Housing</title>
    <link rel="stylesheet" href="./assets/fonts/Poppins.css">
    <script src="./assets/js/tailwind.js"></script>
    <link href="./assets/css/tailwind.css" rel="stylesheet"/>
    <link href="./assets/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="style.css">
    <link href="./assets/RemixIcon-master/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Poppins', sans-serif;
        }

        #map-container-details {
            width: 100%;
            height: 400px;
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

        .slider-image.active {
            opacity: 1;
            position: relative;
        }
    </style>
</head>

<body class="theme">
    <main class="w-full overflow-hidden">
        <!-- Navbar -->
        <?php
        include('./components/navbar.php'); // Include the consistent navbar
        ?>

        <?php if ($property): ?>
            <!-- Hero Section - Property Name -->
            <section class="relative w-full py-20 sm:py-24 hero-bg-pattern flex items-center justify-center text-center pt-24">
                <div class="relative z-1 max-w-3xl mx-auto p-4 sm:p-6 animate-slide-up-fade-in">
                    <h1 class="font-extrabold text-4xl sm:text-5xl md:text-6xl leading-tight mb-4 text-gray-900 drop-shadow-sm"><?php echo htmlspecialchars($property['pro_nm']); ?></h1>
                    <p class="text-base sm:text-lg text-gray-700 mb-6"><?php echo htmlspecialchars($property[("houseno")] . " " . $property[("taluka")] . ", " . $property[("district")]); ?></p>
                </div>
            </section>

            <!-- Property Details Section -->
            <section class="w-full px-4 sm:px-8 md:px-16 lg:px-24 py-16 bg-white">
                <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- Property Image and Overview -->
                    <div class="scroll-reveal">
                        <div class="slider-container h-80 md:h-96 rounded-xl shadow-lg  bg-slate-50 overflow-hidden relative">
                            <?php foreach ($imagePaths as $index => $path): ?>
                                <img src="<?php echo $path; ?>" alt="Property Image <?php echo $index + 1; ?>"
                                    class="slider-image block absolute top-0 left-0 transition duration-[.9s] opacity-0 w-full h-full object-cover <?php echo $index === 0 ? 'active' : ''; ?>"
                                    onerror="this.onerror=null;this.src='https:./assets/images/notFoundImage.png';">
                            <?php endforeach; ?>
                            <?php if (count($imagePaths) > 1): ?>
                                <div class="absolute top-1/2 translate-x-1/2 right-5 mr-2 cursor-pointer z-10" onclick="changeSlide(-1)"><i class="ri-arrow-right-line bg-gray-900 text-white p-2 rounded-lg opacity-50 hover:opacity-100 transition-color duration-200"></i></div>
                                <div class="absolute top-1/2 translate-x-1/2 left-1 -ml-2 cursor-pointer z-10" onclick="changeSlide(1)"><i class="ri-arrow-left-line bg-gray-900 text-white p-2 rounded-lg opacity-50 hover:opacity-100 transition-color duration-200"></i></div>
                            <?php endif; ?>
                        </div>
                        <button
                            id="book"
                            class="book inline-flex items-center bg-blue-600 text-white font-semibold py-3 mt-5 px-6 rounded-lg shadow-lg transition-all duration-300  ease-in-out transform hover:scale-105 hover:-translate-y-1 hover:bg-blue-700  hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-300 focus:ring-opacity-75">
                            <i class="ri-calendar-line mr-2"></i>
                            Book Now
                        </button>
                        <?php if ($property['status'] !== 'Available'): ?>
                            <p class="text-red-500 font-semibold border px-3 py-5 border-red-600 mt-4"><span class="font-bold text-lg">Note : </span> property is currently booked. Please check other available homes.</p>
                        <?php endif; ?>
                        <div class="space-y-4 mt-10 text-gray-700">
                            <h2 class="text-2xl font-bold text-gray-800 border-b pb-2 mb-4">Details</h2>
                            <p class="text-lg"><strong class="font-semibold">Type:</strong> <?php echo htmlspecialchars($property['pro_type']); ?></p>
                            <p class="text-lg"><strong class="font-semibold">Monthly Rent:</strong> ₹<?php echo number_format($property['month_rent']); ?></p>
                            <p class="text-lg"><strong class="font-semibold">Status:</strong>
                                <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $property['status'] === 'Available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo htmlspecialchars($property['status']); ?>
                                </span>
                            </p>
                            <p class="text-lg"><strong class="font-semibold">Description:</strong></p>
                            <p class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-gray-800 leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($property['pro_dis'])); ?>
                            </p>
                        </div>
                        <h2 class="text-2xl mt-10 font-bold text-gray-800 border-b pb-3 mb-4">Location</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700">
                            <div>
                                <p class="text-lg"><strong class="font-semibold">House No./Name:</strong> <?php echo htmlspecialchars($property['houseno']); ?></p>
                                <p class="text-lg"><strong class="font-semibold">Street/Area:</strong> <?php echo htmlspecialchars($property['street']); ?></p>                                
                                <p class="text-lg"><strong class="font-semibold">Taluka:</strong> <?php echo htmlspecialchars($property['taluka']); ?></p>
                                <p class="text-lg"><strong class="font-semibold">District:</strong> <?php echo htmlspecialchars($property['district']); ?></p>
                                <p class="text-lg"><strong class="font-semibold">State:</strong> <?php echo htmlspecialchars($property['state']); ?></p>
                                <p class="text-lg"><strong class="font-semibold">Pincode:</strong> <?php echo htmlspecialchars($property['pincode']); ?></p>
                            </div>
                        </div>
                    </div>
                    

                    <!-- Map and Inquiry Form -->
                    <div class="scroll-reveal delay-300">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Location In Map</h2>
                        <div class="w-full h-96 rounded-xl overflow-hidden shadow-lg border border-gray-200 mb-8">
                            <iframe width="100%" height="600" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=<?= $state ?>,<?= $district ?>,<?= $taluka ?>,<?= $pincode ?>+(My%20Business%20Name)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed">
                                <a href="https://www.mapsdirections.info/fr/calculer-la-population-sur-une-carte">calculer la population sur la carte</a>
                            </iframe>
                        </div>

                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Request More Information</h2>
                        <form id="inquiryForm" class="space-y-6 bg-gray-50 p-6 rounded-xl shadow-md">
                            <div>
                                <label for="inquiryName" class="block text-gray-700 text-sm font-medium mb-2">Full Name</label>
                                <input type="text" id="inquiryName" name="inquiryName" placeholder="Your Full Name"
                                    class="input-focus-effect w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none text-sm transition duration-200">
                            </div>
                            <div>
                                <label for="inquiryEmail" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                                <input type="email" id="inquiryEmail" name="inquiryEmail" placeholder="your@example.com"
                                    class="input-focus-effect w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none text-sm transition duration-200">
                            </div>
                            <div>
                                <label for="inquiryPhone" class="block text-gray-700 text-sm font-medium mb-2">Phone Number (Optional)</label>
                                <input type="tel" id="inquiryPhone" name="inquiryPhone" placeholder="e.g., +123 456 7890"
                                    class="input-focus-effect w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none text-sm transition duration-200">
                            </div>
                            <div>
                                <label for="inquiryMessage" class="block text-gray-700 text-sm font-medium mb-2">Your Message</label>
                                <textarea id="inquiryMessage" name="inquiryMessage" rows="4" placeholder="I'm interested in this property..."
                                    class="input-focus-effect w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none text-sm resize-y transition duration-200"></textarea>
                            </div>
                            <button type="submit" class="inline-block px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-lg hover:bg-blue-700 transition duration-300 ease-in-out text-base transform hover:scale-105">
                                Submit Inquiry <i class="ri-send-plane-line ml-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </section>

        <?php else: ?>
            <!-- Property Not Found Message -->
            <section class="relative w-full py-20 sm:py-24 hero-bg-pattern flex items-center justify-center text-center pt-24 min-h-[50vh]">
                <div class="relative z-1 max-w-3xl mx-auto p-4 sm:p-6 animate-slide-up-fade-in delay-200">
                    <h1 class="font-extrabold text-4xl sm:text-5xl md:text-6xl leading-tight mb-4 text-gray-900 drop-shadow-sm">Property Not Found</h1>
                    <p class="text-base sm:text-lg text-gray-700 mb-6">The property you are looking for does not exist or the ID is invalid.</p>
                    <a href="properties.php" class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-full shadow-lg hover:bg-blue-700 transition duration-300 ease-in-out text-sm transform hover:scale-105">
                        View All Properties
                    </a>
                </div>
            </section>
        <?php endif; ?>

        <!-- Footer -->
        <?php
        include('./components/footer.php'); // Include the consistent footer
        ?>
        <!-- model overview -->
        <div id="tenantModal" class="fixed inset-0 hidden items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" aria-hidden="true"></div>

            <!-- Modal Card -->
            <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white shadow-2xl p-6">
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-2xl font-['Javanese_Text'] font-semibold text-gray-800">Book Property</h2>
                    <button id="closeTenantModal" class="size-9 grid place-items-center rounded-full hover:bg-gray-100">
                        <span class="sr-only">Close</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-5 h-5 text-gray-600">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="mt-2 text-sm text-gray-500">Please enter your full name to proceed. We'll check if you're already registered with us.</p>

                <form id="tenantForm" class="mt-5 space-y-4">
                    <div>
                        <label for="tenantName" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input id="tenantName" name="tenantName" type="text" placeholder="e.g., Parth Poptani"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 text-gray-800"
                            autocomplete="name" required />
                        <p id="nameError" class="mt-2 text-sm text-red-600 hidden">Please enter your full name.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" id="cancelBtn" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700">Continue</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="bookedModal" class="fixed inset-0 hidden items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" aria-hidden="true"></div>

            <!-- Modal Card -->
            <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white shadow-2xl p-6">
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-2xl font-['Javanese_Text'] font-semibold text-gray-800">Property Unavailable</h2>
                    <button id="closeBookedModal" class="size-9 grid place-items-center rounded-full hover:bg-gray-100">
                        <span class="sr-only">Close</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-5 h-5 text-gray-600">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="mt-4 text-gray-600">This property is currently booked and not available. Please check out other available homes.</p>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" id="unavailableModalClose" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Close</button>
                </div>
            </div>
        </div>

        <div id="inquirySuccessModal" class="fixed inset-0 hidden items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" aria-hidden="true"></div>
            <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white shadow-2xl p-6 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="mt-4 text-2xl font-semibold text-gray-800">Inquiry Submitted!</h2>
                <p class="mt-2 text-gray-600">Thank you for your interest. We will get back to you shortly.</p>
                <div class="mt-6">
                    <button type="button" id="closeInquirySuccessModal" class="px-6 py-2.5 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700">Close</button>
                </div>
            </div>
        </div>

    </main>

    <script src="./javascript.js"></script>
    <script>
        // Image Slider Logic
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slider-image');
        const totalSlides = slides.length;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
            });
            slides[index].classList.add('active');
        }

        function changeSlide(direction) {
            currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
            showSlide(currentSlide);
        }
        document.addEventListener('DOMContentLoaded', () => {
            if (totalSlides > 0) {
                showSlide(currentSlide); // Show the first slide on load

                // Auto-slide every 5 seconds (5000 ms)
                setInterval(() => {
                    changeSlide(1); // Go to next slide
                }, 5000);
            }
        });
        const propertyStatus = "<?php echo $property ? htmlspecialchars($property['status']) : ''; ?>";

        function normalizeName(raw) {
            if (!raw) return '';
            const t = String(raw).trim();
            if (!t) return '';
            return t.charAt(0).toUpperCase() + t.slice(1);
        }

        // Show/Hide modal helpers
        const modal = document.getElementById('tenantModal');
        const bookedModal = document.getElementById('bookedModal');

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => document.getElementById('tenantName').focus(), 0);
        }

        function openBookedModal() {
            bookedModal.classList.remove('hidden');
            bookedModal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function closeBookedModal() {
            bookedModal.classList.add('hidden');
            bookedModal.classList.remove('flex');
        }

        // Demo Buy button click -> open modal
        document.getElementById('book').addEventListener('click', () => {
            if (propertyStatus === 'Available') {
                openModal();
            } else {
                openBookedModal();
            }
        });

        // Close actions
        document.getElementById('closeTenantModal').addEventListener('click', closeModal);
        document.getElementById('cancelBtn').addEventListener('click', closeModal);
        document.getElementById('closeBookedModal').addEventListener('click', closeBookedModal);
        document.getElementById('unavailableModalClose').addEventListener('click', closeBookedModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
            if (e.target === bookedModal) closeBookedModal();
        });

        // Handle submit
        document.getElementById('tenantForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('tenantName');
            const err = document.getElementById('nameError');
            let tenantName = normalizeName(input.value);

            if (!tenantName) {
                err.classList.remove('hidden');
                input.classList.add('border-red-500', 'focus:ring-red-100', 'focus:border-red-500');
                return;
            }
            err.classList.add('hidden');
            input.classList.remove('border-red-500', 'focus:ring-red-100', 'focus:border-red-500');


            fetch('check_tenant.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        tenant_name: tenantName,
                    })
                })
                .then(res => res.json())
                .then(data => {
                    const propertyId = <?php echo json_encode($propertyId); ?>;
                    if (data.status === 'registered') {
                        // User is registered, proceed to agreement with their name and property ID.
                        window.location.href = `rentAgreement.php?name=${encodeURIComponent(tenantName)}&property_id=${propertyId}`;
                    } else {
                        // User is not registered. Set session variables via an AJAX call and then redirect.
                        fetch('set_redirect_session.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                'message': 'You are not registered. Please register now to continue.',
                                'prefill_name': tenantName,
                                'redirect_to': `rentAgreement.php?property_id=${propertyId}`
                            })
                        })
                        .then(res => res.json())
                        .then(sessionData => {
                            if (sessionData.success) {
                                window.location.href = 'login.php'; // Redirect to login page, which will read the session
                            } else {
                                alert('An error occurred while preparing your registration.');
                            }
                        })
                        .catch(() => alert('A server error occurred. Please try again.'));
                    }
                })
                .catch(() => {
                    alert('An error occurred. Please try again.');
                });
        });

        // Handle Inquiry Form Submission
        const inquiryForm = document.getElementById('inquiryForm');
        if (inquiryForm) {
            const inquirySuccessModal = document.getElementById('inquirySuccessModal');

            function openInquirySuccessModal() {
                inquirySuccessModal.classList.remove('hidden');
                inquirySuccessModal.classList.add('flex');
            }

            function closeInquirySuccessModal() {
                inquirySuccessModal.classList.add('hidden');
                inquirySuccessModal.classList.remove('flex');
            }

            document.getElementById('closeInquirySuccessModal').addEventListener('click', closeInquirySuccessModal);

            inquiryForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(inquiryForm);
                const submitButton = inquiryForm.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = 'Submitting... <i class="ri-loader-4-line animate-spin ml-2"></i>';

                fetch('submit_inquiry.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            inquiryForm.reset();
                            openInquirySuccessModal();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while submitting your inquiry. Please try again.');
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                    });
            });
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCCnapIC5zK1CkYn8WRZ5mRPMGUmmBXe1Q&callback=initMapDetails" async defer></script>
    <script src="./assets/js/jquery-3.6.0.min.js"></script>
</body>

</html>
