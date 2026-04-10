{{-- Support Chat Panel (Offcanvas)
     Rendered when the user opens the support chat button.
--}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="supportChatPanel"
     aria-labelledby="supportChatLabel" style="width:380px;max-width:100vw">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title d-flex align-items-center gap-2" id="supportChatLabel">
            <i class="bx bx-support text-primary"></i>
            OxNet Support Chat
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-0">
        {{-- Message list --}}
        <div id="chat-messages" class="flex-grow-1 overflow-auto p-3 d-flex flex-column gap-1"
             style="min-height:0">

            @if($messages->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bx bx-message-square-dots bx-lg mb-2"></i>
                    <p class="mb-0 small">No messages yet. Send us a message and we'll respond shortly.</p>
                </div>
            @else
                @foreach($messages as $msg)
                    <div class="chat-message d-flex {{ $msg->sender_type === 'admin' ? 'justify-content-end' : 'justify-content-start' }} mb-1"
                         data-message-id="{{ $msg->id }}">
                        <div class="chat-bubble {{ $msg->sender_type === 'admin' ? 'chat-bubble-admin bg-primary text-white' : 'chat-bubble-support bg-light' }} rounded p-2 px-3 shadow-sm"
                             style="max-width:80%">
                            <div class="small fw-semibold mb-1">
                                {{ $msg->sender_type === 'admin' ? 'You' : 'OxNet Support' }}
                            </div>
                            <div>{{ $msg->message }}</div>
                            <div class="opacity-75" style="font-size:0.7rem">
                                {{ $msg->created_at->format('H:i') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Input --}}
        <div class="border-top p-3">
            <form id="support-chat-form" class="d-flex gap-2">
                @csrf
                <input type="text" name="message" class="form-control form-control-sm"
                       placeholder="Type a message..." autocomplete="off" required>
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bx bx-send"></i>
                </button>
            </form>
        </div>
    </div>
</div>
