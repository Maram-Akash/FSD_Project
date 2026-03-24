/**
 * Skill Learning Academy - Main JavaScript
 * ES6 Syntax
 */

// Close alerts automatically after 5 seconds
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Form validation helper
const validateEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

// Search debounce for catalog
let searchTimeout;
const searchInput = document.getElementById('course-search');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const form = e.target.closest('form');
            if (form) form.submit();
        }, 400);
    });
}

// Toggle wishlist (AJAX)
async function toggleWishlist(courseId, button) {
    try {
        const res = await fetch(`/api/wishlist.php?action=toggle&course_id=${courseId}`);
        const data = await res.json();
        if (data.success) {
            button.classList.toggle('in-wishlist', data.in_wishlist);
            button.textContent = data.in_wishlist ? '★ Saved' : '☆ Save';
        }
    } catch (err) {
        console.error('Wishlist error:', err);
    }
}
