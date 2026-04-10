<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityFollow;
use App\Models\Community\CommunityPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function index()
    {
        $user = Auth::guard('community')->user();

        $followedPostIds = CommunityFollow::where('community_user_id', $user->id)
            ->where('followable_type', CommunityPost::class)
            ->pluck('followable_id');

        $followedPosts = CommunityPost::with(['user', 'category'])
            ->whereIn('id', $followedPostIds)
            ->approved()
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('community.notifications', compact('followedPosts'));
    }
}
