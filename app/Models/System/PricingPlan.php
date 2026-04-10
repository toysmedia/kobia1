<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a subscription pricing plan offered to ISP tenants.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property float $price
 * @property string $billing_cycle
 * @property int $trial_days
 * @property int $max_customers
 * @property int $max_routers
 * @property array|null $feature_flags
 * @property bool $is_active
 * @property int $sort_order
 */
class PricingPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $table = 'pricing_plans';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'trial_days',
        'max_customers',
        'max_routers',
        'feature_flags',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'trial_days'    => 'integer',
        'max_customers' => 'integer',
        'max_routers'   => 'integer',
        'feature_flags' => 'array',
        'is_active'     => 'boolean',
        'sort_order'    => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Tenants subscribed to this plan.
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'plan_id');
    }

    /**
     * Feature flags associated with this plan.
     */
    public function featureFlags(): HasMany
    {
        return $this->hasMany(FeatureFlag::class, 'plan_id');
    }

    /**
     * Subscription payments made for this plan.
     */
    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'plan_id');
    }

    // -------------------------------------------------------------------------
    // Helper methods
    // -------------------------------------------------------------------------

    /**
     * Check whether a specific feature is enabled on this plan.
     */
    public function hasFeature(string $featureKey): bool
    {
        $flags = $this->feature_flags ?? [];

        return isset($flags[$featureKey]) && $flags[$featureKey] === true;
    }
}
