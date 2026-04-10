<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Records a tenant subscription payment collected by Super Admin.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $plan_id
 * @property float $amount
 * @property string $payment_method
 * @property string|null $transaction_id
 * @property string|null $mpesa_receipt_number
 * @property string|null $phone_number
 * @property string $status
 * @property \Carbon\Carbon|null $paid_at
 */
class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'subscription_payments';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'amount',
        'payment_method',
        'transaction_id',
        'mpesa_receipt_number',
        'phone_number',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The tenant this payment belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * The pricing plan associated with this payment.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'plan_id');
    }
}
