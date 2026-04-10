<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityCategory;
use App\Models\Community\CommunityPost;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = CommunityCategory::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->where('is_active', true)])
            ->withCount(['posts' => fn($q) => $q->where('status', 'approved')])
            ->orderBy('order')
            ->get();

        return view('community.categories.index', compact('categories'));
    }

    public function show(string $slug)
    {
        $category = CommunityCategory::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $posts = CommunityPost::with(['user', 'tags'])
            ->approved()
            ->where('category_id', $category->id)
            ->latest()
            ->paginate(15);

        return view('community.categories.show', compact('category', 'posts'));
    }
}
