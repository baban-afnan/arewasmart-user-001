<x-app-layout>
    <x-slot name="header">
        Profile Settings
    </x-slot>

    <div class="container-fluid py-4">
        
        <!-- Alerts -->
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">

            <!-- LEFT COLUMN -->
            <div class="col-lg-4 mb-4">

                <!-- Profile Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <!-- Header Background -->
                    <div class="card-header position-relative border-0" style="height: 120px; background: linear-gradient(45deg, #e93c11ff, #e69b10ff);">
                    </div>

                    <div class="card-body text-center pt-0">
                        <!-- Profile Photo with Edit Button -->
                        <div class="position-relative d-inline-block mb-3" style="margin-top: -60px;">
                            <img src="{{ $user->photo ? asset($user->photo) : asset('assets/img/profiles/avatar-01.jpg') }}"
                                 alt="Profile Photo"
                                 class="rounded-circle img-thumbnail shadow-lg bg-white"
                                 style="width:120px;height:120px;object-fit:cover; border: 4px solid #fff; cursor: pointer;"
                                 data-bs-toggle="modal" data-bs-target="#photoModal"
                                 title="Click to update photo">
                        </div>

                        <h5 class="fw-bold mb-1 text-dark">{{ $user->first_name }} {{ $user->last_name }}</h5>
                        <p class="text-muted mb-2 small">{{ $user->email }}</p>
                         <p class="text-muted mb-2 small">{{ $user->limit }}</p>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                            {{ ucfirst($user->role ?? 'User') }}
                        </span>

                        <hr class="my-4 opacity-10">

                        <!-- Reset Buttons -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary fw-semibold rounded-pill" data-bs-toggle="modal" data-bs-target="#passwordModal">
                                <i class="ti ti-lock me-1"></i> Reset Password
                            </button>

                            <button class="btn btn-outline-danger fw-semibold rounded-pill" data-bs-toggle="modal" data-bs-target="#pinModal">
                                <i class="ti ti-key me-1"></i> Reset PIN
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-lg-8">

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-primary border-0 py-3 px-4">
                        <h5 class="fw-bold mb-0 text-white"><i class="ti ti-user-circle me-2"></i>User Information</h5>
                    </div>

                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <tbody>
                                    <tr class="border-bottom">
                                        <th class="text-muted small text-uppercase py-3" style="width: 35%">First Name</th>
                                        <td class="fw-semibold py-3">{{ $user->first_name }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="text-muted small text-uppercase py-3">Last Name</th>
                                        <td class="fw-semibold py-3">{{ $user->last_name }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="text-muted small text-uppercase py-3">Middle Name</th>
                                        <td class="fw-semibold py-3">{{ $user->middle_name ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="text-muted small text-uppercase py-3">Email</th>
                                        <td class="fw-semibold py-3">{{ $user->email }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="text-muted small text-uppercase py-3">Phone Number</th>
                                        <td class="fw-semibold py-3">{{ $user->phone_no }}</td>
                                    </tr>
                                   
                                    <tr class="border-bottom">
                                        <th class="text-muted small text-uppercase py-3">Business Name</th>
                                        <td class="fw-semibold py-3">{{ $user->business_name ?? 'Not Provided' }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="text-muted small text-uppercase py-3">State</th>
                                        <td class="fw-semibold py-3">{{ $user->state ?? 'Not Provided' }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="text-muted small text-uppercase py-3">LGA</th>
                                        <td class="fw-semibold py-3">{{ $user->lga ?? 'Not Provided' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted small text-uppercase py-3">Address</th>
                                        <td class="fw-semibold py-3">{{ $user->address ?? 'Not Provided' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- MODALS -->
    
    <!-- Photo Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="ti ti-camera me-2"></i>Update Profile Photo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('profile.photo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4 text-center">
                        <div class="mb-4">
                            <div class="rounded-circle bg-primary-subtle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="ti ti-cloud-upload text-primary display-6"></i>
                            </div>
                        </div>
                        <h6 class="fw-bold mb-2">Select a new photo</h6>
                        <p class="text-muted small mb-4">Supported formats: JPG, PNG, WEBP. Max size: 2MB.</p>
                        
                        <input type="file" name="photo" class="form-control" accept="image/*" required>
                    </div>
                    <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Upload Photo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Password Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="ti ti-lock me-2"></i>Reset Password</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="ti ti-lock-open"></i></span>
                                <input type="password" name="current_password" class="form-control border-start-0 ps-0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="ti ti-lock"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="ti ti-lock"></i></span>
                                <input type="password" name="password_confirmation" class="form-control border-start-0 ps-0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- PIN Modal -->
    <div class="modal fade" id="pinModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="ti ti-key me-2"></i>Reset Transaction PIN</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('profile.pin') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                            <i class="ti ti-alert-triangle me-2 fs-4"></i>
                            <div class="small">This PIN is used to authorize transactions. Keep it safe!</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="ti ti-lock-open"></i></span>
                                <input type="password" name="current_password" class="form-control border-start-0 ps-0" required placeholder="Enter login password">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New PIN (5 digits)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="ti ti-dialpad"></i></span>
                                <input type="password" name="pin" maxlength="5" pattern="\d{5}" class="form-control border-start-0 ps-0" required placeholder="*****">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm PIN</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="ti ti-dialpad"></i></span>
                                <input type="password" name="pin_confirmation" maxlength="5" pattern="\d{5}" class="form-control border-start-0 ps-0" required placeholder="*****">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Update PIN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
