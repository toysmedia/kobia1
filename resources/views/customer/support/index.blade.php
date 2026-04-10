@extends('customer.layouts.app')

@section('title', 'Support Tickets')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fa-solid fa-headset text-primary me-2"></i>Support Tickets</h4>
    <a href="{{ route('customer.support.create') }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus me-1"></i>New Ticket
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td class="text-muted small">#{{ $ticket->id }}</td>
                    <td>
                        <a href="{{ route('customer.support.show', $ticket->id) }}" class="text-decoration-none fw-semibold">
                            {{ Str::limit($ticket->subject, 50) }}
                        </a>
                    </td>
                    <td>{{ $ticket->category ?? '—' }}</td>
                    <td>
                        @php
                            $pBadge = match($ticket->priority) {
                                'urgent' => 'bg-danger',
                                'high'   => 'bg-warning text-dark',
                                'medium' => 'bg-info',
                                default  => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $pBadge }} text-capitalize">{{ $ticket->priority }}</span>
                    </td>
                    <td>
                        @php
                            $sBadge = match($ticket->status) {
                                'open'        => 'bg-primary',
                                'in_progress' => 'bg-warning text-dark',
                                'resolved'    => 'bg-success',
                                'closed'      => 'bg-secondary',
                                default       => 'bg-light text-dark',
                            };
                        @endphp
                        <span class="badge {{ $sBadge }} text-capitalize">{{ str_replace('_', ' ', $ticket->status) }}</span>
                    </td>
                    <td class="small text-muted">{{ $ticket->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('customer.support.show', $ticket->id) }}"
                           class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-inbox fa-2x d-block mb-2"></i>
                        No tickets yet. <a href="{{ route('customer.support.create') }}">Create one</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tickets->hasPages())
    <div class="card-footer">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
