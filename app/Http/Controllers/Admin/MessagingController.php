<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageLog;
use App\Models\Subscriber;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use App\Services\EmailService;
use Illuminate\Http\Request;

class MessagingController extends Controller
{
    public function __construct(
        protected SmsService      $sms,
        protected WhatsAppService $whatsapp,
        protected EmailService    $email
    ) {}

    public function sms()
    {
        $logs = MessageLog::where('type', 'sms')->latest()->take(10)->get();
        return view('admin.isp.messaging.sms', compact('logs'));
    }

    public function sendSms(Request $request)
    {
        $request->validate([
            'phone'   => 'required|string|max:20',
            'message' => 'required|string|max:160',
        ]);

        $result = $this->sms->send($request->phone, $request->message);

        MessageLog::create([
            'type'      => 'sms',
            'recipient' => $request->phone,
            'message'   => $request->message,
            'gateway'   => config('sms.driver', 'africastalking'),
            'status'    => isset($result['error']) ? 'failed' : 'sent',
            'response'  => $result,
            'sent_at'   => now(),
        ]);

        return back()->with(
            isset($result['error']) ? 'error' : 'success',
            isset($result['error']) ? 'SMS failed: ' . $result['error'] : 'SMS sent successfully.'
        );
    }

    public function bulkSms(Request $request)
    {
        $request->validate([
            'recipients' => 'required|string',
            'message'    => 'required|string|max:160',
        ]);

        $phones = $this->resolveRecipients('phone', $request->recipients, $request->custom_phones);
        $sent   = 0;
        $failed = 0;

        foreach ($phones as $phone) {
            $result = $this->sms->send($phone, $request->message);
            $ok     = !isset($result['error']);
            $ok ? $sent++ : $failed++;
            MessageLog::create([
                'type'      => 'sms',
                'recipient' => $phone,
                'message'   => $request->message,
                'gateway'   => config('sms.driver', 'africastalking'),
                'status'    => $ok ? 'sent' : 'failed',
                'response'  => $result,
                'sent_at'   => now(),
            ]);
        }

        return back()->with('success', "Bulk SMS: {$sent} sent, {$failed} failed.");
    }

    public function whatsapp()
    {
        $logs = MessageLog::where('type', 'whatsapp')->latest()->take(10)->get();
        return view('admin.isp.messaging.whatsapp', compact('logs'));
    }

    public function sendWhatsapp(Request $request)
    {
        $request->validate([
            'phone'   => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        $result = $this->whatsapp->send($request->phone, $request->message);

        MessageLog::create([
            'type'      => 'whatsapp',
            'recipient' => $request->phone,
            'message'   => $request->message,
            'gateway'   => 'whatsapp_api',
            'status'    => isset($result['error']) ? 'failed' : 'sent',
            'response'  => $result,
            'sent_at'   => now(),
        ]);

        return back()->with(
            isset($result['error']) ? 'error' : 'success',
            isset($result['error']) ? 'WhatsApp failed: ' . $result['error'] : 'WhatsApp message sent.'
        );
    }

    public function bulkWhatsapp(Request $request)
    {
        $request->validate([
            'recipients' => 'required|string',
            'message'    => 'required|string',
        ]);

        $phones = $this->resolveRecipients('phone', $request->recipients, $request->custom_phones);
        $sent   = 0;
        $failed = 0;

        foreach ($phones as $phone) {
            $result = $this->whatsapp->send($phone, $request->message);
            $ok     = !isset($result['error']);
            $ok ? $sent++ : $failed++;
            MessageLog::create([
                'type'      => 'whatsapp',
                'recipient' => $phone,
                'message'   => $request->message,
                'gateway'   => 'whatsapp_api',
                'status'    => $ok ? 'sent' : 'failed',
                'response'  => $result,
                'sent_at'   => now(),
            ]);
        }

        return back()->with('success', "Bulk WhatsApp: {$sent} sent, {$failed} failed.");
    }

    public function email()
    {
        $logs = MessageLog::where('type', 'email')->latest()->take(10)->get();
        return view('admin.isp.messaging.email', compact('logs'));
    }

    public function sendEmail(Request $request)
    {
        $request->validate([
            'to'      => 'required|email',
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        $result = $this->email->send($request->to, $request->subject, $request->body);

        MessageLog::create([
            'type'      => 'email',
            'recipient' => $request->to,
            'subject'   => $request->subject,
            'message'   => $request->body,
            'gateway'   => 'smtp',
            'status'    => isset($result['error']) ? 'failed' : 'sent',
            'response'  => $result,
            'sent_at'   => now(),
        ]);

        return back()->with(
            isset($result['error']) ? 'error' : 'success',
            isset($result['error']) ? 'Email failed: ' . $result['error'] : 'Email sent successfully.'
        );
    }

    public function bulkEmail(Request $request)
    {
        $request->validate([
            'recipients' => 'required|string',
            'subject'    => 'required|string|max:255',
            'body'       => 'required|string',
        ]);

        $emails = $this->resolveRecipients('email', $request->recipients, $request->custom_emails);
        $sent   = 0;
        $failed = 0;

        foreach ($emails as $emailAddr) {
            $result = $this->email->send($emailAddr, $request->subject, $request->body);
            $ok     = !isset($result['error']);
            $ok ? $sent++ : $failed++;
            MessageLog::create([
                'type'      => 'email',
                'recipient' => $emailAddr,
                'subject'   => $request->subject,
                'message'   => $request->body,
                'gateway'   => 'smtp',
                'status'    => $ok ? 'sent' : 'failed',
                'response'  => $result,
                'sent_at'   => now(),
            ]);
        }

        return back()->with('success', "Bulk Email: {$sent} sent, {$failed} failed.");
    }

    public function logs(Request $request)
    {
        $query = MessageLog::latest();
        if ($request->filled('type'))   $query->where('type', $request->type);
        if ($request->filled('status')) $query->where('status', $request->status);

        $logs = $query->paginate(50)->withQueryString();
        return view('admin.isp.messaging.logs', compact('logs'));
    }

    /**
     * Resolve recipients based on selection criteria.
     */
    protected function resolveRecipients(string $field, string $type, ?string $custom): array
    {
        if ($type === 'custom') {
            $lines = preg_split('/[\r\n,]+/', $custom ?? '');
            return array_filter(array_map('trim', $lines));
        }

        $query = Subscriber::whereNotNull($field)->where($field, '!=', '');

        match ($type) {
            'all_pppoe'   => $query->where('connection_type', 'pppoe'),
            'all_hotspot' => $query->where('connection_type', 'hotspot'),
            'active'      => $query->where('status', 'active'),
            'expired'     => $query->where('status', 'expired'),
            default       => null,
        };

        return $query->pluck($field)->unique()->toArray();
    }
}
