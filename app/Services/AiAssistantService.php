<?php

namespace App\Services;

use App\Models\AiTenantSetting;
use App\Models\System\AiConversation;
use App\Models\System\AiKnowledgeBase;
use App\Models\System\AiUnansweredQuestion;
use App\Models\System\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    private const UNCERTAIN_PHRASES = [
        "i'm not sure",
        "i don't know",
        "i cannot",
        "i can't",
        "not certain",
        "unclear",
        "i'm unable",
    ];

    /**
     * Main entry point: answer a user question from any portal.
     *
     * @return array{success: bool, answer: string, session_id: string, was_from_kb: bool, conversation_id: int|null}
     */
    public function ask(
        string  $question,
        string  $portal,
        ?int    $tenantId,
        ?string $userId,
        ?string $userType,
        string  $sessionId
    ): array {
        if (!config('ai.enabled', true)) {
            return $this->fallback($sessionId);
        }

        // Rate-limit check: max N questions per minute per session
        $rateKey  = 'ai_rate:' . $sessionId;
        $attempts = (int) Cache::get($rateKey, 0);
        $limit    = (int) config('ai.rate_limit_per_minute', 20);

        if ($attempts >= $limit) {
            return [
                'success'         => false,
                'answer'          => 'You\'ve sent too many messages. Please wait a moment before trying again. 🙏',
                'session_id'      => $sessionId,
                'was_from_kb'     => false,
                'conversation_id' => null,
            ];
        }

        Cache::put($rateKey, $attempts + 1, now()->addMinute());

        $startTime = microtime(true);

        // 1. Try knowledge base first
        $kbEntry = $this->searchKnowledgeBase($question, $portal);

        if ($kbEntry) {
            $elapsed = (int) ((microtime(true) - $startTime) * 1000);
            $conversation = $this->logConversation(
                sessionId:       $sessionId,
                tenantId:        $tenantId,
                userId:          $userId ? (int) $userId : null,
                userType:        $userType,
                portal:          $portal,
                question:        $question,
                answer:          $kbEntry->answer,
                wasAnswered:     true,
                tokensUsed:      null,
                responseTimeMs:  $elapsed,
                metadata:        ['source' => 'knowledge_base', 'kb_id' => $kbEntry->id]
            );

            return [
                'success'         => true,
                'answer'          => $kbEntry->answer,
                'session_id'      => $sessionId,
                'was_from_kb'     => true,
                'conversation_id' => $conversation->id,
            ];
        }

        // 2. Fall back to OpenAI
        $tenant       = $tenantId ? Tenant::find($tenantId) : null;
        $prompt       = $this->buildSystemPrompt($portal, $tenant);
        $history      = $this->getChatHistory($sessionId);
        $openAiResult = $this->callOpenAI($question, $prompt, $history);

        $elapsed = (int) ((microtime(true) - $startTime) * 1000);

        $conversation = $this->logConversation(
            sessionId:       $sessionId,
            tenantId:        $tenantId,
            userId:          $userId ? (int) $userId : null,
            userType:        $userType,
            portal:          $portal,
            question:        $question,
            answer:          $openAiResult['answer'],
            wasAnswered:     $openAiResult['was_answered'],
            tokensUsed:      $openAiResult['tokens_used'],
            responseTimeMs:  $elapsed,
            metadata:        ['source' => 'openai']
        );

        $this->flagIfUnanswered($conversation);

        // Track tenant token usage
        if ($tenantId && $openAiResult['tokens_used']) {
            $this->trackTenantTokenUsage($tenantId, $openAiResult['tokens_used']);
        }

        return [
            'success'         => true,
            'answer'          => $openAiResult['answer'],
            'session_id'      => $sessionId,
            'was_from_kb'     => false,
            'conversation_id' => $conversation->id,
        ];
    }

    /**
     * Search the knowledge base for a matching answer.
     */
    public function searchKnowledgeBase(string $question, string $portal): ?AiKnowledgeBase
    {
        $q = mb_strtolower(trim($question));

        // Exact/fuzzy match against active KB entries for this portal
        return AiKnowledgeBase::active()
            ->forPortal($portal)
            ->get()
            ->first(function (AiKnowledgeBase $entry) use ($q) {
                $entryQ = mb_strtolower($entry->question);

                // Exact substring match
                if (str_contains($entryQ, $q) || str_contains($q, $entryQ)) {
                    return true;
                }

                // Keyword match
                if (!empty($entry->keywords)) {
                    foreach ($entry->keywords as $keyword) {
                        if (str_contains($q, mb_strtolower($keyword))) {
                            return true;
                        }
                    }
                }

                // Similarity: at least 70% word overlap
                $qWords     = array_filter(explode(' ', $q));
                $entryWords = array_filter(explode(' ', $entryQ));
                if (count($qWords) > 0 && count($entryWords) > 0) {
                    $common = count(array_intersect($qWords, $entryWords));
                    $score  = $common / max(count($qWords), count($entryWords));
                    if ($score >= 0.7) {
                        return true;
                    }
                }

                return false;
            });
    }

    /**
     * Call the OpenAI Chat Completions API.
     *
     * @return array{answer: string, was_answered: bool, tokens_used: int|null}
     */
    public function callOpenAI(string $question, string $systemPrompt, array $chatHistory): array
    {
        $apiKey = config('ai.openai_api_key');

        if (empty($apiKey)) {
            Log::warning('AI Assistant: OPENAI_API_KEY not configured, returning fallback.');
            return [
                'answer'       => config('ai.fallback_message'),
                'was_answered' => false,
                'tokens_used'  => null,
            ];
        }

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach ($chatHistory as $turn) {
            $messages[] = ['role' => 'user',      'content' => $turn['question']];
            $messages[] = ['role' => 'assistant', 'content' => $turn['answer']];
        }

        $messages[] = ['role' => 'user', 'content' => $question];

        try {
            $response = Http::withToken($apiKey)
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => config('ai.openai_model', 'gpt-4o-mini'),
                    'messages'    => $messages,
                    'max_tokens'  => (int) config('ai.openai_max_tokens', 500),
                    'temperature' => (float) config('ai.openai_temperature', 0.7),
                ]);

            if (!$response->successful()) {
                Log::error('AI Assistant: OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
                return [
                    'answer'       => config('ai.fallback_message'),
                    'was_answered' => false,
                    'tokens_used'  => null,
                ];
            }

            $data        = $response->json();
            $answer      = $data['choices'][0]['message']['content'] ?? config('ai.fallback_message');
            $tokensUsed  = $data['usage']['total_tokens'] ?? null;
            $wasAnswered = !$this->isUncertain($answer);

            return [
                'answer'       => $answer,
                'was_answered' => $wasAnswered,
                'tokens_used'  => $tokensUsed,
            ];
        } catch (\Throwable $e) {
            Log::error('AI Assistant: Exception calling OpenAI', ['error' => $e->getMessage()]);
            return [
                'answer'       => config('ai.fallback_message'),
                'was_answered' => false,
                'tokens_used'  => null,
            ];
        }
    }

    /**
     * Build a comprehensive system prompt for the given portal/tenant context.
     */
    public function buildSystemPrompt(string $portal, ?Tenant $tenant): string
    {
        $tenantName = $tenant ? $tenant->name : 'OxNet';
        $tenantInfo = $tenant
            ? "You are assisting a user on the {$tenantName} ISP platform (powered by OxNet)."
            : 'You are the OxNet platform AI assistant.';

        $portalContext = match ($portal) {
            'guest'     => 'The user is visiting the public/guest page and may be a prospective customer curious about the platform.',
            'admin'     => 'The user is an ISP admin managing their tenant portal (customers, routers, billing, MikroTik).',
            'customer'  => 'The user is a customer of an ISP checking their subscription, making payments, or seeking support.',
            'community' => 'The user is on the OxNet Community portal — a forum for ISP professionals to share knowledge.',
            'login'     => 'The user is on a login or registration page and may need help accessing their account.',
            default     => 'The user is interacting with the OxNet platform.',
        };

        return <<<PROMPT
You are OxBot, the friendly AI assistant for the OxNet ISP SaaS platform. {$tenantInfo}

PORTAL CONTEXT: {$portalContext}

ABOUT OXNET:
OxNet is a multi-tenant ISP management SaaS platform built for Kenyan ISPs. It provides:
- Multi-tenant architecture: each ISP gets their own subdomain (e.g., isp1.oxnet.co.ke) and isolated database
- MikroTik router management via RouterOS API (PPPoE, hotspot, DHCP, firewall rules)
- Customer self-service portal: view packages, pay via M-Pesa STK Push, submit support tickets
- Admin portal: manage customers (subscribers), routers, billing, packages, reports
- M-Pesa Daraja API integration for payments (STK Push, C2B)
- Community portal: forum for ISP professionals (posts, replies, categories, tags)
- Super Admin: manages all tenants, subscriptions, pricing plans, CMS, SMS/email gateways

KENYAN ISP CONTEXT:
- M-Pesa is the primary payment method; customers pay via STK Push on their phone
- Common issues: PPPoE disconnections, slow speeds, payment not reflecting
- Many customers speak Swahili or Sheng in addition to English
- Common packages: daily, weekly, monthly; prices in KES (Kenyan Shillings)
- RADIUS/FreeRADIUS is used for PPPoE authentication and accounting

LANGUAGE AWARENESS:
- Respond in the same language the user writes in (English, Swahili, or mixed Sheng)
- Be warm, friendly, and concise
- Use emojis sparingly to keep responses engaging 😊
- Keep responses under 150 words unless more detail is genuinely needed

TONE & STYLE:
- Be helpful, conversational, and professional
- If you don't know something specific to a tenant, say so and direct them to contact support
- Never make up specific pricing, package details, or credentials
- Always offer to help with follow-up questions

If you are unsure, say: "I'm not sure about that. Let me flag this for the support team to review."
PROMPT;
    }

    /**
     * Get the last N conversation turns for a session (for chat context).
     */
    public function getChatHistory(string $sessionId, int $limit = 10): array
    {
        return AiConversation::where('session_id', $sessionId)
            ->whereNotNull('answer')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['question', 'answer'])
            ->reverse()
            ->values()
            ->map(fn ($c) => ['question' => $c->question, 'answer' => $c->answer])
            ->toArray();
    }

    /**
     * Persist the conversation record.
     */
    public function logConversation(
        string  $sessionId,
        ?int    $tenantId,
        ?int    $userId,
        ?string $userType,
        string  $portal,
        string  $question,
        ?string $answer,
        bool    $wasAnswered,
        ?int    $tokensUsed,
        ?int    $responseTimeMs,
        array   $metadata = []
    ): AiConversation {
        return AiConversation::create([
            'session_id'          => $sessionId,
            'tenant_id'           => $tenantId,
            'user_id'             => $userId,
            'user_type'           => $userType,
            'portal'              => $portal,
            'question'            => $question,
            'answer'              => $answer,
            'was_answered'        => $wasAnswered,
            'flagged_for_review'  => false,
            'openai_tokens_used'  => $tokensUsed,
            'response_time_ms'    => $responseTimeMs,
            'ip_address'          => request()->ip(),
            'user_agent'          => request()->userAgent(),
            'metadata'            => $metadata,
        ]);
    }

    /**
     * Flag a conversation for Super Admin review if the answer is uncertain.
     */
    public function flagIfUnanswered(AiConversation $conversation): void
    {
        if (!$conversation->was_answered || $this->isUncertain($conversation->answer ?? '')) {
            $conversation->update(['flagged_for_review' => true]);

            AiUnansweredQuestion::create([
                'conversation_id' => $conversation->id,
                'question'        => $conversation->question,
                'portal'          => $conversation->portal,
                'tenant_id'       => $conversation->tenant_id,
                'status'          => 'pending',
            ]);
        }
    }

    /**
     * Get basic stats for a portal.
     */
    public function getPortalStats(string $portal): array
    {
        $base = AiConversation::forPortal($portal);
        return [
            'total'      => (clone $base)->count(),
            'today'      => (clone $base)->whereDate('created_at', today())->count(),
            'unanswered' => (clone $base)->unanswered()->count(),
            'flagged'    => (clone $base)->flagged()->count(),
        ];
    }

    /**
     * Get all pending unanswered questions.
     */
    public function getUnansweredQuestions(): Collection
    {
        return AiUnansweredQuestion::with('conversation')
            ->pending()
            ->orderByDesc('created_at')
            ->get();
    }

    // ------------------------------------------------------------------ helpers

    private function isUncertain(string $answer): bool
    {
        $lower = mb_strtolower($answer);
        foreach (self::UNCERTAIN_PHRASES as $phrase) {
            if (str_contains($lower, $phrase)) {
                return true;
            }
        }
        return false;
    }

    private function trackTenantTokenUsage(int $tenantId, int $tokens): void
    {
        try {
            $setting = AiTenantSetting::first();
            if ($setting) {
                $setting->increment('tokens_used_this_month', $tokens);
            }
        } catch (\Throwable $e) {
            Log::warning('AI Assistant: Failed to track tenant token usage', ['tenant_id' => $tenantId, 'error' => $e->getMessage()]);
        }
    }

    private function fallback(string $sessionId): array
    {
        return [
            'success'         => false,
            'answer'          => config('ai.fallback_message'),
            'session_id'      => $sessionId,
            'was_from_kb'     => false,
            'conversation_id' => null,
        ];
    }
}
