<?php

namespace App\Models\Community;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityReply extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql';
    protected $table = 'community_replies';

    protected $fillable = [
        'community_post_id', 'community_user_id', 'parent_id', 'body',
        'is_accepted', 'likes_count', 'status',
    ];

    protected $casts = ['is_accepted' => 'boolean'];

    public function post() { return $this->belongsTo(CommunityPost::class, 'community_post_id'); }
    public function user() { return $this->belongsTo(CommunityUser::class, 'community_user_id'); }
    public function parent() { return $this->belongsTo(CommunityReply::class, 'parent_id'); }
    public function children() { return $this->hasMany(CommunityReply::class, 'parent_id'); }
    public function likes() { return $this->morphMany(CommunityLike::class, 'likeable'); }
    public function reports() { return $this->morphMany(CommunityReport::class, 'reportable'); }
}
