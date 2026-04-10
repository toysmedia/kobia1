/**
 * OxNet — Scroll-to-Top Button
 * Shows a floating button after the user scrolls 300px down.
 */

function createScrollToTopButton() {
    const btn = document.createElement('button');
    btn.id = 'scroll-to-top';
    btn.setAttribute('aria-label', 'Scroll to top');
    btn.setAttribute('title', 'Scroll to top');
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>';
    document.body.appendChild(btn);
    return btn;
}

export function initScrollToTop() {
    const btn = document.getElementById('scroll-to-top') || createScrollToTopButton();

    const SCROLL_THRESHOLD = 300;

    function updateVisibility() {
        if (window.scrollY > SCROLL_THRESHOLD) {
            btn.classList.add('visible');
        } else {
            btn.classList.remove('visible');
        }
    }

    window.addEventListener('scroll', updateVisibility, { passive: true });

    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Initial check
    updateVisibility();
}

document.addEventListener('DOMContentLoaded', initScrollToTop);
