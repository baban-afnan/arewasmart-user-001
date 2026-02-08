<!-- Financial Metrics Section -->
<div class="container-fluid mb-4">
    <div class="row g-3 mt-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-0 fw-bold text-dark">
                    <i class="ti ti-currency-naira me-2 text-primary"></i>Financial Overview
                </h4>
                <span class="badge bg-white text-dark shadow-sm border px-3 py-2 text-uppercase">
                    @if($isFiltered ?? false)
                        {{ $startDate->format('M d') }} - {{ $endDate->format('M d') }}
                    @else
                        {{ date('F Y') }}
                    @endif
                </span>
            </div>
        </div>

        <!-- Total Transactions (Debit) -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 fade-in-up" style="animation-delay: 0.1s;">
            <div class="financial-card shadow-sm h-100 p-3" style="background: var(--danger-gradient);">
                <div class="d-flex justify-content-between align-items-start position-relative z-1">
                    <div>
                        <p class="stats-label mb-1" style="color: rgba(255,255,255,0.8);">Total Transactions</p>
                        <h3 class="stats-value mb-0 text-white">₦{{ number_format($totalTransactionAmount ?? 0, 2) }}</h3>
                    </div>
                    <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center">
                        <i class="ti ti-arrow-up-circle fs-24 text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Funded (Credit) -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 fade-in-up" style="animation-delay: 0.2s;">
            <div class="financial-card shadow-sm h-100 p-3" style="background: var(--success-gradient);">
                <div class="d-flex justify-content-between align-items-start position-relative z-1">
                    <div>
                        <p class="stats-label mb-1" style="color: rgba(255,255,255,0.8);">Total Funded</p>
                        <h3 class="stats-value mb-0 text-white">₦{{ number_format($totalFundedAmount ?? 0, 2) }}</h3>
                    </div>
                    <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center">
                        <i class="ti ti-wallet fs-24 text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agency Requests -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 fade-in-up" style="animation-delay: 0.3s;">
            <div class="financial-card shadow-sm h-100 p-3" style="background: var(--primary-gradient);">
                <div class="d-flex justify-content-between align-items-start position-relative z-1">
                    <div>
                        <p class="stats-label mb-1" style="color: rgba(255,255,255,0.8);">Agency Requests</p>
                        <h3 class="stats-value mb-0 text-white">{{ number_format($totalAgencyRequests ?? 0) }}</h3>
                    </div>
                    <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center">
                        <i class="ti ti-building-bank fs-24 text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Referrals -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 fade-in-up" style="animation-delay: 0.4s;">
            <div class="financial-card shadow-sm h-100 p-3" style="background: var(--info-gradient);">
                <div class="d-flex justify-content-between align-items-start position-relative z-1">
                    <div>
                        <p class="stats-label mb-1" style="color: rgba(255,255,255,0.8);">Total Referrals</p>
                        <h3 class="stats-value mb-0 text-white">{{ number_format($totalReferrals ?? 0) }}</h3>
                    </div>
                    <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center">
                        <i class="ti ti-users fs-24 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        --success-gradient: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        --danger-gradient: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
        --info-gradient: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
    }

    .financial-card {
        border-radius: 16px;
        border: none;
        overflow: hidden;
        position: relative;
        transition: all 0.3s ease;
    }

    .financial-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }

    .financial-card::before {
        content: "";
        position: absolute;
        top: -20%;
        right: -10%;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        z-index: 0;
    }

    .stats-label {
        font-size: 0.82rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stats-value {
        font-size: 1.4rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .avatar-lg {
        width: 48px;
        height: 48px;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
    }
</style>
