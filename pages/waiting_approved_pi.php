<main>
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-xl px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="check-circle"></i></div>
                            Waiting Approved PI
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <button class="btn btn-sm btn-light text-primary active me-2"><?php echo date('d'); ?></button>
                        <button class="btn btn-sm btn-light text-primary me-2"><?php echo date('F'); ?></button>
                        <button class="btn btn-sm btn-light text-primary me-2"><?php echo date('Y'); ?></button>
                        <button class="btn btn-sm btn-light text-primary"><?php echo date('H:i T'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main page content -->
    <div class="container-fluid px-4 mt-4">
        <div class="card mb-4">
            <div class="card-header">
                Waiting Approved PI - Billing Details
                <div class="float-end">
                    <button class="btn btn-success btn-sm" id="exportExcelApprovedPI">
                        <i data-feather="file-text" style="width:14px; height:14px;"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body" style="overflow-x: auto;">
                <table class="table table-striped table-hover table-bordered compact-action" id="tabelApprovedPI"
                    style="min-width: 100%; white-space: nowrap;">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>DN Number</th>
                            <th>Sub Project</th>
                            <th>Status Delivery</th>
                            <th>POD Date</th>
                            <th>Type Shipment</th>
                            <th>MOT</th>
                            <th>Date Send SCPOD</th>
                            <th>KPI Uploaded</th>
                            <th>Date Approved SCPOD</th>
                            <th>Date Send HCPOD</th>
                            <th>Date Submit PI</th>
                            <th>Due Date</th>
                            <th>Aging Days</th>
                            <th>No PI</th>
                            <th>Unit Price</th>
                            <th>BTP/BTA</th>
                            <th>Rooftop</th>
                            <th>4-WD</th>
                            <th>Langsir</th>
                            <th>Crane</th>
                            <th>Charter Boat</th>
                            <th>Total Amount</th>
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

<!-- Modal Submit Approved PI -->
<div class="modal fade" id="modalApprovedPI" tabindex="-1" aria-labelledby="modalApprovedPILabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalApprovedPILabel">
                    <i data-feather="check-circle" style="width:20px;height:20px;"></i> Submit Date Approved PI
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formApprovedPI">
                <div class="modal-body">
                    <input type="hidden" id="approved_pi_id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">DN Number</label>
                        <p class="form-control-plaintext border-bottom pb-2" id="approved_pi_dn"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_confirm_vendors" class="form-label">Date Approved PI <span class="text-danger">*</span></label>
                        <input type="date" class="form-control date-picker" id="date_confirm_vendors" name="date_confirm_vendors" placeholder="DD-MM-YYYY" required>
                        <div class="form-text">Pilih tanggal ketika PI sudah diapprove oleh vendor</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i data-feather="x" style="width:14px;height:14px;"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" style="width:14px;height:14px;"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Initialize feather icons when modal is shown
    $('#modalApprovedPI').on('shown.bs.modal', function () {
        feather.replace();
    });
</script>