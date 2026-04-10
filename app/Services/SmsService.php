<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $gateway;

    public function __construct()
    {
        $this->gateway = config('sms.driver', 'africastalking');
    }

    /**
     * Send an SMS using the configured gateway.
     */
    public function send(string $to, string $message): array
    {
        return match ($this->gateway) {
            'blessed_africa' => $this->sendViaBlessedAfrica($to, $message),
            'advanta'        => $this->sendViaAdvanta($to, $message),
            default          => $this->sendViaAfricasTalking($to, $message),
        };
    }

    /**
     * Send via Africa's Talking API.
     */
    protected function sendViaAfricasTalking(string $to, string $message): array
    {
        $apiKey   = config('sms.africastalking.api_key', '');
        $username = config('sms.africastalking.username', 'sandbox');
        $senderId = config('sms.africastalking.sender_id', '');
        $baseUrl  = config('sms.africastalking.base_url');

        if (empty($apiKey)) {
            Log::info('SMS skipped (no Africa\'s Talking API key)', compact('to'));
            return ['status' => 'skipped'];
        }

        $to = $this->formatPhone($to);
        $payload = ['username' => $username, 'to' => $to, 'message' => $message];
        if ($senderId) {
            $payload['from'] = $senderId;
        }

        try {
            $response = Http::withHeaders([
                'apiKey' => $apiKey,
                'Accept' => 'application/json',
            ])->asForm()->post("{$baseUrl}/messaging", $payload);

            Log::info('SMS sent via Africa\'s Talking', ['to' => $to, 'response' => $response->json()]);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Africa\'s Talking SMS error', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Send via Blessed Africa API.
     */
    protected function sendViaBlessedAfrica(string $to, string $message): array
    {
        $apiKey   = config('sms.blessed_africa.api_key', '');
        $senderId = config('sms.blessed_africa.sender_id', '');
        $endpoint = 'https://blessedafrica.com/api/v1/sms/send';

        if (empty($apiKey)) {
            Log::info('SMS skipped (no Blessed Africa API key)', compact('to'));
            return ['status' => 'skipped'];
        }

        $to = $this->formatPhone($to);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->post($endpoint, [
                'to'        => $to,
                'message'   => $message,
                'sender_id' => $senderId,
            ]);

            Log::info('SMS sent via Blessed Africa', ['to' => $to, 'response' => $response->json()]);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Blessed Africa SMS error', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Send via Advanta API.
     */
    protected function sendViaAdvanta(string $to, string $message): array
    {
        $apiKey    = config('sms.advanta.api_key', '');
        $partnerId = config('sms.advanta.partner_id', '');
        $senderId  = config('sms.advanta.sender_id', '');
        $endpoint  = 'https://api.advanta.africa/v1/send';

        if (empty($apiKey)) {
            Log::info('SMS skipped (no Advanta API key)', compact('to'));
            return ['status' => 'skipped'];
        }

        $to = $this->formatPhone($to);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($endpoint, [
                'api_key'    => $apiKey,
                'partner_id' => $partnerId,
                'sender_id'  => $senderId,
                'mobile'     => $to,
                'message'    => $message,
            ]);

            Log::info('SMS sent via Advanta', ['to' => $to, 'response' => $response->json()]);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Advanta SMS error', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Format phone number to international format (+254...).
     */
    public function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '+254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '254')) {
            $phone = '+' . $phone;
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+254' . $phone;
        }
        return $phone;
    }
}

