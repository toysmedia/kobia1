/**
 * Onboarding Guided Tour using Shepherd.js
 * Auto-launches on first login; can be manually restarted.
 */
import Shepherd from 'shepherd.js';
import 'shepherd.js/dist/css/shepherd.css';

const TOUR_COMPLETED_KEY = 'oxnet_tour_completed';

function createTour() {
    const tour = new Shepherd.Tour({
        useModalOverlay: true,
        defaultStepOptions: {
            cancelIcon: { enabled: true },
            classes: 'shepherd-theme-arrows',
            scrollTo: { behavior: 'smooth', block: 'center' },
            buttons: [
                {
                    text: 'Skip',
                    action: tour => tour.cancel(),
                    classes: 'btn btn-outline-secondary btn-sm',
                },
                {
                    text: 'Next →',
                    action: tour => tour.next(),
                    classes: 'btn btn-primary btn-sm',
                },
            ],
        },
    });

    const steps = [
        {
            id: 'welcome',
            title: '👋 Welcome to OxNet Admin!',
            text: 'This quick tour will walk you through the key features of your ISP management portal. It only takes 2 minutes!',
            buttons: [
                { text: 'Skip Tour', action: () => tour.cancel(), classes: 'btn btn-outline-secondary btn-sm' },
                { text: 'Start Tour →', action: () => tour.next(), classes: 'btn btn-primary btn-sm' },
            ],
        },
        {
            id: 'dashboard',
            attachTo: { element: '[data-tour="dashboard"]', on: 'right' },
            title: '📊 Dashboard Overview',
            text: 'Your dashboard gives you a real-time snapshot of revenue, active subscribers, and system health.',
        },
        {
            id: 'routers',
            attachTo: { element: '[data-tour="routers"]', on: 'right' },
            title: '🔌 MikroTik Routers',
            text: 'Add and manage your MikroTik routers here. OxNet can auto-configure PPPoE profiles and hotspot settings.',
        },
        {
            id: 'subscribers',
            attachTo: { element: '[data-tour="subscribers"]', on: 'right' },
            title: '👥 Customers / PPPoE Users',
            text: 'Create and manage your PPPoE and hotspot subscribers. Assign packages, view usage data, and handle expirations.',
        },
        {
            id: 'packages',
            attachTo: { element: '[data-tour="packages"]', on: 'right' },
            title: '📦 Packages',
            text: 'Define your internet packages with speed limits, pricing, and validity periods.',
        },
        {
            id: 'payments',
            attachTo: { element: '[data-tour="payments"]', on: 'right' },
            title: '💳 Payments & Billing',
            text: 'Track M-Pesa payments, issue receipts, and manage billing for your customers.',
        },
        {
            id: 'settings',
            attachTo: { element: '[data-tour="settings"]', on: 'right' },
            title: '⚙️ Settings',
            text: 'Configure your M-Pesa Daraja credentials, SMS gateway, MikroTik details, and branding.',
        },
        {
            id: 'reports',
            attachTo: { element: '[data-tour="reports"]', on: 'right' },
            title: '📈 Reports',
            text: 'Generate revenue reports, PPPoE sales summaries, and package performance analytics.',
        },
        {
            id: 'notifications',
            attachTo: { element: '[data-tour="notifications-bell"]', on: 'bottom' },
            title: '🔔 Notifications',
            text: 'Important alerts, subscription warnings, and system messages appear here.',
        },
        {
            id: 'support-chat',
            attachTo: { element: '[data-tour="support-chat"]', on: 'bottom' },
            title: '💬 Support Chat',
            text: 'Need help? Chat directly with the OxNet support team from anywhere in the portal.',
        },
        {
            id: 'subscription',
            attachTo: { element: '[data-tour="subscription-countdown"]', on: 'bottom' },
            title: '⏱️ Subscription Status',
            text: 'Your subscription countdown is always visible here. Renew before it expires to avoid service interruption.',
        },
        {
            id: 'complete',
            title: '🎉 You\'re all set!',
            text: 'You\'re ready to manage your ISP like a pro. You can restart this tour anytime from the <strong>user menu (top-right) → Restart Tour</strong>.',
            buttons: [
                { text: 'Finish', action: () => tour.complete(), classes: 'btn btn-success btn-sm' },
            ],
        },
    ];

    // Only add steps where the target element exists (skip missing ones gracefully)
    steps.forEach(step => {
        if (step.attachTo) {
            const el = document.querySelector(step.attachTo.element);
            if (!el) {
                delete step.attachTo;
            }
        }
        tour.addStep(step);
    });

    return tour;
}

/**
 * Mark tour as completed via AJAX and store in localStorage as fallback.
 */
async function markTourCompleted() {
    localStorage.setItem(TOUR_COMPLETED_KEY, '1');

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        await fetch(window.baseUrl + '/admin/tour/complete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        });
    } catch (_) {
        // Non-critical — localStorage fallback is sufficient
    }
}

/**
 * Initialise the tour. Called from the admin layout when the page loads.
 * @param {boolean} tourAlreadyCompleted - passed from Blade via server-side flag
 */
export function initTour(tourAlreadyCompleted = false) {
    if (tourAlreadyCompleted) return;
    if (localStorage.getItem(TOUR_COMPLETED_KEY) === '1') return;

    const tour = createTour();

    tour.on('complete', markTourCompleted);
    tour.on('cancel', markTourCompleted);

    // Small delay so the page layout is fully rendered
    setTimeout(() => tour.start(), 800);
}

/**
 * Allow a "Restart Tour" button to re-trigger the tour.
 */
export function restartTour() {
    localStorage.removeItem(TOUR_COMPLETED_KEY);
    const tour = createTour();
    tour.on('complete', markTourCompleted);
    tour.on('cancel', markTourCompleted);
    tour.start();
}

// Attach restart handler to any element with data-action="restart-tour"
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-action="restart-tour"]').forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            restartTour();
        });
    });
});
