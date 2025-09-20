// Mobile menu toggle
const menuButton = document.getElementById('menu-button');
const closeMenuButton = document.getElementById('close-menu-button');
const mobileMenu = document.getElementById('mobile-menu');

menuButton.addEventListener('click', () => {
    mobileMenu.classList.remove('-translate-x-full');
});

closeMenuButton.addEventListener('click', () => {
    mobileMenu.classList.add('-translate-x-full');
});

// Scroll Reveal Animation
const scrollRevealElements = document.querySelectorAll('.scroll-reveal');

const observerOptions = {
    root: null, // viewport
    rootMargin: '0px',
    threshold: 0.1 // 10% of the element must be visible
};

const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            // Optional: Stop observing once visible if you only want it to animate once
            // observer.unobserve(entry.target);
        } else {
            // Optional: Remove 'is-visible' if you want the animation to replay on scroll up
            // entry.target.classList.remove('is-visible');
        }
    });
}, observerOptions);

scrollRevealElements.forEach(el => {
    observer.observe(el);
});