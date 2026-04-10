<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityCategory;
use App\Models\Community\CommunityPost;
use App\Models\Community\CommunityReport;
use App\Models\Community\CommunityReply;
use App\Models\Community\CommunityTag;
use App\Models\Community\CommunityUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CommunityModerationController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users'     => CommunityUser::count(),
            'total_posts'     => CommunityPost::count(),
            'pending_posts'   => CommunityPost::where('status', 'pending')->count(),
            'flagged_posts'   => CommunityPost::where('status', 'flagged')->count(),
            'pending_reports' => CommunityReport::where('status', 'pending')->count(),
            'banned_users'    => CommunityUser::where('is_banned', true)->count(),
            'total_replies'   => CommunityReply::count(),
        ];

        $recentPosts = CommunityPost::with(['user', 'category'])->latest()->limit(10)->get();
        $pendingReports = CommunityReport::with(['reporter', 'reportable'])->where('status', 'pending')->latest()->limit(5)->get();

        return view('super-admin.community.dashboard', compact('stats', 'recentPosts', 'pendingReports'));
    }

    public function posts(Request $request)
    {
        $query = CommunityPost::with(['user', 'category']);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('title', 'LIKE', "%$s%")->orWhere('body', 'LIKE', "%$s%"));
        }
        $posts = $query->latest()->paginate(20)->withQueryString();
        return view('super-admin.community.posts', compact('posts'));
    }

    public function approvePost(CommunityPost $post)
    {
        $post->update(['status' => 'approved']);
        return back()->with('success', 'Post approved.');
    }

    public function rejectPost(Request $request, CommunityPost $post)
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);
        $post->update(['status' => 'rejected', 'rejection_reason' => $request->rejection_reason]);
        $post->user->deductReputation(10);
        return back()->with('success', 'Post rejected.');
    }

    public function pinPost(CommunityPost $post)
    {
        $post->update(['is_pinned' => !$post->is_pinned]);
        return back()->with('success', $post->is_pinned ? 'Post pinned.' : 'Post unpinned.');
    }

    public function featurePost(CommunityPost $post)
    {
        $post->update(['is_featured' => !$post->is_featured]);
        return back()->with('success', $post->is_featured ? 'Post featured.' : 'Post unfeatured.');
    }

    public function lockPost(CommunityPost $post)
    {
        $post->update(['is_locked' => !$post->is_locked]);
        return back()->with('success', $post->is_locked ? 'Post locked.' : 'Post unlocked.');
    }

    public function users(Request $request)
    {
        $query = CommunityUser::query();
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'LIKE', "%$s%")->orWhere('email', 'LIKE', "%$s%"));
        }
        if ($request->filled('status')) {
            $query->where('is_banned', $request->status === 'banned');
        }
        $users = $query->latest()->paginate(20)->withQueryString();
        return view('super-admin.community.users', compact('users'));
    }

    public function banUser(Request $request, CommunityUser $user)
    {
        $request->validate(['ban_reason' => 'required|string|max:500']);
        $user->update(['is_banned' => true, 'ban_reason' => $request->ban_reason]);
        $user->deductReputation(50);
        return back()->with('success', 'User banned.');
    }

    public function unbanUser(CommunityUser $user)
    {
        $user->update(['is_banned' => false, 'ban_reason' => null]);
        return back()->with('success', 'User unbanned.');
    }

    public function reports(Request $request)
    {
        $query = CommunityReport::with(['reporter', 'reportable']);
        if ($request->filled('status')) $query->where('status', $request->status);
        $reports = $query->latest()->paginate(20)->withQueryString();
        return view('super-admin.community.reports', compact('reports'));
    }

    public function reviewReport(Request $request, CommunityReport $report)
    {
        $request->validate([
            'action'       => 'required|in:dismiss,hide_content,ban_user',
            'action_taken' => 'nullable|string|max:500',
        ]);

        $admin = auth('super_admin')->user();

        if ($request->action === 'dismiss') {
            $report->update(['status' => 'dismissed', 'reviewed_by' => $admin->id, 'reviewed_at' => now()]);
        } elseif ($request->action === 'hide_content') {
            $reportable = $report->reportable;
            if ($reportable instanceof CommunityPost) {
                $reportable->update(['status' => 'flagged']);
            } elseif ($reportable instanceof CommunityReply) {
                $reportable->update(['status' => 'hidden']);
            }
            $report->update(['status' => 'actioned', 'reviewed_by' => $admin->id, 'reviewed_at' => now(), 'action_taken' => $request->action_taken]);
        } elseif ($request->action === 'ban_user') {
            $reportable = $report->reportable;
            if ($reportable) {
                $reportable->user->update(['is_banned' => true, 'ban_reason' => 'Reported content violation']);
            }
            $report->update(['status' => 'actioned', 'reviewed_by' => $admin->id, 'reviewed_at' => now(), 'action_taken' => $request->action_taken]);
        }

        return back()->with('success', 'Report reviewed.');
    }

    public function categories()
    {
        $categories = CommunityCategory::withCount('posts')->orderBy('order')->get();
        return view('super-admin.community.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:20',
            'parent_id'   => 'nullable|exists:mysql.community_categories,id',
            'order'       => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        CommunityCategory::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
            'icon'        => $request->icon,
            'color'       => $request->color,
            'parent_id'   => $request->parent_id,
            'order'       => $request->input('order', 0),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, CommunityCategory $category)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:20',
            'order'       => 'integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);

        $category->update([
            'name'        => $request->name,
            'description' => $request->description,
            'icon'        => $request->icon,
            'color'       => $request->color,
            'order'       => $request->input('order', $category->order),
            'is_active'   => $request->boolean('is_active', $category->is_active),
        ]);

        return back()->with('success', 'Category updated.');
    }

    public function toggleCategory(CommunityCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        return back()->with('success', 'Category status toggled.');
    }

    public function tags(Request $request)
    {
        $tags = CommunityTag::withCount('posts')->orderByDesc('usage_count')->paginate(30)->withQueryString();
        return view('super-admin.community.tags', compact('tags'));
    }

    public function deleteTag(CommunityTag $tag)
    {
        $tag->posts()->detach();
        $tag->delete();
        return back()->with('success', 'Tag deleted.');
    }

    public function announcements()
    {
        $announcements = CommunityPost::with('user')
            ->where('type', 'announcement')
            ->latest()
            ->paginate(15);
        $categories = CommunityCategory::where('is_active', true)->get();
        return view('super-admin.community.announcements', compact('announcements', 'categories'));
    }

    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|min:10|max:200',
            'body'        => 'required|string|min:20',
            'category_id' => 'required|exists:mysql.community_categories,id',
            'is_pinned'   => 'nullable|boolean',
        ]);

        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;
        while (CommunityPost::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $systemEmail = config('community.system_email', 'system@oxnet.co.ke');
        $systemUser = CommunityUser::firstOrCreate(
            ['email' => $systemEmail],
            ['name' => 'OxNet Team', 'password' => Hash::make(Str::random(32)), 'is_verified' => true]
        );

        CommunityPost::create([
            'community_user_id' => $systemUser->id,
            'category_id'       => $request->category_id,
            'title'             => $request->title,
            'slug'              => $slug,
            'body'              => $request->body,
            'type'              => 'announcement',
            'status'            => 'approved',
            'is_pinned'         => $request->boolean('is_pinned'),
            'is_featured'       => true,
        ]);

        return back()->with('success', 'Announcement posted.');
    }
}
