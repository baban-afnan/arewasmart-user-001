<x-app-layout>
    <title>Arewa Smart - Open Support Ticket</title>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">Open New Ticket</h3>
                        <p class="text-muted small mb-0">Submit your complaint or inquiry.</p>
                    </div>
                    <div class="col-sm-6 col-12 text-end">
                        <a href="{{ route('support.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Ticket Information</h5>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('support.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control" placeholder="Briefly describe the issue" required value="{{ old('subject') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                                    <textarea name="message" class="form-control" rows="6" placeholder="Detailed explanation of your issue..." required>{{ old('message') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Attachment (Optional)</label>
                                    <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="text-muted">Max size: 2MB. Supported formats: JPG, PNG, PDF.</small>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Submit Ticket</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
