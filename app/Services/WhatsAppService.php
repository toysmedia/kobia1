<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $instanceId;
    protected string $sender;

    public function __construct()
    {
        $this->apiUrl     = config('whatsapp.api_url', '');
        $this->apiKey     = config('whatsapp.api_key', '');
        $this->instanceId = config('whatsapp.instance_id', '');
        $this->sender     = config('whatsapp.sender', '');
    }

    /**
     * Send a single WhatsApp message.
     */
    public function send(string $to, string $message): array
    {
        if (empty($this->apiUrl) || empty($this->apiKey)) {
            Log::info('WhatsApp skipped (not configured)', compact('to', 'message'));
            return ['status' => 'skipped'];
        }

        $to = $this->formatPhone($to);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->post($this->apiUrl . '/send', [
                'instance_id' => $this->instanceId,
                'to'          => $to,
                'message'     => $message,
            ]);

            Log::info('WhatsApp sent', ['to' => $to, 'response' => $response->json()]);
            return $response->json() ?? ['status' => 'sent'];
        } catch (\Exception $e) {
            Log::error('WhatsApp send error', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '254')) {
            $phone = '254' . $phone;
        }
        return $phone . '@c.us';
    }
}
