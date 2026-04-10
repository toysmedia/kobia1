/**
 * OxNet Performance Utilities
 * - Intersection Observer for lazy-loading components
 * - Debounced scroll/resize event handlers
 * - Preload critical resources
 */

/**
 * Debounce a function call.
 * @param {Function} fn
 * @param {number} delay
 * @returns {Function}
 */
export function debounce(fn, delay = 150) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

/**
 * Throttle a function call.
 * @param {Function} fn
 * @param {number} limit
 * @returns {Function}
 */
export function throttle(fn, limit = 100) {
    let inThrottle = false;
    return function (...args) {
        if (!inThrottle) {
            fn.apply(this, args);
            inThrottle = true;
            setTimeout(() => { inThrottle = false; }, limit);
        }
    };
}

/**
 * Lazy-load images using Intersection Observer.
 * Images should use data-src instead of src.
 */
export function initLazyImages() {
    const images = document.querySelectorAll('img[data-src]');
    if (!images.length) return;

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    if (img.dataset.srcset) img.srcset = img.dataset.srcset;
                    img.removeAttribute('data-src');
                    img.removeAttribute('data-srcset');
                    observer.unobserve(img);
                }
            });
        }, { rootMargin: '200px 0px' });

        images.forEach((img) => observer.observe(img));
    } else {
        // Fallback: load all images immediately
        images.forEach((img) => {
            img.src = img.dataset.src;
            if (img.dataset.srcset) img.srcset = img.dataset.srcset;
        });
    }
}

/**
 * Lazy-load iframes (e.g. embedded maps) using Intersection Observer.
 */
export function initLazyIframes() {
    const iframes = document.querySelectorAll('iframe[data-src]');
    if (!iframes.length) return;

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const iframe = entry.target;
                    iframe.src = iframe.dataset.src;
                    iframe.removeAttribute('data-src');
                    observer.unobserve(iframe);
                }
            });
        }, { rootMargin: '100px 0px' });

        iframes.forEach((iframe) => observer.observe(iframe));
    }
}

/**
 * Observe elements and add a CSS class when they enter the viewport.
 * @param {string} selector  CSS selector for elements to observe
 * @param {string} className CSS class to add when visible (default: 'in-view')
 */
export function initInViewObserver(selector = '[data-in-view]', className = 'in-view') {
    const elements = document.querySelectorAll(selector);
    if (!elements.length || !('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add(className);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    elements.forEach((el) => observer.observe(el));
}

/**
 * Add native lazy loading attribute to all images without it.
 */
export function addNativeLazyLoading() {
    document.querySelectorAll('img:not([loading])').forEach((img) => {
        img.setAttribute('loading', 'lazy');
    });
}

/**
 * Register optimised, debounced resize and scroll handlers.
 */
export function initDebouncedHandlers() {
    const resizeHandlers = [];
    const scrollHandlers = [];

    window.__oxnetResize = (fn) => resizeHandlers.push(fn);
    window.__oxnetScroll = (fn) => scrollHandlers.push(fn);

    window.addEventListener('resize', debounce(() => {
        resizeHandlers.forEach((fn) => fn());
    }, 150));

    window.addEventListener('scroll', throttle(() => {
        scrollHandlers.forEach((fn) => fn());
    }, 100), { passive: true });
}

/**
 * Initialise all performance utilities.
 */
export function initPerformance() {
    initLazyImages();
    initLazyIframes();
    addNativeLazyLoading();
    initInViewObserver();
    initDebouncedHandlers();
}

document.addEventListener('DOMContentLoaded', initPerformance);
