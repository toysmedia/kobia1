<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

/**
 * Represents an ISP tenant on the OxNet platform.
 * Each tenant has an isolated MySQL database and operates on its own subdomain.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $subdomain
 * @property string|null $domain
 * @property string $database_name
 * @property string $database_host
 * @property int $database_port
 * @property string $database_username
 * @property string $database_password  (encrypted at rest via Laravel Crypt)
 * @property int|null $plan_id
 * @property string $status
 * @property \Carbon\Carbon|null $trial_ends_at
 * @property \Carbon\Carbon|null $subscription_expires_at
 */
class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    /** @var string The database connection to use for this model. */
    protected $connection = 'mysql';

    /** @var string The table associated with the model. */
    protected $table = 'tenants';

    /** @var array<int, string> Mass-assignable attributes. */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subdomain',
        'domain',
        'database_name',
        'database_host',
        'database_port',
        'database_username',
        'database_password',
        'plan_id',
        'status',
        'trial_ends_at',
        'subscription_expires_at',
        'maintenance_mode',
        'lat',
        'lng',
    ];

    /** @var array<string, string> Attribute casts. */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'database_port' => 'integer',
        'maintenance_mode' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    /** @var array<int, string> Hidden attributes for serialisation. */
    protected $hidden = [
        'database_password',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The pricing plan this tenant is subscribed to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'plan_id');
    }

    /**
     * All subscription payment records for this tenant.
     */
    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'tenant_id');
    }

    // -------------------------------------------------------------------------
    // Accessors / Mutators
    // -------------------------------------------------------------------------

    /**
     * Decrypt the database password when reading.
     */
    public function getDatabasePasswordDecryptedAttribute(): string
    {
        return Crypt::decryptString($this->database_password);
    }

    /**
     * Encrypt the database password when setting.
     */
    public function setDatabasePasswordAttribute(string $value): void
    {
        $this->attributes['database_password'] = Crypt::encryptString($value);
    }

    // -------------------------------------------------------------------------
    // Helper methods
    // -------------------------------------------------------------------------

    /**
     * Determine whether the tenant's subscription is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Determine whether the tenant is currently on a free trial.
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Determine whether the tenant subscription has expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->subscription_expires_at !== null && $this->subscription_expires_at->isPast());
    }

    /**
     * Check whether the tenant is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Return the number of days until the subscription expires (negative if already expired).
     */
    public function daysUntilExpiry(): int
    {
        $expiry = $this->subscription_expires_at ?? $this->trial_ends_at;

        return $expiry ? (int) now()->diffInDays($expiry, false) : 0;
    }

    /**
     * Build the database connection config array for this tenant.
     */
    public function databaseConfig(): array
    {
        return [
            'driver'    => 'mysql',
            'host'      => $this->database_host,
            'port'      => $this->database_port,
            'database'  => $this->database_name,
            'username'  => $this->database_username,
            'password'  => $this->getDatabasePasswordDecryptedAttribute(),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ];
    }
}
