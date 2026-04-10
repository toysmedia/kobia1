<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\System\AiConversation;
use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiAssistantController extends Controller
{
    public function __construct(private readonly AiAssistantService $aiService) {}

    /**
     * POST /api/ai/chat
     * Accept a question from any portal and return an AI-generated answer.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question'   => ['required', 'string', 'max:1000'],
            'portal'     => ['required', 'string', 'in:guest,admin,customer,community,login'],
            'session_id' => ['nullable', 'string', 'max:100'],
        ]);

        $sessionId = $validated['session_id'] ?? Str::uuid()->toString();
        $portal    = $validated['portal'];
        $question  = trim($validated['question']);

        // Resolve authenticated user context
        $tenantId = null;
        $userId   = null;
        $userType = 'guest';

        if (auth('admin')->check()) {
            $userId   = auth('admin')->id();
            $userType = 'admin';
            $tenantId = null; // admin is tenant-scoped via middleware
        } elseif (auth('customer')->check()) {
            $userId   = auth('customer')->id();
            $userType = 'customer';
        } elseif (auth('community')->check()) {
            $userId   = auth('community')->id();
            $userType = 'community_user';
        } elseif (auth('super_admin')->check()) {
            $userId   = auth('super_admin')->id();
            $userType = 'super_admin';
        }

        $result = $this->aiService->ask(
            question:  $question,
            portal:    $portal,
            tenantId:  $tenantId,
            userId:    $userId ? (string) $userId : null,
            userType:  $userType,
            sessionId: $sessionId
        );

        return response()->json($result);
    }

    /**
     * POST /api/ai/feedback
     * Record whether the user found the AI answer helpful.
     */
    public function feedback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'integer'],
            'helpful'         => ['required', 'boolean'],
        ]);

        $conversation = AiConversation::find($validated['conversation_id']);

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversation not found.'], 404);
        }

        $conversation->update(['was_helpful' => $validated['helpful']]);

        return response()->json(['success' => true]);
    }
}
