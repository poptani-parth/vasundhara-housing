<nav
    class="bg-white shadow-lg py-3 px-4 sm:px-8 md:px-16 lg:px-24 flex justify-between items-center fixed w-full z-50 rounded-b-xl">
    <a href="./index.php" class="text-xl font-extrabold text-blue-700 tracking-wider">VH Housing</a>
    <div class="hidden md:flex space-x-8">
        <a href="./index.php"
            class="text-gray-700 hover:text-blue-600 transition duration-300 font-semibold text-sm relative group">
            Home
            <span
                class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="./properties.php"
            class="text-gray-700 hover:text-blue-600 transition duration-300 font-semibold text-sm relative group">
            Properties
            <span
                class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="./about.php"
            class="text-gray-700 hover:text-blue-600 transition duration-300 font-semibold text-sm relative group">
            About Us
            <span
                class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="./contact.php"
            class="text-gray-700 hover:text-blue-600 transition duration-300 font-semibold text-sm relative group">
            Contact
            <span
                class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="./login.php"
            class="text-gray-700 hover:text-blue-600 transition duration-300 font-semibold text-sm relative group">
            Login
            <span
                class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 transition-all duration-300 group-hover:w-full"></span>
        </a>
    </div>
    <div class="md:hidden">
        <button id="menu-button" class="text-gray-700 hover:text-blue-600 focus:outline-none p-1 rounded-md">
            <i class="ri-menu-line text-xl"></i>
        </button>
    </div>
</nav>
<!-- Mobile Menu (hidden by default) -->
<div id="mobile-menu"
    class="fixed inset-0 bg-white z-40 flex flex-col items-center justify-center space-y-6 transform -translate-x-full transition-transform duration-300 ease-in-out shadow-2xl">
    <button id="close-menu-button"
        class="absolute top-4 right-4 text-gray-700 hover:text-blue-600 focus:outline-none p-2 rounded-md">
        <i class="ri-close-line text-2xl"></i>
    </button>
    <a href="./index.php" class="text-gray-800 hover:text-blue-600 text-xl font-semibold">Home</a>
    <a href="./properties.php" class="text-gray-800 hover:text-blue-600 text-xl font-semibold">Properties</a>
    <a href="./about.php" class="text-gray-800 hover:text-blue-600 text-xl font-semibold">About Us</a>
    <a href="./contact.php" class="text-gray-800 hover:text-blue-600 text-xl font-semibold">Contact</a>
</div>