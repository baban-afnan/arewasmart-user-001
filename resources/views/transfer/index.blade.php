<x-app-layout>
    <title>Arewa Smart - {{ $title ?? 'Transfer to Smart User' }}</title>

    <div class="container-fluid px-0 px-lg-3">
        <div class="row justify-content-center py-3 py-lg-4 g-0 g-lg-3">
            <div class="col-12 col-xl-11 col-xxl-10">
                <div class="row g-3 g-lg-4 mt-0">
                    
                    {{-- Transfer Form Column --}}
                    <div class="col-12 col-lg-6 col-xl-6">
                        <div class="card shadow-lg border-0 rounded-4 h-100 mx-2 mx-lg-0">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-3 p-md-4" 
                                 style="background: primary">
                                <h5 class="mb-0 fw-bold fs-12 fs-md-5"><i class="bi bi-send me-2"></i>Transfer Funds</h5>
                                <span class="badge bg-white text-primary fw-bold px-2 px-md-3 py-2 rounded-pill">P2P</span>
                            </div>

                            <div class="card-body p-3 p-md-4">
                                <div class="text-center mb-3 mb-md-4">
                                    <div class="avatar-wrapper bg-primary bg-opacity-10 text-primary rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-wallet2 fs-15"></i>
                                    </div>
                                    <h6 class="fw-bold small">Send Money Instantly</h6>
                                    <p class="text-muted small">Enter the recipient's Wallet ID below.</p>
                                </div>

                                {{-- Flash Messages --}}
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm small py-2" role="alert">
                                        <i class="bi bi-check-circle-fill me-2"></i> {!! session('success') !!}
                                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm small py-2" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm small py-2" role="alert">
                                        <ul class="mb-0 text-start small list-unstyled">
                                            @foreach ($errors->all() as $error)
                                                <li><i class="bi bi-dot me-1"></i>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                {{-- Transfer Form --}}
                                <form id="transferForm" method="POST" action="{{ route('transfer.process') }}">
                                    @csrf

                                    {{-- Wallet ID --}}
                                    <div class="mb-3 mb-md-4 text-start">
                                        <label class="form-label fw-semibold text-dark small text-uppercase">Recipient Wallet ID</label>
                                        <div class="d-flex flex-column flex-sm-row gap-2">
                                            <div class="flex-grow-1">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge text-muted"></i></span>
                                                    <input type="text" id="wallet_id" name="wallet_id"
                                                           class="form-control border-start-0 ps-0"
                                                           placeholder="e.g. WAL-123456"
                                                           required>
                                                </div>
                                            </div>
                                            <button class="btn btn-primary px-3" type="button" id="verifyBtn" onclick="verifyUser()" style="white-space: nowrap;">
                                                Verify
                                            </button>
                                        </div>
                                        <div class="d-flex flex-column mt-2" style="min-height: 40px;">
                                            <small id="userNameDisplay" class="text-success fw-bold"></small>
                                            <small id="userErrorDisplay" class="text-danger fw-bold"></small>
                                        </div>
                                    </div>

                                    {{-- Amount --}}
                                    <div class="mb-3 mb-md-4 text-start">
                                        <label for="amount" class="form-label fw-semibold d-flex flex-column flex-sm-row justify-content-between">
                                            <span>Amount</span>
                                            <small class="text-muted">Balance: 
                                                <strong class="text-success">
                                                    ₦{{ number_format(auth()->user()->wallet->balance ?? 0, 2) }}
                                                </strong>
                                            </small>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-currency-naira text-muted"></i></span>
                                            <input type="number" id="amount" name="amount"
                                                   class="form-control border-start-0 ps-0"
                                                   placeholder="0.00"
                                                   min="0.01" step="0.01"
                                                   required>
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    <div class="mb-3 mb-md-4 text-start">
                                        <label class="form-label fw-semibold text-dark small text-uppercase">Description (Optional)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-card-text text-muted"></i></span>
                                            <textarea name="description" class="form-control border-start-0 ps-0" rows="2" placeholder="What is this for?"></textarea>
                                        </div>
                                    </div>

                                    {{-- Submit --}}
                                    <div class="d-grid mt-3 mt-md-4">
                                        <button type="button" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm py-2 py-md-3"
                                                id="proceedBtn" disabled
                                                data-bs-toggle="modal" data-bs-target="#pinModal">
                                            Proceed to Transfer <i class="bi bi-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Advert Column --}}
                    <div class="col-12 col-lg-6 col-xl-6">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-dark text-white hero-card h-100 mx-2 mx-lg-0">
                            <div class="card-body p-3 p-md-5 d-flex flex-column justify-content-center">
                                <h2 class="h3 h2-lg fw-bold mb-3 text-primary">Send Money Instantly</h2>
                                <p class="lead mb-4 opacity-75 small">
                                    Transfer funds to any Arewa Smart user with zero stress. 
                                    Fast, secure, and reliable peer-to-peer transfers at your fingertips.
                                </p>
                                
                                <div class="mt-auto">
                                    <div class="d-flex flex-column gap-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-25 rounded-circle p-2 me-3">
                                                <i class="bi bi-lightning-charge-fill text-primary"></i>
                                            </div>
                                            <span class="fw-semibold small">Instant Transfer</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-opacity-25 rounded-circle p-2 me-3">
                                                <i class="bi bi-shield-check text-success"></i>
                                            </div>
                                            <span class="fw-semibold small">Secure & Encrypted</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info bg-opacity-25 rounded-circle p-2 me-3">
                                                <i class="bi bi-clock-history text-info"></i>
                                            </div>
                                            <span class="fw-semibold small">24/7 Availability</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-opacity-25 rounded-circle p-2 me-3">
                                                <i class="bi bi-currency-exchange text-warning"></i>
                                            </div>
                                            <span class="fw-semibold small">No Hidden Fees</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 d-none d-lg-block">
                                    <div class="opacity-25 text-end">
                                        <i class="bi bi-send display-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- PIN Confirmation Modal --}}
    <div class="modal fade" id="pinModal" tabindex="-1" aria-labelledby="pinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mx-2 mx-lg-auto">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-semibold small" id="pinModalLabel">
                        <i class="bi bi-shield-lock-fill me-2"></i> Enter Transaction PIN
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center py-3 py-md-4">
                    <p class="text-muted mb-3 small">
                        For your security, please confirm your <strong>5-digit transaction PIN</strong>.
                    </p>

                    <div class="d-flex justify-content-center px-3">
                        <input 
                            type="password" 
                            name="pin" 
                            id="pinInput" 
                            class="form-control text-center fw-bold fs-3 py-3 border-2 border-primary rounded-pill shadow-sm w-100" 
                            maxlength="5" 
                            inputmode="numeric" 
                            placeholder="•••••"
                            required
                            style="letter-spacing: 8px; font-family: 'Courier New', monospace;"
                        >
                    </div>

                    <small id="pinError" class="text-danger d-none mt-3 d-block fw-semibold small">
                        Incorrect PIN. Please try again.
                    </small>
                </div>

                <div class="modal-footer border-0 justify-content-center pb-3 pb-md-4 gap-2">
                    <button type="button" class="btn btn-light px-3 px-md-4 rounded-pill small" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button" id="confirmPinBtn" class="btn btn-primary px-3 px-md-4 rounded-pill fw-semibold small">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="pinLoader" role="status" aria-hidden="true"></span>
                        <span id="confirmPinText">Confirm</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function verifyUser() {
            const walletId = document.getElementById('wallet_id').value;
            const userNameDisplay = document.getElementById('userNameDisplay');
            const userErrorDisplay = document.getElementById('userErrorDisplay');
            const proceedBtn = document.getElementById('proceedBtn');
            const verifyBtn = document.getElementById('verifyBtn');

            if (!walletId) {
                userErrorDisplay.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> Please enter a Wallet ID.';
                userNameDisplay.innerHTML = "";
                return;
            }

            // UI Feedback
            userNameDisplay.innerHTML = "";
            userErrorDisplay.innerHTML = "";
            verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...';
            verifyBtn.disabled = true;

            fetch("{{ route('transfer.verify') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ wallet_id: walletId })
            })
            .then(response => response.json())
            .then(data => {
                verifyBtn.innerHTML = 'Verify';
                verifyBtn.disabled = false;

                if (data.success) {
                    userNameDisplay.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + data.user_name;
                    userErrorDisplay.innerHTML = "";
                    proceedBtn.disabled = false;
                } else {
                    userErrorDisplay.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> User not found.';
                    userNameDisplay.innerHTML = "";
                    proceedBtn.disabled = true;
                }
            })
            .catch(err => {
                console.error("Verification failed:", err);
                verifyBtn.innerHTML = 'Verify';
                verifyBtn.disabled = false;
                userErrorDisplay.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Verification failed.';
                userNameDisplay.innerHTML = "";
                proceedBtn.disabled = true;
            });
        }

        document.getElementById('confirmPinBtn').addEventListener('click', function() {
            const confirmBtn = this;
            const loader = document.getElementById('pinLoader');
            const confirmText = document.getElementById('confirmPinText');
            const pinError = document.getElementById('pinError');
            const pin = document.getElementById('pinInput').value.trim();

            if (!pin || pin.length !== 5) {
                pinError.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> Please enter a valid 5-digit PIN.';
                pinError.classList.remove('d-none');
                return;
            }

            confirmBtn.disabled = true;
            loader.classList.remove('d-none');
            confirmText.textContent = "Verifying...";
            pinError.classList.add('d-none');

            // Verify PIN via AJAX first
            fetch("{{ route('verify.pin') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ pin })
            })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    // Append PIN to the form and submit
                    const form = document.getElementById('transferForm');
                    const pinInput = document.createElement('input');
                    pinInput.type = 'hidden';
                    pinInput.name = 'pin';
                    pinInput.value = pin;
                    form.appendChild(pinInput);
                    
                    form.submit();
                } else {
                    pinError.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Incorrect PIN. Please try again.';
                    pinError.classList.remove('d-none');
                    confirmBtn.disabled = false;
                    loader.classList.add('d-none');
                    confirmText.textContent = "Confirm";
                    
                    // Clear input
                    document.getElementById('pinInput').value = '';
                    document.getElementById('pinInput').focus();
                }
            })
            .catch(err => {
                console.error("PIN check failed:", err);
                pinError.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Network error. Please try again.';
                pinError.classList.remove('d-none');
                confirmBtn.disabled = false;
                loader.classList.add('d-none');
                confirmText.textContent = "Confirm";
            });
        });

        // Auto-focus PIN input when modal opens
        document.getElementById('pinModal').addEventListener('shown.bs.modal', function () {
            document.getElementById('pinInput').focus();
        });

        // Clear PIN input when modal closes
        document.getElementById('pinModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('pinInput').value = '';
            document.getElementById('pinError').classList.add('d-none');
        });
    </script>
</x-app-layout>