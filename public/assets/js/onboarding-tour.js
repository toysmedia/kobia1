/**
 * OxNet Admin Onboarding Tour — Vanilla JS (no external dependencies)
 * Auto-launches on first visit; can be restarted via data-action="restart-tour"
 */
(function () {
    'use strict';

    var TOUR_KEY = 'oxnet_tour_completed';

    var steps = [
        {
            id: 'welcome',
            title: '👋 Welcome to OxNet Admin!',
            text: 'This quick tour will walk you through the key features of your ISP management portal. It only takes 2 minutes!',
            position: 'center',
        },
        {
            id: 'dashboard',
            selector: '[data-tour="dashboard"]',
            title: '📊 Dashboard Overview',
            text: 'Your dashboard gives you a real-time snapshot of revenue, active subscribers, and system health.',
            position: 'right',
        },
        {
            id: 'subscribers',
            selector: '[data-tour="subscribers"]',
            title: '👥 Customers / PPPoE Users',
            text: 'Create and manage your PPPoE and hotspot subscribers. Assign packages, view usage data, and handle expirations.',
            position: 'right',
        },
        {
            id: 'packages',
            selector: '[data-tour="packages"]',
            title: '📦 Packages',
            text: 'Define your internet packages with speed limits, pricing, and validity periods.',
            position: 'right',
        },
        {
            id: 'routers',
            selector: '[data-tour="routers"]',
            title: '🔌 MikroTik Routers',
            text: 'Add and manage your MikroTik routers here. OxNet can auto-configure PPPoE profiles and hotspot settings.',
            position: 'right',
        },
        {
            id: 'payments',
            selector: '[data-tour="payments"]',
            title: '💳 Payments & Billing',
            text: 'Track M-Pesa payments, issue receipts, and manage billing for your customers.',
            position: 'right',
        },
        {
            id: 'reports',
            selector: '[data-tour="reports"]',
            title: '📈 Reports',
            text: 'Generate revenue reports, PPPoE sales summaries, and package performance analytics.',
            position: 'right',
        },
        {
            id: 'settings',
            selector: '[data-tour="settings"]',
            title: '⚙️ Settings',
            text: 'Configure your M-Pesa Daraja credentials, SMS gateway, MikroTik details, and branding.',
            position: 'right',
        },
        {
            id: 'complete',
            title: '🎉 You\'re all set!',
            text: 'You\'re ready to manage your ISP like a pro. You can restart this tour anytime from the navbar.',
            position: 'center',
        },
    ];

    var currentStep = 0;
    var overlay, tooltip, highlight;

    function createOverlay() {
        overlay = document.createElement('div');
        overlay.id = 'oxnet-tour-overlay';
        overlay.style.cssText = [
            'position:fixed;top:0;left:0;width:100%;height:100%;',
            'background:rgba(0,0,0,0.55);z-index:100000;',
            'pointer-events:none;',
        ].join('');
        document.body.appendChild(overlay);

        highlight = document.createElement('div');
        highlight.id = 'oxnet-tour-highlight';
        highlight.style.cssText = [
            'position:fixed;z-index:100001;border-radius:6px;',
            'box-shadow:0 0 0 9999px rgba(0,0,0,0.55);',
            'transition:all 0.3s ease;pointer-events:none;',
            'outline:3px solid #4f46e5;outline-offset:3px;',
        ].join('');
        document.body.appendChild(highlight);

        tooltip = document.createElement('div');
        tooltip.id = 'oxnet-tour-tooltip';
        tooltip.style.cssText = [
            'position:fixed;z-index:100002;background:#fff;',
            'border-radius:12px;padding:20px 24px;width:320px;max-width:90vw;',
            'box-shadow:0 8px 32px rgba(0,0,0,0.22);',
            'font-family:inherit;color:#1e1b4b;transition:all 0.25s ease;',
        ].join('');
        document.body.appendChild(tooltip);
    }

    function positionTooltip(el, position) {
        var padding = 12;
        var rect, tRect, top, left;

        tRect = tooltip.getBoundingClientRect();

        if (!el || position === 'center') {
            top = (window.innerHeight - tRect.height) / 2;
            left = (window.innerWidth - tRect.width) / 2;
            highlight.style.display = 'none';
        } else {
            rect = el.getBoundingClientRect();
            highlight.style.display = 'block';
            highlight.style.top = rect.top - 4 + 'px';
            highlight.style.left = rect.left - 4 + 'px';
            highlight.style.width = rect.width + 8 + 'px';
            highlight.style.height = rect.height + 8 + 'px';

            if (position === 'right') {
                top = rect.top + rect.height / 2 - tRect.height / 2;
                left = rect.right + padding;
                if (left + tRect.width > window.innerWidth - 16) {
                    left = rect.left - tRect.width - padding;
                }
            } else if (position === 'bottom') {
                top = rect.bottom + padding;
                left = rect.left + rect.width / 2 - tRect.width / 2;
            } else {
                top = rect.top - tRect.height - padding;
                left = rect.left + rect.width / 2 - tRect.width / 2;
            }

            top = Math.max(8, Math.min(top, window.innerHeight - tRect.height - 8));
            left = Math.max(8, Math.min(left, window.innerWidth - tRect.width - 8));
        }

        tooltip.style.top = top + 'px';
        tooltip.style.left = left + 'px';
    }

    function renderStep(index) {
        var step = steps[index];
        var el = step.selector ? document.querySelector(step.selector) : null;
        var isLast = index === steps.length - 1;
        var isFirst = index === 0;

        tooltip.innerHTML = [
            '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">',
            '  <span style="font-size:0.75rem;color:#7c3aed;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">',
            '    Step ' + (index + 1) + ' of ' + steps.length,
            '  </span>',
            '  <button id="oxnet-tour-skip" style="background:none;border:none;color:#9ca3af;cursor:pointer;font-size:1.1rem;padding:0;line-height:1;" title="Skip tour">✕</button>',
            '</div>',
            '<h5 style="margin:0 0 8px;font-size:1rem;font-weight:700;color:#1e1b4b;">' + step.title + '</h5>',
            '<p style="margin:0 0 16px;font-size:0.875rem;color:#4b5563;line-height:1.6;">' + step.text + '</p>',
            '<div style="display:flex;gap:8px;justify-content:',
            (isFirst ? 'flex-end' : 'space-between'),
            ';">',
            (!isFirst ? '<button id="oxnet-tour-prev" style="background:#f3f4f6;border:none;border-radius:6px;padding:7px 16px;cursor:pointer;font-size:0.85rem;color:#374151;font-weight:500;">← Back</button>' : ''),
            '<button id="oxnet-tour-next" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);border:none;border-radius:6px;padding:7px 18px;cursor:pointer;font-size:0.85rem;color:#fff;font-weight:600;">',
            (isLast ? '🎉 Finish' : 'Next →'),
            '</button>',
            '</div>',
        ].join('');

        // Position after render so we can measure
        setTimeout(function () {
            positionTooltip(el, step.position || 'right');
        }, 10);

        // Scroll element into view
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        document.getElementById('oxnet-tour-next').onclick = function () {
            if (isLast) {
                completeTour();
            } else {
                currentStep++;
                renderStep(currentStep);
            }
        };

        var prevBtn = document.getElementById('oxnet-tour-prev');
        if (prevBtn) {
            prevBtn.onclick = function () {
                currentStep--;
                renderStep(currentStep);
            };
        }

        document.getElementById('oxnet-tour-skip').onclick = function () {
            completeTour();
        };
    }

    function completeTour() {
        localStorage.setItem(TOUR_KEY, '1');
        cleanup();

        // Notify server (non-critical)
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            var baseUrl = (document.querySelector('meta[name="base-url"]') || {}).content || '';
            fetch(baseUrl + '/admin/tour/complete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfMeta.getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            }).catch(function () {});
        }
    }

    function cleanup() {
        if (overlay) { overlay.remove(); overlay = null; }
        if (highlight) { highlight.remove(); highlight = null; }
        if (tooltip) { tooltip.remove(); tooltip = null; }
    }

    function startTour() {
        cleanup();
        currentStep = 0;
        createOverlay();
        renderStep(currentStep);
    }

    function initTour(serverCompleted) {
        if (serverCompleted) return;
        if (localStorage.getItem(TOUR_KEY) === '1') return;
        setTimeout(startTour, 800);
    }

    // Expose globally
    window.startTour = startTour;
    window.initTour = initTour;

    // Wire up restart buttons
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-action="restart-tour"]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                localStorage.removeItem(TOUR_KEY);
                startTour();
            });
        });
    });

    // Auto-init: the components.onboarding-tour blade include sets window.__oxnetTourCompleted
    // Since this script has defer, the DOM is ready and inline scripts have already run
    var serverCompleted = window.__oxnetTourCompleted === true;
    initTour(serverCompleted);
})();