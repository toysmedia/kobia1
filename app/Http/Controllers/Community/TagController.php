<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityPost;
use App\Models\Community\CommunityTag;

class TagController extends Controller
{
    public function index()
    {
        $tags = CommunityTag::where('usage_count', '>', 0)->orderByDesc('usage_count')->get();
        return view('community.tags.index', compact('tags'));
    }

    public function show(string $slug)
    {
        $tag = CommunityTag::where('slug', $slug)->firstOrFail();
        $posts = CommunityPost::with(['user', 'category', 'tags'])
            ->approved()
            ->byTag($slug)
            ->latest()
            ->paginate(15);

        return view('community.tags.show', compact('tag', 'posts'));
    }
}
