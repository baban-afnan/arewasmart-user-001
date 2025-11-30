<x-app-layout>
    <title>Arewa Smart - {{ $title ?? 'NIN Modification Request Form' }}</title>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">NIN Modification Request Form</h3>
                        <p class="text-muted small mb-0">
                            Submit your request accurately to ensure smooth processing.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">

            {{-- ============================
                NIN MODIFICATION REQUEST FORM
            ============================ --}}
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm border-0 rounded-3">

                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-gear me-2"></i> NIN Modification Request
                        </h5>
                        <span class="badge bg-light text-primary fw-semibold">Arewa Smart</span>
                    </div>

                    <div class="card-body">

                        <div class="text-center mb-3">
                            <p class="text-muted small mb-0">
                                Submit your NIN Modification request below. Ensure all details are correct before submission.
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

                        {{-- FORM START --}}
                        <form method="POST" action="{{ route('nin-modification.store') }}">
                            @csrf

                            <div class="row g-3">

                                {{-- Modification Field --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Select Modification Field <span class="text-danger">*</span>
                                    </label>

                                    <select class="form-control" name="service_field_id" id="service_field" required>
                                        <option value="">-- Select Modification Field --</option>

                                        @foreach ($serviceFields as $field)
                                            <option value="{{ $field->id }}"
                                                    data-price="{{ $field->getPriceForUserType(auth()->user()->role) }}"
                                                    data-description="{{ $field->description ?? 'No description available.' }}"
                                                    {{ old('service_field_id') == $field->id ? 'selected' : '' }}>
                                                {{ $field->field_name }} -
                                                ₦{{ number_format($field->getPriceForUserType(auth()->user()->role), 2) }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <small class="text-muted" id="field-description"></small>
                                </div>

                                {{-- NIN Number --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold d-flex justify-content-between">
                                        <span>NIN ID <span class="text-danger">*</span></span>
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm py-0"
                                                data-bs-toggle="modal"
                                                data-bs-target="#sampleInfoModal">
                                            <i class="bi bi-info-circle"></i> Guide
                                        </button>
                                    </label>

                                    <input class="form-control text-center"
                                           name="nin"
                                           type="text"
                                           placeholder="Enter NIN Number (11 digits)"
                                           value="{{ old('nin') }}"
                                           maxlength="11"
                                           minlength="11"
                                           pattern="[0-9]{11}"
                                           title="11-digit NIN Number"
                                           required>
                                </div>

                                {{-- Data Information --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        Data Information <span class="text-danger">*</span>
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm ms-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#sampleInfoModal">
                                            <i class="bi bi-info-circle"></i> Guidelines
                                        </button>
                                    </label>

                                    <textarea class="form-control"
                                              name="description"
                                              rows="4"
                                              placeholder="Enter details you want to modify"
                                              required>{{ old('description') }}</textarea>
                                </div>

                                {{-- Service Fee --}}
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

                                {{-- Terms --}}
                                <div class="col-md-12">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input"
                                               id="termsCheckbox"
                                               type="checkbox"
                                               required>

                                        <label class="form-check-label fw-semibold small" for="termsCheckbox">
                                            I confirm that the provided information is accurate and agree to the NIN policy.
                                        </label>
                                    </div>
                                </div>

                                {{-- Submit --}}
                                <div class="col-md-12 d-grid mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                                        <i class="bi bi-send-fill me-2"></i> Submit Request
                                    </button>
                                </div>

                            </div>
                        </form>
                        {{-- FORM END --}}
                    </div>

                </div>
            </div>

            {{-- ======================
                SUBMISSION HISTORY
            ======================= --}}
            <div class="col-xl-6">
                <div class="card shadow-sm border-0">

                    <div class="card-header bg-primary text-white">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-clock-history me-2"></i> Submission History
                        </h5>
                    </div>

                    <div class="card-body">

                        {{-- Filters --}}
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <form method="GET" action="{{ route('nin-modification') }}">
                                    <div class="input-group">
                                        <input type="text"
                                               name="search"
                                               class="form-control"
                                               placeholder="Search by NIN..."
                                               value="{{ request('search') }}">

                                        <button class="btn btn-outline-primary" type="submit">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="col-md-4">
                                <form method="GET" action="{{ route('nin-modification') }}">
                                    <select name="status"
                                            class="form-control"
                                            onchange="this.form.submit()">

                                        <option value="">All Status</option>

                                        @foreach (['pending','query','processing','resolved','successful','rejected'] as $status)
                                            <option value="{{ $status }}"
                                                {{ request('status') == $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach

                                    </select>
                                </form>
                            </div>
                        </div>

                        {{-- History Table --}}
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>NIN</th>
                                        <th>Service Field</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($crmSubmissions as $submission)
                                        <tr>
                                            <td>{{ $loop->iteration + ($crmSubmissions->currentPage() - 1) * $crmSubmissions->perPage() }}</td>

                                            <td>{{ $submission->nin }}</td>

                                            <td>{{ $submission->service_field_name ?? 'N/A' }}</td>

                                            {{-- Status Badge --}}
                                            <td>
                                                <span class="badge bg-{{ match($submission->status) {
                                                    'resolved', 'successful' => 'success',
                                                    'processing'             => 'primary',
                                                    'rejected'               => 'danger',
                                                    'query'                  => 'info',
                                                    'remark'                 => 'secondary',
                                                    default                  => 'warning',
                                                } }}">
                                                    {{ ucfirst($submission->status) }}
                                                </span>
                                            </td>

                                            <td>{{ $submission->submission_date->format('M d, Y') }}</td>

                                            <td>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#commentModal"
                                                        data-comment="{{ $submission->comment ?? 'No comment yet.' }}"
                                                        data-file-url="{{ $submission->file_url ? asset($submission->file_url) : '' }}"
                                                        data-approved-by="{{ $submission->approved_by }}"
                                                        title="View Comment">
                                                    <i class="bi bi-chat-left-text"></i>
                                                </button>
                                            </td>
                                        </tr>

                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                No submissions found.
                                            </td>
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
        {{-- Comment Modal --}}
        @include('pages.comment')

        <link rel="stylesheet"
              href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    </div>
</x-app-layout>
