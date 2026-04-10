<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\IspPackage;
use App\Models\MpesaPayment;
use App\Models\Subscriber;
use App\Services\Customer\CustomerMpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly CustomerMpesaService $mpesa) {}

    public function index(): View
    {
        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();

        $payments = MpesaPayment::where('subscriber_id', $subscriber->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('customer.payments.index', compact('payments'));
    }

    public function renew(): View
    {
        $packages = IspPackage::where('is_active', true)->orderBy('price')->get();
        return view('customer.payments.renew', compact('packages'));
    }

    public function processRenewal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => ['required', 'exists:isp_packages,id'],
            'phone'      => ['required', 'string', 'min:9', 'max:15'],
        ]);

        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();
        $package    = IspPackage::findOrFail($data['package_id']);

        try {
            $response = $this->mpesa->stkPush(
                $data['phone'],
                $package->price,
                "RENEW-{$subscriber->id}",
                "Internet renewal: {$package->name}"
            );

            MpesaPayment::create([
                'subscriber_id'       => $subscriber->id,
                'isp_package_id'      => $package->id,
                'phone'               => $this->mpesa->formatPhone($data['phone']),
                'amount'              => $package->price,
                'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
                'merchant_request_id' => $response['MerchantRequestID'] ?? null,
                'status'              => 'pending',
                'connection_type'     => 'pppoe',
            ]);

            return back()->with('success', 'STK Push sent! Enter your M-Pesa PIN on your phone to complete payment.')
                         ->with('checkout_request_id', $response['CheckoutRequestID'] ?? null);
        } catch (\Exception $e) {
            Log::error('[CustomerPortal] STK Push failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Payment initiation failed: ' . $e->getMessage());
        }
    }

    public function receipt(int $id): View
    {
        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();

        $payment = MpesaPayment::where('subscriber_id', $subscriber->id)
            ->with('package')
            ->findOrFail($id);

        return view('customer.payments.receipt', compact('payment', 'subscriber'));
    }

    // -------------------------------------------------------------------------
    // API endpoints
    // -------------------------------------------------------------------------

    public function apiStkPush(Request $request): JsonResponse
    {
        $data = $request->validate([
            'package_id' => ['required', 'exists:isp_packages,id'],
            'phone'      => ['required', 'string', 'min:9', 'max:15'],
        ]);

        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();
        $package    = IspPackage::findOrFail($data['package_id']);

        try {
            $response = $this->mpesa->stkPush(
                $data['phone'],
                $package->price,
                "RENEW-{$subscriber->id}",
                "Internet renewal: {$package->name}"
            );

            $payment = MpesaPayment::create([
                'subscriber_id'       => $subscriber->id,
                'isp_package_id'      => $package->id,
                'phone'               => $this->mpesa->formatPhone($data['phone']),
                'amount'              => $package->price,
                'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
                'merchant_request_id' => $response['MerchantRequestID'] ?? null,
                'status'              => 'pending',
                'connection_type'     => 'pppoe',
            ]);

            return response()->json([
                'message'              => 'STK Push sent. Enter your PIN.',
                'checkout_request_id'  => $response['CheckoutRequestID'] ?? null,
                'payment_id'           => $payment->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function stkCallback(Request $request): JsonResponse
    {
        Log::info('[CustomerPortal] STK callback received', $request->all());

        $body = $request->input('Body.stkCallback');
        if (!$body) {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $checkoutRequestId = $body['CheckoutRequestID'] ?? null;
        $resultCode        = $body['ResultCode'] ?? -1;

        $payment = MpesaPayment::where('checkout_request_id', $checkoutRequestId)->first();
        if (!$payment || $payment->status === 'completed') {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        if ($resultCode === 0) {
            $items   = collect($body['CallbackMetadata']['Item'] ?? []);
            $receipt = $items->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
            $amount  = $items->firstWhere('Name', 'Amount')['Value'] ?? null;

            $payment->update([
                'status'               => 'completed',
                'mpesa_receipt_number' => $receipt,
                'amount'               => $amount ?? $payment->amount,
                'result_code'          => 0,
                'result_desc'          => 'Success',
                'raw_callback'         => $request->all(),
            ]);

            // Provision subscriber
            if ($payment->subscriber && $payment->package) {
                app(\App\Services\Customer\CustomerBillingService::class)
                    ->renewSubscription($payment->subscriber, $payment->package, $receipt ?? '');
            }
        } else {
            $payment->update([
                'status'       => 'failed',
                'result_code'  => $resultCode,
                'result_desc'  => $body['ResultDesc'] ?? 'Failed',
                'raw_callback' => $request->all(),
            ]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function paymentStatus(Request $request, string $checkoutRequestId): JsonResponse
    {
        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();

        $payment = MpesaPayment::where('checkout_request_id', $checkoutRequestId)
            ->where('subscriber_id', $subscriber->id)
            ->first();

        if (!$payment) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status'               => $payment->status,
            'mpesa_receipt_number' => $payment->mpesa_receipt_number,
            'amount'               => $payment->amount,
        ]);
    }
}

