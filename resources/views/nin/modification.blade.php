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

                                {{-- Data Information (Generic) --}}
                                <div class="col-12" id="generic-data-info">
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
                                              id="description-field"
                                              rows="4"
                                              placeholder="Enter details you want to modify"
                                              required>{{ old('description') }}</textarea>
                                </div>

                                {{-- DOB Modification Wizard (Hidden by default) --}}
                                <div class="col-12 d-none" id="dob-wizard">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-lines-fill me-2"></i> Attestation Form for DOB Modification</h6>
                                            
                                            {{-- Step 1: Personal Details --}}
                                            <div class="wizard-step" id="step-1">
                                                <h6 class="text-muted border-bottom pb-2 mb-3">Step 1/8: Personal Details</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[first_name]" placeholder="First Name" disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Surname <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[surname]" placeholder="Surname" disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Middle Name</label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[middle_name]" placeholder="Middle Name (Optional)" disabled>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Gender <span class="text-danger">*</span></label>
                                                        <select class="form-control form-control-sm dob-input" name="modification_data[gender]" disabled>
                                                            <option value="">Select</option>
                                                            <option value="Male">Male</option>
                                                            <option value="Female">Female</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Marital Status <span class="text-danger">*</span></label>
                                                        <select class="form-control form-control-sm dob-input" name="modification_data[marital_status]" disabled>
                                                            <option value="">Select</option>
                                                            <option value="Single">Single</option>
                                                            <option value="Married">Married</option>
                                                            <option value="Divorced">Divorced</option>
                                                            <option value="Widowed">Widowed</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 text-end mt-3">
                                                        <button type="button" class="btn btn-primary btn-sm next-step" data-next="step-2">Next <i class="bi bi-arrow-right"></i></button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Step 2: DOB & Nationality --}}
                                            <div class="wizard-step d-none" id="step-2">
                                                <h6 class="text-muted border-bottom pb-2 mb-3">Step 2/8: DOB & Origin</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control form-control-sm dob-input" name="modification_data[new_dob]" disabled>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Nationality <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[nationality]" value="Nigeria" disabled>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">State of Origin <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[state_of_origin]" disabled>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">LGA of Origin <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[lga_of_origin]" disabled>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label small fw-semibold">Town of Origin <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[town_of_origin]" disabled>
                                                    </div>
                                                    <div class="col-12 text-end mt-3">
                                                        <button type="button" class="btn btn-secondary btn-sm prev-step" data-prev="step-1"><i class="bi bi-arrow-left"></i> Previous</button>
                                                        <button type="button" class="btn btn-primary btn-sm next-step" data-next="step-3">Next <i class="bi bi-arrow-right"></i></button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Step 3: Residence --}}
                                            <div class="wizard-step d-none" id="step-3">
                                                <h6 class="text-muted border-bottom pb-2 mb-3">Step 3/8: Residence Details</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">State (Residence) <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[residence_state]" disabled>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">City (Residence) <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[residence_city]" disabled>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Town (Residence) <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[residence_town]" disabled>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">LGA (Residence) <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[residence_lga]" disabled>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label small fw-semibold">Residence Address <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[residence_address]" disabled>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[phone_number]" disabled>
                                                    </div>
                                                    <div class="col-12 text-end mt-3">
                                                        <button type="button" class="btn btn-secondary btn-sm prev-step" data-prev="step-2"><i class="bi bi-arrow-left"></i> Previous</button>
                                                        <button type="button" class="btn btn-primary btn-sm next-step" data-next="step-4">Next <i class="bi bi-arrow-right"></i></button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Step 4: Birth Details --}}
                                            <div class="wizard-step d-none" id="step-4">
                                                <h6 class="text-muted border-bottom pb-2 mb-3">Step 4/8: Birth Information</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label small fw-semibold">Place of Birth <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[place_of_birth]" disabled>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">State of Birth <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[state_of_birth]" disabled>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">LGA of Birth <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[lga_of_birth]" disabled>
                                                    </div>
                                                    <div class="col-12 text-end mt-3">
                                                        <button type="button" class="btn btn-secondary btn-sm prev-step" data-prev="step-3"><i class="bi bi-arrow-left"></i> Previous</button>
                                                        <button type="button" class="btn btn-primary btn-sm next-step" data-next="step-5">Next <i class="bi bi-arrow-right"></i></button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Step 5: Socio-Economic --}}
                                            <div class="wizard-step d-none" id="step-5">
                                                <h6 class="text-muted border-bottom pb-2 mb-3">Step 5/8: Socio-Economic Details</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Occupation <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[occupation]" disabled>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Highest Level of Education <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[education_level]" disabled>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label small fw-semibold">Occupation Address <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[occupation_address]" disabled>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label small fw-semibold">Request Reason <span class="text-danger">*</span></label>
                                                        <select class="form-control form-control-sm dob-input" name="modification_data[reason]" disabled>
                                                            <option value="As Requirement for">As Requirement for</option>
                                                            <option value="Others">Others</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 text-end mt-3">
                                                        <button type="button" class="btn btn-secondary btn-sm prev-step" data-prev="step-4"><i class="bi bi-arrow-left"></i> Previous</button>
                                                        <button type="button" class="btn btn-primary btn-sm next-step" data-next="step-6">Next <i class="bi bi-arrow-right"></i></button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Step 6: Father's Details --}}
                                            <div class="wizard-step d-none" id="step-6">
                                                <h6 class="text-muted border-bottom pb-2 mb-3">Step 6/8: Father's Details</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Surname <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[father_surname]" disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[father_firstname]" disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Middle Name</label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[father_middlename]" placeholder="Optional" disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Town <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[father_town]" disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">State <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[father_state]" disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">LGA <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[father_lga]" disabled>
                                                    </div>
                                                    <div class="col-12 text-end mt-3">
                                                        <button type="button" class="btn btn-secondary btn-sm prev-step" data-prev="step-5"><i class="bi bi-arrow-left"></i> Previous</button>
                                                        <button type="button" class="btn btn-primary btn-sm next-step" data-next="step-7">Next <i class="bi bi-arrow-right"></i></button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Step 7: Mother's Details --}}
                                            <div class="wizard-step d-none" id="step-7">
                                                <h6 class="text-muted border-bottom pb-2 mb-3">Step 7/8: Mother's Details</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Surname <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[mother_surname]" disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[mother_firstname]" disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Middle Name</label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[mother_middlename]" placeholder="Optional" disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Town <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[mother_town]" disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">State <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[mother_state]" disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">LGA <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[mother_lga]" disabled>
                                                    </div>
                                                    <div class="col-12 text-end mt-3">
                                                        <button type="button" class="btn btn-secondary btn-sm prev-step" data-prev="step-6"><i class="bi bi-arrow-left"></i> Previous</button>
                                                        <button type="button" class="btn btn-primary btn-sm next-step" data-next="step-8">Next <i class="bi bi-arrow-right"></i></button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Step 8: Registration Centre --}}
                                            <div class="wizard-step d-none" id="step-8">
                                                <h6 class="text-muted border-bottom pb-2 mb-3">Step 8/8: Registration Centre</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Registration State <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[reg_state]" disabled>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Registration LGA <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[reg_lga]" disabled>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label small fw-semibold">Registration Centre <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm dob-input" name="modification_data[reg_centre]" disabled>
                                                    </div>

                                                    <div class="col-12 text-end mt-3">
                                                        <button type="button" class="btn btn-secondary btn-sm prev-step" data-prev="step-7"><i class="bi bi-arrow-left"></i> Previous</button>
                                                         <span class="text-success small fw-bold ms-2 me-2"><i class="bi bi-check-circle"></i> Ready</span>
                                                         {{-- The actual submit button for DOB flow --}}
                                                         <button type="submit" class="btn btn-success btn-sm fw-semibold">
                                                            <i class="bi bi-send-fill me-1"></i> Confirm & Submit
                                                         </button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
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

                                {{-- Submit (Generic) --}}
                                <div class="col-md-12 d-grid mt-3" id="generic-submit-btn">
                                    <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                                        <i class="bi bi-send-fill me-2"></i> Submit Request
                                    </button>
                                </div>
                                
                                {{-- Proceed to Attestation (DOB) --}}
                                <div class="col-md-12 d-grid mt-3 d-none" id="dob-proceed-btn">
                                    <button type="button" class="btn btn-primary btn-lg fw-semibold" id="proceed-attestation-btn">
                                        Proceed with Attestation <i class="bi bi-arrow-right ms-2"></i>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const serviceField = document.getElementById('service_field');
            const fieldPrice = document.getElementById('field-price');
            const fieldDescription = document.getElementById('field-description');
            
            const genericDataInfo = document.getElementById('generic-data-info');
            const genericInput = document.getElementById('description-field');
            const dobWizard = document.getElementById('dob-wizard');
            
            const genericSubmitBtn = document.getElementById('generic-submit-btn');
            const dobProceedBtn = document.getElementById('dob-proceed-btn');
            const proceedAttestationBtn = document.getElementById('proceed-attestation-btn');

            // Handle Field Change
            serviceField.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                const description = selectedOption.getAttribute('data-description');
                const fieldName = selectedOption.textContent.toLowerCase();

                // Update Price
                if (price) {
                    fieldPrice.textContent = '₦' + new Intl.NumberFormat('en-NG').format(price);
                } else {
                    fieldPrice.textContent = '₦0.00';
                }

                // Update Description
                if (description) {
                    fieldDescription.textContent = description;
                } else {
                    fieldDescription.textContent = '';
                }

                // Toggle Form Mode
                if (fieldName.includes('date of birth') || fieldName.includes('dob')) {
                    // DOB Mode
                    genericDataInfo.classList.add('d-none');
                    genericInput.removeAttribute('required');
                    
                    // Show Proceed Button, Hide Submit Button
                    genericSubmitBtn.classList.add('d-none');
                    dobProceedBtn.classList.remove('d-none');
                    
                    // Hide Wizard initially (Wait for Proceed)
                    dobWizard.classList.add('d-none');

                } else {
                    // Generic Mode
                    genericDataInfo.classList.remove('d-none');
                    genericInput.setAttribute('required', 'required');
                    
                    // Show Submit Button, Hide Proceed Button
                    genericSubmitBtn.classList.remove('d-none');
                    dobProceedBtn.classList.add('d-none');
                    
                    // Hide Wizard
                    dobWizard.classList.add('d-none');
                    disableWizardInputs();
                }
            });
            
            // Proceed Button Click
            proceedAttestationBtn.addEventListener('click', function() {
                 // Check if NIN is entered
                 const ninInput = document.querySelector('input[name="nin"]');
                 if (!ninInput.value || ninInput.value.length !== 11) {
                     alert('Please enter a valid 11-digit NIN first.');
                     ninInput.focus();
                     return;
                 }
                 
                 // Show Wizard
                 dobWizard.classList.remove('d-none');
                 enableWizardInputs();
                 
                 // Hide Proceed Button (User is now in wizard)
                 dobProceedBtn.classList.add('d-none');
                 
                 // Reset Wizard
                 document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('d-none'));
                 document.getElementById('step-1').classList.remove('d-none');
            });

            function enableWizardInputs() {
                document.querySelectorAll('.dob-input').forEach(input => {
                    // Middle names are optional
                    if (!input.name.includes('middle_name') && !input.name.includes('middlename')) {
                        input.setAttribute('required', 'required');
                    } else {
                         input.removeAttribute('required');
                    }
                    input.removeAttribute('disabled');
                });
            }

            function disableWizardInputs() {
                document.querySelectorAll('.dob-input').forEach(input => {
                    input.removeAttribute('required');
                    input.setAttribute('disabled', 'disabled');
                });
            }

            // Wizard Navigation
            document.querySelectorAll('.next-step').forEach(button => {
                button.addEventListener('click', function() {
                    const currentStep = this.closest('.wizard-step');
                    const nextStepId = this.getAttribute('data-next');
                    
                    // Validation
                    const inputs = currentStep.querySelectorAll('input, select');
                    let valid = true;
                    inputs.forEach(input => {
                        if (input.hasAttribute('required') && !input.value) {
                            valid = false;
                            input.classList.add('is-invalid');
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    });

                    if (valid) {
                        currentStep.classList.add('d-none');
                        document.getElementById(nextStepId).classList.remove('d-none');
                    } else {
                        alert('Please fill all required fields in this step.');
                    }
                });
            });

            document.querySelectorAll('.prev-step').forEach(button => {
                button.addEventListener('click', function() {
                    const currentStep = this.closest('.wizard-step');
                    const prevStepId = this.getAttribute('data-prev');
                    
                    currentStep.classList.add('d-none');
                    document.getElementById(prevStepId).classList.remove('d-none');
                });
            });
        });
    </script>
</x-app-layout>
