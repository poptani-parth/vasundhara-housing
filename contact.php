<?php
    session_start(); // Always start the session at the very beginning of the script
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vasundhara Housing - Contact Us</title>
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

        <!-- Hero Section - Contact Page -->
        <section class="relative w-full py-20 sm:py-24 hero-bg-pattern flex items-center justify-center text-center pt-24">
            <div class="relative z-1 max-w-3xl mx-auto p-4 sm:p-6 animate-slide-up-fade-in delay-200">
                <h1 class="font-black font-['Aharoni'] text-4xl sm:text-5xl md:text-6xl leading-tight mb-4 text-gray-900 drop-shadow-sm">Contact Us</h1>
                <p class="text-base sm:text-lg text-gray-700 mb-6">We'd love to hear from you. Reach out for any inquiries.</p>
            </div>
        </section>

        <!-- Contact Content Section -->
        <section class="w-full px-4 sm:px-8 md:px-16 lg:px-24 py-16 bg-white">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Form -->
                <div class="scroll-reveal delay-100">
                    <div class="flex items-center gap-3 mb-4">
                        <hr class="border-t-2 border-blue-600 w-12">
                        <h2 class="text-lg font-bold uppercase text-blue-600 tracking-wider">Get in Touch</h2>
                    </div>
                    
                    <h3 class="text-3xl sm:text-4xl font-['Sitka_Small'] font-bold tracking-tight text-gray-900 mb-8">Send Us a Message</h3>

                    <form id="contactForm" class="space-y-6">
                        <div>
                            <label for="fullName" class="block text-gray-700 text-sm font-medium mb-2">Full Name</label>
                            <input type="text" id="fullName" name="fullName" placeholder="Your Full Name"
                                class="input-focus-effect w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none text-sm transition duration-200">
                        </div>
                        <div>
                            <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="your@example.com"
                                class="input-focus-effect w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none text-sm transition duration-200">
                        </div>
                        <div>
                            <label for="subject" class="block text-gray-700 text-sm font-medium mb-2">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="Inquiry Subject"
                                class="input-focus-effect w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none text-sm transition duration-200">
                        </div>
                        <div>
                            <label for="message" class="block text-gray-700 text-sm font-medium mb-2">Your Message</label>
                            <textarea id="message" name="message" rows="6" placeholder="Type your message here..."
                                class="input-focus-effect w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none text-sm resize-y transition duration-200"></textarea>
                        </div>
                        <button type="submit" class="inline-block px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-lg hover:bg-blue-700 transition duration-300 ease-in-out text-base transform hover:scale-105">
                            Send Message <i class="ri-send-plane-line ml-2"></i>
                        </button>
                    </form>
                </div>

                <hr class="border-t-2 border-gray-200 my-8 lg:hidden col-span-full">

                <!-- Contact Information & Map -->
                <div class="scroll-reveal delay-300">
                    <div class="flex items-center gap-3 mb-4">
                        <hr class="border-t-2 border-blue-600 w-12">
                        <h2 class="text-lg font-bold uppercase text-blue-600 tracking-wider">Our Location</h2>
                    </div>
                    <h3 class="text-3xl sm:text-4xl font-['Sitka_Small'] font-bold tracking-tight text-gray-900 mb-8">Find Us on the Map</h3>

                    <div class="space-y-6 mb-8 text-gray-700 text-base">
                        <p class="flex items-start gap-3">
                            <i class="ri-map-pin-line text-2xl text-blue-600 flex-shrink-0"></i>
                            <span>
                                Vasundhara Housing HQ<br>
                                SG Highway, Ahmedabad, Gujarat, India
                            </span>
                        </p>
                        <p class="flex items-center gap-3">
                            <i class="ri-phone-line text-2xl text-blue-600"></i>
                            <a href="tel:+917201918520" class="hover:text-blue-600 transition duration-300">+91 72019 18520</a>
                        </p>
                        <p class="flex items-center gap-3">
                            <i class="ri-mail-line text-2xl text-blue-600"></i>
                            <a href="mailto:info@vasundharahousing.com" class="hover:text-blue-600 transition duration-300">info@vasundharahousing.com</a>
                        </p>
                        <p class="flex items-center gap-3">
                            <i class="ri-time-line text-2xl text-blue-600"></i>
                            <span>Mon - Sat: 9:00 AM - 6:00 PM</span>
                        </p>
                    </div>

                    <!-- Google Map Embed -->
                    <div class="w-full h-96 rounded-xl overflow-hidden shadow-lg border border-gray-200">
                        <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=SG%20Highway,ahmadabad+(Vasundhara%20Housing)&amp;t=&amp;z=10&amp;ie=UTF8&amp;iwloc=B&amp;output=embed">
                            <a href="https://www.mapsdirections.info/fr/calculer-la-population-sur-une-carte">Estimer la population sur la carte</a>
                        </iframe>
                    </div>
                </div>
            </div>
        </section>  
        <!-- Success Modal -->
        <div id="contactSuccessModal" class="fixed inset-0 hidden items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" aria-hidden="true"></div>
            <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white shadow-2xl p-6 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="mt-4 text-2xl font-semibold text-gray-800">Message Sent!</h2>
                <p class="mt-2 text-gray-600">Thank you for contacting us. We will get back to you shortly.</p>
                <div class="mt-6">
                    <button type="button" id="closeContactSuccessModal" class="px-6 py-2.5 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700">Close</button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php
            include('./components/footer.php'); // Include the consistent footer
        ?>
    </main>
    <script src="./javascript.js"></script>
    <script>
        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            const successModal = document.getElementById('contactSuccessModal');
            const closeModalButton = document.getElementById('closeContactSuccessModal');

            function openSuccessModal() {
                successModal.classList.remove('hidden');
                successModal.classList.add('flex');
            }

            function closeSuccessModal() {
                successModal.classList.add('hidden');
                successModal.classList.remove('flex');
            }

            closeModalButton.addEventListener('click', closeSuccessModal);

            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(contactForm);
                const submitButton = contactForm.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;

                submitButton.disabled = true;
                submitButton.innerHTML = 'Sending... <i class="ri-loader-4-line animate-spin ml-2"></i>';

                fetch('submit_contact.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            contactForm.reset();
                            openSuccessModal();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while sending your message. Please try again.');
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                    });
            });
        }
    </script>
</body>
</html>
