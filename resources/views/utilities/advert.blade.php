<div class="col-xl-6">
    <div class="card h-100 border-0 shadow-lg position-relative overflow-hidden rounded-4 premium-advert-card"
         style="background: linear-gradient(135deg, #111827 0%, #F26522 100%); color: white; min-height: 250px;">
        
        <!-- HOT Mark -->
        <div class="position-absolute top-0 end-0 p-3" style="z-index: 5;">
            <span class="badge bg-danger pulse-hot shadow-sm px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">
                <i class="ti ti-flame me-1"></i> HOT
            </span>
        </div>

        <!-- Decorative Circles -->
        <div class="position-absolute top-0 start-0 translate-middle rounded-circle bg-white opacity-5" style="width: 250px; height: 250px;"></div>
        <div class="position-absolute bottom-0 end-0 translate-middle rounded-circle bg-primary opacity-10" style="width: 350px; height: 350px;"></div>

        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4 p-md-5 position-relative" style="z-index: 2;">
            
            <div class="mb-4">
                <div class="icon-box bg-white text-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center mx-auto bounce-animation" style="width: 70px; height: 70px; font-size: 2.2rem; color: #F26522 !important;">
                    <i class="ti ti-rocket"></i>
                </div>
            </div>

            <h2 class="fw-bold mb-3 text-uppercase text-warning" style="letter-spacing: 1px; font-size: 1.5rem;">Elevate Your Connectivity</h2>
            <p class="lead mb-2 opacity-90 fw-semibold" style="font-size: 1.1rem;">
                Instant <strong>Airtime</strong> & <strong>Data</strong> Top-up
            </p>
            <p class="mb-4 small opacity-80" style="max-width: 90%;">
                Don't get left behind. Power your devices with the fastest delivery and cleanest service in the market. Your reliable partner for all digital recharges.
            </p>

            <div class="card bg-white bg-opacity-10 border border-white border-opacity-20 rounded-4 p-3 mb-3 w-100 backdrop-blur hover-glow">
                <div class="d-flex align-items-center justify-content-center text-start">
                    <i class="ti ti-bolt text-warning fs-1 me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold text-white">Smart Edge Pricing</h6>
                        <small class="text-white opacity-75">Unbeatable rates + Instant ₦0.00 convenience fee</small>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-3 w-100" style="max-width: 350px;">
                <a href="{{ route('airtime') }}" class="btn btn-warning btn-lg fw-extrabold shadow-lg py-3 rounded-pill text-dark border-0">
                    <i class="ti ti-world me-2"></i> Get Started Now
                </a>
            </div>

        </div>
    </div>
</div>

<style>
    .backdrop-blur {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .opacity-5 { opacity: 0.05; }
    .opacity-10 { opacity: 0.1; }
    
    .bounce-animation {
        animation: bounce-advert 3s infinite;
    }
    @keyframes bounce-advert {
        0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
        40% {transform: translateY(-8px);}
        60% {transform: translateY(-4px);}
    }

    .pulse-hot {
        animation: pulse-hot-anim 2s infinite;
        background-color: #dc3545 !important;
        border: 2px solid rgba(255,255,255,0.2);
    }
    
    @keyframes pulse-hot-anim {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }

    .hover-glow:hover {
        background-color: rgba(255, 255, 255, 0.15) !important;
        box-shadow: 0 0 20px rgba(242, 101, 34, 0.3);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }

    .premium-advert-card {
        transition: transform 0.4s ease;
    }
    .premium-advert-card:hover {
        transform: scale(1.01);
    }

    @media (max-width: 768px) {
        .premium-advert-card {
            min-height: auto !important;
        }
        .premium-advert-card h2 {
            font-size: 1.25rem !important;
        }
    }
</style>
