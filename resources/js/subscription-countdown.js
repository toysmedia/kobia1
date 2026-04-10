/**
 * Subscription Countdown Timer
 * Reads data-expires-at attribute and updates every second.
 * Color changes: < 7 days = warning, < 3 days = danger, expired = locked message.
 */
export function initSubscriptionCountdown() {
    const el = document.getElementById('subscription-countdown');
    if (!el) return;

    const expiresAtRaw = el.dataset.expiresAt;
    if (!expiresAtRaw) {
        el.innerHTML = '<span class="text-muted small">No expiry date</span>';
        return;
    }

    const expiresAt = new Date(expiresAtRaw).getTime();

    function update() {
        const now = Date.now();
        const diff = expiresAt - now;

        if (diff <= 0) {
            el.innerHTML = `
                <span class="badge bg-danger fw-bold">
                    <i class="bx bx-lock-alt me-1"></i>EXPIRED
                </span>
                <a href="/subscription/renew" class="ms-2 text-danger small fw-bold">Renew Now</a>`;
            el.closest('.subscription-wrapper')?.classList.add('expired');
            clearInterval(timer);
            return;
        }

        const days    = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours   = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        const pad = n => String(n).padStart(2, '0');

        let badgeClass = 'bg-success';
        let icon = 'bx-time-five';

        if (days < 3) {
            badgeClass = 'bg-danger';
            icon = 'bx-error-circle';
        } else if (days < 7) {
            badgeClass = 'bg-warning text-dark';
            icon = 'bx-alarm-exclamation';
        }

        el.innerHTML = `
            <span class="badge ${badgeClass} d-inline-flex align-items-center gap-1 px-2 py-1">
                <i class="bx ${icon}"></i>
                ${days}d ${pad(hours)}h ${pad(minutes)}m ${pad(seconds)}s
            </span>`;
    }

    update();
    const timer = setInterval(update, 1000);
}

document.addEventListener('DOMContentLoaded', initSubscriptionCountdown);
