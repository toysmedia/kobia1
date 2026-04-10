<?php

namespace App\Models\Community;

use Illuminate\Database\Eloquent\Model;

class CommunityLike extends Model
{
    protected $connection = 'mysql';
    protected $table = 'community_likes';
    protected $fillable = ['community_user_id', 'likeable_id', 'likeable_type'];

    public function likeable() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(CommunityUser::class, 'community_user_id'); }
}
