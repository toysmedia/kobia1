<?php

namespace App\Services\Customer;

use App\Models\Tenant\TenantSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerMpesaService
{
    protected string $baseUrl = 'https://api.safaricom.co.ke';

    protected function getSetting(string $key): ?string
    {
        return TenantSetting::get($key);
    }

    /**
     * Obtain an OAuth access token from Safaricom Daraja.
     */
    public function getAccessToken(): string
    {
        $consumerKey    = $this->getSetting('mpesa_consumer_key');
        $consumerSecret = $this->getSetting('mpesa_consumer_secret');

        if (!$consumerKey || !$consumerSecret) {
            throw new \RuntimeException('M-Pesa credentials are not configured for this tenant.');
        }

        $response = Http::withBasicAuth($consumerKey, $consumerSecret)
            ->get("{$this->baseUrl}/oauth/v1/generate", ['grant_type' => 'client_credentials']);

        Log::info('[CustomerMpesa] Access token request', ['status' => $response->status()]);

        if ($response->failed()) {
            throw new \RuntimeException('M-Pesa OAuth failed: ' . $response->body());
        }

        return $response->json('access_token');
    }

    /**
     * Initiate an STK push to the customer's phone.
     * Rate-limited: one push per phone+amount combination per 60 seconds.
     */
    public function stkPush(string $phone, float $amount, string $accountRef, string $description = 'Internet Renewal'): array
    {
        $phone = $this->formatPhone($phone);

        // Prevent duplicate STK pushes for the same phone + amount within 60 seconds
        $cacheKey = "customer_mpesa:stk_push:{$phone}:{$amount}";
        $cached   = Cache::get($cacheKey);
        if ($cached) {
            Log::info('[CustomerMpesa] STK push rate-limited, returning cached response', compact('phone', 'amount'));
            return $cached;
        }

        $shortcode = $this->getSetting('mpesa_shortcode');
        $passkey   = $this->getSetting('mpesa_passkey');
        $callbackUrl = $this->getSetting('mpesa_callback_url') ?? config('mpesa.callback_url');

        if (!$shortcode || !$passkey) {
            throw new \RuntimeException('M-Pesa shortcode or passkey not configured for this tenant.');
        }

        $token     = $this->getAccessToken();
        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($shortcode . $passkey . $timestamp);

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => (int) ceil($amount),
            'PartyA'            => $phone,
            'PartyB'            => $shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => $callbackUrl,
            'AccountReference'  => $accountRef,
            'TransactionDesc'   => $description,
        ];

        Log::info('[CustomerMpesa] STK Push request', ['phone' => $phone, 'amount' => $amount, 'ref' => $accountRef]);

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", $payload);

        $result = $response->json() ?? [];

        Log::info('[CustomerMpesa] STK Push response', $result);

        if ($response->failed() || ($result['ResponseCode'] ?? '0') !== '0') {
            throw new \RuntimeException('STK Push failed: ' . ($result['ResponseDescription'] ?? $response->body()));
        }

        Cache::put($cacheKey, $result, 60);

        return $result;
    }

    /**
     * Format phone number to 254XXXXXXXXX format.
     */
    public function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '254' . substr($phone, 1);
        }

        if (str_starts_with($phone, '+')) {
            return ltrim($phone, '+');
        }

        if (!str_starts_with($phone, '254')) {
            return '254' . $phone;
        }

        return $phone;
    }
}
