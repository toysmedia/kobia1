<?php
namespace App\Http\Controllers;

use App\Models\MpesaPayment;
use App\Models\IspPackage;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    public function __construct(protected MpesaService $mpesa) {}

    /**
     * Initiate STK Push from customer dashboard or public buy page.
     */
    public function stkPush(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone'          => 'required|string|min:9|max:15',
            'package_id'     => 'required|exists:isp_packages,id',
            'connection_type'=> 'required|in:pppoe,hotspot',
            'subscriber_id'  => 'nullable|exists:subscribers,id',
        ]);

        $package = IspPackage::findOrFail($data['package_id']);

        try {
            $phone    = $this->mpesa->formatPhone($data['phone']);
            $response = $this->mpesa->stkPush(
                $phone,
                $package->price,
                "ISP-{$package->id}",
                "Payment for {$package->name}"
            );

            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                // Create pending payment record
                MpesaPayment::create([
                    'subscriber_id'       => $data['subscriber_id'] ?? null,
                    'phone'               => $phone,
                    'amount'              => $package->price,
                    'isp_package_id'      => $package->id,
                    'connection_type'     => $data['connection_type'],
                    'status'              => 'pending',
                    'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
                    'merchant_request_id' => $response['MerchantRequestID'] ?? null,
                    'account_reference'   => "ISP-{$package->id}",
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'STK Push sent to your phone. Enter your M-Pesa PIN.',
                    'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response['CustomerMessage'] ?? 'STK Push failed.',
            ], 422);
        } catch (\Exception $e) {
            Log::error('STK Push error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Payment initiation failed. Try again.'], 500);
        }
    }

    /**
     * Handle STK Push callback from Safaricom (webhook).
     * No CSRF — this is called by Safaricom servers.
     */
    public function stkCallback(Request $request): JsonResponse
    {
        $data = $request->all();
        Log::info('M-Pesa STK Callback received', [
            'MerchantRequestID' => $data['Body']['stkCallback']['MerchantRequestID'] ?? 'N/A',
            'CheckoutRequestID' => $data['Body']['stkCallback']['CheckoutRequestID'] ?? 'N/A',
            'ResultCode'        => $data['Body']['stkCallback']['ResultCode'] ?? 'N/A',
        ]);

        try {
            $this->mpesa->handleCallback($data);
        } catch (\Exception $e) {
            Log::error('STK Callback processing error', ['error' => $e->getMessage()]);
        }

        // Always return 200 to Safaricom
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * C2B Validation URL — called by Safaricom before confirming payment.
     */
    public function c2bValidation(Request $request): JsonResponse
    {
        Log::info('M-Pesa C2B Validation', [
            'TransID'      => $request->input('TransID', 'N/A'),
            'TransAmount'  => $request->input('TransAmount', 'N/A'),
            'BillRefNumber'=> $request->input('BillRefNumber', 'N/A'),
        ]);
        // Accept all payments
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * C2B Confirmation URL — called by Safaricom after payment is confirmed.
     */
    public function c2bConfirmation(Request $request): JsonResponse
    {
        $data = $request->all();
        Log::info('M-Pesa C2B Confirmation received', [
            'TransID'      => $data['TransID'] ?? 'N/A',
            'TransAmount'  => $data['TransAmount'] ?? 'N/A',
            'BillRefNumber'=> $data['BillRefNumber'] ?? 'N/A',
        ]);

        try {
            $this->mpesa->handleC2BConfirmation($data);
        } catch (\Exception $e) {
            Log::error('C2B Confirmation processing error', ['error' => $e->getMessage()]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Check payment status by checkout request ID or receipt number.
     */
    public function checkPayment(string $ref): JsonResponse
    {
        $payment = MpesaPayment::where('checkout_request_id', $ref)
            ->orWhere('mpesa_receipt_number', $ref)
            ->first();

        if (!$payment) {
            return response()->json(['found' => false, 'status' => 'not_found']);
        }

        return response()->json([
            'found'          => true,
            'status'         => $payment->status,
            'receipt'        => $payment->mpesa_receipt_number,
            'amount'         => $payment->amount,
            'voucher'        => $payment->connection_type === 'hotspot'
                ? $payment->mpesa_receipt_number
                : null,
        ]);
    }

    /**
     * Register C2B URLs with Safaricom (admin action).
     */
    public function registerC2BUrls(): JsonResponse
    {
        try {
            $result = $this->mpesa->registerC2BUrl();
            return response()->json(['success' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
