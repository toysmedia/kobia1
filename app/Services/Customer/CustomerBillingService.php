<?php

namespace App\Services\Customer;

use App\Models\IspPackage;
use App\Models\MpesaPayment;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Log;

class CustomerBillingService
{
    /**
     * Renew a subscriber's account after a successful M-Pesa payment.
     */
    public function renewSubscription(Subscriber $subscriber, IspPackage $package, string $mpesaReceipt): Subscriber
    {
        $base = $subscriber->expires_at && $subscriber->expires_at->isFuture()
            ? $subscriber->expires_at
            : now();

        $newExpiry = $base
            ->copy()
            ->addDays($package->validity_days)
            ->addHours($package->validity_hours);

        $subscriber->update([
            'status'         => 'active',
            'expires_at'     => $newExpiry,
            'isp_package_id' => $package->id,
        ]);

        MpesaPayment::where('mpesa_receipt_number', $mpesaReceipt)->update([
            'subscriber_id'  => $subscriber->id,
            'isp_package_id' => $package->id,
            'connection_type' => 'pppoe',
        ]);

        Log::info('[CustomerBilling] Subscription renewed', [
            'subscriber_id' => $subscriber->id,
            'package'       => $package->name,
            'expires_at'    => $newExpiry,
            'receipt'       => $mpesaReceipt,
        ]);

        return $subscriber->fresh();
    }

    /**
     * Return a countdown array showing time remaining until the subscriber's expiry.
     */
    public function getExpiryCountdown(Subscriber $subscriber): array
    {
        if (!$subscriber->expires_at || $subscriber->expires_at->isPast()) {
            return [
                'days'    => 0,
                'hours'   => 0,
                'minutes' => 0,
                'seconds' => 0,
                'expired' => true,
            ];
        }

        $diff    = now()->diff($subscriber->expires_at);
        $totalSeconds = $subscriber->expires_at->timestamp - now()->timestamp;

        return [
            'days'    => $diff->days,
            'hours'   => $diff->h,
            'minutes' => $diff->i,
            'seconds' => $diff->s,
            'expired' => $totalSeconds <= 0,
            'expires_at_iso' => $subscriber->expires_at->toISOString(),
        ];
    }
}
