<?php

namespace App\Models\Community;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityPost extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql';
    protected $table = 'community_posts';

    protected $fillable = [
        'community_user_id', 'category_id', 'title', 'slug', 'body', 'type', 'status',
        'is_pinned', 'is_featured', 'is_locked', 'views_count', 'likes_count',
        'replies_count', 'rejection_reason',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_featured' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function user() { return $this->belongsTo(CommunityUser::class, 'community_user_id'); }
    public function category() { return $this->belongsTo(CommunityCategory::class, 'category_id'); }
    public function replies() { return $this->hasMany(CommunityReply::class)->whereNull('parent_id'); }
    public function allReplies() { return $this->hasMany(CommunityReply::class); }
    public function likes() { return $this->morphMany(CommunityLike::class, 'likeable'); }
    public function tags() { return $this->belongsToMany(CommunityTag::class, 'community_post_tag', 'community_post_id', 'community_tag_id'); }
    public function reports() { return $this->morphMany(CommunityReport::class, 'reportable'); }
    public function follows() { return $this->morphMany(CommunityFollow::class, 'followable'); }

    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopeFeatured($query) { return $query->where('is_featured', true); }
    public function scopePinned($query) { return $query->where('is_pinned', true); }
    public function scopeByCategory($query, $slug) { return $query->whereHas('category', fn($q) => $q->where('slug', $slug)); }
    public function scopeByTag($query, $slug) { return $query->whereHas('tags', fn($q) => $q->where('slug', $slug)); }
    public function scopePopular($query) { return $query->orderByRaw('(likes_count + replies_count) DESC'); }
}
