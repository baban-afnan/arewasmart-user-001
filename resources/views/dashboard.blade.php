<x-app-layout>
     <title>Arewa Smart - {{ $title ?? 'Dashboard' }}</title>
    <!-- Add space between header and content -->
    <div class="mt-4">
    <!-- User + Wallet Section -->
     <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body user-wallet-wrap">
            <div class="d-flex align-items-center gap-3 flex-wrap">
            <!-- User Image -->
            <div class="avatar flex-shrink-0">
                <img src="{{ Auth::user()->photo ?? asset('assets/img/profiles/avatar-31.jpg') }}"
                     class="rounded-circle border border-3 border-primary shadow-sm user-avatar"
                     alt="User Avatar">
            </div>

            <!-- Welcome Message -->
            <div class="me-auto">
                <h4 class="fw-semibold text-dark mb-1 welcome-text">
                    Welcome back, {{ Auth::user()->first_name . ' ' . Auth::user()->surname ?? 'User' }} 👋
                </h4>
                <small class="text-danger">Your Wallet Id is {{ $wallet->wallet_number ?? 'N/A' }}</small>
            </div>

            <!-- Wallet Info -->
            <div class="d-flex align-items-center gap-2 ms-2">
                <span class="fw-medium text-muted small mb-0">Balance:</span>

                <h5 id="wallet-balance" class="mb-0 text-success fw-bold balance-text">
                    ₦{{ number_format($wallet->balance ?? 0, 2) }}
                </h5>

                <!-- Toggle Balance Button -->
                <button id="toggle-balance" class="btn btn-sm btn-outline-secondary ms-1 p-1 toggle-btn"
                        aria-pressed="true" title="Toggle balance visibility">
                    <i class="fas fa-eye eye-icon" aria-hidden="true"></i>
                </button>

                <!-- Wallet Icon -->
                <a href="{{ route('wallet') }}" class="btn btn-light ms-1 border-0 p-0 wallet-btn"
                   title="View Wallet Details" aria-label="View wallet">
                    <i class="fas fa-wallet wallet-icon text-primary"></i>
                </a>
            </div>
        </div>
    </div>
</div>

        <!-- Alerts (kept as in project) -->
        @include('pages.alart')

        <!-- Dashboard widgets and sections -->
        <div class="row mt-3">
            @include('pages.dashboard.wedget')
        </div>

        <div class="row">
            @include('pages.dashboard.services')
        </div>

        <div class="row">
            @include('pages.dashboard.trans')
        </div>

        <div class="row">
            @include('pages.dashboard.kyc')
        </div>
    </div>
</x-app-layout>
