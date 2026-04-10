/**
 * Notifications Bell Polling
 * Polls the unread count endpoint every 30 seconds and updates the badge.
 */
const POLL_INTERVAL_MS = 30_000;

function updateBadge(count) {
    const badge = document.getElementById('notifications-badge');
    if (!badge) return;

    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'inline-flex';
    } else {
        badge.style.display = 'none';
    }
}

async function fetchUnreadCount() {
    try {
        const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';
        const res = await fetch(`${baseUrl}/admin/notifications/count`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return;
        const data = await res.json();
        updateBadge(data.count ?? 0);
    } catch (_) {
        // Silently ignore network errors
    }
}

export function initNotificationPolling() {
    fetchUnreadCount();
    setInterval(fetchUnreadCount, POLL_INTERVAL_MS);
}

document.addEventListener('DOMContentLoaded', initNotificationPolling);
