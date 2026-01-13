<x-app-layout>
    <title>Arewa Smart - {{ $title ?? 'Develper API' }}</title>
    
    <div class="page-body">
        <div class="container-fluid">
            
            <!-- Hero Section -->
            <div class="row align-items-center mb-5 mt-4">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-3 py-2 mb-3 fw-bold">
                        <i class="bi bi-code-slash me-2"></i>Developer API
                    </span>
                    <h1 class="display-4 fw-bold text-dark mb-3">Build Faster with Our <span class="text-primary">Powerful API</span></h1>
                    <p class="lead text-muted mb-4">
                        Seamlessly integrate identity verification, bill payments, and other services into your applications. 
                        Reliable, secure, and developer-friendly.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="https://api.arewasmart.com.ng" target="_blank" class="btn btn-primary btn-lg fw-bold px-4 shadow-sm hover-scale">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login to Developer Portal
                        </a>
                        <a href="https://api.arewasmart.com.ng" target="_blank" class="btn btn-outline-secondary btn-lg fw-bold px-4 hover-scale">
                            <i class="bi bi-book me-2"></i>Documentation
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                   <img src="{{ asset('assets/img/api-hero-illustration.svg') }}" onerror="this.src='https://cdn-icons-png.flaticon.com/512/8297/8297437.png'" alt="API Integration" class="img-fluid" style="max-height: 350px;"> 
                </div>
            </div>

            <!-- Features Grid -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 feature-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-primary bg-opacity-10 text-primary mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-lightning-charge-fill fs-3"></i>
                            </div>
                            <h5 class="fw-bold fs-18">Lightning Fast</h5>
                            <p class="text-muted small mb-0">Experience sub-second response times optimized for high-volume transactions.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 feature-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-success bg-opacity-10 text-success mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-shield-lock-fill fs-3"></i>
                            </div>
                            <h5 class="fw-bold fs-18">Bank-Grade Security</h5>
                            <p class="text-muted small mb-0">Your data and transactions are protected with industry-standard encryption protocols.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 feature-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-info bg-opacity-10 text-info mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-code-square fs-3"></i>
                            </div>
                            <h5 class="fw-bold fs-18">Easy Integration</h5>
                            <p class="text-muted small mb-0">Well-documented endpoints and SDKs make integration a breeze for any developer.</p>
                        </div>
                    </div>
                </div>
            </div>

    <style>
        .hover-scale { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .hover-scale:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
        .feature-card { transition: transform 0.3s ease; }
        .feature-card:hover { transform: translateY(-5px); }
        .avatar-sm { width: 40px; height: 40px; }
        .bg-lighter { background-color: #f8f9fa; }
    </style>
</x-app-layout>