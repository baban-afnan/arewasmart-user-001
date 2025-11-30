<x-app-layout>
 <title>Arewa Smart - {{ $title ?? 'CRM Request Form' }}</title>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                     <h3 class="fw-bold text-primary">CRM on failed Enrolment Request Form</h3>
                        <p class="text-muted small mb-0">Submit your request accurately to ensure smooth processing.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">

            <!-- BVN CRM Form -->
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-gear me-2"></i>BVN CRM Request</h5>
                        <span class="badge bg-light text-primary fw-semibold">Arewa Smart</span>
                    </div>

                    <div class="card-body">
                        <div class="text-center mb-3">
                            <p class="text-muted small mb-0">
                                Submit your BVN CRM request below. Ensure all details are correct before submission.
                            </p>
                        </div>

                        {{-- Alerts --}}
                        @if (session('status'))
                            <div class="alert alert-{{ session('status') === 'success' ? 'success' : 'danger' }} alert-dismissible fade show">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <ul class="mb-0 small text-start">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- BVN CRM Request Form --}}
                        <form method="POST" action="{{ route('crm.store') }}">
                            @csrf
                            <div class="row g-3">

                                <!-- CRM Type -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">CRM Type <span class="text-danger">*</span></label>
                                    <select class="form-select text-center" name="field_code" id="service_field" required>
                                        <option value="">-- Select CRM Type --</option>
                                        @foreach ($fieldname as $field)
                                            @php
                                                $price = $field->prices
                                                    ->where('user_type', auth()->user()->role)
                                                    ->first()?->price ?? $field->base_price;
                                            @endphp
                                            <option value="{{ $field->id }}"
                                                    data-price="{{ $price }}"
                                                    data-description="{{ $field->description }}"
                                                    {{ old('field_code') == $field->id ? 'selected' : '' }}>
                                                {{ $field->field_name }} - ₦{{ number_format($price, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" id="field-description"></small>
                                </div>

                                <!-- Batch ID -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold d-flex justify-content-between">
                                        <span>Batch ID</span>
                                        <button type="button" class="btn btn-outline-primary btn-sm py-0" data-bs-toggle="modal" data-bs-target="#sampleInfoModal">
                                            <i class="bi bi-info-circle"></i> Guide
                                        </button>
                                    </label>
                                    <input class="form-control text-center" name="batch_id" type="text" required
                                           placeholder="Enter Batch ID (7 digits)"
                                           value="{{ old('batch_id') }}" maxlength="7" minlength="7"
                                           pattern="[0-9]{7}" title="7-digit Batch ID">
                                </div>

                                <!-- Ticket ID -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Ticket ID <span class="text-danger">*</span></label>
                                    <input class="form-control text-center" name="ticket_id" type="text" required
                                           placeholder="Enter Ticket ID (8 digits)"
                                           value="{{ old('ticket_id') }}" maxlength="8" minlength="8"
                                           pattern="[0-9]{8}" title="8-digit Ticket ID">
                                </div>

                                <!-- Service Fee -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Service Fee</label>
                                    <div class="alert alert-info py-2 mb-0 text-center">
                                        <strong id="field-price">₦0.00</strong>
                                    </div>
                                    <small class="text-muted">
                                        Balance:
                                        <strong class="text-success">
                                            ₦{{ number_format($wallet->balance ?? 0, 2) }}
                                        </strong>
                                    </small>
                                </div>

                                <!-- Terms -->
                                <div class="col-md-12">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" id="termsCheckbox" type="checkbox" required>
                                        <label class="form-check-label fw-semibold small" for="termsCheckbox">
                                            I confirm that the provided information is accurate and agree to the CRM policy.
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit -->
                                <div class="col-md-12 d-grid mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                                        <i class="bi bi-send-fill me-2"></i> Submit Request
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>

    <!-- Submission History -->
<div class="col-xl-6">
    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clock-history me-2"></i> CRM Submission History
            </h5>
        </div>

        <div class="card-body">

            <!-- Filter Form -->
            <form method="GET" class="row g-3 mb-3">

                <div class="col-md-6">
                    <input class="form-control"
                           name="search"
                           type="text"
                           placeholder="Search by Ticket/Batch ID"
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-4">
                    <select class="form-control" name="status">
                        <option value="">All Status</option>
                        @foreach(['pending','processing','successful','query','resolved','rejected','remark'] as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
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
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Reference</th>
                        <th>Ticket ID</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td>{{ $loop->iteration + $submissions->firstItem() - 1 }}</td>

                            <td>{{ $submission->reference }}</td>

                            <td>{{ $submission->ticket_id ?? $submission->batch_id ?? 'N/A' }}</td>

                            <td>
                                <span class="badge bg-{{ match($submission->status) {
                                    'resolved', 'successful' => 'success',
                                    'processing'             => 'primary',
                                    'rejected'               => 'danger',
                                    'query'                  => 'info',
                                    'remark'                 => 'secondary',
                                    default                  => 'warning'
                                } }}">
                                    {{ ucfirst($submission->status) }}
                                </span>
                            </td>

                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#commentModal"
                                        data-comment="{{ $submission->comment ?? 'No comment yet.' }}"
                                        data-file-url="{{ $submission->file_url ? asset($submission->file_url) : '' }}">
                                    <i class="bi bi-chat-left-text"></i> View
                                </button>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                No submissions found.
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


    <!-- BVN CRM Guidelines Modal -->
    <div class="modal fade" id="sampleInfoModal" tabindex="-1" aria-labelledby="sampleInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg rounded-4">
                <div class="modal-header bg-primary text-white py-3 rounded-top-4">
                    <h4 class="modal-title fw-bold" id="sampleInfoModalLabel">
                        <i class="bi bi-person-badge-fill me-2"></i> BVN CRM Submission Guidelines
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body px-4 py-4">
                    <p class="fs-6 text-muted mb-3">
                        Please follow the instructions below to locate your <strong>Enrollment Batch ID</strong> and <strong>Ticket ID</strong> before submitting your CRM request.
                    </p>

                    <div class="bg-light p-4 rounded-3 mb-4 border-start border-4 border-primary">
                        <h6 class="fw-semibold mb-3 text-primary">
                            <i class="bi bi-list-check me-2"></i> Steps to Retrieve Required IDs
                        </h6>
                        <ul class="fw-semibold mb-3 text-primary">
                            <li class="mb-2"><i class="bi bi-arrow-return-right text-success me-2"></i> Navigate to the <strong>failed BVN enrollment</strong> on your system or portal.</li>
                            <li class="mb-2"><i class="bi bi-chat-dots-fill text-warning me-2"></i> Check the response or error message — your <strong>Batch ID</strong> and <strong>Ticket ID</strong> will be clearly displayed there.</li>
                            <li><i class="bi bi-clipboard-check-fill text-info me-2"></i> Copy the displayed IDs accurately and proceed to submit your <strong>BVN CRM request</strong>.</li>
                        </ul>
                    </div>

                    <div class="p-4 mb-4 bg-white border rounded-3 shadow-sm">
                        <h6 class="fw-bold text-secondary mb-2">
                            <i class="bi bi-lightbulb-fill me-2 text-warning"></i> Tips
                        </h6>
                        <p class="mb-0 text-muted">Ensure there are no typos in the Batch or Ticket ID to avoid delays or rejection of your CRM request.</p>
                    </div>

                    <div class="alert alert-info d-flex align-items-center py-3 px-4 rounded-3">
                        <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                        <div>
                            <strong>Note:</strong> All CRM requests are subject to verification and final approval by the Nigeria Inter-Bank Settlement System (NIBSS).
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-3">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="bi bi-check-circle me-2"></i> Understood
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Comment Modal --}}
    @include('pages.comment')

    {{-- JS for dynamic fee & description --}}
  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</x-app-layout>
