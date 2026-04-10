<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\SupportMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * Handles the tenant support chat between tenant admin and Super Admin.
 */
class SupportChatController extends Controller
{
    /**
     * Return the chat panel view (for modal/slide-out).
     */
    public function index(): View
    {
        $messages = SupportMessage::latest()->limit(50)->get()->reverse()->values();

        return view('partials.support-chat', compact('messages'));
    }

    /**
     * Store a new message from the tenant admin.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = SupportMessage::create([
            'user_id'     => auth('admin')->id(),
            'message'     => $validated['message'],
            'sender_type' => 'admin',
            'is_read'     => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id'          => $message->id,
                'message'     => $message->message,
                'sender_type' => $message->sender_type,
                'created_at'  => $message->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * Return recent messages as JSON (for polling).
     */
    public function getMessages(Request $request): JsonResponse
    {
        $since = $request->query('since');

        $query = SupportMessage::latest()->limit(50);

        if ($since) {
            $query->where('id', '>', (int) $since);
        }

        $messages = $query->get()->map(fn ($m) => [
            'id'          => $m->id,
            'message'     => e($m->message),
            'sender_type' => $m->sender_type,
            'is_read'     => $m->is_read,
            'created_at'  => $m->created_at->toISOString(),
        ]);

        // Mark super_admin messages as read when admin fetches them
        SupportMessage::fromSuperAdmin()->unread()->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['messages' => $messages]);
    }
}
