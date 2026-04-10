<?php

namespace App\Models\Community;

use App\Models\System\SuperAdmin;
use Illuminate\Database\Eloquent\Model;

class CommunityReport extends Model
{
    protected $connection = 'mysql';
    protected $table = 'community_reports';

    protected $fillable = [
        'community_user_id', 'reportable_id', 'reportable_type',
        'reason', 'details', 'status', 'reviewed_by', 'reviewed_at', 'action_taken',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function reportable() { return $this->morphTo(); }
    public function reporter() { return $this->belongsTo(CommunityUser::class, 'community_user_id'); }
    public function reviewer() { return $this->belongsTo(SuperAdmin::class, 'reviewed_by'); }
}
