<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\Tenant\SupportTicket;
use App\Models\Tenant\SupportTicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerSupportController extends Controller
{
    public function index(): View
    {
        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();

        $tickets = SupportTicket::where('customer_id', $subscriber->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('customer.support.index', compact('tickets'));
    }

    public function create(): View
    {
        return view('customer.support.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject'     => ['required', 'string', 'max:255'],
            'category'    => ['required', 'in:Billing,Connectivity,Speed,Other'],
            'priority'    => ['required', 'in:low,medium,high,urgent'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
        ]);

        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();

        SupportTicket::create([
            'customer_id' => $subscriber->id,
            'subject'     => $data['subject'],
            'category'    => $data['category'],
            'message'     => $data['description'],
            'status'      => 'open',
            'priority'    => $data['priority'],
        ]);

        return redirect()->route('customer.support.index')
            ->with('success', 'Your support ticket has been submitted. We will get back to you shortly.');
    }

    public function show(int $id): View
    {
        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();

        $ticket = SupportTicket::where('customer_id', $subscriber->id)
            ->with('replies')
            ->findOrFail($id);

        return view('customer.support.show', compact('ticket', 'subscriber'));
    }

    public function reply(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        /** @var Subscriber $subscriber */
        $subscriber = auth('customer')->user();

        $ticket = SupportTicket::where('customer_id', $subscriber->id)->findOrFail($id);

        SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $subscriber->id,
            'user_type' => 'customer',
            'message'   => $data['message'],
        ]);

        // Re-open the ticket if it was resolved/closed
        if (in_array($ticket->status, ['resolved', 'closed'])) {
            $ticket->update(['status' => 'open']);
        }

        return redirect()->route('customer.support.show', $id)
            ->with('success', 'Reply submitted.');
    }
}
