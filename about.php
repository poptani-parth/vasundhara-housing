<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vasundhara Housing - About Us</title>
    <link rel="stylesheet" href="./assets/fonts/Poppins.css">
    <script src="./assets/js/tailwind.js"></script>
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

        <!-- Hero Section - About Page -->
        <section class="relative w-full py-20 sm:py-24 hero-bg-pattern flex items-center justify-center text-center pt-24">
            <div class="relative z-1 max-w-3xl mx-auto p-4 sm:p-6 animate-slide-up-fade-in">
                <h1 class="font-black font-['Aharoni'] text-4xl sm:text-5xl md:text-6xl leading-tight mb-4 text-gray-900 drop-shadow-sm">About Vasundhara Housing</h1>
                <p class="text-base sm:text-lg text-gray-700 mb-6">Your trusted partner in finding the perfect place to call home.</p>
            </div>
        </section>

        <!-- Company Story/Mission Section -->
        <section class="w-full px-4 sm:px-8 md:px-16 lg:px-24 py-16 bg-white">
            <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="scroll-reveal delay-100">
                    <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs mb-3">Our Story</span>
                    <h2 class="text-3xl md:text-4xl font-['Sitka_Small'] font-bold text-gray-900 mb-6 leading-tight">Building Dreams, One Home at a Time</h2>
                    <p class="text-base text-gray-700 mb-4">
                        At Vasundhara Housing, we believe that finding a home is more than just a transaction; it's about finding a place where memories are made and futures are built. Founded by Poptani Parth, our journey began with a simple yet profound vision: to create a real estate experience built on trust, transparency, and genuine relationships.
                    </p>
                    <p class="text-base text-gray-700">
                        With years of expertise in the real estate market, we pride ourselves on our deep understanding of local communities and our commitment to matching every client with their ideal property. Whether you're a first-time homebuyer, looking to upgrade, or seeking an investment, we are dedicated to guiding you every step of the way with personalized service and expert advice.
                    </p>
                    <a href="./properties.php" class="inline-block px-6 py-3 mt-4 bg-blue-600 text-white font-semibold rounded-xl shadow-lg hover:bg-blue-700 transition duration-300 ease-in-out text-sm transform hover:scale-105">
                        Explore Our Properties
                    </a>
                </div>
                <div class="scroll-reveal delay-300">
                    <!-- Placeholder image, as per previous instructions -->
                    <img src="assets/images/aboutUs_img.png" alt="Our Mission" class="w-full h-auto rounded-xl shadow-lg transform hover:scale-105 transition-transform duration-300">
                </div>
            </div>
        </section>

        <!-- Our Values Section -->
        <section class="w-full px-4 sm:px-8 md:px-16 lg:px-24 py-16 bg-gray-50">
            <div class="max-w-6xl mx-auto text-center mb-12 scroll-reveal">
                <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs mb-3">Our Core</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Our Mission & Values</h2>
                <p class="text-base text-gray-700 max-w-2xl mx-auto">These values are at the heart of everything we do, ensuring we deliver exceptional service and build lasting relationships.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div class="flex flex-col items-center text-center p-6 bg-white rounded-xl shadow-md scroll-reveal delay-100">
                    <div class="bg-blue-600 p-4 rounded-full mb-4 shadow-xl animate-scale-in-pop delay-200">
                        <i class="ri-hand-heart-line text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 font-['Sitka_Text']">Trust & Integrity</h3>
                    <p class="text-sm text-gray-700">We operate with the highest ethical standards, ensuring honesty and transparency in every interaction.</p>
                </div>  
                <div class="flex flex-col items-center text-center p-6 bg-white rounded-xl shadow-md scroll-reveal delay-300">
                    <div class="bg-blue-600 p-4 rounded-full mb-4 shadow-xl animate-scale-in-pop delay-400">
                        <i class="ri-user-smile-line text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 font-['Sitka_Text']">Client-Centric</h3>
                    <p class="text-sm text-gray-700">Your needs are our priority. We listen, understand, and tailor our services to exceed your expectations.</p>
                </div>
                <div class="flex flex-col items-center text-center p-6 bg-white rounded-xl shadow-md scroll-reveal delay-500">
                    <div class="bg-blue-600 p-4 rounded-full mb-4 shadow-xl animate-scale-in-pop delay-600">
                        <i class="ri-lightbulb-line text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 font-['Sitka_Text']">Expertise & Innovation</h3>
                    <p class="text-sm text-gray-700">Leveraging market insights and modern tools to provide you with the best solutions.</p>
                </div>
            </div>
        </section>

        <!-- Founder/Team Section -->
        <section class="w-full mt-1 sm:px-8 md:px-16 lg:px-24 py-16 bg-white">
            <div class="max-w-4xl mx-auto text-center mb-12 scroll-reveal">
                <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs mb-3">Our Leadership</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 font-['Sitka_Small'] ">Meet Our Founder</h2>
                <p class="text-base text-gray-700 max-w-2xl mx-auto">Our experienced founder is dedicated to your success and leads our team with a forward-thinking approach.</p>
            </div>

            <div class="flex flex-col md:flex-row items-center md:items-start gap-8  mx-auto bg-gray-50 p-8 rounded-xl shadow-md scroll-reveal delay-100">
                <div class="flex-shrink-0">
                    <img src="./assets/images/not_found.png" alt="Poptani Parth" class="rounded-full w-36 h-36 object-cover shadow-lg border-4 border-blue-200">
                </div>
                <div class="text-center md:text-left">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Poptani Parth</h3>
                    <p class="text-blue-600 font-medium text-base mb-3">Founder & CEO</p>
                    <p class="text-sm text-gray-700">
                        Poptani Parth established Vasundhara Housing with a commitment to redefining the real estate experience. His vision was to build a company grounded in strong relationships, where client satisfaction and ethical practices are paramount. With a passion for connecting people with their dream homes, Parth leads our team with dedication and a forward-thinking approach.
                    </p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <?php
            include('./components/footer.php'); // Include the consistent footer
        ?>
    </main>

    <script src="./javascript.js"></script>
</body>
</html>
