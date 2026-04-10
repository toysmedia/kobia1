<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityCategory;
use App\Models\Community\CommunityPost;
use App\Models\Community\CommunityTag;

class DashboardController extends Controller
{
    public function index()
    {
        $featuredPosts = CommunityPost::with(['user', 'category'])
            ->approved()->featured()
            ->latest()
            ->limit(5)
            ->get();

        $latestPosts = CommunityPost::with(['user', 'category', 'tags'])
            ->approved()
            ->latest()
            ->paginate(10);

        $pinnedPosts = CommunityPost::with(['user', 'category'])
            ->approved()->pinned()
            ->latest()
            ->limit(3)
            ->get();

        $popularCategories = CommunityCategory::where('is_active', true)
            ->withCount(['posts' => fn($q) => $q->where('status', 'approved')])
            ->orderByDesc('posts_count')
            ->limit(8)
            ->get();

        $trendingTags = CommunityTag::orderByDesc('usage_count')->limit(20)->get();

        return view('community.index', compact(
            'featuredPosts', 'latestPosts', 'pinnedPosts', 'popularCategories', 'trendingTags'
        ));
    }
}
