<?php

namespace App\Models\Community;

use Illuminate\Database\Eloquent\Model;

class CommunityTag extends Model
{
    protected $connection = 'mysql';
    protected $table = 'community_tags';
    protected $fillable = ['name', 'slug', 'usage_count'];

    public function posts() { return $this->belongsToMany(CommunityPost::class, 'community_post_tag', 'community_tag_id', 'community_post_id'); }
}
