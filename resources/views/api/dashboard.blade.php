<x-app-layout>
    <title>Arewa Smart - {{ $title ?? 'Api Services' }}</title>
    
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">Api Services</h3>
                        <p class="text-muted small mb-0">Get the best API services for your business.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0 header-elements">
                            <div class="card-header-elements ms-auto">
                                @if(isset($application) && $application)
                                    @php
                                        $statusColor = match($application->status) {
                                            'approved' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">
                                        Application Status: {{ ucfirst($application->status) }} check your email for instructions
                                    </span>
                                @else
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#apiApplyModal">
                                        Apply for API Access
                                    </button>
                                @endif
                            </div>
                        </div>
                        
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="text-nowrap table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Service Name</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($service)
                                            <tr>
                                                <td>{{ $service->name }}</td>
                                                <td>{{ Str::limit($service->description, 50) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $service->is_active ? 'success' : 'danger' }}">
                                                        {{ $service->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="p-0">
                                                    <div class="p-3 bg-lighter">
                                                        <h6>Available Services</h6>
                                                        <table class="table table-sm table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>S/N</th>
                                                                    <th>Service API</th>
                                                                    <th>Your Price ({{ ucfirst($userRole) }})</th>
                                                                    <th>Description</th>
                                                                    <th>Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($fields as $index => $field)
                                                                    <tr>
                                                                        <td>{{ $fields->firstItem() + $index }}</td> 
                                                                        <td>{{ $field->field_name }}</td>
                                                                        <td>{{ number_format($field->getPriceForUserType($userRole), 2) }}</td>
                                                                        <td>{{ $field->description }}</td>
                                                                        <td>{{ $field->is_active ? 'Active' : 'Inactive' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                                @if($fields->isEmpty())
                                                                    <tr>
                                                                        <td colspan="5" class="text-center">No specific services available.</td>
                                                                    </tr>
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-center">No API services available at the moment.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                             <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-3">
                                {{ $fields->links('vendor.pagination.custom') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Apply Modal -->
    <div class="modal fade" id="apiApplyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="apiApplyModalLabel">Apply for API Access</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('api.apply') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="business_name" class="form-label">Business Name</label>
                                <input type="text" id="business_name" name="business_name" class="form-control" placeholder="My Business Name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="website_link" class="form-label">Website Link</label>
                                <input type="url" id="website_link" name="website_link" class="form-control" placeholder="https://yourbusiness.com" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="business_nature" class="form-label">Nature of Business</label>
                                <input type="text" id="business_nature" name="business_nature" class="form-control" placeholder="e.g. VTU Portal, Fintech App" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="business_description" class="form-label">Business Description</label>
                                <textarea id="business_description" name="business_description" class="form-control" rows="3" placeholder="Describe your business model and API usage..." required></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#">API Terms and Conditions</a>.
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>