<!-- Services Section -->
<section class="py-4">
    <div class="container">
        <div class="row service-grid justify-content-center">

            <!-- Service Box (Reusable Template) -->
            @php
                $services = [
                    ['route' => route('wallet'), 'icon' => 'ti-wallet', 'color' => 'bg-primary', 'name' => 'Wallet'],
                    ['route' => route('airtime'), 'icon' => 'ti-phone-call', 'color' => 'bg-info', 'name' => 'Airtime'],
                    ['modal' => '#dataplans', 'icon' => 'ti-world', 'color' => 'bg-warning', 'name' => 'Data'],
                    ['route' => route('electricity'), 'icon' => 'ti-bolt', 'color' => 'bg-danger', 'name' => 'Electricity'],
                    ['route' => route('education'), 'icon' => 'ti-home', 'color' => 'bg-success', 'name' => 'Educational Pin'],
                    ['route' => route('jamb'), 'icon' => 'ti-home', 'color' => 'bg-secondary', 'name' => 'Jamb Pin'],
                    ['route' => route('bvn-crm'), 'icon' => 'ti-user-plus', 'color' => 'bg-info', 'name' => 'CRM'],
                    ['route' => route('send-vnin'), 'icon' => 'ti-user-plus', 'color' => 'bg-warning', 'name' => 'Vnin/Fas'],
                    ['route' => route('modification'), 'icon' => 'ti-user-plus', 'color' => 'bg-danger', 'name' => 'BVN Modification'],
                    ['route' => route('phone.search.index'), 'icon' => 'ti-user-plus', 'color' => 'bg-success', 'name' => 'Search BVN'],
                    ['modal' => '#agentEnrolmentModal', 'icon' => 'ti-user-plus', 'color' => 'bg-secondary', 'name' => 'BVN Agent'],
                    ['route' => route('nin-modification'), 'icon' => 'ti-user-plus', 'color' => 'bg-info', 'name' => 'NIN Modification'],
                    ['route' => route('nin-validation'), 'icon' => 'ti-user-plus', 'color' => 'bg-warning', 'name' => 'Validation & IPE'],
                    ['route' => route('affidavit.index'), 'icon' => 'ti-home-plus', 'color' => 'bg-danger', 'name' => 'Affidavit'],
                    ['route' => route('cac.index'), 'icon' => 'ti-user-plus', 'color' => 'bg-success', 'name' => 'CAC Reg'],
                    ['route' => route('enrolment.report'), 'icon' => 'ti-user-plus', 'color' => 'bg-secondary', 'name' => 'Enrolment Report'],
                    ['modal' => '#verifyModal', 'icon' => 'ti-id-badge', 'color' => 'bg-info', 'name' => 'Verify NIN/DEMO'],
                    ['modal' => '#verifyModalbvn', 'icon' => 'ti-message', 'color' => 'bg-secondary', 'name' => 'Verify (BVN/TIN)'],
                ];
            @endphp

            @foreach ($services as $sv)
                <div class="col-4 col-md-2 d-flex">
                    <a 
                        @if(isset($sv['route'])) href="{{ $sv['route'] }}" 
                        @elseif(isset($sv['modal'])) href="#" data-bs-toggle="modal" data-bs-target="{{ $sv['modal'] }}"
                        @else href="#"
                        @endif
                        class="w-100"
                    >
                        <div class="card flex-fill shadow-sm text-center border-0 rounded-3 service-card">
                            <div class="card-body p-3 d-flex flex-column align-items-center">
                                <span class="avatar rounded-circle {{ $sv['color'] }} mb-2 p-3">
                                    <i class="ti {{ $sv['icon'] }} text-white fs-18"></i>
                                </span>
                                <h6 class="fs-13 fw-semibold text-default mb-0">{{ $sv['name'] }}</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach

        </div>
    </div>
</section>
<!-- /Services Section -->

<!-- Agent Enrolment Modal -->
<div class="modal fade" id="agentEnrolmentModal" tabindex="-1" aria-labelledby="agentEnrolmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 shadow-lg border-0">

      <!-- HEADER -->
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="agentEnrolmentModalLabel">
          <i class="ti ti-id-badge fs-3"></i>
          Become a Certified BVN Agent
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body p-4">

        <!-- INTRO -->
        <div class="mb-4 p-3 bg-light rounded-3 border">
          <p class="mb-4 text-secondary fs-12">
            Join thousands of trusted agents across Nigeria and start earning instantly.  
            As a certified <strong>BVN Enrollment Agent</strong>, you provide essential identity services, support your community, and make steady income every day.
          </p>
        </div>

        <!-- REQUIREMENTS -->
        <h6 class="fw-bold mb-2 text-primary d-flex align-items-center gap-2">
          <i class="ti ti-file-check fs-5"></i> Requirements
        </h6>

        <ul class="list-group mb-4 shadow-sm">
          <li class="list-group-item">✔✔ Valid BVN, active bank account & government-issued ID card</li>
          <li class="list-group-item">✔✔ A working phone number and email (unused on any BVN enrolment account)</li>
          <li class="list-group-item">✔✔ A business location or shop (optional but highly recommended)</li>
          <li class="list-group-item">✔✔ Minimum activation balance of <strong>₦10,000</strong> in your wallet</li>
        </ul>

        <!-- BENEFITS -->
        <h6 class="fw-bold mb-2 text-success d-flex align-items-center gap-2">
          <i class="ti ti-gift fs-5"></i> Benefits of Becoming an Agent
        </h6>

        <ul class="list-group mb-4 shadow-sm">
          <li class="list-group-item">✔✔ Earn attractive commissions on every successful BVN enrollment</li>
          <li class="list-group-item">✔✔ Become a trusted and verified financial identity service provider</li>
          <li class="list-group-item">✔✔ Access more exclusive, high-value financial services</li>
          <li class="list-group-item">✔✔ Priority customer support from Arewa Smart</li>
          <li class="list-group-item">✔✔ 48h Bvn approval on submitted enrollment </li>
          <li class="list-group-item">✔✔ Real time Bvn enrollment report and provinant dashboard for enrolment support</li>
          <li class="list-group-item">✔✔ Build your own agent network and grow a sustainable income stream</li>
          <li class="list-group-item">✔✔ Get Agent ID card and Certificate for Security verification and authentications</li>
        </ul>

        <div class="alert alert-success text-center fw-semibold py-3 rounded-3">
          🌟 <strong>You’re one step away!</strong> Start your enrollment today and unlock a world of opportunities.
        </div>

      </div>

      <!-- FOOTER -->
      <div class="modal-footer p-3">
        <a href="{{route ('bvn.index')}}" class="btn btn-primary w-100 fw-semibold">
          Start Registration
          <i class="ti ti-arrow-right ms-1"></i>
        </a>
      </div>

    </div>
  </div>
</div>
<!-- /Agent Enrolment Modal -->


<!-- Verify Modal -->
<div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow-lg border-0 overflow-hidden">

      <!-- HEADER -->
      <div class="modal-header bg-primary text-white py-3 justify-content-center position-relative">
        <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="verifyModalLabel">
          <i class="ti ti-id-badge fs-3"></i>
          NIN / DEMO / PHONE NO. Verification
        </h5>
        <button type="button" class="btn-close btn-close-white position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body p-4 bg-light">

        @php
            $verifyServices = [
                [
                    'route' => route('nin.phone.index'),
                    'icon' => 'ti-fingerprint',
                    'color' => 'success',
                    'name' => 'NIN Phone NO.'
                ],
                [
                    'route' => route('nin.verification.index'),
                    'icon' => 'ti-credit-card',
                    'color' => 'primary',
                    'name' => 'Verify NIN'
                ],
                [
                    'route' => route('nin.demo.index'),
                    'icon' => 'ti-file-certificate',
                    'color' => 'danger',
                    'name' => 'Verify NIN Demo'
                ],
            ];
        @endphp

        <div class="row g-3 justify-content-center">
            @foreach ($verifyServices as $sv)
                <div class="col-4 d-flex">
                    <a href="{{ $sv['route'] }}" class="w-100 text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm service-card">
                            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                                <span class="avatar rounded-circle bg-white text-{{ $sv['color'] }} shadow-sm mb-3 p-3">
                                    <i class="ti {{ $sv['icon'] }} fs-2"></i>
                                </span>
                                <h6 class="fs-13 fw-bold text-dark mb-0">{{ $sv['name'] }}</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
      </div>

      <!-- FOOTER -->
      <div class="modal-footer p-3 justify-content-center border-top-0">
        <button type="button" class="btn btn-outline-danger px-5 rounded-pill fw-semibold" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
<!-- /Verify Modal -->

<!-- Verify Modal (BVN/TIN only) -->
<div class="modal fade" id="verifyModalbvn" tabindex="-1" aria-labelledby="verifyModalLabelBvn" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow-lg border-0 overflow-hidden">

      <!-- HEADER -->
      <div class="modal-header bg-primary text-white py-3 justify-content-center position-relative">
        <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="verifyModalLabelBvn">
          <i class="ti ti-id-badge fs-3"></i>
          BVN / TIN Verification
        </h5>
        <button type="button" class="btn-close btn-close-white position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body p-4 bg-light">

        @php
            $verifyServicesBvn = [
                [
                    'route' => route('bvn.verification.index'),
                    'icon' => 'ti-fingerprint',
                    'color' => 'success',
                    'name' => 'Verify BVN'
                ],
                [
                    'route' => route('cac.tin'),
                    'icon' => 'ti-file-certificate',
                    'color' => 'danger',
                    'name' => 'Verify TIN'
                ],
            ];
        @endphp

        <div class="row g-3 justify-content-center">
            @foreach ($verifyServicesBvn as $sv)
                <div class="col-4 d-flex">
                    <a href="{{ $sv['route'] }}" class="w-100 text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm service-card">
                            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                                <span class="avatar rounded-circle bg-white text-{{ $sv['color'] }} shadow-sm mb-3 p-3">
                                    <i class="ti {{ $sv['icon'] }} fs-2"></i>
                                </span>
                                <h6 class="fs-13 fw-bold text-dark mb-0">{{ $sv['name'] }}</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
      </div>

      <!-- FOOTER -->
      <div class="modal-footer p-3 justify-content-center border-top-0">
        <button type="button" class="btn btn-outline-danger px-5 rounded-pill fw-semibold" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
<!-- /Verify Modal (BVN/TIN only) -->


<!-- Buy data plans for sme and data -->
<div class="modal fade" id="dataplans" tabindex="-1" aria-labelledby="verifyModalLabelBvn" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow-lg border-0 overflow-hidden">

      <!-- HEADER -->
      <div class="modal-header bg-primary text-white py-3 justify-content-center position-relative">
        <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="verifyModalLabelBvn">
          <i class="ti ti-id-badge fs-3"></i>
          Buy direct plans or SME data
        </h5>
        <button type="button" class="btn-close btn-close-white position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body p-4 bg-light">

        @php
            $verifyServicesBvn = [
                [
                    'route' => route('buy-data'),
                    'icon' => 'ti-world',
                    'color' => 'success',
                    'name' => 'Direct Data Plans'
                ],
                [
                    'route' => route('buy-data'),
                    'icon' => 'ti-world',
                    'color' => 'info',
                    'name' => 'SME Data Plans'
                ],
            ];
        @endphp

        <div class="row g-3 justify-content-center">
            @foreach ($verifyServicesBvn as $sv)
                <div class="col-4 d-flex">
                    <a href="{{ $sv['route'] }}" class="w-100 text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm service-card">
                            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                                <span class="avatar rounded-circle bg-white text-{{ $sv['color'] }} shadow-sm mb-3 p-3">
                                    <i class="ti {{ $sv['icon'] }} fs-2"></i>
                                </span>
                                <h6 class="fs-13 fw-bold text-dark mb-0">{{ $sv['name'] }}</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
      </div>

      <!-- FOOTER -->
      <div class="modal-footer p-3 justify-content-center border-top-0">
        <button type="button" class="btn btn-outline-danger px-5 rounded-pill fw-semibold" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
<!-- /Buy data plans for sme and data -->


<style>

  /* Arewa Smart Dashboard Service Styling */
.service-card {
    transition: 0.2s ease;
    border-radius: 14px !important;
}

.service-card:hover {
    transform: translateY(-3px);
}

/* Reduce service box size on phones */
@media (max-width: 576px) {
    .service-card .card-body {
        padding: 0.7rem !important;
    }
    .service-card h6 {
        font-size: 12px !important;
    }
    .service-card .avatar {
        padding: 0.4rem !important;
    }
    .service-card i {
        font-size: 14px !important;
    }
}

/* Perfect grid spacing */
.service-grid {
    row-gap: 14px;
}

</style>