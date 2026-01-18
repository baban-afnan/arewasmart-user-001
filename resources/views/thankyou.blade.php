<x-app-layout>
    <title>Arewa Smart - Transaction Receipt</title>

    @push('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --receipt-bg: #ffffff;
            --receipt-text: #2d3436;
            --receipt-muted: #636e72;
            --receipt-accent: #0984e3;
            --receipt-success: #00b894;
            --receipt-width: 380px; /* Reduced width for better slip appearance */
        }

        .receipt-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 30px 15px;
            background: #f1f2f6;
            min-height: calc(100vh - 80px);
        }

        .receipt-wrapper {
            width: 100%;
            max-width: var(--receipt-width);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .receipt-slip {
            width: 100%;
            background: var(--receipt-bg);
            position: relative;
            padding: 35px 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        }

        /* Jagged Edge Effect */
        .receipt-slip::before,
        .receipt-slip::after {
            content: "";
            position: absolute;
            left: 0;
            width: 100%;
            height: 10px;
            background-repeat: repeat-x;
            background-size: 16px 10px;
            z-index: 10;
        }

        .receipt-slip::before {
            top: 0;
            background-image: radial-gradient(circle at 8px -4px, transparent 10px, var(--receipt-bg) 11px);
            transform: scaleY(-1);
        }

        .receipt-slip::after {
            bottom: 0;
            background-image: radial-gradient(circle at 8px -4px, transparent 10px, var(--receipt-bg) 11px);
        }

        /* Success Animation */
        .success-checkmark {
            width: 70px;
            height: 70px;
            margin: 0 auto 15px;
            background: rgba(0, 184, 148, 0.1);
            color: var(--receipt-success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.3s both;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 1px dashed #dfe6e9;
            padding-bottom: 20px;
        }

        .logo-img {
            max-height: 40px;
            margin-bottom: 12px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
        }

        .status-badge {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 800;
            color: var(--receipt-success);
            margin-bottom: 5px;
            display: block;
        }

        .receipt-body {
            margin-bottom: 25px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }

        .info-label {
            color: var(--receipt-muted);
            font-weight: 400;
        }

        .info-value {
            color: var(--receipt-text);
            font-weight: 600;
            text-align: right;
            max-width: 65%;
        }

        .amount-section {
            background: #fcfcfc;
            border-radius: 10px;
            padding: 18px;
            margin: 20px 0;
            text-align: center;
            border: 1px solid #f0f0f0;
        }

        .amount-label {
            display: block;
            font-size: 0.8rem;
            color: var(--receipt-muted);
            margin-bottom: 4px;
        }

        .amount-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: #2d3436;
            display: block;
        }

        .discount-tag {
            font-size: 0.75rem;
            color: var(--receipt-success);
            font-weight: 600;
            display: inline-block;
            margin-top: 5px;
            background: rgba(0, 184, 148, 0.08);
            padding: 2px 8px;
            border-radius: 20px;
        }

        .token-card {
            background: linear-gradient(135deg, #0984e3, #00cec9);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
            box-shadow: 0 8px 15px rgba(9, 132, 227, 0.15);
        }

        .token-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
            display: block;
            opacity: 0.9;
        }

        .token-value {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 2px;
            font-family: 'Courier New', Courier, monospace;
        }

        .receipt-footer {
            border-top: 1px dashed #dfe6e9;
            padding-top: 20px;
            text-align: center;
        }

        .ty-msg {
            font-style: italic;
            color: var(--receipt-muted);
            font-size: 0.8rem;
            margin-bottom: 20px;
        }

        .action-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-receipt {
            flex: 1;
            min-width: calc(50% - 5px);
            padding: 10px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-print {
            background: #fff;
            border: 1.5px solid #dfe6e9;
            color: var(--receipt-text);
        }

        .btn-print:hover {
            background: #f8f9fa;
            border-color: #ced6e0;
        }

        .btn-download {
            background: #f1f2f6;
            color: var(--receipt-text);
        }

        .btn-download:hover {
            background: #e1e2e6;
        }

        .btn-home {
            background: var(--receipt-accent);
            color: white;
            flex-basis: 100%;
        }

        .btn-home:hover {
            background: #0873c4;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(9, 132, 227, 0.2);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        @media screen and (max-width: 480px) {
            :root { --receipt-width: 100%; }
            .receipt-container { padding: 20px 10px; }
            .receipt-slip { padding: 30px 20px; }
        }

        @media print {
            body * { visibility: hidden; }
            #receipt-capture, #receipt-capture * { visibility: visible; }
            #receipt-capture {
                position: absolute;
                left: 50%;
                top: 0;
                transform: translateX(-50%);
                width: 380px !important;
                box-shadow: none !important;
                border: 1px solid #f0f0f0;
            }
            .action-btns, .back-nav, .extra-info { display: none !important; }
            .receipt-slip::before, .receipt-slip::after { display: block !important; }
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

    <div class="receipt-container">
        <div class="receipt-wrapper">
            
            <div class="back-nav mb-3 w-100">
                <a href="{{ route('dashboard') }}" class="text-muted text-decoration-none small d-flex align-items-center">
                    <i class="bi bi-chevron-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <div class="receipt-slip" id="receipt-capture">
                <div class="receipt-header">
                    <div class="success-checkmark">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <span class="status-badge">Transaction Successful</span>
                    <h5 class="mb-0 fw-bold">Arewa Smart Idea</h5>
                    <p class="text-muted extra-small mb-0">Receipt: #{{ $ref }}</p>
                </div>

                <div class="receipt-body">
                    <div class="info-row">
                        <span class="info-label">Date</span>
                        <span class="info-value">{{ now()->format('d/m/Y • h:i A') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Customer</span>
                        <span class="info-value">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Service</span>
                        <span class="info-value">{{ $serviceName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Category</span>
                        <span class="info-value">{{ strtoupper($network) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Number</span>
                        <span class="info-value">{{ $mobile }}</span>
                    </div>

                      @if(session('token'))
                    <div class="info-row">
                        <span class="info-label">Exam PIN / Token</span>
                        <div class="info-value">{{ session('token') }}</div>
                    </div>
                    @endif
                </div>

                    <div class="amount-section">
                        <span class="amount-label">Amount Paid</span>
                        <span class="amount-value">₦{{ number_format($paid, 2) }}</span>
                        @if($discount > 0)
                            <span class="discount-tag">Saved ₦{{ number_format($discount, 2) }}</span>
                        @endif
                    </div>


                <div class="receipt-footer">
                    <p class="ty-msg">Thanks for your patronage!</p>
                    
                    <div class="action-btns">
                        <button onclick="window.print()" class="btn-receipt btn-print">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <button onclick="downloadAsPNG()" class="btn-receipt btn-download">
                            <i class="bi bi-download"></i> Save Image
                        </button>
                        @if(session('token'))
                        <button class="btn-receipt btn-download" onclick="copyToClipboard('{{ session('token') }}')">
                            <i class="bi bi-clipboard"></i> Copy PIN
                        </button>
                        @endif
                        <a href="{{ route('dashboard') }}" class="btn-receipt btn-primary">
                            <i class="bi bi-house-door"></i> Home
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-muted extra-small mt-4 text-center extra-info" style="font-size: 0.7rem;">
                Arewa Smart Idea &copy; {{ date('Y') }}
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
        // AI Voice Notification on Load
        window.addEventListener('load', () => {
            const message = "Your purchase is successful and delivered";
            const utterance = new SpeechSynthesisUtterance(message);
            utterance.rate = 1.0;
            utterance.pitch = 1.0;
            
            // Note: Speech synthesis might require user interaction in some browsers, 
            // but for a "thank you" page post-transaction, it often works or can be triggered.
            window.speechSynthesis.speak(utterance);
        });

        // Download as PNG
        function downloadAsPNG() {
            const receipt = document.getElementById('receipt-capture');
            const btns = receipt.querySelector('.action-btns');
            
            // Temporarily hide buttons for the screenshot
            btns.style.visibility = 'hidden';
            
            html2canvas(receipt, {
                backgroundColor: '#ffffff',
                scale: 3, // High quality,
                logging: false,
                useCORS: true
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = `ArewaSmart_Receipt_{{ $ref }}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
                btns.style.visibility = 'visible';
            }).catch(err => {
                console.error('Download failed:', err);
                btns.style.visibility = 'visible';
                alert('Snapshot failed. Please try again or use the Print button.');
            });
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('PIN copied to clipboard!');
            });
        }
    </script>
    @endpush
</x-app-layout>
