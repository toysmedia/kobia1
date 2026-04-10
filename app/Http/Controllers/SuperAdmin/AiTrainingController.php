<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\System\AiConversation;
use App\Models\System\AiKnowledgeBase;
use App\Models\System\AiUnansweredQuestion;
use App\Services\AiAssistantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiTrainingController extends Controller
{
    public function __construct(private readonly AiAssistantService $aiService) {}

    /** GET /super-admin/ai — AI Dashboard */
    public function index(): View
    {
        $totalConversations  = AiConversation::count();
        $todayConversations  = AiConversation::whereDate('created_at', today())->count();
        $totalKbEntries      = AiKnowledgeBase::count();
        $unansweredCount     = AiUnansweredQuestion::pending()->count();
        $totalAnswered       = AiConversation::where('was_answered', true)->count();
        $unansweredRate      = $totalConversations > 0
            ? round((AiConversation::where('was_answered', false)->count() / $totalConversations) * 100, 1)
            : 0;

        // Token usage (sum)
        $tokensUsed = AiConversation::whereNotNull('openai_tokens_used')->sum('openai_tokens_used');

        // Top 5 most-asked questions
        $topQuestions = AiConversation::selectRaw('question, COUNT(*) as cnt')
            ->groupBy('question')
            ->orderByDesc('cnt')
            ->limit(5)
            ->get();

        // Conversations over last 30 days
        $dailyStats = AiConversation::selectRaw('DATE(created_at) as date, COUNT(*) as cnt')
            ->where('created_at', '>=', now()->subDays(29))
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        return view('super-admin.ai.index', compact(
            'totalConversations',
            'todayConversations',
            'totalKbEntries',
            'unansweredCount',
            'unansweredRate',
            'tokensUsed',
            'topQuestions',
            'dailyStats'
        ));
    }

    /** GET /super-admin/ai/knowledge */
    public function knowledgeBase(Request $request): View
    {
        $query = AiKnowledgeBase::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('question', 'like', '%' . $request->search . '%')
                  ->orWhere('answer', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        $entries    = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $categories = AiKnowledgeBase::distinct()->pluck('category')->filter()->values();

        return view('super-admin.ai.knowledge.index', compact('entries', 'categories'));
    }

    /** GET /super-admin/ai/knowledge/create */
    public function createKnowledge(): View
    {
        return view('super-admin.ai.knowledge.create');
    }

    /** POST /super-admin/ai/knowledge */
    public function storeKnowledge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category'         => ['nullable', 'string', 'max:100'],
            'question'         => ['required', 'string'],
            'answer'           => ['required', 'string'],
            'keywords'         => ['nullable', 'string'],
            'portal_context'   => ['nullable', 'array'],
            'portal_context.*' => ['string', 'in:guest,admin,customer,community,login'],
            'language'         => ['required', 'string', 'in:en,sw'],
            'is_active'        => ['boolean'],
        ]);

        // Parse comma-separated keywords into array
        $keywords = null;
        if (!empty($validated['keywords'])) {
            $keywords = array_values(array_filter(array_map('trim', explode(',', $validated['keywords']))));
        }

        AiKnowledgeBase::create([
            'category'       => $validated['category'] ?? null,
            'question'       => $validated['question'],
            'answer'         => $validated['answer'],
            'keywords'       => $keywords,
            'portal_context' => $validated['portal_context'] ?? null,
            'language'       => $validated['language'],
            'is_active'      => $request->boolean('is_active', true),
            'created_by'     => auth('super_admin')->user()->email ?? 'super_admin',
        ]);

        return redirect()->route('super-admin.ai.knowledge')
            ->with('success', 'Knowledge base entry created successfully.');
    }

    /** GET /super-admin/ai/knowledge/{id}/edit */
    public function editKnowledge(int $id): View
    {
        $entry = AiKnowledgeBase::findOrFail($id);
        return view('super-admin.ai.knowledge.edit', compact('entry'));
    }

    /** PUT /super-admin/ai/knowledge/{id} */
    public function updateKnowledge(Request $request, int $id): RedirectResponse
    {
        $entry = AiKnowledgeBase::findOrFail($id);

        $validated = $request->validate([
            'category'         => ['nullable', 'string', 'max:100'],
            'question'         => ['required', 'string'],
            'answer'           => ['required', 'string'],
            'keywords'         => ['nullable', 'string'],
            'portal_context'   => ['nullable', 'array'],
            'portal_context.*' => ['string', 'in:guest,admin,customer,community,login'],
            'language'         => ['required', 'string', 'in:en,sw'],
            'is_active'        => ['boolean'],
        ]);

        $keywords = null;
        if (!empty($validated['keywords'])) {
            $keywords = array_values(array_filter(array_map('trim', explode(',', $validated['keywords']))));
        }

        $entry->update([
            'category'       => $validated['category'] ?? null,
            'question'       => $validated['question'],
            'answer'         => $validated['answer'],
            'keywords'       => $keywords,
            'portal_context' => $validated['portal_context'] ?? null,
            'language'       => $validated['language'],
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return redirect()->route('super-admin.ai.knowledge')
            ->with('success', 'Knowledge base entry updated.');
    }

    /** DELETE /super-admin/ai/knowledge/{id} */
    public function deleteKnowledge(int $id): RedirectResponse
    {
        AiKnowledgeBase::findOrFail($id)->delete();
        return redirect()->route('super-admin.ai.knowledge')
            ->with('success', 'Entry deleted.');
    }

    /** GET /super-admin/ai/unanswered */
    public function unanswered(Request $request): View
    {
        $query = AiUnansweredQuestion::with('conversation');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->pending();
        }

        $items = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('super-admin.ai.unanswered.index', compact('items'));
    }

    /** POST /super-admin/ai/unanswered/{id}/resolve */
    public function resolveUnanswered(Request $request, int $id): RedirectResponse
    {
        $item = AiUnansweredQuestion::findOrFail($id);

        $validated = $request->validate([
            'resolved_answer' => ['required', 'string'],
            'add_to_kb'       => ['nullable', 'boolean'],
            'kb_category'     => ['nullable', 'string', 'max:100'],
        ]);

        $item->update([
            'status'          => 'answered',
            'resolved_answer' => $validated['resolved_answer'],
            'resolved_by'     => auth('super_admin')->id(),
            'resolved_at'     => now(),
        ]);

        if ($request->boolean('add_to_kb') && $item->conversation) {
            AiKnowledgeBase::create([
                'category'       => $validated['kb_category'] ?? 'general',
                'question'       => $item->question,
                'answer'         => $validated['resolved_answer'],
                'portal_context' => [$item->portal],
                'language'       => 'en',
                'is_active'      => true,
                'created_by'     => auth('super_admin')->user()->email ?? 'super_admin',
            ]);
        }

        return redirect()->route('super-admin.ai.unanswered')
            ->with('success', 'Question resolved' . ($request->boolean('add_to_kb') ? ' and added to Knowledge Base' : '') . '.');
    }

    /** POST /super-admin/ai/unanswered/{id}/dismiss */
    public function dismissUnanswered(int $id): RedirectResponse
    {
        AiUnansweredQuestion::findOrFail($id)->update(['status' => 'dismissed']);
        return redirect()->route('super-admin.ai.unanswered')
            ->with('success', 'Question dismissed.');
    }

    /** GET /super-admin/ai/conversations */
    public function conversations(Request $request): View
    {
        $query = AiConversation::query();

        if ($request->filled('portal')) {
            $query->where('portal', $request->portal);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $conversations = $query->orderByDesc('created_at')->paginate(30)->withQueryString();
        $portals = config('ai.portals', []);

        return view('super-admin.ai.conversations.index', compact('conversations', 'portals'));
    }

    /** GET /super-admin/ai/conversations/{id} */
    public function conversationDetail(int $id): View
    {
        $conversation = AiConversation::with('unansweredQuestion')->findOrFail($id);

        // Thread: all messages in same session
        $thread = AiConversation::where('session_id', $conversation->session_id)
            ->orderBy('created_at')
            ->get();

        return view('super-admin.ai.conversations.show', compact('conversation', 'thread'));
    }

    /** GET /super-admin/ai/reports */
    public function reports(Request $request): View
    {
        $dateFrom = $request->input('date_from', now()->subDays(29)->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());

        $baseQuery = AiConversation::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        $totalQueries      = (clone $baseQuery)->count();
        $avgResponseTime   = (clone $baseQuery)->whereNotNull('response_time_ms')->avg('response_time_ms');
        $kbHits            = (clone $baseQuery)->whereJsonContains('metadata->source', 'knowledge_base')->count();
        $kbHitRate         = $totalQueries > 0 ? round(($kbHits / $totalQueries) * 100, 1) : 0;
        $unansweredQueries = (clone $baseQuery)->where('was_answered', false)->count();
        $unansweredRate    = $totalQueries > 0 ? round(($unansweredQueries / $totalQueries) * 100, 1) : 0;

        // Queries by portal
        $byPortal = (clone $baseQuery)
            ->selectRaw('portal, COUNT(*) as cnt')
            ->groupBy('portal')
            ->get();

        // Queries over time
        $overTime = (clone $baseQuery)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as cnt')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Top 20 questions
        $topQuestions = (clone $baseQuery)
            ->selectRaw('question, COUNT(*) as cnt')
            ->groupBy('question')
            ->orderByDesc('cnt')
            ->limit(20)
            ->get();

        return view('super-admin.ai.reports.index', compact(
            'dateFrom', 'dateTo',
            'totalQueries', 'avgResponseTime',
            'kbHitRate', 'unansweredRate',
            'byPortal', 'overTime', 'topQuestions'
        ));
    }
}
