<x-app-layout>
    <div class="container-fluid">
          <div class="page-body">
    <div class="container-fluid">
      <div class="page-title">
        <div class="row">
          <div class="col-sm-6 col-12">
          </div>
        </div>
      </div>
    </div>
        <!-- BVN Services Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pb-0">
                        <h4 class="fw-bold text-primary">
                            <i class="fas fa-id-card me-2"></i> BVN Services
                        </h4>
                        <p class="text-muted mb-0">Comprehensive Bank Verification Number solutions</p>
                    </div>
                    
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- CRM Service -->
                            <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
                                <a href="{{route ('bvn-crm')}}" class="card service-card h-100 text-decoration-none">
                                    <div class="card-body text-center p-3">
                                        <div class="icon-wrapper bg-primary-light mb-3 mx-auto">
                                            <img src="../assets/images/apps/bvnlogo.png" alt="CRM" class="img-fluid" style="width: 40px; height: 40px;">
                                        </div>
                                        <h5 class="mb-0 fw-bold">CRM</h5>
                                        <small class="text-muted">Customer Lost BVN</small>
                                    </div>
                                </a>
                            </div>
                            
                            <!-- BVN User -->
                            <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
                                <a href="{{route ('bvn.index')}}" class="card service-card h-100 text-decoration-none">
                                    <div class="card-body text-center p-3">
                                        <div class="icon-wrapper bg-info-light mb-3 mx-auto">
                                            <img src="../assets/images/apps/bvnlogo.png" alt="BVN User" class="img-fluid" style="width: 40px; height: 40px;">
                                        </div>
                                        <h5 class="mb-0 fw-bold">BVN User</h5>
                                        <small class="text-muted">BVN Enrolment</small>
                                    </div>
                                </a>
                            </div>
                            
                            <!-- VNIN to NIBSS -->
                            <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
                                <a href="{{route ('send-vnin')}}" class="card service-card h-100 text-decoration-none">
                                    <div class="card-body text-center p-3">
                                        <div class="icon-wrapper bg-success-light mb-3 mx-auto">
                                            <img src="../assets/images/apps/bvnlogo.png" alt="VNIN to NIBSS" class="img-fluid" style="width: 40px; height: 40px;">
                                        </div>
                                        <h5 class="mb-0 fw-bold">VNIN to NIBSS</h5>
                                        <small class="text-muted">Virtual NIN Service</small>
                                    </div>
                                </a>
                            </div>
                            
                            <!-- Modification -->
                            <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
                                <a href="{{route ('modification')}}" class="card service-card h-100 text-decoration-none">
                                    <div class="card-body text-center p-3">
                                        <div class="icon-wrapper bg-warning-light mb-3 mx-auto">
                                            <img src="../assets/images/apps/bvnlogo.png" alt="Modification" class="img-fluid" style="width: 40px; height: 40px;">
                                        </div>
                                        <h5 class="mb-0 fw-bold">Modification</h5>
                                        <small class="text-muted">BVN Updates</small>
                                    </div>
                                </a>
                            </div>
                            
                            <!-- Get BVN Link -->
                            <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
                                <a href="{{route ('phone.search.index')}}" class="card service-card h-100 text-decoration-none">
                                    <div class="card-body text-center p-3">
                                        <div class="icon-wrapper bg-danger-light mb-3 mx-auto">
                                            <img src="../assets/images/apps/bvnlogo.png" alt="Get BVN Link" class="img-fluid" style="width: 40px; height: 40px;">
                                        </div>
                                        <h5 class="mb-0 fw-bold">BVN Link P/N</h5>
                                        <small class="text-muted">Phone Number Search</small>
                                    </div>
                                </a>
                            </div>
                              <!-- Other BVN Related Issue -->
                            <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
                                <a href="{{route ('enrollments.index')}}" class="card service-card h-100 text-decoration-none">
                                    <div class="card-body text-center p-3">
                                        <div class="icon-wrapper bg-danger-light mb-3 mx-auto">
                                            <img src="../assets/images/apps/bvnlogo.png" alt="Get BVN Link" class="img-fluid" style="width: 40px; height: 40px;">
                                        </div>
                                        <h5 class="mb-0 fw-bold">Enrolment Report</h5>
                                        <small class="text-muted">Bvn enrollment Report</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>