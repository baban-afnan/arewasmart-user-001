<x-app-layout>
    <title>Arewa Smart - Transaction Receipt</title>
    
    @push('styles')
    <style>
        .text-gray { color: #888; }
        .text-gray-9 { color: #333; }
        .text-title { font-size: 1.1rem; color: #333; }
        .invoice-card { border: none; box-shadow: 0 0 20px rgba(0,0,0,0.05); border-radius: 15px; }
        .invoice-header { background: #f8f9fa; border-radius: 15px 15px 0 0; padding: 20px; }
        .back-icon { color: #666; text-decoration: none; transition: 0.3s; }
        .back-icon:hover { color: var(--bs-primary); }
        .back-icon span { width: 30px; height: 30px; background: #eee; transition: 0.3s; }
        .back-icon:hover span { background: var(--bs-primary); color: #fff; }
    </style>
    @endpush

    <div class="page-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-10 mx-auto">
                    <a href="{{ route('dashboard') }}" class="back-icon d-flex align-items-center fs-12 fw-medium mb-3 d-inline-flex">
                        <span class="d-flex justify-content-center align-items-center rounded-circle me-2">
                            <i class="bi bi-arrow-left"></i>
                        </span>
                        Back to Dashboard
                    </a>
                    
                    <div class="card invoice-card">
                        <div class="card-body p-4">
                            <!-- Header -->
                            <div class="row justify-content-between align-items-center border-bottom pb-4 mb-4">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <img src="{{ asset('assets/img/logo/logo.png') }}" class="img-fluid" alt="logo" style="max-height: 50px;">
                                    </div>
                                    <p class="text-muted mb-0">Arewa Smart Idea</p>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-end">
                                        <h5 class="text-gray mb-1">Transaction Ref <span class="text-primary">#{{ session('ref') ?? 'N/A' }}</span></h5>
                                        <p class="mb-1 fw-medium">Date: <span class="text-dark">{{ now()->format('M d, Y h:i A') }}</span></p>
                                        <p class="fw-medium">Status: <span class="badge bg-success bg-opacity-10 text-success">Successful</span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- From / To -->
                            <div class="row border-bottom pb-4 mb-4">
                                <div class="col-md-5">
                                    <p class="text-dark mb-2 fw-bold text-uppercase small">From</p>
                                    <div>
                                        <h4 class="mb-1 fw-medium">Arewa Smart Idea</h4>
                                        <p class="mb-1 text-muted">Arewa Smart Payment</p>
                                        <p class="mb-1 text-muted">Email: <span class="text-dark">support@arewasmart.com.ng</span></p>
                                        <p class="mb-1 text-muted">Phone No: <span class="text-dark">09110501995</span></p>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <p class="text-dark mb-2 fw-bold text-uppercase small">To</p>
                                    <div>
                                        <h4 class="mb-1 fw-medium">{{ Auth::user()->first_name ?? 'Customer' }} {{ Auth::user()->surname ?? '' }}</h4>
                                        <p class="mb-1 text-muted">{{ Auth::user()->email ?? '' }}</p>
                                        <p class="mb-1 text-muted">Phone: <span class="text-dark">{{ session('mobile') ?? 'N/A' }}</span></p>
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <div class="mb-3">
                                        <div class="qr-placeholder bg-light d-inline-flex align-items-center justify-content-center rounded" style="width: 80px; height: 80px;">
                                            <i class="bi bi-qr-code fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Details -->
                            <div>
                                @php
                                    $serviceName = 'Service Purchase';
                                    if(session('token')) {
                                        $serviceName = 'Educational Pin';
                                    } elseif (session('network') && str_contains(session('network'), 'data')) {
                                        $serviceName = 'Data Purchase';
                                    } elseif (session('network')) {
                                        $serviceName = 'Airtime Purchase';
                                    }
                                    
                                    $amount = session('amount') ?? 0;
                                    $paid = session('paid') ?? $amount; // Default to amount if paid not set (e.g. for pins where no discount logic shown yet)
                                    $discount = $amount - $paid;
                                @endphp
                                <p class="fw-medium mb-3">Service: <span class="text-dark fw-bold">{{ $serviceName }}</span></p>
                                <div class="table-responsive mb-4">
                                    <table class="table table-borderless align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Description</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-end">Discount</th>
                                                <th class="text-end">Total Paid</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <h6 class="mb-1 fw-medium">{{ $serviceName }}</h6>
                                                    <small class="text-muted">
                                                        {{ strtoupper(session('network') ?? 'N/A') }} - {{ session('mobile') }}
                                                    </small>
                                                    @if(session('token'))
                                                        <div class="mt-2 p-2 bg-success bg-opacity-10 border border-success rounded d-inline-block">
                                                            <span class="text-success mb-1 fw-medium small text-uppercase me-2">PIN:</span>
                                                            <span class="mb-1 fw-medium text-dark fs-6 font-monospace">{{ session('token') }}</span>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="text-end">1</td>
                                                <td class="text-end">₦{{ number_format($amount, 2) }}</td>
                                                <td class="text-end text-success">-₦{{ number_format($discount, 2) }}</td>
                                                <td class="text-end mb-1 fw-medium">₦{{ number_format($paid, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Footer / Totals -->
                            <div class="row border-bottom pb-4 mb-4">
                                <div class="col-md-7">
                                    <div class="py-3">
                                        <div class="mb-3">
                                            <h6 class="mb-1 fw-bold">Terms & Conditions</h6>
                                            <p class="text-muted small">This transaction is subject to the terms and conditions of Arewa Smart Idea. No refunds for successful transactions.</p>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="mb-1 fw-bold">Note</h6>
                                            <p class="text-muted small">Thank you for your patronage. Please save this receipt for your records.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="bg-light p-3 rounded-3">
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                            <p class="mb-0 text-muted">Sub Total</p>
                                            <p class="text-dark fw-medium mb-0">₦{{ number_format($amount, 2) }}</p>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                            <p class="mb-0 text-muted">Discount</p>
                                            <p class="text-success fw-medium mb-0">-₦{{ number_format($discount, 2) }}</p>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center pt-1">
                                            <h5 class="mb-0 text-primary fw-bold">Total Paid</h5>
                                            <h5 class="mb-0 text-primary fw-bold">₦{{ number_format($paid, 2) }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sign / Actions -->
                            <div class="row justify-content-between align-items-center">
                                <div class="col-md-6">
                                    <p class="text-muted small mb-0">Payment Method: <span class="text-dark fw-medium">Wallet Balance</span></p>
                                    <p class="text-muted small">Transaction ID: <span class="text-dark fw-medium">{{ session('ref') }}</span></p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button onclick="window.print()" class="btn btn-outline-secondary">
                                            <i class="bi bi-printer me-1"></i> Print
                                        </button>
                                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                            <i class="bi bi-house-door me-1"></i> Dashboard
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-5 pt-3 border-top">
                                <p class="text-muted small mb-0">Arewa Smart Idea &copy; {{ date('Y') }}</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
