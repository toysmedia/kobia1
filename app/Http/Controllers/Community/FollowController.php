<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityCategory;
use App\Models\Community\CommunityFollow;
use App\Models\Community\CommunityPost;
use App\Models\Community\CommunityUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:post,category,user'],
            'id'   => ['required', 'integer'],
        ]);

        $userId = Auth::guard('community')->id();
        $morphType = match($request->type) {
            'post'     => CommunityPost::class,
            'category' => CommunityCategory::class,
            'user'     => CommunityUser::class,
        };

        $existing = CommunityFollow::where([
            'community_user_id' => $userId,
            'followable_id'     => $request->id,
            'followable_type'   => $morphType,
        ])->first();

        if ($existing) {
            $existing->delete();
            $following = false;
        } else {
            CommunityFollow::create([
                'community_user_id' => $userId,
                'followable_id'     => $request->id,
                'followable_type'   => $morphType,
            ]);
            $following = true;
        }

        return response()->json(['following' => $following]);
    }
}
