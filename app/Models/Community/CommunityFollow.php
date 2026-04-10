<?php

namespace App\Models\Community;

use Illuminate\Database\Eloquent\Model;

class CommunityFollow extends Model
{
    protected $connection = 'mysql';
    protected $table = 'community_follows';
    protected $fillable = ['community_user_id', 'followable_id', 'followable_type'];

    public function followable() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(CommunityUser::class, 'community_user_id'); }
}
