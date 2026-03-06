<x-app-layout>
    <x-slot name="header">
        Profile Settings
    </x-slot>

    <!-- Custom CSS for this page -->
    <style>
        .profile-header-bg {
            background: linear-gradient(135deg, rgba(233,60,17,1) 0%, rgba(230,155,16,1) 100%);
            height: 140px;
            border-radius: 1rem 1rem 0 0;
            position: relative;
        }
        .profile-avatar-wrapper {
            margin-top: -70px;
            position: relative;
            z-index: 2;
        }
        .profile-avatar {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
            background-color: #fff;
        }
        .profile-avatar:hover {
            transform: scale(1.05);
        }
        .avatar-edit-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: #e93c11;
            color: white;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid #fff;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
            transition: background 0.2s ease;
        }
        .avatar-edit-badge:hover {
            background: #cd320c;
        }
        .info-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            height: 100%;
        }
        .info-card:hover {
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
            border-left-color: #e93c11;
            transform: translateY(-2px);
        }
        .icon-circle {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(233,60,17,0.1);
            color: #e93c11;
            font-size: 1.25rem;
        }
        .settings-btn {
            border-radius: 0.75rem;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            transition: all 0.2s ease;
            background-color: #f8f9fa;
        }
        .settings-btn:hover {
            transform: translateY(-1px);
            background-color: #eef1f5;
        }
        .modal-content-custom {
            border: none;
            border-radius: 1.25rem;
            overflow: hidden;
        }
        .modal-header-custom {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.5rem 1.5rem 1rem;
            background-color: #f8f9fa;
        }
        .modal-footer-custom {
            border-top: none;
            padding: 1rem 1.5rem 1.5rem;
        }
        .input-group-custom .input-group-text {
            border-right: none;
            background-color: transparent;
            color: #6c757d;
        }
        .input-group-custom .form-control {
            border-left: none;
            padding-left: 0;
        }
        .input-group-custom .form-control:focus {
            box-shadow: none;
            border-color: #dee2e6;
        }
        .input-group-custom:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(233, 60, 17, 0.25);
            border-radius: 0.375rem;
        }
        .input-group-custom:focus-within .input-group-text,
        .input-group-custom:focus-within .form-control {
            border-color: #e93c11;
        }
        .badge-custom {
            background: rgba(233,60,17,0.1);
            color: #e93c11;
        }
        .text-c-primary {
            color: #e93c11;
        }
    </style>

    <!-- Cropper.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

    <div class="container-fluid py-4">
        
        <!-- Alerts -->
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 bg-success text-white d-flex align-items-center" role="alert">
                <i class="ti ti-check-circle fs-4 me-2"></i>
                <div>
                    <strong>Success!</strong> {{ session('status') }}
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 bg-danger text-white d-flex align-items-center" role="alert">
                <i class="ti ti-alert-triangle fs-4 me-2"></i>
                <div>
                    <strong>Error!</strong> {{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 bg-danger text-white d-flex align-items-start" role="alert">
                <i class="ti ti-alert-circle fs-4 me-2 mt-1"></i>
                <div>
                    <strong class="d-block mb-1">Please fix the following errors:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">

            <!-- LEFT COLUMN -->
            <div class="col-xl-4 col-lg-5 mb-4">
                <!-- Profile Card -->
                <div class="card border-0 shadow-sm rounded-4 text-center overflow-hidden">
                    <!-- Header Background -->
                    <div class="profile-header-bg">
                        <div style="background: url('{{ asset('assets/img/pattern.png') }}') repeat; opacity: 0.1; width: 100%; height: 100%; position: absolute; top:0; left:0;"></div>
                    </div>

                    <div class="card-body pt-0 pb-4 px-4">
                        <!-- Profile Photo with Edit Button -->
                        <div class="profile-avatar-wrapper text-center">
                            <div class="position-relative d-inline-block" style="width: 140px; height: 140px;">
                                <img src="{{ $user->photo ? (str_starts_with($user->photo, 'http') ? $user->photo : asset($user->photo)) : asset('assets/img/profiles/avatar-01.jpg') }}"
                                     alt="Profile Photo"
                                     class="rounded-circle profile-avatar shadow-sm w-100 h-100">
                                <div class="avatar-edit-badge" data-bs-toggle="modal" data-bs-target="#photoModal" title="Update Photo">
                                    <i class="ti ti-camera"></i>
                                </div>
                            </div>
                        </div>

                        <h4 class="fw-bold mt-3 mb-1 text-dark">{{ $user->first_name }} {{ $user->last_name }}</h4>
                        <div class="text-muted small mb-3 d-flex align-items-center justify-content-center">
                            <i class="ti ti-mail me-1"></i> {{ $user->email }}
                        </div>
                        
                        <div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
                            <span class="badge badge-custom rounded-pill px-3 py-2 fw-semibold border border-white shadow-sm">
                                <i class="ti ti-shield-check me-1"></i> {{ ucfirst($user->role ?? 'User') }}
                            </span>
                            @if($user->limit)
                            <span class="badge bg-dark rounded-pill px-3 py-2 fw-semibold text-white border border-white shadow-sm" title="Daily Limit">
                                <i class="ti ti-wallet me-1"></i> ₦{{ number_format((float)$user->limit, 2) }} Limit
                            </span>
                            @endif
                        </div>

                        <hr class="border-light mb-4">

                        <!-- Action Buttons -->
                        <div class="d-flex flex-column gap-3">
                            <button class="btn btn-light settings-btn text-start d-flex align-items-center justify-content-between border" data-bs-toggle="modal" data-bs-target="#passwordModal">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-white shadow-sm d-inline-flex me-3" style="width: 38px; height: 38px;">
                                        <i class="ti ti-lock"></i>
                                    </div>
                                    <span class="text-dark fw-medium">Change Password</span>
                                </div>
                                <i class="ti ti-chevron-right text-muted"></i>
                            </button>

                            <button class="btn btn-light settings-btn text-start d-flex align-items-center justify-content-between border" data-bs-toggle="modal" data-bs-target="#pinModal">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-white shadow-sm d-inline-flex me-3 text-danger" style="width: 38px; height: 38px;">
                                        <i class="ti ti-key"></i>
                                    </div>
                                    <span class="text-dark fw-medium">Reset Transaction PIN</span>
                                </div>
                                <i class="ti ti-chevron-right text-muted"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-xl-8 col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom border-light py-4 px-4 d-flex align-items-center">
                        <div class="icon-circle me-3 shadow-sm bg-primary text-white" style="width: 45px; height: 45px; background: #e93c11 !important;">
                            <i class="ti ti-user-scan"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Personal Information</h4>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Info Items -->
                            <div class="col-12">
                                <div class="info-card p-3 rounded-3 bg-light border">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-c-primary fs-3"><i class="ti ti-user"></i></div>
                                        <div>
                                            <div class="text-muted small text-uppercase fw-semibold mb-1">Full Name</div>
                                            <div class="fw-bold text-primary fs-15">{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-card p-3 rounded-3 bg-light border">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-c-primary fs-3"><i class="ti ti-mail"></i></div>
                                        <div>
                                            <div class="text-muted small text-uppercase fw-semibold mb-1">Email Address</div>
                                            <div class="fw-bold text-primary fs-15">{{ $user->email ?: 'Not Provided' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-card p-3 rounded-3 bg-light border">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-c-primary fs-3"><i class="ti ti-phone"></i></div>
                                        <div>
                                            <div class="text-muted small text-uppercase fw-semibold mb-1">Phone Number</div>
                                            <div class="fw-bold text-primary fs-15">{{ $user->phone_no ?: 'Not Provided' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-card p-3 rounded-3 bg-light border">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-c-primary fs-3"><i class="ti ti-briefcase"></i></div>
                                        <div>
                                            <div class="text-muted small text-uppercase fw-semibold mb-1">Business Name</div>
                                            <div class="fw-bold text-primary fs-15">{{ $user->business_name ?: 'Not Provided' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-card p-3 rounded-3 bg-light border">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-c-primary fs-3"><i class="ti ti-map-pin"></i></div>
                                        <div>
                                            <div class="text-muted small text-uppercase fw-semibold mb-1">Location Details</div>
                                            <div class="fw-bold text-primary fs-15">{{ $user->state ? $user->state . ' State' : 'State Not Provided' }}</div>
                                            <div class="text-muted small mt-1">{{ $user->lga ? 'LGA: ' . $user->lga : 'LGA Not Provided' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="info-card p-3 rounded-3 bg-light border">
                                    <div class="d-flex align-items-start">
                                        <div class="mt-1 me-3 text-c-primary fs-3"><i class="ti ti-home"></i></div>
                                        <div>
                                            <div class="text-muted small text-uppercase fw-semibold mb-1">Full Address</div>
                                            <div class="fw-bold text-primary fs-15" style="line-height: 1.5;">{{ $user->address ?: 'Address not provided. Please update your profile.' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dedicated Contact Officer Section -->
                            <div class="col-12 mt-4">
                                <div class="p-4 rounded-3 border position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(25, 135, 84, 0.05) 0%, rgba(25, 135, 84, 0.15) 100%); border-color: #198754 !important;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 position-relative z-1">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle shadow-sm bg-success text-white me-3" style="width: 50px; height: 50px;">
                                                <i class="ti ti-brand-whatsapp fs-2"></i>
                                            </div>
                                            <div>
                                                <h5 class="fw-bold text-dark mb-1">Need help with your profile?</h5>
                                                <p class="text-muted mb-0 small">Get in touch with our dedicated Account Officer directly on WhatsApp.</p>
                                            </div>
                                        </div>
                                        <a href="https://wa.me/2348064333983" target="_blank" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm text-nowrap">
                                            <i class="ti ti-brand-whatsapp me-2"></i> Contact Account Officer
                                        </a>
                                    </div>
                                    <!-- Decorative Icon -->
                                    <i class="ti ti-brand-whatsapp position-absolute text-success" style="font-size: 150px; right: -20px; bottom: -40px; opacity: 0.1; z-index: 0;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS -->
    
    <!-- Photo Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom shadow">
                <div class="modal-header modal-header-custom align-items-center">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                        <div class="icon-circle me-2 bg-primary-subtle text-primary" style="width: 32px; height: 32px; font-size: 1rem;">
                            <i class="ti ti-camera"></i>
                        </div>
                        Update Profile Photo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="photoUploadForm" action="{{ route('profile.photo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4 text-center">
                        <div class="mb-4 d-none d-md-block" id="uploadPlaceholder">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: rgba(233,60,17,0.1);">
                                <i class="ti ti-cloud-upload text-c-primary display-6"></i>
                            </div>
                            <h6 class="fw-bold mt-2 mb-1">Select a new photo</h6>
                            <p class="text-muted small mb-0">Supported formats: JPG, PNG, WEBP. Max size: 2MB.</p>
                        </div>
                        
                        <input type="file" id="photoInput" name="photo" class="form-control mb-3" accept="image/*" required>

                        <!-- Cropper Container -->
                        <div id="cropperContainer" class="d-none mt-3" style="max-height: 400px; width: 100%; overflow: hidden;">
                            <img id="cropperImage" src="" style="max-width: 100%; display: block;">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="cropAndUploadBtn" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background-color: #e93c11; border-color: #e93c11;" disabled>Crop & Upload Photo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Password Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom shadow">
                <div class="modal-header modal-header-custom align-items-center">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                        <div class="icon-circle me-2 bg-primary-subtle text-primary" style="width: 32px; height: 32px; font-size: 1rem;">
                            <i class="ti ti-lock"></i>
                        </div>
                        Reset Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Current Password</label>
                            <div class="input-group input-group-custom border rounded-3 overflow-hidden">
                                <span class="input-group-text"><i class="ti ti-lock-open"></i></span>
                                <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">New Password</label>
                            <div class="input-group input-group-custom border rounded-3 overflow-hidden">
                                <span class="input-group-text"><i class="ti ti-lock"></i></span>
                                <input type="password" name="password" class="form-control" required placeholder="Enter new password">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Confirm Password</label>
                            <div class="input-group input-group-custom border rounded-3 overflow-hidden">
                                <span class="input-group-text"><i class="ti ti-lock"></i></span>
                                <input type="password" name="password_confirmation" class="form-control" required placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-custom bg-light">
                        <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background-color: #e93c11; border-color: #e93c11;">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- PIN Modal -->
    <div class="modal fade" id="pinModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom shadow">
                <div class="modal-header modal-header-custom align-items-center">
                    <h5 class="modal-title fw-bold text-danger d-flex align-items-center">
                        <div class="icon-circle me-2 bg-danger-subtle text-danger" style="width: 32px; height: 32px; font-size: 1rem;">
                            <i class="ti ti-key"></i>
                        </div>
                        Reset Transaction PIN
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('profile.pin') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-warning d-flex align-items-center mb-4 rounded-3 border-0 bg-warning-subtle text-dark" role="alert">
                            <i class="ti ti-alert-triangle fs-3 text-warning me-3"></i>
                            <div class="small fw-medium">This PIN is used to authorize your transactions. Keep it safe and secure!</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Current Password</label>
                            <div class="input-group input-group-custom border rounded-3 overflow-hidden">
                                <span class="input-group-text"><i class="ti ti-lock-open"></i></span>
                                <input type="password" name="current_password" class="form-control" required placeholder="Enter login password">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">New PIN (5 digits)</label>
                            <div class="input-group input-group-custom border rounded-3 overflow-hidden">
                                <span class="input-group-text"><i class="ti ti-dialpad"></i></span>
                                <input type="password" name="pin" maxlength="5" pattern="\d{5}" class="form-control" required placeholder="*****" inputmode="numeric">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Confirm PIN</label>
                            <div class="input-group input-group-custom border rounded-3 overflow-hidden">
                                <span class="input-group-text"><i class="ti ti-dialpad"></i></span>
                                <input type="password" name="pin_confirmation" maxlength="5" pattern="\d{5}" class="form-control" required placeholder="*****" inputmode="numeric">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-custom bg-light">
                        <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">Update PIN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>

<!-- Cropper.js Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let cropper;
        const photoInput = document.getElementById('photoInput');
        const cropperImage = document.getElementById('cropperImage');
        const cropperContainer = document.getElementById('cropperContainer');
        const uploadBtn = document.getElementById('cropAndUploadBtn');
        const form = document.getElementById('photoUploadForm');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');

        // Initialize Cropper when file is selected
        photoInput.addEventListener('change', function (e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                const url = URL.createObjectURL(file);
                
                cropperImage.src = url;
                cropperContainer.classList.remove('d-none');
                if(uploadPlaceholder) uploadPlaceholder.classList.add('d-none');
                uploadBtn.disabled = false;

                if (cropper) {
                    cropper.destroy();
                }

                cropper = new Cropper(cropperImage, {
                    aspectRatio: 1, // Enforce 1:1 aspect ratio ideal for circular profile pictures
                    viewMode: 1,    // Restrict the crop box to not exceed the size of the canvas
                    autoCropArea: 0.8,
                });
            }
        });

        // Add circle overlay CSS for Cropper to visualize the crop area as a circle
        const style = document.createElement('style');
        style.innerHTML = `
            .cropper-view-box,
            .cropper-face {
              border-radius: 50%;
            }
        `;
        document.head.appendChild(style);

        // Handle the crop and upload action
        uploadBtn.addEventListener('click', function () {
            if (!cropper) return;

            // Show loading state
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Uploading...';
            this.disabled = true;

            // Get the cropped canvas
            cropper.getCroppedCanvas({
                width: 400,
                height: 400,
            }).toBlob((blob) => {
                const formData = new FormData(form);
                // Override the chosen file with the cropped blob representation
                formData.set('photo', blob, 'profile.jpg');

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        throw new Error('Upload failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during upload. Please try again.');
                    this.innerHTML = 'Crop & Upload Photo';
                    this.disabled = false;
                });
            }, 'image/jpeg', 0.9);
        });

        // Reset the modal when it gets closed
        const photoModal = document.getElementById('photoModal');
        if(photoModal) {
            photoModal.addEventListener('hidden.bs.modal', function () {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                photoInput.value = '';
                cropperContainer.classList.add('d-none');
                cropperImage.src = '';
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = 'Crop & Upload Photo';
                if(uploadPlaceholder) uploadPlaceholder.classList.remove('d-none');
            });
        }
    });
</script>
