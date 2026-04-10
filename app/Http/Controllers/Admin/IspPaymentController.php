<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MpesaPayment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IspPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = MpesaPayment::with(['subscriber', 'package']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('connection_type', $request->type);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();
        return view('admin.isp.payments.index', compact('payments'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = MpesaPayment::with('package')->where('status', 'completed');

        if ($request->filled('from')) $query->whereDate('created_at', '>=', $request->from);
        if ($request->filled('to'))   $query->whereDate('created_at', '<=', $request->to);

        $payments = $query->orderBy('created_at', 'desc')->get();

        $filename = 'payments-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Receipt#', 'Phone', 'Amount (KES)', 'Package', 'Type', 'Status', 'Date']);
            foreach ($payments as $p) {
                fputcsv($handle, [
                    $p->mpesa_receipt_number,
                    $p->phone,
                    $p->amount,
                    $p->package?->name ?? 'N/A',
                    $p->connection_type,
                    $p->status,
                    $p->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
