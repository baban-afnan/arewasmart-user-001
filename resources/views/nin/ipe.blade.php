<x-app-layout>
    <title>Arewa Smart - IPE Clearance</title>
   <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">NIN ipe Request Form</h3>
                        <p class="text-muted small mb-0">
                            Submit your request accurately to ensure smooth processing.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <!-- Form Section -->
                <div class="col-xl-5 mb-4">
                    <div class="card shadow-lg border-0 rounded-4">
                        <div class="card-header bg-primary text-white p-3 p-md-4 border-0 rounded-top-4 text-center text-sm-start">
                            <h5 class="mb-0 fw-bold fs-15">New Request</h5>
                            <p class="mb-0 small text-white-50 mt-1">Please ensure all details are correct. Fees are non-refundable.</p>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('ipe-validation.store') }}" class="row g-4">
                                @csrf

                                <!-- Service Field Selection -->
                                <div class="col-12">
                                    <label for="service_field" class="form-label fw-semibold text-dark">Select Service Field <span class="text-danger">*</span></label>
                                    <select name="service_field" id="service_field" class="form-select form-select-lg bg-light border-0 shadow-sm" required>
                                        <option value="">-- Choose a Field --</option>
                                        @foreach($services as $field)
                                            <option value="{{ $field['id'] }}" data-price="{{ $field['price'] }}">
                                                {{ $field['name'] }} - ₦{{ number_format($field['price'], 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2">
                                        <small class="text-muted" id="field-description"></small>
                                    </div>
                                </div>

                                <!-- Tracking ID Input -->
                                <div class="col-12" id="tracking_wrapper">
                                    <label class="form-label fw-semibold text-dark">Tracking ID <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-lg shadow-sm">
                                        <span class="input-group-text bg-white border-0"><i class="bi bi-upc-scan text-primary"></i></span>
                                        <input type="text" name="tracking_id" class="form-control bg-light border-0 ps-0" placeholder="Enter Tracking ID (min 15 chars)" required>
                                    </div>
                                </div>

                                <!-- Price Display -->
                                <div class="col-12">
                                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-0 rounded-3 shadow-sm border-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-wallet2 fs-15 me-2 text-info"></i>
                                            <span class="fw-medium">Service Fee:</span>
                                        </div>
                                        <strong id="price_display" class="fs-15 text-info">₦0.00</strong>
                                    </div>
                                    <div class="d-flex justify-content-end mt-2">
                                        <small class="text-muted">
                                            Wallet Balance: <span class="text-success fw-bold">₦{{ number_format($wallet->balance ?? 0, 2) }}</span>
                                        </small>
                                    </div>
                                </div>

                                <!-- Warning -->
                                <div class="col-12">
                                    <div class="alert alert-warning py-3 rounded-3 shadow-sm border-0 d-flex align-items-center">
                                        <i class="bi bi-exclamation-circle text-warning fs-15 me-3"></i>
                                        <div class="small">
                                            <strong>Non-refundable Service</strong><br>
                                            Please verify all details carefully before submission.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-2">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2">
                                        Submit Request <i class="bi bi-arrow-right-circle"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- History Section -->
                <div class="col-xl-7">
                    <div class="card shadow-lg border-0 rounded-4">
                        <!-- Header -->
                        <div class="card-header bg-white p-3 p-md-4 border-bottom d-flex justify-content-center justify-content-sm-between align-items-center rounded-top-4">
                            <h5 class="mb-0 fw-bold text-dark fs-15"><i class="bi bi-clock-history text-primary me-2"></i>Request History</h5>
                        </div>

                        <div class="card-body p-3 p-md-4">
                            <!-- Filters -->
                            <form class="row g-3 mb-4" method="GET">
                                <div class="col-12 col-md-5">
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                                        <input class="form-control bg-light border-0 ps-0" name="search" type="text" placeholder="Search Tracking ID" value="{{ request('search') }}">
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <select class="form-select bg-light border-0 shadow-sm" name="status">
                                        <option value="">All Statuses</option>
                                        @foreach(['pending', 'processing', 'successful', 'failed', 'rejected'] as $status)
                                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-3">
                                    <button class="btn btn-primary w-100 rounded-pill shadow-sm fw-medium" type="submit">Filter</button>
                                </div>
                            </form>

                            <!-- Table -->
                            <div class="table-responsive rounded-3 border">
                                <table class="table table-hover table-borderless align-middle mb-0 text-nowrap">
                                    <thead class="table-light border-bottom">
                                        <tr>
                                            <th class="ps-4">#</th>
                                            <th>Reference</th>
                                            <th>Tracking ID</th>
                                            <th>Status</th>
                                            <th class="text-end pe-4">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($submissions as $submission)
                                            <tr class="border-bottom">
                                                <!-- Serial Number -->
                                                <td class="ps-4 text-muted">{{ $loop->iteration + $submissions->firstItem() - 1 }}</td>

                                                <!-- Reference -->
                                                <td class="fw-medium text-dark">{{ $submission->reference }}</td>

                                                <!-- Tracking ID -->
                                                <td>
                                                    <span class="fw-bold">{{ $submission->tracking_id }}</span>
                                                    <br>
                                                    <small class="text-primary fw-medium">
                                                        {{ $submission->service_field_name }}
                                                    </small>
                                                </td>
                                                <!-- Status Badge -->
                                                <td>
                                                    @php
                                                        $statusLower = strtolower($submission->status);
                                                        $badgeInfo = match($statusLower) {
                                                            'successful', 'success', 'resolved' => ['color' => 'success', 'icon' => 'check-circle'],
                                                            'processing', 'in-progress' => ['color' => 'info', 'icon' => 'arrow-repeat'],
                                                            'pending' => ['color' => 'warning', 'icon' => 'hourglass-split'],
                                                            'failed', 'rejected', 'error' => ['color' => 'danger', 'icon' => 'x-circle'],
                                                            default => ['color' => 'secondary', 'icon' => 'dash-circle']
                                                        };
                                                    @endphp
                                                    <span class="badge bg-{{ $badgeInfo['color'] }}-subtle text-{{ $badgeInfo['color'] }} px-3 py-2 rounded-pill fw-semibold border border-{{ $badgeInfo['color'] }}-subtle">
                                                        <i class="bi bi-{{ $badgeInfo['icon'] }} me-1"></i> {{ ucfirst($submission->status) }}
                                                    </span>
                                                </td>

                                                <!-- Action Buttons -->
                                                <td class="text-end pe-4">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <!-- Check Status -->
                                                        @if(in_array($statusLower, ['pending', 'processing', 'in-progress']))
                                                            <a href="{{ route('ipe-validation.check', $submission->id) }}" class="btn btn-sm btn-light text-primary shadow-sm rounded-circle d-flex align-items-center justify-content-center" data-bs-toggle="tooltip" title="Check Status" style="width: 35px; height: 35px;">
                                                                <i class="bi bi-arrow-repeat fs-15"></i>
                                                            </a>
                                                        @endif

                                                        <!-- View Comment -->
                                                        <button type="button" class="btn btn-sm btn-light text-secondary shadow-sm rounded-circle d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#responseModal" data-response="{{ $submission->comment ?? 'No comment yet.' }}" data-bs-toggle="tooltip" title="View Response" style="width: 35px; height: 35px;">
                                                            <i class="bi bi-chat-left-text fs-15"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                                            <i class="bi bi-inbox fs-2 text-muted"></i>
                                                        </div>
                                                        <h6 class="fw-semibold">No requests found</h6>
                                                        <p class="small text-muted mb-0">Your submitted IPE clearance requests will appear here.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-4 d-flex justify-content-end">
                                {{ $submissions->withQueryString()->links('vendor.pagination.custom') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- End Row -->

            <!-- Response Modal -->
            <div class="modal fade" id="responseModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

                        <!-- Header -->
                        <div class="modal-header bg-gradient-primary text-white py-3 px-3 py-md-4 px-md-4 border-bottom-0" style="background: linear-gradient(135deg, var(--bs-primary) 0%, #2a52be 100%);">
                            <div class="d-flex align-items-center gap-2 gap-sm-3">
                                <div class="bg-white bg-opacity-25 p-2 rounded-circle d-none d-sm-block">
                                    <i class="bi bi-chat-left-text fs-15 text-white"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title text-white mb-0 fw-bold fs-15">IPE Clearance Response</h5>
                                    <p class="small text-white-50 mb-0">Details of your submission</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body p-3 p-md-4 bg-light" style="font-size: 1rem; min-height: 200px;">
                            <!-- Loading State -->
                            <div id="responseLoading" class="text-center text-secondary py-4">
                                <div class="spinner-border text-primary mb-3" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
                                <h6 class="mb-0 fw-medium">Processing request...</h6>
                            </div>

                            <!-- Response Content -->
                            <div id="responseContentWrapper" class="d-none">
                                <div class="bg-white p-3 p-md-4 rounded-4 shadow-sm border mb-0">
                                    <pre id="responseContent" class="mb-0 text-dark" style="white-space: pre-wrap; font-size: 0.9rem; font-family: inherit;"></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer bg-white border-top d-flex flex-column flex-sm-row justify-content-between align-items-center py-3 px-3 px-md-4 gap-3">
                            <!-- Quick Access Buttons -->
                            <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 w-100 w-sm-auto">
                                <a href="#" class="btn btn-outline-primary rounded-pill px-4 fw-medium shadow-sm transition-all hover-lift w-100 w-sm-auto mb-1 mb-sm-0">
                                    <i class="bi bi-file-earmark-text me-2"></i> BVN Report
                                </a>
                                <a href="#" class="btn btn-outline-warning rounded-pill px-4 fw-medium shadow-sm transition-all hover-lift w-100 w-sm-auto mb-1 mb-sm-0">
                                    <i class="bi bi-award me-2"></i> VIP Access
                                </a>
                                <a href="#" class="btn btn-outline-info rounded-pill px-4 fw-medium shadow-sm transition-all hover-lift w-100 w-sm-auto mb-1 mb-sm-0">
                                    <i class="bi bi-question-circle me-2"></i> Complain
                                </a>
                            </div>
                            <!-- Encouragement Text -->
                            <div id="encouragement" class="text-success fw-medium small d-flex align-items-center gap-1 w-100 w-sm-auto justify-content-center">
                                <i class="bi bi-check-circle-fill"></i> Reviewed successfully
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <style>
                .hover-lift:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
                }
                .transition-all {
                    transition: all .2s ease-in-out;
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function() {

                    const serviceFieldSelect = document.getElementById('service_field');
                    const priceDisplay = document.getElementById('price_display');

                    // Initialize tooltips
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })

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
                        responseModal.addEventListener('show.bs.modal', function(event) {
                            // Button that triggered the modal
                            const button = event.relatedTarget;
                            // Extract info from data-bs-* attributes
                            const responseText = button.getAttribute('data-response');

                            // Update the modal's content.
                            const modalBodyContentWrapper = responseModal.querySelector('#responseContentWrapper');
                            const modalBodyContent = responseModal.querySelector('#responseContent');
                            const modalLoading = responseModal.querySelector('#responseLoading');

                            if (modalLoading) modalLoading.classList.add('d-none');
                            if (modalBodyContentWrapper && modalBodyContent) {
                                modalBodyContentWrapper.classList.remove('d-none');
                                modalBodyContent.textContent = responseText;
                            }
                        });
                        
                        responseModal.addEventListener('hidden.bs.modal', function(event) {
                            const modalBodyContentWrapper = responseModal.querySelector('#responseContentWrapper');
                            const modalLoading = responseModal.querySelector('#responseLoading');
                            if (modalLoading) modalLoading.classList.remove('d-none');
                            if (modalBodyContentWrapper) modalBodyContentWrapper.classList.add('d-none');
                        });
                    }

                }); // DOM Loaded END
            </script>
        </div>
    </div>
</x-app-layout>
