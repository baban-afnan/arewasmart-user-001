<!-- Widget Info (Visible only on Desktop) -->
<div class="d-none d-md-flex justify-content-center my-4">
    <div class="container-fluid"> 
        <div class="row g-3 justify-content-center">

            <!-- Total Transaction Amount -->
            <div class="col-md-3 d-flex">
                <div class="card flex-fill shadow-sm rounded-4 border-0 cursor-pointer hover-card" data-bs-toggle="modal" data-bs-target="#filterTransactionModal">
                    <div class="card-body p-4 text-center">
                        <div class="avatar avatar-xl rounded-circle bg-primary-subtle text-primary mb-3 mx-auto d-flex align-items-center justify-content-center">
                            <i class="ti ti-currency-naira fs-24"></i>
                        </div>
                        <h6 class="fs-14 fw-semibold text-muted text-uppercase mb-1">Total Transactions</h6>
                        <h3 class="mb-0 fw-bold text-dark">
                            ₦{{ number_format($totalTransactionAmount ?? 0, 2) }}
                        </h3>
                        <div class="small text-muted mt-2">
                            <i class="ti ti-calendar me-1"></i> 
                            @if($isFiltered ?? false)
                                {{ $startDate->format('M d') }} - {{ $endDate->format('M d') }}
                            @else
                                Monthly
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Agency Request -->
            <div class="col-md-3 d-flex">
                <div class="card flex-fill shadow-sm rounded-4 border-0 cursor-pointer hover-card" data-bs-toggle="modal" data-bs-target="#filterAgencyModal">
                    <div class="card-body p-4 text-center">
                        <div class="avatar avatar-xl rounded-circle bg-info-subtle text-info mb-3 mx-auto d-flex align-items-center justify-content-center">
                            <i class="ti ti-building-bank fs-24"></i>
                        </div>
                        <h6 class="fs-14 fw-semibold text-muted text-uppercase mb-1">Agency Requests</h6>
                        <h3 class="mb-0 fw-bold text-dark">
                            {{ number_format($totalAgencyRequests ?? 0) }}
                        </h3>
                        <div class="small text-muted mt-2">
                            <i class="ti ti-calendar me-1"></i> 
                            @if($isFiltered ?? false)
                                {{ $startDate->format('M d') }} - {{ $endDate->format('M d') }}
                            @else
                                Monthly
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Funded Amount -->
            <div class="col-md-3 d-flex">
                <div class="card flex-fill shadow-sm rounded-4 border-0 cursor-pointer hover-card" data-bs-toggle="modal" data-bs-target="#filterFundedModal">
                    <div class="card-body p-4 text-center">
                        <div class="avatar avatar-xl rounded-circle bg-success-subtle text-success mb-3 mx-auto d-flex align-items-center justify-content-center">
                            <i class="ti ti-wallet fs-24"></i>
                        </div>
                        <h6 class="fs-14 fw-semibold text-muted text-uppercase mb-1">Total Funded</h6>
                        <h3 class="mb-0 fw-bold text-dark">
                            ₦{{ number_format($totalFundedAmount ?? 0, 2) }}
                        </h3>
                        <div class="small text-muted mt-2">
                            <i class="ti ti-calendar me-1"></i> 
                            @if($isFiltered ?? false)
                                {{ $startDate->format('M d') }} - {{ $endDate->format('M d') }}
                            @else
                                Monthly
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Referrals -->
            <div class="col-md-3 d-flex">
                <div class="card flex-fill shadow-sm rounded-4 border-0 cursor-pointer hover-card" data-bs-toggle="modal" data-bs-target="#filterReferralModal">
                    <div class="card-body p-4 text-center">
                        <div class="avatar avatar-xl rounded-circle bg-warning-subtle text-warning mb-3 mx-auto d-flex align-items-center justify-content-center">
                            <i class="ti ti-users fs-24"></i>
                        </div>
                        <h6 class="fs-14 fw-semibold text-muted text-uppercase mb-1">Total Referrals</h6>
                        <h3 class="mb-0 fw-bold text-dark">
                            {{ number_format($totalReferrals ?? 0) }}
                        </h3>
                        <div class="small text-muted mt-2">
                            <i class="ti ti-calendar me-1"></i> 
                            @if($isFiltered ?? false)
                                {{ $startDate->format('M d') }} - {{ $endDate->format('M d') }}
                            @else
                                All Time
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modals for Filtering -->

<!-- Transaction Filter Modal -->
<div class="modal fade" id="filterTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="ti ti-filter me-2"></i>Filter Transactions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('dashboard') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="ti ti-calendar"></i></span>
                            <input type="date" name="start_date" class="form-control" min="{{ Auth::user()->created_at->format('Y-m-d') }}" required>
                            <span class="input-group-text bg-light">to</span>
                            <input type="date" name="end_date" class="form-control" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-text text-muted">Select a date range starting from your registration date.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Agency Request Filter Modal -->
<div class="modal fade" id="filterAgencyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="ti ti-filter me-2"></i>Filter Agency Requests</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('dashboard') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="ti ti-calendar"></i></span>
                            <input type="date" name="start_date" class="form-control" min="{{ Auth::user()->created_at->format('Y-m-d') }}" required>
                            <span class="input-group-text bg-light">to</span>
                            <input type="date" name="end_date" class="form-control" max="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill px-4">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Funded Amount Filter Modal -->
<div class="modal fade" id="filterFundedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="ti ti-filter me-2"></i>Filter Funded Amount</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('dashboard') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="ti ti-calendar"></i></span>
                            <input type="date" name="start_date" class="form-control" min="{{ Auth::user()->created_at->format('Y-m-d') }}" required>
                            <span class="input-group-text bg-light">to</span>
                            <input type="date" name="end_date" class="form-control" max="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Referral Filter Modal -->
<div class="modal fade" id="filterReferralModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title fw-bold"><i class="ti ti-filter me-2"></i>Filter Referrals</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('dashboard') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="ti ti-calendar"></i></span>
                            <input type="date" name="start_date" class="form-control" min="{{ Auth::user()->created_at->format('Y-m-d') }}" required>
                            <span class="input-group-text bg-light">to</span>
                            <input type="date" name="end_date" class="form-control" max="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning text-white rounded-pill px-4">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .hover-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>
<!-- /Widget Info -->
