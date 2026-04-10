{{-- AI Chat Widget — floats bottom-right on all portals --}}
@props(['portal' => 'guest'])

<div id="ai-chat-widget" data-portal="{{ $portal }}">
    {{-- Toggle button --}}
    <button id="ai-chat-toggle" aria-label="Open AI Assistant"
            style="position:fixed;bottom:90px;right:24px;z-index:9999;
                   width:52px;height:52px;border-radius:50%;border:none;
                   background:linear-gradient(135deg,#4f46e5,#7c3aed);
                   color:#fff;box-shadow:0 4px 16px rgba(79,70,229,.45);
                   cursor:pointer;display:flex;align-items:center;justify-content:center;
                   transition:transform .2s;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
            <path d="M2.678 11.894a1 1 0 0 1 .287.801 10.97 10.97 0 0 1-.398 2c1.395-.323 2.247-.697 2.634-.893a1 1 0 0 1 .71-.074A8.06 8.06 0 0 0 8 14c3.996 0 7-2.807 7-6 0-3.192-3.004-6-7-6S1 4.808 1 8c0 1.468.617 2.83 1.678 3.894zm-3.631 4.028a11.037 11.037 0 0 0 .714-3.227C-.757 11.576-1 9.825-1 8 -1 3.806 3.134.5 8 .5s9 3.306 9 7.5-4.134 7.5-9 7.5a9.06 9.06 0 0 1-2.347-.306c-.52.263-1.639.742-3.468 1.167-.902.205-1.467-.765-.938-1.539z"/>
        </svg>
    </button>

    {{-- Chat panel --}}
    <div id="ai-chat-panel"
         style="position:fixed;bottom:155px;right:24px;z-index:9998;width:340px;max-width:calc(100vw - 48px);
                background:var(--bs-body-bg,#fff);border:1px solid rgba(0,0,0,.12);border-radius:16px;
                box-shadow:0 8px 32px rgba(0,0,0,.18);display:none;flex-direction:column;
                overflow:hidden;font-family:inherit;">
        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;padding:14px 16px;
                    display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;background:rgba(255,255,255,.25);border-radius:50%;
                            display:flex;align-items:center;justify-content:center;font-size:16px;">🤖</div>
                <div>
                    <div style="font-weight:600;font-size:.9rem;">OxBot</div>
                    <div style="font-size:.75rem;opacity:.85;">AI Assistant</div>
                </div>
            </div>
            <button id="ai-chat-close" style="background:none;border:none;color:#fff;cursor:pointer;font-size:1.2rem;padding:0 4px;" aria-label="Close">✕</button>
        </div>

        {{-- Messages --}}
        <div id="ai-messages" style="flex:1;overflow-y:auto;padding:12px;max-height:300px;
                                      display:flex;flex-direction:column;gap:8px;">
            <div class="ai-msg ai-msg--bot" style="align-self:flex-start;max-width:85%;">
                <div style="background:#f3f4ff;color:#1e1b4b;padding:10px 12px;border-radius:12px 12px 12px 2px;font-size:.85rem;line-height:1.5;">
                    👋 Hi! I'm OxBot, your AI assistant. How can I help you today?
                </div>
            </div>
        </div>

        {{-- Typing indicator --}}
        <div id="ai-typing" style="display:none;padding:4px 12px 0;">
            <div style="background:#f3f4ff;color:#6b7280;padding:8px 12px;border-radius:12px 12px 12px 2px;
                        font-size:.8rem;display:inline-block;">
                <span class="ai-dot">●</span><span class="ai-dot">●</span><span class="ai-dot">●</span>
            </div>
        </div>

        {{-- Input --}}
        <div style="padding:10px;border-top:1px solid rgba(0,0,0,.08);display:flex;gap:8px;">
            <input id="ai-input" type="text" placeholder="Ask me anything…"
                   style="flex:1;border:1px solid #d1d5db;border-radius:8px;padding:8px 12px;font-size:.85rem;outline:none;background:var(--bs-body-bg,#fff);color:var(--bs-body-color,#212529);"
                   maxlength="500" autocomplete="off" />
            <button id="ai-send"
                    style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;
                           border-radius:8px;padding:8px 12px;cursor:pointer;font-size:.9rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083l6-15Zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471-.63 1.178Z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

@vite(['resources/js/ai-chat.js'])

<style>
.ai-dot { animation: ai-blink 1.4s infinite both; font-size:.6rem; margin:0 1px; }
.ai-dot:nth-child(2) { animation-delay:.2s; }
.ai-dot:nth-child(3) { animation-delay:.4s; }
@keyframes ai-blink { 0%,80%,100% { opacity:.3; } 40% { opacity:1; } }
#ai-chat-toggle:hover { transform: scale(1.1); }
</style>