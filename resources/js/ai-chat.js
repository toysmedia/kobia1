/**
 * OxNet AI Chat Widget — ai-chat.js
 * Handles the floating OxBot chat bubble for all portals.
 */

(function () {
    'use strict';

    const CHAT_API  = '/api/ai/chat';
    const FB_API    = '/api/ai/feedback';
    const SESSION_KEY = 'oxbot_session_id';

    // ---- DOM refs (resolved after DOMContentLoaded) ----
    let widget, panel, toggleBtn, closeBtn, messagesEl, inputEl, sendBtn, typingEl;
    let portal = 'guest';
    let sessionId = null;

    function init() {
        widget = document.getElementById('ai-chat-widget');
        if (!widget) return; // widget not on this page

        portal       = widget.dataset.portal || 'guest';
        panel        = document.getElementById('ai-chat-panel');
        toggleBtn    = document.getElementById('ai-chat-toggle');
        closeBtn     = document.getElementById('ai-chat-close');
        messagesEl   = document.getElementById('ai-messages');
        inputEl      = document.getElementById('ai-input');
        sendBtn      = document.getElementById('ai-send');
        typingEl     = document.getElementById('ai-typing');

        sessionId = sessionStorage.getItem(SESSION_KEY) || generateSessionId();
        sessionStorage.setItem(SESSION_KEY, sessionId);

        toggleBtn.addEventListener('click', openPanel);
        closeBtn.addEventListener('click', closePanel);
        sendBtn.addEventListener('click', handleSend);
        inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleSend(); }
        });
    }

    function openPanel() {
        panel.style.display = 'flex';
        toggleBtn.style.display = 'none';
        inputEl.focus();
        scrollToBottom();
    }

    function closePanel() {
        panel.style.display = 'none';
        toggleBtn.style.display = 'flex';
    }

    async function handleSend() {
        const question = inputEl.value.trim();
        if (!question) return;

        inputEl.value = '';
        sendBtn.disabled = true;
        inputEl.disabled = true;

        appendMessage('user', question);
        showTyping(true);
        scrollToBottom();

        try {
            const res = await fetch(CHAT_API, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ question, portal, session_id: sessionId }),
            });

            const data = await res.json();

            if (data.session_id) sessionId = data.session_id;
            sessionStorage.setItem(SESSION_KEY, sessionId);

            showTyping(false);

            if (data.success !== false) {
                appendMessage('bot', data.answer, data.conversation_id, data.was_from_kb);
            } else {
                appendMessage('bot', data.answer || "Sorry, I couldn't process that. Please try again. 🙏");
            }
        } catch (err) {
            showTyping(false);
            appendMessage('bot', "⚠️ Connection error. Please check your internet and try again.");
        } finally {
            sendBtn.disabled = false;
            inputEl.disabled = false;
            inputEl.focus();
            scrollToBottom();
        }
    }

    function appendMessage(role, text, conversationId, wasFromKb) {
        const wrapper = document.createElement('div');
        wrapper.style.cssText = 'display:flex;flex-direction:column;' + (role === 'user' ? 'align-items:flex-end;' : 'align-items:flex-start;');

        const bubble = document.createElement('div');
        bubble.style.cssText = 'max-width:85%;padding:10px 12px;border-radius:' +
            (role === 'user' ? '12px 12px 2px 12px;background:#4f46e5;color:#fff;' : '12px 12px 12px 2px;background:#f3f4ff;color:#1e1b4b;') +
            'font-size:.85rem;line-height:1.5;';
        bubble.innerHTML = renderMarkdown(text);
        wrapper.appendChild(bubble);

        // Feedback buttons for bot messages
        if (role === 'bot' && conversationId) {
            const fb = document.createElement('div');
            fb.style.cssText = 'display:flex;gap:6px;margin-top:4px;';
            fb.innerHTML = `
                <button data-cid="${conversationId}" data-val="1" class="ai-fb-btn" title="Helpful"
                    style="background:none;border:1px solid #d1d5db;border-radius:6px;padding:2px 8px;cursor:pointer;font-size:.75rem;">👍</button>
                <button data-cid="${conversationId}" data-val="0" class="ai-fb-btn" title="Not helpful"
                    style="background:none;border:1px solid #d1d5db;border-radius:6px;padding:2px 8px;cursor:pointer;font-size:.75rem;">👎</button>
            `;
            fb.querySelectorAll('.ai-fb-btn').forEach(btn => btn.addEventListener('click', handleFeedback));
            wrapper.appendChild(fb);
        }

        if (wasFromKb) {
            const tag = document.createElement('div');
            tag.style.cssText = 'font-size:.7rem;color:#6b7280;margin-top:2px;';
            tag.textContent = '📚 From knowledge base';
            wrapper.appendChild(tag);
        }

        messagesEl.appendChild(wrapper);
    }

    async function handleFeedback(e) {
        const btn = e.currentTarget;
        const conversationId = parseInt(btn.dataset.cid);
        const helpful = btn.dataset.val === '1';

        // Disable both feedback buttons
        btn.closest('div').querySelectorAll('.ai-fb-btn').forEach(b => { b.disabled = true; b.style.opacity = '.5'; });

        try {
            await fetch(FB_API, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ conversation_id: conversationId, helpful }),
            });
        } catch (_) { /* silent fail */ }
    }

    function renderMarkdown(text) {
        if (!text) return '';
        let html = text
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/`(.+?)`/g, '<code style="background:#e5e7ff;padding:1px 4px;border-radius:3px;">$1</code>')
            .replace(/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>')
            .replace(/^[-*]\s(.+)/gm, '<li>$1</li>')
            .replace(/\n/g, '<br>');
        // Wrap consecutive <li> in <ul>
        html = html.replace(/(<li>.*?<\/li>)+/gs, function(m) { return '<ul style="margin:.25rem 0 .25rem 1rem;padding:0;">' + m + '</ul>'; });
        return html;
    }

    function showTyping(show) {
        typingEl.style.display = show ? 'block' : 'none';
        if (show) scrollToBottom();
    }

    function scrollToBottom() {
        setTimeout(() => { messagesEl.scrollTop = messagesEl.scrollHeight; }, 50);
    }

    function generateSessionId() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
