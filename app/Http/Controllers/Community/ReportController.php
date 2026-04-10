<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityPost;
use App\Models\Community\CommunityReport;
use App\Models\Community\CommunityReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type'    => ['required', 'in:post,reply'],
            'id'      => ['required', 'integer'],
            'reason'  => ['required', 'in:spam,harassment,inappropriate,misinformation,other'],
            'details' => ['nullable', 'string', 'max:500'],
        ]);

        $morphType = $request->type === 'post' ? CommunityPost::class : CommunityReply::class;

        $existing = CommunityReport::where([
            'community_user_id' => Auth::guard('community')->id(),
            'reportable_id'     => $request->id,
            'reportable_type'   => $morphType,
        ])->exists();

        if ($existing) {
            return response()->json(['message' => 'Already reported.'], 409);
        }

        CommunityReport::create([
            'community_user_id' => Auth::guard('community')->id(),
            'reportable_id'     => $request->id,
            'reportable_type'   => $morphType,
            'reason'            => $request->reason,
            'details'           => $request->details,
        ]);

        return response()->json(['message' => 'Report submitted. Thank you.']);
    }
}
