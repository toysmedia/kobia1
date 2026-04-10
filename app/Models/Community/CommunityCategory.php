<?php

namespace App\Models\Community;

use Illuminate\Database\Eloquent\Model;

class CommunityCategory extends Model
{
    protected $connection = 'mysql';
    protected $table = 'community_categories';

    protected $fillable = ['name', 'slug', 'description', 'icon', 'color', 'parent_id', 'order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function posts() { return $this->hasMany(CommunityPost::class, 'category_id'); }
    public function children() { return $this->hasMany(CommunityCategory::class, 'parent_id'); }
    public function parent() { return $this->belongsTo(CommunityCategory::class, 'parent_id'); }
}
