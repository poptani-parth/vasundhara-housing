<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vasundhara Housing - Your Dream Home Awaits</title>
    <link rel="stylesheet" href="./assets/fonts/Poppins.css">
    <link rel="stylesheet" href="style.css">
    <script src="./assets/js/tailwind.js"></script>
    <link href="./assets/css/tailwind.css" rel="stylesheet" />
    <link href="./assets/css/all.min.css" rel="stylesheet" />
    <link href="./assets/RemixIcon-master/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>

<body class="theme">
    <main class="w-full overflow-hidden">
        <!-- Navbar -->
        <?php include './components/navbar.php'; ?>

        <!-- Hero Section - Split Layout -->
        <section
            class="relative w-full min-h-screen flex flex-col lg:flex-row items-center justify-center pt-16 pb-8 sm:pt-20 sm:pb-10 hero-bg-pattern">
            <div
                class="relative z-1 flex flex-col items-center lg:items-start text-center lg:text-left text-gray-900 p-4 sm:p-6 max-w-xl lg:max-w-3xl mx-auto lg:mx-0 lg:ml-16 animate-slide-up-fade-in delay-100">
                <p class="text-base sm:text-lg tracking-wider mb-2 font-medium text-blue-700">Your Future Awaits</p>
                <h1 class="font-extrabold text-3xl sm:text-4xl md:text-5xl leading-tight mb-4 drop-shadow-sm">Find Your
                    Dream Home With Ease</h1>
                <p class="text-sm sm:text-base text-gray-700 mb-6 max-w-lg">Discover a curated selection of properties
                    that match your lifestyle. From cozy apartments to spacious family homes, your perfect place is
                    here.</p>
                <div class="flex flex-col sm:flex-row gap-2">
                    <a href="./properties.php">
                        <button
                            class="px-5 py-2 bg-blue-600 text-white font-bold rounded-full shadow-lg hover:bg-blue-700 transition duration-300 ease-in-out text-sm transform hover:scale-105">
                            Explore Properties
                        </button>
                    </a>
                    <a href="./about.php">
                        <button
                            class="px-5 py-2 border-2 border-blue-600 text-blue-700 font-bold rounded-full shadow-lg hover:bg-blue-100 transition duration-300 ease-in-out text-sm transform hover:scale-105">
                            About More
                        </button>
                    </a>
                </div>
            </div>

            <!-- Search Bar - Integrated into Hero visually -->
            <div
                class="relative z-1 w-full lg:w-2/5 max-w-sm bg-white p-5 sm:p-6 rounded-xl shadow-xl mt-8 lg:mt-0 lg:mr-16 animate-slide-up-fade-in delay-100 border border-blue-100">
                <h3 class="text-lg sm:text-xl font-bold mb-4 text-gray-800">Start Your Search</h3>
                <div class="grid grid-cols-1 gap-3">
                    <div class="relative">
                        <label for="houseType" class="sr-only">House Type</label>
                        <input type="text" id="houseType" name="HouseType" placeholder="e.g., Apartment, Villa, House"
                            class="input-focus-effect w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none text-xs pr-8 transition duration-200">
                        <i class="ri-home-4-line absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-base"></i>
                    </div>
                    <div class="relative">
                        <label for="location" class="sr-only">Location</label>
                        <input type="text" id="location" name="Location" placeholder="e.g., New York, London, Mumbai"
                            class="input-focus-effect w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none text-xs pr-8 transition duration-200">
                        <i
                            class="ri-map-pin-2-line absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-base"></i>
                    </div>
                    <div class="relative">
                        <label for="budget" class="sr-only">Budget</label>
                        <input type="number" id="budget" name="Budget" placeholder="Max Budget (e.g., 50000)"
                            class="input-focus-effect w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none text-xs pr-8 transition duration-200">
                        <i
                            class="ri-money-dollar-circle-fill absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-base"></i>
                    </div>
                    <button
                        class="w-full px-4 py-2 bg-blue-600 text-white font-bold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300 ease-in-out text-sm transform hover:scale-105">
                        <i class="ri-search-line mr-1"></i> Search Now
                    </button>
                </div>
            </div>
        </section>

        <!-- Featured Listings Section -->
        <div class="w-full px-4 sm:px-8 md:px-16 lg:px-24 py-12 bg-white">
            <section class="mb-10 text-center scroll-reveal">
                <span
                    class="inline-block px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs mb-2">Discover</span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3 leading-tight">Our Handpicked
                    Properties</h2>
                <p class="text-sm text-gray-600 max-w-xl mx-auto">Explore a diverse range of properties, carefully
                    selected to meet the highest standards of quality and comfort.</p>
            </section>

            <section id="property-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16 scroll-reveal">
                <!-- Property Card 1 -->

            </section>

            <div class="text-center mt-8">
                <a href="./properties.php"
                    class="inline-flex items-center px-5 py-2.5 bg-blue-50 text-blue-700 font-bold rounded-full shadow-lg hover:bg-blue-100 transition duration-300 ease-in-out text-sm transform hover:scale-105 scroll-reveal">
                    View All Properties <i class="ri-arrow-right-line ml-1.5 text-lg"></i>
                </a>
            </div>
        </div>

        <!-- Call to Action Section - Refined -->
        <section
            class="bg-indigo-700 text-white py-12 px-4 sm:px-8 md:px-16 lg:px-24 text-center rounded-t-xl">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 delay-100 drop-shadow-md">Your Dream Home Awaits!</h2>
            <p class="text-base md:text-lg mb-6 opacity-90 delay-300 max-w-xl mx-auto">Don't miss out on the perfect
                property. Get in touch with our team today and let's make it happen.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-3 delay-500">
                <a href="./properties.php">
                    <button
                        class="bg-white text-blue-700 font-bold px-6 py-2.5 rounded-full shadow-lg hover:bg-gray-200 transition duration-300 ease-in-out text-sm transform hover:scale-105">
                        Get Started Today
                    </button>
                </a>
                <a href="tel:7201918520">
                    <button
                        class="border-2 border-white text-white font-bold px-6 py-2.5 rounded-full shadow-lg hover:bg-white hover:text-blue-700 transition duration-300 ease-in-out text-sm transform hover:scale-105">
                        Call Us Now
                    </button>
                </a>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-10 px-4 sm:px-8 md:px-16 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div class="flex flex-col items-start scroll-reveal delay-100">
                        <h2 class="text-3xl font-extrabold font-['Sitka_Subheading'] mb-2 text-blue-400">VH Housing</h2>
                        <p class="text-gray-400 text-sm leading-relaxed">Dedicated to helping you find your perfect home
                            with ease and confidence. Your satisfaction is our priority.</p>
                    </div>
                    <div class="scroll-reveal delay-200">
                        <p class="text-2xl font-['Sitka_Subheading'] font-semibold mb-3">Quick Links</p>
                        <ul class="space-y-1.5 text-gray-400 text-sm pl-4">
                            <li><a href="#" class="hover:text-blue-300 transition duration-300">Home</a></li>
                            <li><a href="#" class="hover:text-blue-300 transition duration-300">Properties</a></li>
                            <li><a href="#" class="hover:text-blue-300 transition duration-300">About Us</a></li>
                            <li><a href="#" class="hover:text-blue-300 transition duration-300">Contact</a></li>
                        </ul>
                    </div>
                    <div class="flex flex-col items-start scroll-reveal delay-300">
                        <p class="text-2xl font-['Sitka_Subheading'] font-semibold mb-3">Contact Us</p>
                        <p class="text-sm mb-1.5">Number: <a href="tel:+911234567890"
                                class="text-blue-400 hover:text-blue-300 transition duration-300">+91 12345 67890</a>
                        </p>
                        <p class="text-sm mb-3">Email: <a href="mailto:info@vasundharahousing.com"
                                class="text-blue-400 hover:text-blue-300 transition duration-300">info@vasundharahousing.com</a>
                        </p>
                        <div class="flex gap-3">
                            <a href="#" class="text-gray-400 hover:text-blue-500 transition duration-300 text-3xl"><i
                                    class="ri-facebook-box-fill"></i><span class="sr-only">Facebook</span></a>
                            <a href="#" class="text-gray-400 hover:text-pink-500 transition duration-300 text-3xl"><i
                                    class="ri-instagram-fill"></i><span class="sr-only">Instagram</span></a>
                            <a href="#" class="text-gray-400 hover:text-blue-500 transition duration-300 text-3xl"><i
                                    class="ri-twitter-fill"></i><span class="sr-only">Twitter</span></a>
                            <a href="#" class="text-gray-400 hover:text-red-500 transition duration-300 text-3xl"><i
                                    class="ri-youtube-fill"></i><span class="sr-only">YouTube</span></a>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-700 my-8 scroll-reveal delay-400">


                <div class="text-center text-gray-100 pb-8 text-sm scroll-reveal delay-100">
                    <p>&copy; 2023 Vasundhara Housing. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </main>

    <script src="./javascript.js"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
     <script src="./assets/js/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            // Fetch properties
            function fetchProperties() {
                $.ajax({
                    url: "homePage_properties.php",
                    method: "POST",
                    data: $("#filterForm").serialize(),
                    beforeSend: function() {
                        $("#property-list").html("<p class='text-center absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2'>Loading properties...</p>");
                    },
                    success: function(data) {
                        $("#property-list").html(data);
                    }
                });
            }

            // On form submit
            $("#filterForm").on("submit", function(e) {
                e.preventDefault();
                fetchProperties();
            });

            // Fetch on input change
            $("#filterForm input").on("input", function() {
                fetchProperties();
            });

            // Default load all
            fetchProperties();
        });
    </script>
</body>

</html>