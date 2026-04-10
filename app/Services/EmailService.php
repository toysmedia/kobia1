<?php
namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send a single email.
     */
    public function send(string $to, string $subject, string $body, ?string $fromName = null, ?string $fromEmail = null): array
    {
        try {
            $from     = $fromEmail ?? config('mail.from.address', 'noreply@example.com');
            $fromName = $fromName  ?? config('mail.from.name', config('app.name'));
            $html     = $body;
            $text     = strip_tags($body);

            Mail::send([], [], function (Message $mail) use ($to, $subject, $from, $fromName, $html, $text) {
                $mail->to($to)
                     ->subject($subject)
                     ->from($from, $fromName)
                     ->setBody($html, 'text/html')
                     ->addPart($text, 'text/plain');
            });

            Log::info('Email sent', ['to' => $to, 'subject' => $subject]);
            return ['status' => 'sent'];
        } catch (\Exception $e) {
            Log::error('Email send error', ['error' => $e->getMessage(), 'to' => $to]);
            return ['error' => $e->getMessage()];
        }
    }
}
