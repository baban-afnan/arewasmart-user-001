<x-app-layout>
    <title>Arewa Smart - Support Dashboard</title>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">Support Dashboard</h3>
                        <p class="text-muted small mb-0">We are here to help you.</p>
                    </div>
                    <div class="col-sm-6 col-12 text-end">
                        <a href="{{ route('support.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus"></i> Open New Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <!-- WhatsApp Banner -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-success text-white shadow-sm border-0">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1"><i class="ti ti-brand-whatsapp me-2"></i>Need Immediate Assistance?</h5>
                                <p class="mb-0 small text-white-50">Chat with our support team on WhatsApp for quick resolution.</p>
                            </div>
                            <a href="https://wa.me/+2349110501995" target="_blank" class="btn btn-light text-success fw-bold">
                                Chat Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="mb-0 fw-bold text-primary">Your Support Tickets</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 align-middle">
                                    <thead class="bg-light text-primary">
                                        <tr>
                                            <th>Ticket ID</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Last Update</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tickets as $ticket)
                                            <tr>
                                                <td><span class="fw-bold text-dark">{{ $ticket->ticket_reference }}</span></td>
                                                <td>{{ Str::limit($ticket->subject, 50) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ match($ticket->status) {
                                                        'open' => 'success',
                                                        'answered' => 'primary',
                                                        'customer_reply' => 'warning',
                                                        'closed' => 'secondary',
                                                        default => 'info'
                                                    } }}">
                                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                                    </span>
                                                </td>
                                                <td>{{ $ticket->updated_at->diffForHumans() }}</td>
                                                <td>
                                                    <a href="{{ route('support.show', $ticket->ticket_reference) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="ti ti-message-circle"></i> View Chat
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5">
                                                    <i class="ti ti-ticket fs-1 d-block mb-2"></i>
                                                    No support tickets found. <br>
                                                    <a href="{{ route('support.create') }}" class="fw-bold text-primary">Create one now</a>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            {{ $tickets->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
