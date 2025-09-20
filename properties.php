<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vasundhara Housing - Properties</title>
    <link rel="stylesheet" href="./assets/fonts/Poppins.css">
    <script src="./assets/js/tailwind.js"></script>
    <script src="./assets/js/html2pdf.bundle.min.js"></script>
    <link href="./assets/css/tailwind.css" rel="stylesheet"/>
    <link href="./assets/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="./style.css">
    <link href="./assets/RemixIcon-master/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>

<body class="theme">
    <main class="w-full overflow-hidden">
        <!-- Navbar -->
        <?php
        include('./components/navbar.php'); // Include the consistent navbar
        ?>

        <!-- Hero Section - Properties Page -->
        <section
            class="relative w-full py-20 sm:py-24 hero-bg-pattern flex items-center justify-center text-center pt-24">
            <div class="relative z-1 max-w-3xl mx-auto p-4 sm:p-6 animate-slide-up-fade-in delay-200">
                <h1
                    class="font-black font-['Aharoni'] text-4xl sm:text-5xl md:text-6xl leading-tight mb-4 text-gray-900 drop-shadow-sm">
                    Our Properties</h1>
                <p class="text-base sm:text-lg text-gray-700 mb-6">Explore a diverse range of homes and find your
                    perfect match.</p>
            </div>
        </section>

        <!-- Search and Filter Section -->
        <!-- Search and Filter Section -->
        <div
            class="relative w-full max-w-4xl mx-auto p-6 sm:p-8 rounded-2xl shadow-2xl my-5 animate-slide-up-fade-in delay-100 border border-blue-100 z-20">

            <h3 class="text-xl sm:text-3xl font-['Sitka_Small'] font-bold mb-6 text-gray-800 text-center tracking-tight">
                Find Your Perfect House
            </h3>

            <form id="filterForm">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="relative md:col-span-2">
                        <label for="propertyType" class="block text-sm font-medium text-gray-700 mb-1">Property Type</label>
                        <input type="text" id="propertyType" name="propertyType" placeholder="e.g., Apartment, Villa"
                            class="input-focus-effect w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none text-sm pr-10 transition duration-200">
                    </div>
                    <div class="relative md:col-span-2">
                        <label for="propertyLocation" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" id="propertyLocation" name="propertyLocation" placeholder="e.g., City, Neighborhood"
                            class="input-focus-effect w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none text-sm pr-10 transition duration-200">
                    </div>
                    <div class="relative">
                        <label for="minPrice" class="block text-sm font-medium text-gray-700 mb-1">Min Price</label>
                        <input type="number" id="minPrice" name="minPrice" placeholder="Min (₹)" min="0"
                            class="input-focus-effect w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none text-sm pr-10 transition duration-200">
                    </div>
                    <div class="relative">
                        <label for="maxPrice" class="block text-sm font-medium text-gray-700 mb-1">Max Price</label>
                        <input type="number" id="maxPrice" name="maxPrice" placeholder="Max (₹)" min="0"
                            class="input-focus-effect w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none text-sm pr-10 transition duration-200">
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit"
                            class="w-full py-7 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300 ease-in-out transform hover:scale-105">
                            <i class="ri-search-line mr-2"></i> Search Properties
                        </button>
                    </div>
                </div>
            </form>
        </div>

       

        <!-- Properties Listing Section -->
        <section class="w-full px-4 sm:px-8 md:px-16 lg:px-24 py-16 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs mb-3">Browse</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 leading-tight">Available Listings</h2>
                    <p class="text-base text-gray-700 max-w-2xl mx-auto">From cozy apartments to spacious family homes,
                        we have something for everyone.</p>
                </div>


                <div id="property-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16 relative">


                </div>

                <!-- Pagination (Example)
                <div class="flex justify-center items-center space-x-2 ">
                    <button
                        class="px-3 py-1.5 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 transition duration-200 text-sm">Previous</button>
                    <button
                        class="px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition duration-200 text-sm">1</button>
                    <button
                        class="px-3 py-1.5 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 transition duration-200 text-sm">2</button>
                    <button
                        class="px-3 py-1.5 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 transition duration-200 text-sm">3</button>
                    <button
                        class="px-3 py-1.5 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 transition duration-200 text-sm">Next</button>
                </div> -->
            </div>
        </section>

        <?php
        include('./components/footer.php'); // Include the consistent footer
        ?>

    </main>

    <script src="./javascript.js"></script>
    <script src="./assets/js/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            // Fetch properties
            function fetchProperties() {
                $.ajax({
                    url: "fetch_properties.php",
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