<x-app-layout>
    <title>Arewa Smart - {{ $title ?? 'Affidavit Service' }}</title>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">Affidavit Service Request</h3>
                        <p class="text-muted small mb-0">Submit your affidavit request with required documents.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">

            <!-- Affidavit Form -->
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2"></i>New Affidavit Request</h5>
                        <span class="badge bg-light text-primary fw-semibold">Arewa Smart</span>
                    </div>

                    <div class="card-body">
                        <div class="text-center mb-3">
                            <p class="text-muted small mb-0">
                                Fill in the details below and upload required documents.
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

                        {{-- Affidavit Request Form --}}
                        <form method="POST" action="{{ route('affidavit.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">

                                <!-- Affidavit Type -->
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Affidavit Type <span class="text-danger">*</span></label>
                                    <select class="form-select text-center" name="field_code" id="service_field" required>
                                        <option value="">-- Select Affidavit Type --</option>
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

                                <!-- Old Details -->
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Old Details <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="old_details" rows="3" required placeholder="Enter old information here...">{{ old('old_details') }}</textarea>
                                </div>

                                <!-- New Details -->
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">New Details <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="new_details" rows="3" required placeholder="Enter new information here...">{{ old('new_details') }}</textarea>
                                </div>

                                <!-- NIN Slip Upload -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Upload NIN Slip <span class="text-danger">*</span></label>
                                    <input class="form-control" type="file" name="nin_slip" accept=".jpg,.jpeg,.png,.pdf" required>
                                    <small class="text-muted">Max size: 2MB (JPG, PNG, PDF)</small>
                                </div>

                                <!-- Passport ID Card Upload -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Upload Passport ID Card <span class="text-danger">*</span></label>
                                    <input class="form-control" type="file" name="passport" accept=".jpg,.jpeg,.png,.pdf" required>
                                    <small class="text-muted">Max size: 2MB (JPG, PNG, PDF)</small>
                                </div>

                                <!-- Service Fee -->
                                <div class="col-md-12">
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
                                            I confirm that the provided information and documents are accurate.
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
                            <i class="bi bi-clock-history me-2"></i> Submission History
                        </h5>
                    </div>

                    <div class="card-body">

                        <!-- Filter Form -->
                        <form method="GET" class="row g-3 mb-3">

                            <div class="col-md-6">
                                <input class="form-control"
                                       name="search"
                                       type="text"
                                       placeholder="Search by Reference"
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
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse ($submissions as $submission)
                                    <tr>
                                        <td>{{ $loop->iteration + $submissions->firstItem() - 1 }}</td>

                                        <td>{{ $submission->reference }}</td>

                                        <td>{{ $submission->field_name ?? 'N/A' }}</td>

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
                                                    data-file-url="{{ $submission->affidavit_file_url ? asset($submission->affidavit_file_url) : '' }}"
                                                    data-approved-by="{{ $submission->approved_by }}">
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
        </div>
    </div>

    {{-- Comment Modal --}}
    @include('pages.comment')

    {{-- JS for dynamic fee & description --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const serviceField = document.getElementById('service_field');
            const fieldPrice = document.getElementById('field-price');
            const fieldDescription = document.getElementById('field-description');

            serviceField.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                const description = selectedOption.getAttribute('data-description');

                if (price) {
                    fieldPrice.textContent = '₦' + new Intl.NumberFormat().format(price);
                } else {
                    fieldPrice.textContent = '₦0.00';
                }

                if (description) {
                    fieldDescription.textContent = description;
                } else {
                    fieldDescription.textContent = '';
                }
            });
        });
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</x-app-layout>
