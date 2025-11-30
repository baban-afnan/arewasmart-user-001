<x-app-layout>
    <title>Arewa Smart - {{ $title ?? 'Bvn Modification' }}</title>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">BVN Modification Request Form</h3>
                        <p class="text-muted small mb-0">Submit your request accurately to ensure smooth processing.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <!-- BVN Modification Form -->
                <div class="col-xl-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-1">BVN Modification Form</h3>
                            <p class="mb-0 small">Request for BVN modification. This process follows NIBSS regulations.</p>
                        </div>

                        <div class="card-body">
                            @if (session('message'))
                                <div class="alert alert-{{ session('status') === 'success' ? 'success' : 'danger' }} alert-dismissible fade show mt-3">
                                    {{ session('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show mt-3">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('modification.store') }}" enctype="multipart/form-data" class="row g-3">
                                @csrf

                                <!-- Bank Selection -->
                                <div class="col-md-6">
                                    <label for="enrolment_bank" class="form-label">Select Bank <span class="text-danger">*</span></label>
                                    <select name="enrolment_bank" id="enrolment_bank" class="form-select" required>
                                        <option value="">-- Select Bank --</option>
                                        @foreach($bankServices as $service)
                                            <option value="{{ $service->id }}" {{ old('enrolment_bank') == $service->id ? 'selected' : '' }}>
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Service Field Selection -->
                              <div class="col-md-6">
                                    <label for="service_field" class="form-label">Select Modification Field <span class="text-danger">*</span></label>
                                    <select name="service_field" id="service_field" class="form-select" required>
                                        <option value="">-- Select Field --</option>
                                        <!-- Options will be loaded dynamically via AJAX -->
                                    </select>
                                    <div class="mt-2">
                                        <small class="text-muted" id="field-description"></small>
                                    </div>
                                </div>

                               <!-- BVN -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">BVN <span class="text-danger">*</span></label>
                                    <input class="form-control text-center" name="bvn" type="text" required
                                           placeholder="Enter 11-digit BVN"
                                           value="{{ old('bvn') }}" maxlength="11" minlength="11"
                                           pattern="[0-9]{11}" title="11-digit BVN">
                                </div>

                                <!-- NIN -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">NIN <span class="text-danger">*</span></label>
                                    <input class="form-control text-center" name="nin" type="text" required
                                           placeholder="Enter 11-digit NIN"
                                           value="{{ old('nin') }}" maxlength="11" minlength="11"
                                           pattern="[0-9]{11}" title="11-digit NIN">
                                </div>

                                <!-- New Data Description -->
                                <div class="col-12">
                                    <label class="form-label">
                                        New Data Information <span class="text-danger">*</span>
                                        <button type="button" class="btn btn-outline-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#sampleInfoModal">View Sample</button>
                                    </label>
                                    <textarea name="description" rows="4" class="form-control" placeholder="Enter new information" required>{{ old('description') }}</textarea>
                                </div>

                                <!-- Affidavit Selection -->
                                <div class="col-12">
                                    <label class="form-label text-warning">Affidavit fee is ₦2000 only if the affidavit is not available</label>
                                    <select name="affidavit" id="affidavit" class="form-select" required>
                                        <option value="">-- Select Affidavit Type --</option>
                                        <option value="available" {{ old('affidavit') === 'available' ? 'selected' : '' }}>Affidavit is Available</option>
                                        <option value="not_available" {{ old('affidavit') === 'not_available' ? 'selected' : '' }}>Affidavit Not Available</option>
                                    </select>
                                </div>

                                <!-- Affidavit Upload -->
                                <div class="col-12" id="affidavit_upload_wrapper" style="display: none;">
                                    <label class="form-label">Upload Affidavit (PDF only)</label>
                                    <input type="file" name="affidavit_file" accept="application/pdf" class="form-control">
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

                                <!-- Total Amount Display -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Total Amount</label>
                                    <div class="alert alert-warning py-2 mb-0 text-center">
                                        <strong id="total-amount">₦0.00</strong>
                                    </div>
                                    <small class="text-muted" id="fee-breakdown"></small>
                                </div>

                                <!-- Terms Checkbox -->
                                <div class="col-12 form-check mt-3">
                                    <input type="checkbox" class="form-check-input" id="termsCheck" required>
                                    <label class="form-check-label" for="termsCheck">I agree to the BVN modification policies</label>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

              <!-- Submission History -->
            <div class="col-xl-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-black">Submission History</h5>
                    </div>
                    <div class="card-body">
                        <form class="row g-3 mb-3" method="GET" action="{{ route('send-vnin') }}">
                            <div class="col-md-6">
                                <input class="form-control" name="search" type="text" placeholder="Search by Request ID" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4">
                                <select class="form-control" name="status">
                                    <option value="">All Status</option>
                                    @foreach(['pending', 'processing', 'successful', 'query', 'resolved', 'rejected', 'remark'] as $status)
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

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Reference</th>
                                        <th>Bank</th>
                                        <th>BVN</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($crmSubmissions as $submission)
                                        <tr>
                                            <td>{{ $loop->iteration + $crmSubmissions->firstItem() - 1 }}</td>
                                            <td>{{ $submission->reference }}</td>
                                            <td>{{ $submission->bank }}</td>
                                            <td>{{ $submission->bvn }}</td>
                                            <td>
                                                <span class="badge bg-{{ match($submission->status) {
                                                    'resolved', 'successful' => 'success',
                                                    'processing' => 'primary',
                                                    'query' => 'info',
                                                    'rejected' => 'danger',
                                                    'remark' => 'secondary',
                                                    default => 'warning'
                                                } }}">{{ ucfirst($submission->status) }}</span>
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
                                            <td colspan="5" class="text-center text-muted">No submissions found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $crmSubmissions->withQueryString()->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Comment Modal --}}
    @include('pages.comment')

        <!-- Sample Info Modal -->
        <div class="modal fade" id="sampleInfoModal" tabindex="-1" aria-labelledby="sampleInfoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg rounded-4">
                    <div class="modal-header bg-primary text-white py-3 rounded-top-4">
                        <h4 class="modal-title fw-bold">
                            <i class="bi bi-pencil-square me-2"></i> BVN Modification Guidelines
                        </h4>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 py-4">
                        <p class="fs-6 text-muted mb-3">
                            Please follow the steps below when submitting a request to modify your BVN details.
                        </p>
                        <div class="bg-light p-4 rounded-3 mb-4 border-start border-4 border-primary">
                            <h6 class="fw-semibold mb-3 text-primary">
                                <i class="bi bi-list-check me-2"></i> Modification Instructions
                            </h6>
                            <ul class="fw-semibold mb-3 text-primary">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i> Clearly state the specific information to modify.</li>
                                <li><i class="bi bi-arrow-repeat text-warning me-2"></i> Provide the correct and updated information.</li>
                                <li><i class="bi bi-chat-left-text-fill text-info me-2"></i> Include a valid reason for the request.</li>
                            </ul>
                        </div>

                        <div class="p-4 mb-4 bg-white border rounded-3 shadow-sm">
                            <h6 class="fw-bold text-secondary mb-3">
                                <i class="bi bi-lightbulb-fill me-2 text-warning"></i> Example: Name Correction
                            </h6>
                            <p><strong>New First Name:</strong> ADEBAYO</p>
                            <p><strong>New Surname:</strong> ADEKUNLE</p>
                            <p><strong>New Middle Name:</strong> BOLA</p>
                            <p><strong>Reason:</strong> Spelling error during initial registration</p>
                        </div>

                        <div class="alert alert-info d-flex align-items-center py-3 px-4 rounded-3">
                            <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                            <div>
                                <strong>Note:</strong> All modification requests are thoroughly reviewed by NIBSS.
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
    </div>

    <!-- Scripts -->
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</x-app-layout>