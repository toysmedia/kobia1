/**
 * Dark / Light Mode Toggle
 * Persists preference to localStorage and applies data-theme attribute.
 */
const STORAGE_KEY = 'oxnet_theme';
const DARK = 'dark';
const LIGHT = 'light';

function getStoredTheme() {
    return localStorage.getItem(STORAGE_KEY) || LIGHT;
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.body.setAttribute('data-bs-theme', theme);

    const btn = document.getElementById('theme-toggle');
    if (btn) {
        const icon = btn.querySelector('i');
        if (icon) {
            icon.className = theme === DARK ? 'bx bx-sun bx-sm' : 'bx bx-moon bx-sm';
        }
        btn.setAttribute('title', theme === DARK ? 'Switch to Light Mode' : 'Switch to Dark Mode');
    }
}

function toggleTheme() {
    const current = getStoredTheme();
    const next = current === DARK ? LIGHT : DARK;
    localStorage.setItem(STORAGE_KEY, next);
    applyTheme(next);
}

export function initDarkMode() {
    // Apply stored theme immediately to avoid flash
    applyTheme(getStoredTheme());

    const btn = document.getElementById('theme-toggle');
    if (btn) {
        btn.addEventListener('click', toggleTheme);
    }
}

// Apply theme as early as possible (before DOMContentLoaded)
applyTheme(getStoredTheme());

document.addEventListener('DOMContentLoaded', initDarkMode);
