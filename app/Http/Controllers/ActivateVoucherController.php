<?php
namespace App\Http\Controllers;

use App\Models\MpesaPayment;
use App\Models\Radcheck;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActivateVoucherController extends Controller
{
    /**
     * Activate a hotspot voucher using M-Pesa receipt code.
     * Called from hotspot login page via POST.
     */
    public function activate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ref' => 'required|string|min:6|max:20',
        ]);

        $ref = strtoupper(trim($data['ref']));

        // Check if voucher already exists in RADIUS (re-activation is allowed)
        $exists = Radcheck::where('username', $ref)
            ->where('attribute', 'Cleartext-Password')
            ->exists();

        if ($exists) {
            return response()->json([
                'success'  => true,
                'username' => $ref,
                'password' => $ref,
                'message'  => 'Voucher activated. Use this code to login.',
            ]);
        }

        // Check payment record
        $payment = MpesaPayment::where('mpesa_receipt_number', $ref)
            ->where('status', 'completed')
            ->first();

        if ($payment) {
            // Provision it now (in case it wasn't done automatically)
            if ($payment->isp_package_id) {
                $radius = app(\App\Services\RadiusService::class);
                $radius->provisionHotspotVoucher($ref, $payment->package);
            }

            // Track first activation time
            if (is_null($payment->used_at)) {
                $payment->update(['used_at' => now()]);
            }

            return response()->json([
                'success'  => true,
                'username' => $ref,
                'password' => $ref,
                'message'  => 'Voucher activated.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired voucher code. Please check the M-Pesa message.',
        ], 404);
    }
}
