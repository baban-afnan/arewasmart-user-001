<x-app-layout>
    <title>Arewa Smart - {{ $title ?? 'Referral Dashboard' }}</title>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h3 class="fw-bold text-dark mb-0">
                        <i class="ti ti-user-plus me-2 text-primary"></i>Referral System
                    </h3>
                    <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill">
                        Earn Rewards
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden stats-card" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                    <div class="card-body p-4 text-white position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fs-14 fw-medium opacity-75 text-uppercase">Pending Milestone</span>
                            <div class="avatar bg-opacity-20 rounded-3 p-2">
                                <i class="ti ti-users fs-40"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold mb-1">{{ number_format($pendingCount) }}</h2>
                        <p class="mb-0 fs-12 opacity-75">Users working towards 5 transactions</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden stats-card" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                    <div class="card-body p-4 text-white position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fs-14 fw-medium opacity-75 text-uppercase">Total Earnings</span>
                            <div class="avatar bg-opacity-20 rounded-3 p-2">
                                <i class="ti ti-currency-naira fs-40"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold mb-1">₦{{ number_format($totalEarnings, 2) }}</h2>
                        <p class="mb-0 fs-12 opacity-75">Accumulated referral bonuses</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden stats-card" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
                    <div class="card-body p-4 text-white position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fs-14 fw-medium opacity-75 text-uppercase">Claimable Bonus</span>
                            <div class="avatar bg-opacity-20 rounded-3 p-2">
                                <i class="ti ti-wallet fs-40"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold mb-1">₦{{ number_format($wallet->bonus ?? 0, 2) }}</h2>
                        @if(($wallet->bonus ?? 0) > 0)
                            <form action="{{ route('wallet.claimBonus') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-light text-primary fw-bold mt-2">
                                    Claim Now
                                </button>
                            </form>
                        @else
                            <p class="mb-0 fs-12 opacity-75">No pending bonus to claim</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Sharing Card -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Spread the word</h5>
                        <p class="text-muted mb-4 small">
                            Share your referral link with friends and business owners. When they register, you earn a bonus instantly!
                        </p>
                        
                        <div class="referral-box bg-light rounded-4 p-4 border border-dashed mb-4">
                            <label class="form-label fw-semibold text-dark fs-12 text-uppercase mb-2">Your Invitation Link</label>
                            <div class="input-group">
                                <input type="text" class="form-control border-0 bg-transparent fw-bold" id="referralLink" readonly value="{{ $referralLink }}">
                                <button class="btn btn-primary rounded-3 px-3" onclick="copyReferral()">
                                    <i class="ti ti-copy me-1"></i> Copy
                                </button>
                            </div>
                            <div id="copySuccess" class="alert alert-success mt-3 py-2 px-3 fs-12 d-none">
                                <i class="ti ti-check me-1"></i> Link copied to clipboard!
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 text-muted fs-12 text-uppercase">Fast Share</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-soft-success flex-grow-1" onclick="shareWhatsApp()">
                                <i class="ti ti-brand-whatsapp me-2"></i> WhatsApp
                            </button>
                            <button class="btn btn-soft-primary flex-grow-1" onclick="shareFacebook()">
                                <i class="ti ti-brand-facebook me-2"></i> Facebook
                            </button>
                            <button class="btn btn-soft-info flex-grow-1" onclick="shareTwitter()">
                                <i class="ti ti-brand-twitter me-2"></i> Twitter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How it works -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">How it works?</h5>
                        
                        <div class="d-flex align-items-start mb-4">
                            <div class="step-number bg-white text-primary rounded-circle me-3 flex-shrink-0 d-flex align-items-center justify-content-center fw-bold">1</div>
                            <div>
                                <h6 class="fw-bold mb-1">Send Invitation</h6>
                                <p class="mb-0 fs-13 opacity-75">Send your referral link to your friends and associates.</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-4">
                            <div class="step-number bg-white text-primary rounded-circle me-3 flex-shrink-0 d-flex align-items-center justify-content-center fw-bold">2</div>
                            <div>
                                <h6 class="fw-bold mb-1">Registration</h6>
                                <p class="mb-0 fs-13 opacity-75">They sign up as a user or agent using your specific link.</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start">
                            <div class="step-number bg-white text-primary rounded-circle me-3 flex-shrink-0 d-flex align-items-center justify-content-center fw-bold">3</div>
                            <div>
                                <h6 class="fw-bold mb-1">Get Reward</h6>
                                <p class="mb-0 fs-13 opacity-75">Once their registration is successful, you get credited with your bonus!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold mb-0">Active Referrals (In-Progress)</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle custom-table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 rounded-start">Date</th>
                                        <th class="border-0">Referred User</th>
                                        <th class="border-0">Amount</th>
                                        <th class="border-0 rounded-end text-end">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bonusHistory as $history)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium text-dark">{{ $history->created_at->format('M d, Y') }}</span>
                                                    <small class="text-muted">{{ $history->created_at->format('h:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-soft-primary text-primary rounded-circle me-3">
                                                        <i class="ti ti-user"></i>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold text-dark">
                                                            {{ $history->referredUser->first_name ?? 'New' }} 
                                                            {{ $history->referredUser->middle_name ?? '' }} 
                                                            {{ $history->referredUser->last_name ?? 'User' }}
                                                        </span>
                                                        <small class="text-muted">
                                                            <i class="ti ti-activity me-1"></i>
                                                            {{ $history->referred_user_transaction_count }}/5 Transactions
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold {{ $history->status == 'success' ? 'text-success' : 'text-warning' }}">
                                                    {{ $history->status == 'success' ? '+' : '' }} ₦{{ number_format($history->amount, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                @if($history->status == 'success')
                                                    <span class="badge bg-soft-success text-success px-2 py-1 rounded-pill fs-11">
                                                        Credited
                                                    </span>
                                                @else
                                                    <span class="badge bg-soft-warning text-warning px-2 py-1 rounded-pill fs-11">
                                                        Pending
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="ti ti-package fs-1 opacity-25 d-block mb-3"></i>
                                                No referral history found yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .stats-card {
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .bg-soft-primary { background-color: rgba(78, 115, 223, 0.1); }
        .bg-soft-success { background-color: rgba(28, 200, 138, 0.1); }
        .bg-soft-info { background-color: rgba(54, 185, 204, 0.1); }
        
        .btn-soft-success {
            background-color: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
            border: none;
        }
        .btn-soft-success:hover {
            background-color: #1cc88a;
            color: white;
        }
        
        .btn-soft-primary {
            background-color: rgba(78, 115, 223, 0.1);
            color: #4e73df;
            border: none;
        }
        .btn-soft-primary:hover {
            background-color: #4e73df;
            color: white;
        }

        .btn-soft-info {
            background-color: rgba(54, 185, 204, 0.1);
            color: #36b9cc;
            border: none;
        }
        .btn-soft-info:hover {
            background-color: #36b9cc;
            color: white;
        }

        .step-number {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }

        .custom-table thead th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 15px;
        }
        .custom-table tbody td {
            padding: 15px;
            font-size: 13px;
        }
        .avatar-sm {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function copyReferral() {
            const copyText = document.getElementById("referralLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            
            const successMsg = document.getElementById("copySuccess");
            successMsg.classList.remove("d-none");
            setTimeout(() => {
                successMsg.classList.add("d-none");
            }, 3000);
        }

        function shareWhatsApp() {
            const link = document.getElementById("referralLink").value;
            const text = encodeURIComponent("Join me on Arewa Smart and earn bonuses! " + link);
            window.open("https://api.whatsapp.com/send?text=" + text, "_blank");
        }

        function shareFacebook() {
            const url = encodeURIComponent(document.getElementById("referralLink").value);
            window.open("https://www.facebook.com/sharer/sharer.php?u=" + url, "_blank");
        }

        function shareTwitter() {
            const link = document.getElementById("referralLink").value;
            const text = encodeURIComponent("Join me on Arewa Smart! Earn bonuses when you sign up: " + link);
            window.open("https://twitter.com/intent/tweet?text=" + text, "_blank");
        }
    </script>
    @endpush
</x-app-layout>
