<main>
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-xl px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="check-circle"></i></div>
                            Waiting Approved SCPOD
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                    <button class="btn btn-sm btn-light text-primary active me-2" id="dayBtn"></button>
                    <button class="btn btn-sm btn-light text-primary me-2" id="monthBtn"></button>
                    <button class="btn btn-sm btn-light text-primary" id="yearBtn"></button>
                    <button class="btn btn-sm btn-light text-primary" id="timeBtn"></button>
                </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main page content -->
    <div class="container-fluid px-4 mt-4">
        <div class="card mb-4">
            <div class="card-header">
                Waiting Approval - SCPOD Details
                <div class="float-end">
                    <button class="btn btn-success btn-sm" id="exportExcelapprovedscpod">
                        <i data-feather="file-text" style="width:14px; height:14px;"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body" style="overflow-x: auto;">
                <table class="table table-striped table-hover table-bordered compact-action" id="approvedscpod"
                    style="min-width: 100%; white-space: nowrap;">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>DN Number</th>
                            <th>Sub Project</th>
                            <th>Status Shipment</th>
                            <th>POD Date</th>
                            <th>Type Shipment</th>
                            <th>MOT</th>
                            <th>Date Upload SCPOD</th>
                            <th>KPI Uploaded</th>
                            <th>Status SCPOD</th>
                            <th>Action</th>
                        </tr>
                        <tr class="table-search">
                            <th><input type="text" class="form-control form-control-sm"></th>
                            <th><input type="text" class="form-control form-control-sm"></th>
                            <th><input type="text" class="form-control form-control-sm"></th>
                            <th><input type="text" class="form-control form-control-sm"></th>
                            <th><input type="text" class="form-control form-control-sm"></th>
                            <th><input type="text" class="form-control form-control-sm"></th>
                            <th><input type="text" class="form-control form-control-sm"></th>
                            <th><input type="text" class="form-control form-control-sm"></th>
                            <th><input type="text" class="form-control form-control-sm"></th>
                            <th><input type="text" class="form-control form-control-sm"></th>
                            
                            <th class="action-column"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Modal Approve SCPOD -->
<div class="modal fade" id="modalApproveSCPOD" tabindex="-1" aria-labelledby="modalApproveSCPODLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalApproveSCPODLabel">
                    <i data-feather="check-circle"></i> Approve SCPOD
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form_approve_scpod">
                <div class="modal-body">
                    <input type="hidden" id="modal_approved_id" name="id">
                    
                    <div class="alert alert-info mb-3">
                        <i data-feather="info"></i>
                        <small>Approve dokumen SCPOD yang telah dikirim</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">DN Number</label>
                        <p class="form-control-plaintext text-primary fw-bold" id="modal_approved_dn"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_approved_sc_pod" class="form-label">
                            Date Approved SCPOD <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control date-picker" placeholder="DD-MM-YYYY" id="date_approved_sc_pod" name="date_approved_sc_pod" required>
                        <div class="form-text">
                            <i data-feather="calendar" style="width:14px;height:14px;"></i>
                            Tanggal approval SCPOD
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i data-feather="x"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i data-feather="check-circle"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Initialize feather icons when modal is shown
    $('#modalApproveSCPOD').on('shown.bs.modal', function () {
        feather.replace();
    });
</script>