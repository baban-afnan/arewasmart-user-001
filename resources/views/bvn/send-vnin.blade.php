<x-app-layout> 
    <title>Arewa Smart - {{ $title ?? 'SEND VNIN TO NIBSS Request Form' }}</title>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">SEND VNIN TO NIBSS Request</h3>
                        <p class="text-muted small mb-0">Submit your request accurately to ensure smooth processing.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <!-- SEND VNIN TO NIBSS Request Form -->
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-send-fill me-2"></i> SEND VNIN TO NIBSS Request
                        </h5>
                        <span class="badge bg-light text-primary fw-semibold">Arewa Smart</span>
                    </div>

                    <div class="card-body">
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

                        {{-- SEND VNIN Form --}}
                        <form method="POST" action="{{ route('send-vnin.store') }}">
                            @csrf
                            <div class="row g-3">

                                <!-- Verification Type -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Verification Type <span class="text-danger">*</span></label>
                                    <select class="form-select text-center" name="field_code" id="service_field" required>
                                        <option value="">-- Select Verification Type --</option>
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

                                <!-- Request ID -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Request ID <span class="text-danger">*</span></label>
                                    <input class="form-control text-center" name="request_id" type="text" required
                                           placeholder="Enter Request ID (7 digits)"
                                           value="{{ old('request_id') }}" maxlength="7" minlength="7"
                                           pattern="[0-9]{7}" title="7-digit Request ID">
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

                                <!-- Modification Field -->
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Modification Field <span class="text-danger">*</span></label>
                                    <input class="form-control text-center" name="field" type="text" required
                                           placeholder="Enter field to modify"
                                           value="{{ old('field') }}" maxlength="50">
                                </div>

                                <!-- Service Fee -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Service Fee</label>
                                    <div class="alert alert-info py-2 mb-0 text-center shadow-sm">
                                        <strong id="field-price">₦0.00</strong>
                                    </div>
                                    <small class="text-muted">
                                        Wallet Balance: 
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
                                            I confirm that the provided information is accurate and agree to the NIBSS policy.
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
                                        <th>Request ID</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($submissions as $submission)
                                        <tr>
                                            <td>{{ $loop->iteration + $submissions->firstItem() - 1 }}</td>
                                            <td>{{ $submission->reference }}</td>
                                            <td>{{ $submission->request_id }}</td>
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
                            {{ $submissions->withQueryString()->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Comment Modal --}}
    @include('pages.comment')

       <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</x-app-layout>
