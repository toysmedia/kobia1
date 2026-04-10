<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Granular feature flag for a pricing plan.
 *
 * @property int $id
 * @property int $plan_id
 * @property string $feature_key
 * @property string $feature_name
 * @property bool $is_enabled
 */
class FeatureFlag extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'feature_flags';

    protected $fillable = [
        'plan_id',
        'feature_key',
        'feature_name',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The plan this feature flag belongs to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'plan_id');
    }
}
