<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MpesaPayment;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class EReceiptController extends Controller
{
    public function index()
    {
        return view('admin.isp.e_receipts');
    }

    public function getData(Request $request)
    {
        $filter = $request->get('filter', 'today');

        $query = MpesaPayment::where('connection_type', 'hotspot')
            ->where('status', 'completed')
            ->with(['package', 'subscriber'])
            ->orderBy('created_at', 'desc');

        switch ($filter) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->where('created_at', '>=', now()->startOfWeek());
                break;
        }

        $payments = $query->get();

        $data = $payments->map(function ($payment) {
            $subscriber = $payment->subscriber;
            $status     = 'used';

            if ($subscriber) {
                if ($subscriber->status === 'active' && $subscriber->expires_at && $subscriber->expires_at->isFuture()) {
                    $status = 'active';
                } elseif ($subscriber->expires_at && $subscriber->expires_at->isPast()) {
                    $status = 'expired';
                }
            }

            return [
                'id'           => $payment->id,
                'mpesa_receipt'=> $payment->transaction_id ?? '-',
                'phone'        => $payment->phone ?? '-',
                'amount'       => number_format($payment->amount, 2),
                'package'      => $payment->package->name ?? '-',
                'voucher_code' => $payment->mpesa_code ?? $payment->reference ?? $payment->transaction_id ?? '-',
                'created_at'   => $payment->created_at->format('Y-m-d H:i:s'),
                'expires_at'   => $subscriber && $subscriber->expires_at
                    ? $subscriber->expires_at->format('Y-m-d H:i:s')
                    : null,
                'status'       => $status,
                'is_new'       => $payment->created_at->diffInMinutes(now()) <= 5,
            ];
        });

        return response()->json(['data' => $data]);
    }
}
