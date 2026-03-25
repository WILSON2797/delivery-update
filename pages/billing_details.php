<main>
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-xl px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="truck"></i></div>
                            Billing Details Report
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
                Handover Done - Billing Details
                <div class="float-end">
                    <button class="btn btn-success btn-sm" id="exportExcelbilling">
                        <i data-feather="file-text" style="width:14px; height:14px;"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body" style="overflow-x: auto;">
                <table class="table table-striped table-hover table-bordered compact-action" id="tabelbilling"
                    style="min-width: 100%; white-space: nowrap;">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>DN Number</th>
                            <th>Sub Project</th>
                            <th>POD Date</th>
                            <th>Type Shipment</th>
                            <th>MOT</th>
                            <th>Status</th>
                            <th>Date Send SCPOD</th>
                            <th>KPI Uploaded</th>
                            <th>Date Approved SCPOD</th>
                            <th>Date Send HCPOD</th>
                            <th>Date Submit PI</th>
                            <th>Due Date</th>
                            <th>Aging Days</th>
                            <th>No PI</th>
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
                            <th class="action-column"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- ======================================== -->
<!-- Modal EDIT - Billing Details -->
<!-- ======================================== -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editModalLabel">
                    <i data-feather="edit" style="width:20px;height:20px;"></i> 
                    Edit Billing Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEdit">
                    <input type="hidden" id="edit_id" name="id">

                    <!-- SECTION 1: DATE INFORMATION -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>📅 Date Information</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- BARIS 1 -->
                                <div class="col-md-6">
                                    <label class="form-label">Date Send SCPOD</label>
                                    <input type="date" class="form-control" id="edit_date_send_sc_pod" name="date_send_sc_pod">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date Approved SCPOD</label>
                                    <input type="date" class="form-control" id="edit_date_approved_sc_pod" name="date_approved_sc_pod">
                                </div>

                                <!-- BARIS 2 -->
                                <div class="col-md-6">
                                    <label class="form-label">Date Send HCPOD</label>
                                    <input type="date" class="form-control" id="edit_date_send_hc_pod" name="date_send_hc_pod">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date Submit PI <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="edit_date_submit_pi" name="date_submit_pi" required>
                                </div>

                                <!-- BARIS 3 -->
                                <div class="col-md-6">
                                    <label class="form-label">Date Confirm Vendors (Date Approved PI)</label>
                                    <input type="date" class="form-control" id="edit_date_confirm_vendors" name="date_confirm_vendors">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No PI</label>
                                    <input type="text" class="form-control" id="edit_no_pi" name="no_pi" placeholder="Enter PI number">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: COST INFORMATION -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>💰 Cost Information</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- BARIS 1 -->
                                <div class="col-md-4">
                                    <label class="form-label">Unit Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control rupiah-input" id="edit_unit_price" name="unit_price" placeholder="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">BTP / BTA</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control rupiah-input" id="edit_btp_bta" name="btp_bta" placeholder="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Rooftop</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control rupiah-input" id="edit_rooftop" name="rooftop" placeholder="0">
                                    </div>
                                </div>

                                <!-- BARIS 2 -->
                                <div class="col-md-4">
                                    <label class="form-label">4WD</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control rupiah-input" id="edit_4wd" name="4wd" placeholder="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Langsir</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control rupiah-input" id="edit_langsir" name="langsir" placeholder="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Crane</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control rupiah-input" id="edit_crane" name="crane" placeholder="0">
                                    </div>
                                </div>

                                <!-- BARIS 3 -->
                                <div class="col-md-6">
                                    <label class="form-label">Charter Boat</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control rupiah-input" id="edit_charter_boat" name="charter_boat" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i data-feather="x" style="width:14px;height:14px;"></i> Cancel
                </button>
                <button type="submit" form="formEdit" class="btn btn-primary">
                    <i data-feather="save" style="width:14px;height:14px;"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>