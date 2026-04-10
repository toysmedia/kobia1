<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityLike;
use App\Models\Community\CommunityPost;
use App\Models\Community\CommunityReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:post,reply'],
            'id'   => ['required', 'integer'],
        ]);

        $userId = Auth::guard('community')->id();
        $model = $request->type === 'post' ? CommunityPost::findOrFail($request->id) : CommunityReply::findOrFail($request->id);
        $morphType = $request->type === 'post' ? CommunityPost::class : CommunityReply::class;

        $existing = CommunityLike::where([
            'community_user_id' => $userId,
            'likeable_id'       => $model->id,
            'likeable_type'     => $morphType,
        ])->first();

        if ($existing) {
            $existing->delete();
            $model->decrement('likes_count');
            $model->user->deductReputation(1);
            $liked = false;
        } else {
            CommunityLike::create([
                'community_user_id' => $userId,
                'likeable_id'       => $model->id,
                'likeable_type'     => $morphType,
            ]);
            $model->increment('likes_count');
            $model->user->addReputation(1);
            $liked = true;
        }

        return response()->json([
            'liked'       => $liked,
            'likes_count' => $model->fresh()->likes_count,
        ]);
    }
}
