<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IspPackage extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'isp_packages';
    protected $guarded = ['id'];

    public function getSessionTimeoutAttribute(): int
    {
        $days = $this->validity_days;
        $hours = $this->validity_hours;
        return ($days * 86400) + ($hours * 3600);
    }

    public function getRateLimitAttribute(): string
    {
        return "{$this->speed_upload}M/{$this->speed_download}M";
    }

    public function subscribers()
    {
        return $this->hasMany(Subscriber::class, 'isp_package_id');
    }
}
