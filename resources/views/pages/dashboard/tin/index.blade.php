<x-app-layout>
    <title>Arewa Smart - {{ $title ?? 'TIN Registration' }}</title>
    
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">TIN Registration Service</h3>
                        <p class="text-muted small mb-0">Register for Tax Identification Number (Individual & Corporate).</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <!-- TIN Registration Form -->
            <div class="col-xl-8 mb-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2"></i>New Application</h5>
                        <span class="badge bg-light text-primary fw-semibold">Arewa Smart</span>
                    </div>

                    <div class="card-body">
                        <div class="text-center mb-3">
                            <p class="text-muted small mb-0">
                                Fill in the details below to apply for TIN.
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

                        {{-- TIN Form Wizard --}}
                        <form method="POST" action="{{ route('tin.store') }}" enctype="multipart/form-data" id="tinForm">
                            @csrf
                            
                            {{-- Step 1: Service Type --}}
                            <div class="wizard-step" id="step-1">
                                <div class="mb-4 p-3 bg-light rounded border">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold">Registration Type <span class="text-danger">*</span></label>
                                            <select class="form-select text-center" name="service_field_id" id="service_field" required>
                                                <option value="">-- Select TIN Type --</option>
                                                @foreach ($fields as $field)
                                                    @php
                                                        $price = $field->prices
                                                            ->where('user_type', auth()->user()->role)
                                                            ->first()?->price ?? $field->base_price;
                                                        $isCorporate = stripos($field->field_name, 'Corporate') !== false || stripos($field->field_name, 'Organisation') !== false || stripos($field->field_name, 'Company') !== false;
                                                    @endphp
                                                    <option value="{{ $field->id }}"
                                                            data-price="{{ $price }}"
                                                            data-type="{{ $isCorporate ? 'corporate' : 'individual' }}"
                                                            {{ old('service_field_id') == $field->id ? 'selected' : '' }}>
                                                        {{ $field->field_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                         <div class="col-md-12 text-center mt-3 mt-md-0">
                                            <div class="p-2 border rounded bg-white">
                                                <small class="text-muted d-block">Service Fee</small>
                                                <h4 class="fw-bold text-primary mb-0" id="price-display">₦0.00</h4>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">Wallet Balance:</small>
                                                <strong class="text-success">₦{{ number_format($wallet->balance, 2) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-primary next-step">Next <i class="bi bi-arrow-right ms-1"></i></button>
                                </div>
                            </div>

                            {{-- Step 2: Personal / Company Details --}}
                            <div class="wizard-step d-none" id="step-2">
                                
                                <!-- Individual Fields -->
                                <div id="individual-fields" class="d-none">
                                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Personal Information</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label">BVN <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="bvn" value="{{ old('bvn') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">NIN <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="nin" value="{{ old('nin') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Surname <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="last_name" value="{{ old('last_name') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="first_name" value="{{ old('first_name') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Middle Name</label>
                                            <input type="text" class="form-control" name="middle_name" value="{{ old('middle_name') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Marital Status <span class="text-danger">*</span></label>
                                            <select class="form-select" name="marital_status">
                                                <option value="">Select</option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Divorced">Divorced</option>
                                                <option value="Widowed">Widowed</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Corporate Fields -->
                                <div id="corporate-fields" class="d-none">
                                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Organization Details</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-12">
                                            <label class="form-label">Organization Type <span class="text-danger">*</span></label>
                                            <select class="form-select" name="organization_type">
                                                <option value="">Select Type</option>
                                                <option value="INCORPORATED TRUSTEE">INCORPORATED TRUSTEE</option>
                                                <option value="LIMITED BY GUARANTEE">LIMITED BY GUARANTEE</option>
                                                <option value="PARTNERSHIP">PARTNERSHIP</option>
                                                <option value="PRIVATE LIMITED COMPANY">PRIVATE LIMITED COMPANY</option>
                                                <option value="PRIVATE UNLIMITED COMPANY">PRIVATE UNLIMITED COMPANY</option>
                                                <option value="PUBLIC LIMITED COMPANY">PUBLIC LIMITED COMPANY</option>
                                                <option value="SOLE PROPRIETORSHIP">SOLE PROPRIETORSHIP</option>
                                                <option value="BUSINESS NAME">BUSINESS NAME</option>
                                                <option value="LIMITED LIABILITY PARTNERSHIP">LIMITED LIABILITY PARTNERSHIP</option>
                                                <option value="LIMITED PARTNERSHIP">LIMITED PARTNERSHIP</option>
                                            </select>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Company/Business Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="company_name" value="{{ old('company_name') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Registration Number <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="registration_number" value="{{ old('registration_number') }}">
                                        </div>
                                    </div>
                                    
                                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Contact Person Details</h6>
                                    <p class="small text-muted">Please provide details of the primary contact person.</p>
                                    <!-- Reusing Surname/First Name fields but they are inside individual-fields div which is hidden. 
                                         I should duplicate them or move them out if they are shared. 
                                         Since I want to toggle sections cleanly, I'll add them here with same names but I need to be careful about ID conflicts or just use same names and rely on browser submitting only visible ones? 
                                         No, browser submits all unless disabled. 
                                         I will use JS to disable inputs in the hidden section.
                                    -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Surname <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control corporate-contact" name="last_name" value="{{ old('last_name') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control corporate-contact" name="first_name" value="{{ old('first_name') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Common Contact Info -->
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Contact Information</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" name="phone_number" value="{{ old('phone_number') }}" required>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary prev-step"><i class="bi bi-arrow-left ms-1"></i> Previous</button>
                                    <button type="button" class="btn btn-primary next-step">Next <i class="bi bi-arrow-right ms-1"></i></button>
                                </div>
                            </div>

                            {{-- Step 3: Address Details --}}
                            <div class="wizard-step d-none" id="step-3">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Address Details</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label">State <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="state" value="{{ old('state') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">LGA <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="lga" value="{{ old('lga') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">City <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="city" value="{{ old('city') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">House Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="house_number" value="{{ old('house_number') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Street Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="street_name" value="{{ old('street_name') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nearest Bus Stop <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nearest_bus_stop" value="{{ old('nearest_bus_stop') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Country <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="country" value="Nigeria" required>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary prev-step"><i class="bi bi-arrow-left ms-1"></i> Previous</button>
                                    <button type="button" class="btn btn-primary next-step">Next <i class="bi bi-arrow-right ms-1"></i></button>
                                </div>
                            </div>

                            {{-- Step 4: Uploads & Submit --}}
                            <div class="wizard-step d-none" id="step-4">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Supporting Documents</h6>
                                
                                <div id="individual-uploads" class="d-none">
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-12">
                                            <label class="form-label">Passport Photograph <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" name="passport_upload" accept="image/*,.pdf">
                                        </div>
                                    </div>
                                </div>

                                <div id="corporate-uploads" class="d-none">
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-12">
                                            <label class="form-label">CAC Certificate <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" name="cac_certificate" accept="image/*,.pdf">
                                        </div>
                                    </div>
                                </div>

                                <!-- Terms -->
                                <div class="col-md-12">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" id="termsCheckbox" type="checkbox" required>
                                        <label class="form-check-label fw-semibold small" for="termsCheckbox">
                                            I confirm that the provided information is accurate and agree to the service terms.
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit -->
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary prev-step"><i class="bi bi-arrow-left ms-1"></i> Previous</button>
                                    <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                                        <i class="bi bi-send-fill me-2"></i> Submit Application
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Submission History -->
            <div class="col-xl-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-white">
                            <i class="bi bi-clock-history me-2"></i> History
                        </h5>
                    </div>

                    <div class="card-body">
                        <!-- Filter Form -->
                        <form method="GET" class="mb-3">
                            <div class="input-group">
                                <input class="form-control" name="search" type="text" placeholder="Search Ref..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ref</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($submissions as $submission)
                                        <tr>
                                            <td class="small">{{ $submission->reference }}</td>
                                            <td>
                                                <span class="badge bg-{{ match($submission->status) {
                                                    'successful' => 'success',
                                                    'processing' => 'info',
                                                    'pending' => 'warning',
                                                    'query'      => 'info',
                                                    'rejected'   => 'danger',
                                                    'failed'   => 'danger',
                                                    default      => 'secondary'
                                                } }}">
                                                    {{ ucfirst($submission->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-xs btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#submissionModal{{ $submission->id }}">
                                                    View
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted">No history found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            {{ $submissions->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @foreach ($submissions as $submission)
    <div class="modal fade" id="submissionModal{{ $submission->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Submission: {{ $submission->reference }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Status:</strong> {{ ucfirst($submission->status) }} <br>
                        <strong>Comment:</strong> {{ $submission->comment ?? 'None' }}
                        @if($submission->status == 'successful' && $submission->approved_by)
                            <br><strong>Approved By:</strong> {{ $submission->approved_by }}
                        @endif
                    </div>

                    @if($submission->status == 'successful' && $submission->tin_file)
                        <div class="card mb-3 border-success">
                            <div class="card-header bg-success text-white">Download Certificate</div>
                            <div class="card-body">
                                <a href="{{ asset($submission->tin_file) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-download me-1"></i> Download TIN Certificate
                                </a>
                            </div>
                        </div>
                    @endif

                    <h6 class="fw-bold">Submitted Information</h6>
                    @php 
                        $details = json_decode($submission->field, true); 
                    @endphp
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Service:</strong><br>
                            {{ $submission->service_name }} - {{ $submission->field_name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Name/Company:</strong><br>
                            {{ $submission->business_name ?? ($submission->first_name . ' ' . $submission->last_name) }}
                        </div>
                    </div>

                    <h6 class="fw-bold">Uploads</h6>
                    <div class="row">
                        @if(isset($details['uploads']['passport']))
                            <div class="col-4 mb-2">
                                <a href="{{ asset('storage/'.$details['uploads']['passport']) }}" target="_blank" class="btn btn-sm btn-outline-secondary w-100">Passport</a>
                            </div>
                        @endif
                        @if(isset($details['uploads']['cac_certificate']))
                            <div class="col-4 mb-2">
                                <a href="{{ asset('storage/'.$details['uploads']['cac_certificate']) }}" target="_blank" class="btn btn-sm btn-outline-secondary w-100">CAC Cert</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serviceSelect = document.getElementById('service_field');
            const priceDisplay = document.getElementById('price-display');
            const steps = document.querySelectorAll('.wizard-step');
            const nextButtons = document.querySelectorAll('.next-step');
            const prevButtons = document.querySelectorAll('.prev-step');
            
            // Sections to toggle
            const individualFields = document.getElementById('individual-fields');
            const corporateFields = document.getElementById('corporate-fields');
            const individualUploads = document.getElementById('individual-uploads');
            const corporateUploads = document.getElementById('corporate-uploads');
            
            let currentStep = 0;

            function toggleSections(type) {
                if (type === 'corporate') {
                    individualFields.classList.add('d-none');
                    corporateFields.classList.remove('d-none');
                    individualUploads.classList.add('d-none');
                    corporateUploads.classList.remove('d-none');
                    
                    // Disable inputs in hidden sections to avoid validation errors
                    toggleInputs(individualFields, true);
                    toggleInputs(corporateFields, false);
                    toggleInputs(individualUploads, true);
                    toggleInputs(corporateUploads, false);
                } else {
                    individualFields.classList.remove('d-none');
                    corporateFields.classList.add('d-none');
                    individualUploads.classList.remove('d-none');
                    corporateUploads.classList.add('d-none');
                    
                    toggleInputs(individualFields, false);
                    toggleInputs(corporateFields, true);
                    toggleInputs(individualUploads, false);
                    toggleInputs(corporateUploads, true);
                }
            }

            function toggleInputs(container, disabled) {
                const inputs = container.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.disabled = disabled;
                });
            }

            serviceSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                const type = selectedOption.getAttribute('data-type');
                
                if (price) {
                    priceDisplay.textContent = '₦' + new Intl.NumberFormat().format(price);
                    toggleSections(type);
                } else {
                    priceDisplay.textContent = '₦0.00';
                }
            });

            // Navigation
            function showStep(stepIndex) {
                steps.forEach((step, index) => {
                    if (index === stepIndex) {
                        step.classList.remove('d-none');
                    } else {
                        step.classList.add('d-none');
                    }
                });
                window.scrollTo(0, 0);
            }

            function validateStep(stepIndex) {
                const currentStepEl = steps[stepIndex];
                // Only validate inputs that are NOT disabled and NOT in a hidden container (though disabled handles it mostly)
                const requiredInputs = currentStepEl.querySelectorAll('[required]:not([disabled])');
                let isValid = true;

                if (stepIndex === 0 && serviceSelect.value === '') {
                    alert('Please select a Registration Type.');
                    return false;
                }

                requiredInputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                if (!isValid) {
                    alert('Please fill in all required fields.');
                }

                return isValid;
            }

            nextButtons.forEach(button => {
                button.addEventListener('click', () => {
                    if (validateStep(currentStep)) {
                        currentStep++;
                        showStep(currentStep);
                    }
                });
            });

            prevButtons.forEach(button => {
                button.addEventListener('click', () => {
                    if (currentStep > 0) {
                        currentStep--;
                        showStep(currentStep);
                    }
                });
            });

            // Initialize
            if (serviceSelect.value) {
                serviceSelect.dispatchEvent(new Event('change'));
            } else {
                // Default to individual if nothing selected, but keep hidden until selection
                toggleSections('individual');
            }
        });
    </script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</x-app-layout>
