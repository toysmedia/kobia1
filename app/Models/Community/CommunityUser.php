<?php

namespace App\Models\Community;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CommunityUser extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $connection = 'mysql';
    protected $table = 'community_users';

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'bio', 'location', 'website',
        'is_verified', 'is_banned', 'ban_reason', 'reputation', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_banned' => 'boolean',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function posts() { return $this->hasMany(CommunityPost::class); }
    public function replies() { return $this->hasMany(CommunityReply::class); }
    public function likes() { return $this->hasMany(CommunityLike::class); }
    public function follows() { return $this->hasMany(CommunityFollow::class); }
    public function reports() { return $this->hasMany(CommunityReport::class); }

    public function addReputation(int $points): void
    {
        $this->increment('reputation', $points);
    }

    public function deductReputation(int $points): void
    {
        $this->decrement('reputation', $points);
    }
}
