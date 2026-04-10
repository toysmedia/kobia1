/**
 * Support Chat Panel
 * Handles submitting new messages and polling for replies every 10 seconds.
 */
const POLL_INTERVAL_MS = 10_000;
let lastMessageId = 0;

function getBaseUrl() {
    return document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function appendMessage(msg, container) {
    const isAdmin = msg.sender_type === 'admin';
    const div = document.createElement('div');
    div.className = `chat-message d-flex ${isAdmin ? 'justify-content-end' : 'justify-content-start'} mb-2`;
    div.dataset.messageId = msg.id;

    const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    div.innerHTML = `
        <div class="chat-bubble ${isAdmin ? 'chat-bubble-admin' : 'chat-bubble-support'} rounded p-2 px-3 shadow-sm" style="max-width:80%">
            <div class="small fw-semibold mb-1">${isAdmin ? 'You' : 'OxNet Support'}</div>
            <div>${msg.message}</div>
            <div class="text-muted" style="font-size:0.7rem">${time}</div>
        </div>`;

    container.appendChild(div);
    if (msg.id > lastMessageId) lastMessageId = msg.id;
}

async function sendMessage(form) {
    const input = form.querySelector('[name="message"]');
    const message = input.value.trim();
    if (!message) return;

    try {
        const res = await fetch(`${getBaseUrl()}/admin/support-chat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message }),
        });

        if (res.ok) {
            const data = await res.json();
            const container = document.getElementById('chat-messages');
            if (container && data.message) {
                appendMessage(data.message, container);
                container.scrollTop = container.scrollHeight;
            }
            input.value = '';
        }
    } catch (_) {
        // Silently handle network errors
    }
}

async function pollMessages() {
    const container = document.getElementById('chat-messages');
    if (!container) return;

    try {
        const url = `${getBaseUrl()}/admin/support-chat/messages?since=${lastMessageId}`;
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return;

        const data = await res.json();
        if (data.messages && data.messages.length > 0) {
            data.messages.forEach(msg => {
                // Only append if not already in DOM
                if (!container.querySelector(`[data-message-id="${msg.id}"]`)) {
                    appendMessage(msg, container);
                }
            });
            container.scrollTop = container.scrollHeight;
        }
    } catch (_) {
        // Silently ignore
    }
}

export function initSupportChat() {
    const form = document.getElementById('support-chat-form');
    if (form) {
        form.addEventListener('submit', e => {
            e.preventDefault();
            sendMessage(form);
        });
    }

    // Seed lastMessageId from existing messages
    const container = document.getElementById('chat-messages');
    if (container) {
        container.querySelectorAll('[data-message-id]').forEach(el => {
            const id = parseInt(el.dataset.messageId, 10);
            if (id > lastMessageId) lastMessageId = id;
        });
        container.scrollTop = container.scrollHeight;
    }

    setInterval(pollMessages, POLL_INTERVAL_MS);
}

document.addEventListener('DOMContentLoaded', initSupportChat);
