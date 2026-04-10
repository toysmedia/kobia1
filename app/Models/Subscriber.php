<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Subscriber extends Authenticatable
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    protected $hidden = ['password_hash', 'radius_password', 'remember_token'];
    protected $casts = ['expires_at' => 'datetime'];

    /**
     * Laravel auth uses getAuthPassword() to retrieve the hashed password for verification.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash ?? '';
    }

    public function package()
    {
        return $this->belongsTo(IspPackage::class, 'isp_package_id');
    }

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function payments()
    {
        return $this->hasMany(MpesaPayment::class, 'subscriber_id');
    }

    public function sessions()
    {
        return $this->hasMany(Radacct::class, 'username', 'username');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function supportTickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Tenant\SupportTicket::class, 'customer_id');
    }
}
