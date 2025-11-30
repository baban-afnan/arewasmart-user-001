<x-app-layout>
    <title>Arewa Smart - NIN Validation & IPE</title>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">NIN Validation & IPE</h3>
                        <p class="text-muted small mb-0">Submit requests for NIN Validation or IPE Clearance.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <!-- Form Section -->
                <div class="col-xl-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">New Request</h5>
                            <p class="mb-0 small text-white-50">Please ensure all details are correct. Fees are non-refundable.</p>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('nin-validation.store') }}" class="row g-3">
                                @csrf
                                <!-- Service Type Selection -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Select Service Type <span class="text-danger">*</span></label>
                                    <select name="service_type_select" id="service_type_select" class="form-select" required>
                                        <option value="">-- Select Type --</option>
                                        <option value="validation">NIN Validation</option>
                                        <option value="ipe">IPE Clearance</option>
                                    </select>
                                </div>

                                <!-- Service Field Selection -->
                                <div class="col-md-6">
                                    <label for="service_field" class="form-label">Select Service Field <span class="text-danger">*</span></label>
                                    <select name="service_field" id="service_field" class="form-select" required disabled>
                                        <option value="">-- Select Field --</option>
                                        <!-- Options will be loaded dynamically -->
                                    </select>
                                    <div class="mt-2">
                                        <small class="text-muted" id="field-description"></small>
                                    </div>
                                </div>

                                <!-- Hidden input for actual service type submission -->
                                <input type="hidden" name="service_type" id="service_type">

                                <!-- NIN Input (Validation) -->
                                <div class="col-12" id="nin_wrapper" style="display: none;">
                                    <label class="form-label fw-bold">NIN <span class="text-danger">*</span></label>
                                    <input type="text" name="nin" class="form-control" placeholder="Enter 11-digit NIN" maxlength="11" pattern="\d{11}">
                                </div>

                                <!-- Tracking ID Input (IPE) -->
                                <div class="col-12" id="tracking_wrapper" style="display: none;">
                                    <label class="form-label fw-bold">Tracking ID <span class="text-danger">*</span></label>
                                    <input type="text" name="tracking_id" class="form-control" placeholder="Enter Tracking ID (min 15 chars)">
                                </div>

                                <!-- Data Store for JS -->
                                <div id="service-data" style="display: none;">
                                    @json($services)
                                </div>

                                <!-- Price Display -->
                                <div class="col-12">
                                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                                        <span>Service Fee:</span>
                                        <strong id="price_display">₦0.00</strong>
                                    </div>
                                    <small class="text-muted mt-1 d-block">
                                        Wallet Balance: <span class="text-success fw-bold">₦{{ number_format($wallet->balance ?? 0, 2) }}</span>
                                    </small>
                                </div>

                                <!-- Warning -->
                                <div class="col-12">
                                    <div class="alert alert-warning py-2">
                                        <i class="ti ti-alert-triangle me-1"></i>
                                        <strong>Note:</strong> This service is non-refundable. Verify details before submitting.
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">Submit Request</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

          <!-- History Section -->
<!-- Request History -->
<div class="col-xl-6">
    <div class="card shadow-sm border-0">

        <!-- Header -->
        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-black">Request History</h5>
        </div>

        <div class="card-body">

            <!-- Filters -->
            <form class="row g-3 mb-3" method="GET">
                <div class="col-md-6">
                    <input 
                        class="form-control" 
                        name="search" 
                        type="text" 
                        placeholder="Search NIN / Tracking ID"
                        value="{{ request('search') }}"
                    >
                </div>

                <div class="col-md-4">
                    <select class="form-control" name="status">
                        <option value="">All Status</option>
                        @foreach(['pending', 'processing', 'successful', 'failed', 'rejected'] as $status)
                            <option value="{{ $status }}" 
                                {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th>NIN / Tracking</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($submissions as $submission)
                            <tr>
                                <!-- Serial Number -->
                                <td>{{ $loop->iteration + $submissions->firstItem() - 1 }}</td>

                                <!-- Reference -->
                                <td>{{ $submission->reference }}</td>

                                <!-- NIN or Tracking ID -->
                                <td>{{ $submission->nin ?? $submission->tracking_id }}
                                    <br>
                                   <small class="fw-semibold text-primary">
                                        {{ $submission->service_field_name }}
                                    </small>
                                  </td>
                                <!-- Status Badge -->
                                <td>
                                    @php
                                        $color = match($submission->status) {
                                            'successful', 'success', 'resolved' => 'success',
                                            'processing', 'in-progress' => 'info',
                                            'pending' => 'warning',
                                            'failed', 'rejected', 'error' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ ucfirst($submission->status) }}
                                    </span>
                                </td>

                                <!-- Action Buttons -->
                                <td class="text-end">
                                    <div class="btn-group">

                                        <!-- Check Status -->
                                        <a href="{{ route('nin-validation.check', $submission->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Check Status">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </a>

                                        <!-- View Comment -->
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#responseModal"
                                                data-response="{{ $submission->comment ?? 'No comment yet.' }}">
                                            <i class="bi bi-chat-left-text"></i>
                                        </button>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    No requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $submissions->withQueryString()->links('vendor.pagination.custom') }}
            </div>

        </div>
    </div>
</div>



       <!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            <!-- Header -->
            <div class="modal-header bg-primary text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-text fs-4"></i>
                    <h5 class="modal-title text-white mb-0 fw-semibold">Validation Response</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4" style="font-size: 1rem; min-height: 180px;">

                <!-- Loading State -->
                <div id="responseLoading" class="text-center text-secondary">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="mb-0">Processing request...</p>
                </div>

                <!-- Response Content -->
                <pre id="responseContent" 
                     class="bg-white p-3 rounded border d-none"
                     style="white-space: pre-wrap; font-size: 0.95rem;">
                </pre>

            </div>

            <!-- Footer -->
            <div class="modal-footer bg-white border-top d-flex justify-content-between align-items-center py-2 px-4">
                
                <!-- Quick Access Buttons -->
                <div class="d-flex flex-wrap gap-2">
                    <a href="#" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-file-earmark-text me-1"></i> BVN Report
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                        <i class="bi bi-award me-1"></i> VIP Access
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-info rounded-pill px-3">
                        <i class="bi bi-question-circle me-1"></i> Complain
                    </a>
                </div>

                <!-- Encouragement Text -->
                <div id="encouragement" class="text-muted small fst-italic"></div>
            </div>

        </div>
    </div>
</div>


      <script>
document.addEventListener('DOMContentLoaded', function() {

    /* ---------------------------------------------------
       SERVICE FIELD LOGIC
    ----------------------------------------------------*/
    const serviceTypeSelect = document.getElementById('service_type_select');
    const serviceFieldSelect = document.getElementById('service_field');
    const serviceTypeInput = document.getElementById('service_type');
    const ninWrapper = document.getElementById('nin_wrapper');
    const trackingWrapper = document.getElementById('tracking_wrapper');
    const priceDisplay = document.getElementById('price_display');
    const ninInput = document.querySelector('input[name="nin"]');
    const trackingInput = document.querySelector('input[name="tracking_id"]');

    // Parse the services data from the hidden element
    const servicesDataElement = document.getElementById('service-data');
    let servicesData = [];
    
    if (servicesDataElement) {
        try {
            servicesData = JSON.parse(servicesDataElement.textContent);
        } catch (e) {
            console.error('Failed to parse service data', e);
        }
    }

    // When choosing service type
    if (serviceTypeSelect) {
        serviceTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            serviceTypeInput.value = selectedType;

            serviceFieldSelect.innerHTML = '<option value="">-- Select Field --</option>';
            serviceFieldSelect.disabled = true;
            priceDisplay.textContent = '₦0.00';

            // Hide input fields by default
            ninWrapper.style.display = 'none';
            trackingWrapper.style.display = 'none';
            if (ninInput) ninInput.required = false;
            if (trackingInput) trackingInput.required = false;

            if (selectedType) {
                const filteredServices = servicesData.filter(service => service.type === selectedType);

                filteredServices.forEach(service => {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = `${service.name} - ₦${parseFloat(service.price).toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
                    option.setAttribute('data-price', service.price);
                    serviceFieldSelect.appendChild(option);
                });

                serviceFieldSelect.disabled = false;

                // Show the correct input field
                if (selectedType === 'validation') {
                    ninWrapper.style.display = 'block';
                    if (ninInput) ninInput.required = true;
                } 
                else if (selectedType === 'ipe') {
                    trackingWrapper.style.display = 'block';
                    if (trackingInput) trackingInput.required = true;
                }
            }
        });
    }

    // When selecting specific service field
    if (serviceFieldSelect) {
        serviceFieldSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');

            if (price) {
                priceDisplay.textContent = '₦' + parseFloat(price).toLocaleString('en-NG', {
                    minimumFractionDigits: 2
                });
            } else {
                priceDisplay.textContent = '₦0.00';
            }
        });
    }

    /* ---------------------------------------------------
       MODERN RESPONSE MODAL HANDLING
    ----------------------------------------------------*/
    const responseModal = document.getElementById('responseModal');
    if (responseModal) {
        responseModal.addEventListener('show.bs.modal', function (event) {
            // Button that triggered the modal
            const button = event.relatedTarget;
            // Extract info from data-bs-* attributes
            const responseText = button.getAttribute('data-response');

            // Update the modal's content.
            const modalBodyContent = responseModal.querySelector('#responseContent');
            const modalLoading = responseModal.querySelector('#responseLoading');
            const encouragement = responseModal.querySelector('#encouragement');

            if (modalLoading) modalLoading.classList.add('d-none');
            if (modalBodyContent) {
                modalBodyContent.classList.remove('d-none');
                modalBodyContent.textContent = responseText;
            }
            if (encouragement) encouragement.innerText = "Reviewed successfully ✓";
        });
    }

}); // DOM Loaded END
</script>
    </div>
</x-app-layout>
