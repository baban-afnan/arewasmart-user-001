<x-app-layout>
    <title>Arewa Smart - Transaction Receipt</title>

    @push('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        /* Minimal custom styling for specialized receipt features */
        .receipt-card {
            max-width: 420px;
            width: 100%;
        }
        @media print {
            .no-print { display: none !important; }
            .receipt-card { 
                box-shadow: none !important; 
                border: 1px solid #dee2e6 !important;
                margin: 0 auto;
            }
        }
        .success-icon-bg {
            width: 80px;
            height: 80px;
            background-color: rgba(25, 135, 84, 0.1);
        }
    </style>
    @endpush

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
        $paid = session('paid') ?? $amount;
        $discount = $amount - $paid;
        $network = session('network') ?? 'N/A';
        $mobile = session('mobile') ?? 'N/A';
        $ref = session('ref') ?? 'N/A';
    @endphp

    <div class="container py-3 py-sm-5 d-flex flex-column align-items-center bg-light min-vh-100 px-2 px-md-0">
        <!-- Navigation -->
        <div class="receipt-card mb-3 no-print">
            <a href="{{ route('dashboard') }}" class="text-secondary text-decoration-none small d-flex align-items-center fw-semibold transition-all hover-translate-x">
                <i class="bi bi-arrow-left me-2"></i>Dashboard
            </a>
        </div>

        <!-- Receipt Slip -->
        <div class="card border-0 shadow-lg rounded-5 overflow-hidden receipt-card mx-auto" id="receipt-capture">
            <!-- Header Section -->
            <div class="card-header bg-white border-0 text-center pt-4 pt-sm-5 pb-3 pb-sm-4">
                <div class="success-icon-bg rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 70px; height: 70px;">
                    <i class="bi bi-check2-circle text-success fs-1"></i>
                </div>
                <h6 class="text-success fw-bold text-uppercase ls-wide mb-1" style="font-size: 0.7rem;">Transaction Successful</h6>
                <h4 class="fw-extrabold text-dark mb-1 fs-5 fs-sm-4">Arewa Smart Idea</h4>
                <p class="text-muted small mb-0 font-monospace" style="font-size: 0.75rem;">Ref: #{{ $ref }}</p>
            </div>

            <!-- Body Section -->
            <div class="card-body px-3 px-sm-4 pt-0">
                <div class="list-group list-group-flush border-top border-bottom py-3 mb-4">
                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-2">
                        <span class="text-secondary small fw-medium">Date & Time</span>
                        <span class="text-dark small fw-bold">{{ now()->format('d M Y, h:i A') }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-2">
                        <span class="text-secondary small fw-medium">Customer</span>
                        <span class="text-dark small fw-bold text-end">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-2">
                        <span class="text-secondary small fw-medium">Service Type</span>
                        <span class="text-dark small fw-bold">{{ $serviceName }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-2">
                        <span class="text-secondary small fw-medium">Network/Provider</span>
                        <span class="badge bg-primary-subtle text-primary border-0 rounded-pill px-3">{{ strtoupper($network) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-2">
                        <span class="text-secondary small fw-medium">Phone/Account</span>
                        <span class="text-dark small fw-bold">{{ $mobile }}</span>
                    </div>
                </div>

                @if(session('token'))
                <div class="bg-primary bg-opacity-10 rounded-4 p-3 text-center mb-4 border border-primary border-opacity-10 shadow-sm">
                    <span class="text-primary small fw-bold text-uppercase ls-wider d-block mb-1">Examination PIN / Token</span>
                    <div class="text-primary fs-12 fw-extrabold font-monospace letter-spacing-1">{{ session('token') }}</div>
                </div>
                @endif

                <!-- Amount Section -->
                <div class="bg-light rounded-4 p-3 p-sm-4 text-center border shadow-sm mb-4">
                    <span class="text-secondary small fw-semibold text-uppercase mb-1 d-block" style="font-size: 0.75rem;">Amount Charged</span>
                    <h2 class="fw-extrabold text-dark mb-0 fs-3 fs-sm-2">₦{{ number_format($paid, 2) }}</h2>
                    @if($discount > 0)
                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 mt-2 extra-small border border-success border-opacity-10">
                            <i class="bi bi-lightning-fill me-1"></i>You saved ₦{{ number_format($discount, 2) }}
                        </span>
                    @endif
                </div>

                <!-- Footer Section & Actions -->
                <div class="no-print pb-3 pb-sm-4">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <button onclick="window.print()" class="btn btn-outline-dark w-100 rounded-3 py-2 fw-bold extra-small shadow-sm d-flex align-items-center justify-content-center">
                                <i class="bi bi-printer me-2"></i>Print
                            </button>
                        </div>
                        <div class="col-6">
                            <button onclick="downloadAsPNG()" class="btn btn-outline-dark w-100 rounded-3 py-2 fw-bold extra-small shadow-sm d-flex align-items-center justify-content-center">
                                <i class="bi bi-camera me-2"></i>Snapshot
                            </button>
                        </div>
                        @if(session('token'))
                        <div class="col-12">
                            <button onclick="copyToClipboard('{{ session('token') }}')" class="btn btn-primary bg-gradient w-100 rounded-3 py-2 fw-bold extra-small shadow-sm mb-2 d-flex align-items-center justify-content-center border-0">
                                <i class="bi bi-clipboard-check me-2"></i>Copy PIN
                            </button>
                        </div>
                        @endif
                        <div class="col-12">
                            <a href="{{ url()->previous() }}" class="btn btn-primary w-100 rounded-3 py-2 fw-bold extra-small shadow-sm d-flex align-items-center justify-content-center text-nowrap">
                                <i class="bi bi-arrow-repeat me-2"></i>Buy Again
                            </a>
                        </div>
                    </div>
                    <p class="text-muted text-center extra-small mb-0 px-2" style="line-height: 1.4; font-size: 0.7rem;">
                        Transaction complete. Thanks for choosing <span class="fw-bold text-dark">Arewa Smart Idea</span>.
                    </p>
                </div>
            </div>
            
            <!-- Bottom Design Element -->
            <div class="bg-primary py-1 w-100"></div>
        </div>
    </div>

    @push('scripts')
    <script>
        // AI Voice Notification
        window.addEventListener('load', () => {
            if ('speechSynthesis' in window) {
                const message = "Your purchase is successful and delivered. Thank you for using Arewa Smart Idea.";
                const utterance = new SpeechSynthesisUtterance(message);
                utterance.rate = 1.0;
                utterance.pitch = 1.1;
                window.speechSynthesis.speak(utterance);
            }
        });

        // Download as PNG
        function downloadAsPNG() {
            const receipt = document.getElementById('receipt-capture');
            const noPrintElements = receipt.querySelectorAll('.no-print');
            
            // Hide elements that shouldn't be in the snapshot
            noPrintElements.forEach(el => el.style.display = 'none');
            
            html2canvas(receipt, {
                backgroundColor: '#f8f9fa',
                scale: 3, 
                logging: false,
                useCORS: true,
                onclone: (clonedDoc) => {
                    // Ensure the card looks perfect in the clone
                    const clonedReceipt = clonedDoc.getElementById('receipt-capture');
                    clonedReceipt.classList.remove('shadow-lg');
                    clonedReceipt.style.border = '1px solid #eee';
                }
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = `ArewaSmart_Receipt_{{ $ref }}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
                
                // Show elements back
                noPrintElements.forEach(el => el.style.display = 'block');
            }).catch(err => {
                console.error('Download failed:', err);
                noPrintElements.forEach(el => el.style.display = 'block');
                alert('Snapshot failed. Please try again or use the Print button.');
            });
        }

        function copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('PIN copied to clipboard successfully!');
                });
            } else {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    alert('PIN copied to clipboard successfully!');
                } catch (err) {
                    alert('Failed to copy PIN. Please copy it manually.');
                }
                document.body.removeChild(textArea);
            }
        }
    </script>
    <style>
        .ls-wide { letter-spacing: 0.05em; }
        .ls-wider { letter-spacing: 0.1em; }
        .fw-extrabold { font-weight: 800; }
        .fs-extra-small { font-size: 0.75rem; }
        .extra-small { font-size: 0.8rem; }
        .transition-all { transition: all 0.2s ease; }
        .hover-translate-x:hover { transform: translateX(-4px); }
        .letter-spacing-1 { letter-spacing: 1px; }
    </style>
    @endpush
</x-app-layout>
