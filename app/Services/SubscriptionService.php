<?php

namespace App\Services;

use App\Models\System\PricingPlan;
use App\Models\System\SubscriptionPayment;
use App\Models\System\Tenant;

class SubscriptionService
{
    public function create(Tenant $tenant, PricingPlan $plan, array $paymentData): SubscriptionPayment
    {
        $payment = SubscriptionPayment::create(array_merge([
            'tenant_id' => $tenant->id,
            'plan_id'   => $plan->id,
            'amount'    => $plan->price,
            'status'    => 'completed',
        ], $paymentData));

        $expiresAt = match ($plan->billing_cycle) {
            'quarterly' => now()->addMonths(3),
            'yearly'    => now()->addYear(),
            default     => now()->addMonth(),
        };

        $tenant->update([
            'plan_id'                 => $plan->id,
            'subscription_expires_at' => $expiresAt,
            'status'                  => 'active',
        ]);

        return $payment;
    }

    public function extend(Tenant $tenant, int $days = 30): void
    {
        $current = $tenant->subscription_expires_at ?? now();
        $tenant->update([
            'subscription_expires_at' => $current->addDays($days),
            'status'                  => 'active',
        ]);
    }

    public function expire(Tenant $tenant): void
    {
        $tenant->update(['status' => 'expired']);
    }

    public function lock(Tenant $tenant): void
    {
        $tenant->update(['status' => 'suspended']);
    }
}
