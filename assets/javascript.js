function locomotive() {
    (function () {
        const locomotiveScroll = new LocomotiveScroll();
    })();
}
locomotive();

document.querySelectorAll('.filter-tenant-btn').forEach(button => {
    button.addEventListener('click', () => {
        // Remove active classes from all buttons
        document.querySelectorAll('.filter-tenant-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'shadow-md');
            btn.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
        });
        // Add active classes to the clicked button
        button.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
        button.classList.add('bg-blue-600', 'text-white', 'shadow-md');
    });
});