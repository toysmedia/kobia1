<?php
namespace App\Services;

use App\Models\MpesaPayment;
use App\Models\IspPackage;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MpesaService
{
    protected string $baseUrl;
    protected string $consumerKey;
    protected string $consumerSecret;
    protected string $shortcode;
    protected string $passkey;

    public function __construct()
    {
        $this->baseUrl        = config('mpesa.base_url');
        $this->consumerKey    = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
        $this->shortcode      = config('mpesa.shortcode');
        $this->passkey        = config('mpesa.passkey');
    }

    /**
     * Get OAuth access token from Daraja API.
     */
    public function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->get("{$this->baseUrl}/oauth/v1/generate", ['grant_type' => 'client_credentials']);

        if ($response->failed()) {
            throw new \Exception('M-Pesa OAuth failed: ' . $response->body());
        }

        return $response->json('access_token');
    }

    /**
     * Initiate STK Push (Lipa Na M-Pesa Online).
     */
    public function stkPush(string $phone, float $amount, string $accountRef, string $description = 'ISP Payment'): array
    {
        $token     = $this->getAccessToken();
        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);
        $phone     = $this->formatPhone($phone);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => (int) ceil($amount),
            'PartyA'            => $phone,
            'PartyB'            => $this->shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => config('mpesa.callback_url'),
            'AccountReference'  => $accountRef,
            'TransactionDesc'   => $description,
        ];

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", $payload);

        Log::info('M-Pesa STK Push Response', $response->json() ?? []);

        if ($response->failed()) {
            throw new \Exception('STK Push failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Register C2B Validation and Confirmation URLs with Safaricom.
     */
    public function registerC2BUrl(): array
    {
        $token = $this->getAccessToken();

        $payload = [
            'ShortCode'       => config('mpesa.c2b_shortcode'),
            'ResponseType'    => 'Completed',
            'ConfirmationURL' => config('mpesa.c2b_confirmation_url'),
            'ValidationURL'   => config('mpesa.c2b_validation_url'),
        ];

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/mpesa/c2b/v1/registerurl", $payload);

        return $response->json();
    }

    /**
     * Handle STK Push callback from Safaricom.
     * Returns the MpesaPayment record or null on failure.
     */
    public function handleCallback(array $data): ?MpesaPayment
    {
        Log::info('M-Pesa STK Callback', $data);

        $body = $data['Body']['stkCallback'] ?? null;
        if (!$body) {
            return null;
        }

        $checkoutRequestId = $body['CheckoutRequestID'] ?? null;
        $resultCode        = $body['ResultCode'] ?? -1;
        $resultDesc        = $body['ResultDesc'] ?? '';

        $payment = MpesaPayment::where('checkout_request_id', $checkoutRequestId)->first();
        if (!$payment) {
            return null;
        }

        // Idempotency: skip re-processing already completed payments
        if ($payment->status === 'completed') {
            Log::info('M-Pesa STK Callback: payment already completed, skipping.', ['checkout_request_id' => $checkoutRequestId]);
            return $payment;
        }

        if ($resultCode == 0) {
            // Payment successful
            $items = collect($body['CallbackMetadata']['Item'] ?? []);
            $receipt   = $items->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
            $amount    = $items->firstWhere('Name', 'Amount')['Value'] ?? null;
            $phone     = $items->firstWhere('Name', 'PhoneNumber')['Value'] ?? null;
            $transDate = $items->firstWhere('Name', 'TransactionDate')['Value'] ?? null;

            $payment->update([
                'status'               => 'completed',
                'mpesa_receipt_number' => $receipt,
                'amount'               => $amount ?? $payment->amount,
                'phone'                => $phone ?? $payment->phone,
                'transaction_date'     => $transDate ? $this->parseMpesaDate((string)$transDate) : now(),
                'result_code'          => $resultCode,
                'result_desc'          => $resultDesc,
                'raw_callback'         => $data,
            ]);

            // Provision the service
            $this->provisionAfterPayment($payment);
        } else {
            $payment->update([
                'status'       => 'failed',
                'result_code'  => $resultCode,
                'result_desc'  => $resultDesc,
                'raw_callback' => $data,
            ]);
        }

        return $payment;
    }

    /**
     * Handle C2B Confirmation callback.
     */
    public function handleC2BConfirmation(array $data): ?MpesaPayment
    {
        Log::info('M-Pesa C2B Confirmation', $data);

        $transId   = $data['TransID'] ?? null;
        $amount    = $data['TransAmount'] ?? 0;
        $phone     = $data['MSISDN'] ?? null;
        $billRef   = $data['BillRefNumber'] ?? null;

        if (!$transId) {
            return null;
        }

        // Idempotency: return existing record if this TransID was already processed
        $existing = MpesaPayment::where('mpesa_receipt_number', $transId)->first();
        if ($existing) {
            Log::info('M-Pesa C2B Confirmation: duplicate TransID, returning existing record.', ['trans_id' => $transId]);
            return $existing;
        }

        // Find matching package by bill reference or amount
        $package = IspPackage::where('is_active', true)
            ->where('price', '<=', $amount)
            ->orderBy('price', 'desc')
            ->first();

        $payment = MpesaPayment::create([
            'phone'                => $this->formatPhone($phone ?? ''),
            'amount'               => $amount,
            'mpesa_receipt_number' => $transId,
            'transaction_date'     => now(),
            'mpesa_reference'      => $transId,
            'account_reference'    => $billRef,
            'status'               => 'completed',
            'result_code'          => 0,
            'result_desc'          => 'The service request is processed successfully.',
            'isp_package_id'       => $package?->id,
            'connection_type'      => 'hotspot',
            'raw_callback'         => $data,
        ]);

        $this->provisionAfterPayment($payment);

        return $payment;
    }

    /**
     * After a successful payment, provision the user/voucher in RADIUS.
     */
    protected function provisionAfterPayment(MpesaPayment $payment): void
    {
        if (!$payment->isp_package_id) {
            Log::warning('M-Pesa payment has no package assigned', ['payment_id' => $payment->id]);
            return;
        }

        $package = $payment->package;
        if (!$package) {
            return;
        }

        $radiusService = app(RadiusService::class);

        if ($payment->connection_type === 'hotspot') {
            // Use M-Pesa receipt as username AND password
            $receipt = $payment->mpesa_receipt_number;
            if ($receipt) {
                $radiusService->provisionHotspotVoucher($receipt, $package);

                // Update payment with the voucher info
                $payment->update(['mpesa_reference' => $receipt]);

                // Send SMS with the voucher
                $this->sendVoucherSms($payment->phone, $receipt, $package);
            }
        } else {
            // PPPoE: extend existing subscriber
            $subscriber = $payment->subscriber;
            if ($subscriber) {
                $expiresAt = $subscriber->expires_at && $subscriber->expires_at->isFuture()
                    ? $subscriber->expires_at
                    : now();

                $newExpiry = $expiresAt
                    ->addDays($package->validity_days)
                    ->addHours($package->validity_hours);

                $subscriber->update([
                    'expires_at'     => $newExpiry,
                    'status'         => 'active',
                    'isp_package_id' => $package->id,
                ]);

                $radiusService->provisionUser($subscriber->username, $subscriber->radius_password, $package);
            }
        }
    }

    /**
     * Send voucher SMS via SmsService.
     */
    protected function sendVoucherSms(string $phone, string $voucher, IspPackage $package): void
    {
        try {
            $sms = app(SmsService::class);
            $message = "Your WiFi voucher: {$voucher}\n"
                . "Package: {$package->name} ({$package->speed_download}Mbps)\n"
                . "Valid for: {$package->validity_days} days\n"
                . "Login: Enter this code at the WiFi login page.";
            $sms->send($phone, $message);
        } catch (\Exception $e) {
            Log::warning('Failed to send voucher SMS', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Safely parse an M-Pesa date string (YmdHis), falling back to now() on failure.
     */
    protected function parseMpesaDate(string $date): Carbon
    {
        try {
            return Carbon::createFromFormat('YmdHis', $date);
        } catch (\Exception $e) {
            Log::warning('Failed to parse M-Pesa transaction date', ['date' => $date]);
            return now();
        }
    }

    /**
     * Format phone number to 254XXXXXXXXX format.
     */
    public function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+')) {
            $phone = ltrim($phone, '+');
        } elseif (!str_starts_with($phone, '254')) {
            $phone = '254' . $phone;
        }
        return $phone;
    }
}
