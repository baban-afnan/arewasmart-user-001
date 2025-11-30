<x-app-layout>
   <title>Arewa Smart - {{ $title ?? 'Wallet Funding' }}</title>
  <div class="container mt-4">
    <div class="row g-4">

      <!-- Automatic Wallet Funding -->
      <div class="col-xl-6 col-lg-6">
        <div class="card shadow-sm border-0 rounded-3">
          <div class="card-header bg-gradient text-white bg-primary rounded-top">
            <h4 class="mb-0">
              <i class="bi bi-bank me-2"></i>Automatic Wallet Funding
            </h4>
          </div>
          <div class="card-body">

            <p class="text-muted">
              Fund your Arewa Smart wallet easily using your assigned virtual account.
              Once you make payment, your wallet will be credited instantly after confirmation.
            </p>

            @if (session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
              <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @php
              $ws = \App\Models\Webservice::where('name', 'wallet funding')
                    ->where('status', 'active')
                    ->first();
            @endphp

            @if($ws)
              @if($virtualAccount)
              <form class="row g-3">
                <div class="col-12">
                <label class="form-label">Account Name</label>
                <input type="text" class="form-control" readonly value="{{ $virtualAccount->accountName }}">
                </div>
                <div class="col-12">
                <label class="form-label">Account Number</label>
                <input type="text" class="form-control" readonly value="{{ $virtualAccount->accountNo }}">
                </div>
                <div class="col-12">
                <label class="form-label">Bank Name</label>
                <input type="text" class="form-control" readonly value="{{ $virtualAccount->bankName }}">
                </div>
              </form>
              @else
              <div class="text-center mt-3">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#virtualAccountModal">
                <i class="bi bi-plus-circle"></i> Create Virtual Account
                </a>
              </div>
              @endif
            @else
              {{-- Wallet Funding service is disabled — hide this section --}}
            @endif
            </div>
          </div>
          </div>

      <!-- Referral Bonus Section -->
      <div class="col-xl-6 col-lg-6">
        <div class="card shadow-sm border-0 rounded-3">
          <div class="card-header bg-gradient bg-primary text-white rounded-top d-flex align-items-center justify-content-between">
            <h4 class="mb-0">
              <i class="bi bi-gift me-2"></i>Referral Bonus
            </h4>
            <span class="badge bg-light text-primary fw-bold">Active</span>
          </div>
          <div class="card-body">

            <p class="text-muted">
              Earn rewards when you invite your friends to Arewa Smart.  
              Share your unique referral link below.
            </p>

            <!-- Referral Code -->
            <div class="input-group mb-3">
              <input type="text" class="form-control" id="referralCode" readonly
                value="https://arewasmart.com.ng/register?ref={{ auth()->user()->referral_code }}">
              <button class="btn btn-outline-primary" type="button" onclick="copyReferral()">
                <i class="bi bi-clipboard-check"></i> Copy
              </button>
            </div>
            <small id="copyMsg" class="text-success d-none"><i class="bi bi-check-circle"></i> Copied!</small>

            <!-- Share Buttons -->
            <div class="d-flex flex-wrap gap-2 mt-3">
              <button class="btn btn-success" onclick="shareWhatsApp()">
                <i class="bi bi-whatsapp"></i> WhatsApp
              </button>
              <button class="btn btn-primary" onclick="shareFacebook()">
                <i class="bi bi-facebook"></i> Facebook
              </button>
              <button class="btn btn-info text-white" onclick="shareTwitter()">
                <i class="bi bi-twitter-x"></i> Twitter
              </button>
              <button class="btn btn-dark" onclick="nativeShare()">
                <i class="bi bi-share-fill"></i> More
              </button>
            </div>

            <!-- Bonus Details -->
            <div class="mt-4 text-center">
              <div class="card bg-light border-0 shadow-sm">
                <div class="card-body">
                  <span class="badge bg-success px-3 py-2 mb-2">
                    <i class="bi bi-wallet2"></i> Bonus Balance
                  </span>
                  <h3 class="fw-bold text-primary mb-1">₦{{ number_format($walletData['bonus'], 2) }}</h3>
                  <p class="text-muted small">Total referral earnings</p>

                  @if($walletData['bonus'] > 0)
                  <form action="{{ route('wallet.claimBonus') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-cash-coin"></i> Claim Bonus
                    </button>
                  </form>
                  @endif
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Virtual Account Modal -->
  <div class="modal fade" id="virtualAccountModal" tabindex="-1" aria-labelledby="virtualAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content shadow rounded-4">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Create Virtual Account</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <form method="POST" action="{{ route('virtual.account.create') }}" class="row g-4">
            @csrf
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control" name="name" value="{{ auth()->user()->first_name.' '.auth()->user()->last_name.' '.auth()->user()->middle_name }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input type="text" class="form-control" name="phone" value="{{ auth()->user()->phone_no }}" required>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="confirmCheck" required>
                <label class="form-check-label" for="confirmCheck">
                  I confirm that the above details are accurate and consent to create a virtual account.
                </label>
              </div>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-send-fill me-2"></i> Submit Request
              </button>
            </div>
          </form>
        </div>

        <div class="modal-footer bg-light">
          <small class="text-muted">Your virtual account will be generated instantly and linked to your wallet.</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Copy & Share Scripts -->
  <script>
    function copyReferral() {
      const copyText = document.getElementById("referralCode");
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      navigator.clipboard.writeText(copyText.value);
      const msg = document.getElementById("copyMsg");
      msg.classList.remove("d-none");
      setTimeout(() => msg.classList.add("d-none"), 2000);
    }

    function shareWhatsApp() {
      const link = document.getElementById("referralCode").value;
      const text = encodeURIComponent("Join me on Arewa Smart and earn bonuses! " + link);
      window.open("https://api.whatsapp.com/send?text=" + text, "_blank");
    }

    function shareFacebook() {
      const url = encodeURIComponent(document.getElementById("referralCode").value);
      window.open("https://www.facebook.com/sharer/sharer.php?u=" + url, "_blank");
    }

    function shareTwitter() {
      const link = document.getElementById("referralCode").value;
      const text = encodeURIComponent("Join me on Arewa Smart! Earn bonuses when you sign up: " + link);
      window.open("https://twitter.com/intent/tweet?text=" + text, "_blank");
    }

    function nativeShare() {
      const link = document.getElementById("referralCode").value;
      if (navigator.share) {
        navigator.share({
          title: 'Arewa Smart Referral',
          text: 'Join me and earn bonuses!',
          url: link
        });
      } else {
        alert("Sharing not supported on this browser.");
      }
    }
  </script>

  <style>
    .bg-gradient {
      background: linear-gradient(90deg, #005baa, #00b894);
    }
    .hover-shadow:hover {
      box-shadow: 0 0.9rem 1.6rem rgba(0, 0, 0, 0.15);
      transform: translateY(-4px);
      transition: all 0.3s ease-in-out;
    }
  </style>
</x-app-layout>
