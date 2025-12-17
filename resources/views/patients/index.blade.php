@extends('layouts.dashboard')

@section('title', 'Patients')
@section('page-title', 'Patients')
@section('page-i18n', 'patients')

@section('content')
<!-- Action Toolbar -->
<div class="action-toolbar d-flex gap-3 flex-wrap align-items-center mb-4 fade-in">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="patientSearch" class="form-control" placeholder="Search patients..."
            data-i18n-placeholder="searchPatients">
    </div>

    <button class="btn btn-light btn-sm d-inline-flex align-items-center gap-2" data-action="export-csv">
        <i class="fas fa-file-csv"></i> CSV
    </button>

    <button class="btn btn-light btn-sm d-inline-flex align-items-center gap-2" data-action="export-json">
        <i class="fas fa-file-code"></i> JSON
    </button>

    <button class="btn btn-primary d-flex align-items-center gap-2 ms-auto" onclick="patientsManager.add()">
        <i class="fas fa-plus"></i>
        <span data-i18n="addPatient">Add Patient</span>
    </button>
</div>

<!-- Patients Table -->
<div class="card fade-in">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 sortable-header" data-sort="id" style="width: 50px;">
                            # <i class="fas fa-sort"></i>
                        </th>
                        <th class="py-3 sortable-header" data-sort="name">
                            <span data-i18n="patientName">Patient Name</span> <i class="fas fa-sort"></i>
                        </th>
                        <th class="py-3 sortable-header" data-sort="age">
                            <span data-i18n="age">Age</span> <i class="fas fa-sort"></i>
                        </th>
                        <th class="py-3 sortable-header" data-sort="gender">
                            <span data-i18n="gender">Gender</span> <i class="fas fa-sort"></i>
                        </th>
                        <th class="py-3" data-i18n="phone">Phone Number</th>
                        <th class="py-3" data-i18n="address">Address</th>
                        <th class="pe-4 py-3 text-center" data-i18n="actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="patientsTableBody">
                    <!-- Injected via JS -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-controls">
            <div class="pagination-info">
                Showing <strong>1-10</strong> of <strong>0</strong> patients
            </div>
            <div class="pagination-buttons">
                <button data-action="prev-page" disabled>
                    <i class="fas fa-chevron-left"></i> <span data-i18n="previous">Previous</span>
                </button>
                <button data-action="next-page" disabled>
                    <span data-i18n="next">Next</span> <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Patient Modal -->
<div class="modal fade" id="patientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-glass border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="patientModalTitle" data-i18n="addPatient">Add Patient
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="patientForm">
                    <input type="hidden" id="patientId">
                    <div class="mb-3">
                        <label class="form-label" data-i18n="fullName">Full Name</label>
                        <input type="text" class="form-control" id="patientName" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="age">Age</label>
                            <input type="number" class="form-control" id="patientAge" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" data-i18n="gender">Gender</label>
                            <select class="form-select" id="patientGender" required>
                                <option value="male" data-i18n="male">Male</option>
                                <option value="female" data-i18n="female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" data-i18n="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="patientPhone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" data-i18n="address">Address</label>
                        <textarea class="form-control" id="patientAddress" rows="2" required></textarea>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary" data-i18n="save">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-glass border-0">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-exclamation-circle text-danger display-1 pulse-anim"></i>
                </div>
                <h4 class="fw-bold mb-2" data-i18n="areYouSure">Are you sure?</h4>
                <p class="text-muted mb-4" data-i18n="deletePatientConfirmation">Do you really want to
                    delete this patient?
                    This process cannot be undone.</p>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                        data-i18n="cancel">Cancel</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn"
                        data-i18n="delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Patient History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-glass border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-history me-2 text-primary"></i>
                    <span data-i18n="patientHistory">Patient History</span>
                    <span id="historyPatientName" class="text-secondary ms-2"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="historyContent">
                    <!-- History content will be injected here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/patients.js') }}"></script>
@endsection
