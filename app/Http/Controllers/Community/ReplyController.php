<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityPost;
use App\Models\Community\CommunityReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReplyController extends Controller
{
    public function store(Request $request, CommunityPost $post)
    {
        if ($post->is_locked) {
            return back()->with('error', 'This post is locked.');
        }

        $request->validate([
            'body'      => ['required', 'string', 'min:5', 'max:5000'],
            'parent_id' => ['nullable', 'exists:mysql.community_replies,id'],
        ]);

        $reply = CommunityReply::create([
            'community_post_id' => $post->id,
            'community_user_id' => Auth::guard('community')->id(),
            'parent_id'         => $request->parent_id,
            'body'              => $request->body,
        ]);

        $post->increment('replies_count');

        Auth::guard('community')->user()->addReputation(2);

        return back()->with('success', 'Reply posted!')->withFragment('reply-' . $reply->id);
    }

    public function update(Request $request, CommunityReply $reply)
    {
        if ($reply->community_user_id !== Auth::guard('community')->id()) {
            abort(403);
        }

        $request->validate(['body' => ['required', 'string', 'min:5', 'max:5000']]);
        $reply->update(['body' => $request->body]);

        return back()->with('success', 'Reply updated.');
    }

    public function destroy(CommunityReply $reply)
    {
        if ($reply->community_user_id !== Auth::guard('community')->id()) {
            abort(403);
        }

        $reply->post->decrement('replies_count');
        $reply->delete();

        return back()->with('success', 'Reply deleted.');
    }

    public function acceptAnswer(CommunityReply $reply)
    {
        $post = $reply->post;

        if ($post->community_user_id !== Auth::guard('community')->id()) {
            abort(403, 'Only the post author can accept an answer.');
        }

        if ($post->type !== 'question') {
            return back()->with('error', 'Only questions can have accepted answers.');
        }

        $post->allReplies()->where('is_accepted', true)->update(['is_accepted' => false]);

        $reply->update(['is_accepted' => true]);

        $reply->user->addReputation(15);

        return back()->with('success', 'Answer accepted!');
    }
}
