<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SuperAdminMpesaService
{
    private string $consumerKey;
    private string $consumerSecret;
    private string $shortcode;
    private string $passkey;
    private string $baseUrl;

    public function __construct()
    {
        $this->consumerKey    = env('SA_MPESA_CONSUMER_KEY', '');
        $this->consumerSecret = env('SA_MPESA_CONSUMER_SECRET', '');
        $this->shortcode      = env('SA_MPESA_SHORTCODE', '');
        $this->passkey        = env('SA_MPESA_PASSKEY', '');
        $this->baseUrl        = app()->environment('production')
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    public function getAccessToken(): ?string
    {
        return Cache::remember('sa_mpesa_token', 3500, function () {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get("{$this->baseUrl}/oauth/v1/generate?grant_type=client_credentials");
            if ($response->successful()) {
                return $response->json('access_token');
            }
            Log::error('SA MPesa token error', $response->json());
            return null;
        });
    }

    public function stkPush(string $phone, float $amount, int $tenantId, string $description): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'message' => 'Could not get access token'];
        }
        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);
        $phone = preg_replace('/^0/', '254', $phone);
        $phone = preg_replace('/^\+/', '', $phone);
        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", [
                'BusinessShortCode' => $this->shortcode,
                'Password'          => $password,
                'Timestamp'         => $timestamp,
                'TransactionType'   => 'CustomerPayBillOnline',
                'Amount'            => (int) $amount,
                'PartyA'            => $phone,
                'PartyB'            => $this->shortcode,
                'PhoneNumber'       => $phone,
                'CallBackURL'       => url('/api/super-admin/mpesa/callback'),
                'AccountReference'  => "TENANT-{$tenantId}",
                'TransactionDesc'   => $description,
            ]);
        if ($response->successful() && $response->json('ResponseCode') === '0') {
            return ['success' => true, 'data' => $response->json()];
        }
        return ['success' => false, 'message' => $response->json('errorMessage') ?? 'STK Push failed'];
    }
}
