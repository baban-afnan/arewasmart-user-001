<div class="col-xl-6 d-none d-md-block">
    <div class="card h-100 border-0 shadow-lg position-relative overflow-hidden rounded-4"
         style="background: linear-gradient(135deg, #d46524ff 0%, #764ba2 100%); color: white;">
        
        <!-- Decorative Circles -->
        <div class="position-absolute top-0 start-0 translate-middle rounded-circle bg-white opacity-10" style="width: 200px; height: 200px;"></div>
        <div class="position-absolute bottom-0 end-0 translate-middle rounded-circle bg-white opacity-10" style="width: 300px; height: 300px;"></div>

        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-5 position-relative" style="z-index: 2;">
            
            <div class="mb-4">
                <div class="icon-box bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; font-size: 2rem;">
                    <i class="bi bi-broadcast"></i>
                </div>
            </div>

            <h2 class="fw-bold mb-3">Stay Connected</h2>
            <p class="lead mb-4 opacity-75">
                Experience seamless airtime top-ups with instant delivery. Never run out of talk time again!
            </p>

            <div class="card bg-white bg-opacity-10 border-0 rounded-3 p-3 mb-4 w-100 backdrop-blur">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="bi bi-stars text-warning fs-4 me-2"></i>
                    <div class="text-start">
                        <h6 class="mb-0 fw-bold text-warning">Special Bonus</h6>
                        <small class="text-white opacity-75">Get up to 3% cashback on every recharge</small>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-3 w-100" style="max-width: 400px;">
                <a href="{{ route('buy-data') }}" class="btn btn-light btn-lg fw-semibold shadow-sm">
                    <i class="bi bi-wifi me-2"></i> Buy Data Bundles
                </a>
                <div class="row g-2">
                    <div class="col">
                        <button class="btn btn-outline-light w-100">
                            <i class="bi bi-lightbulb me-1"></i> Electricity
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn btn-outline-light w-100">
                            <i class="bi bi-tv me-1"></i> Cable TV
                        </button>
                    </div>
                </div>
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
</style>
