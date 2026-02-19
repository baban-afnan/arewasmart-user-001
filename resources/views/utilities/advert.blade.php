<div class="col-xl-6 d-none d-md-block">
    <div class="card h-100 border-0 shadow-lg position-relative overflow-hidden rounded-4"
         style="background: linear-gradient(135deg, #111827 0%, #F26522 100%); color: white;">
        
        <!-- Decorative Circles -->
        <div class="position-absolute top-0 start-0 translate-middle rounded-circle bg-white opacity-5" style="width: 250px; height: 250px;"></div>
        <div class="position-absolute bottom-0 end-0 translate-middle rounded-circle bg-primary opacity-10" style="width: 350px; height: 350px;"></div>

        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-5 position-relative" style="z-index: 2;">
            
            <div class="mb-4">
                <div class="icon-box bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center mx-auto bounce-animation" style="width: 80px; height: 80px; font-size: 2.5rem; color: #F26522 !important;">
                    <i class="ti ti-rocket"></i>
                </div>
            </div>

            <h2 class="fw-bold mb-3 text-uppercase text-warning" style="letter-spacing: 1px;">Elevate Your Connectivity</h2>
            <p class="lead mb-2 opacity-90 fw-semibold">
                Instant <strong>Airtime</strong> & <strong>Data</strong> Top-up
            </p>
            <p class="mb-4 small opacity-80" style="max-width: 90%;">
                Don't get left behind. Power your devices with the fastest delivery and cleanest service in the market. Your reliable partner for all digital recharges.
            </p>

            <div class="card bg-white bg-opacity-10 border border-white border-opacity-20 rounded-4 p-3 mb-4 w-100 backdrop-blur pulse-warning">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="ti ti-bolt text-warning fs-1 me-3"></i>
                    <div class="text-start">
                        <h6 class="mb-1 fw-bold text-white">Smart Edge Pricing</h6>
                        <small class="text-white opacity-90">Unbeatable rates + Instant ₦0.00 convenience fee</small>
                    </div>
                </div>
            </div>

            <div class="card bg-white bg-opacity-10 border border-white border-opacity-20 rounded-4 p-3 mb-4 w-100 backdrop-blur pulse-warning">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="ti ti-bolt text-warning fs-1 me-3"></i>
                    <div class="text-start">
                        <h6 class="mb-1 fw-bold text-white">Very Fast Delivery</h6>
                        <small class="text-white opacity-90">Instant Delivery of Data and Airtime</small>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-3 w-100" style="max-width: 400px;">
                <a href="{{ route('airtime') }}" class="btn btn-primary btn-lg fw-bold shadow-sm py-3 rounded-pill text-white" style="background-color: #F26522; border: none;">
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
    .opacity-10 {
        opacity: 0.1;
    }
    .opacity-90 {
        opacity: 0.9;
    }
    .bounce-animation {
        animation: bounce 2s infinite;
    }
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
        40% {transform: translateY(-10px);}
        60% {transform: translateY(-5px);}
    }
    .pulse-warning {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
        animation: pulse-warn 2s infinite;
    }
    @keyframes pulse-warn {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
</style>
