<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityPost;
use App\Models\Community\CommunityReply;
use App\Models\Community\CommunityTag;
use App\Models\Community\CommunityUser;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $tab = $request->get('tab', 'posts');

        $posts = collect();
        $replies = collect();
        $users = collect();
        $tags = collect();

        if (strlen($query) >= 2) {
            $posts = CommunityPost::with(['user', 'category'])
                ->approved()
                ->where(fn($q) => $q->where('title', 'LIKE', "%{$query}%")->orWhere('body', 'LIKE', "%{$query}%"))
                ->latest()
                ->paginate(10, ['*'], 'posts_page')
                ->withQueryString();

            $replies = CommunityReply::with(['user', 'post'])
                ->where('status', 'visible')
                ->where('body', 'LIKE', "%{$query}%")
                ->latest()
                ->paginate(10, ['*'], 'replies_page')
                ->withQueryString();

            $tags = CommunityTag::where('name', 'LIKE', "%{$query}%")->limit(20)->get();

            $users = CommunityUser::where('is_banned', false)
                ->where(fn($q) => $q->where('name', 'LIKE', "%{$query}%")->orWhere('bio', 'LIKE', "%{$query}%"))
                ->limit(20)
                ->get();
        }

        return view('community.search.index', compact('query', 'tab', 'posts', 'replies', 'tags', 'users'));
    }
}
