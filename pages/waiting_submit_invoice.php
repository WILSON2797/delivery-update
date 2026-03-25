<main>
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-xl px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="truck"></i></div>
                            Waiting Submit INVOICE
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
                Waiting Submit Invoice - List
                <div class="float-end">
                    <button class="btn btn-success btn-sm" id="exportExcelinvoice">
                        <i data-feather="file-text" style="width:14px; height:14px;"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body" style="overflow-x: auto;">
                <table class="table table-striped table-hover table-bordered compact-action" id="tabelinvoice"
                    style="min-width: 100%; white-space: nowrap;">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>DN Number</th>
                            <th>Sub Project</th>
                            <th>POD Date</th>
                            <th>Type Shipment</th>
                            <th>MOT</th>
                            <th>Date Upload SCPOD</th>
                            <th>KPI Uploaded</th>
                            <th>Date Approved SCPOD</th>
                            <th>Date Send HCPOD</th>
                            <th>Date Submit PI</th>
                            <th>Due Date</th>
                            <th>Aging Days</th>
                            <th>No PI</th>
                            <th>Total Amount</th>
                            <th>Date PI Confirm</th>
                            <th>Status Var Vendor</th>
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
<!-- Modal EDIT - Update Invoice Information -->
<!-- ======================================== -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editModalLabel">
                    <i data-feather="file-text" style="width:20px;height:20px;"></i> 
                    Update Invoice Information - <span id="editDnNumberHeader" class="fw-bold">DN-XXXXX</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEdit">
                    <input type="hidden" id="edit_id" name="id">

                    <!-- SECTION 2: INVOICE INFORMATION (EDITABLE) -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <strong>Invoice Information</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- BARIS 1 -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Invoice Send To Customer 
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input type="date" 
                                               class="form-control date-picker" 
                                               id="edit_invoice_send_to_customer" 
                                               name="invoice_send_to_customer" placeholder="DD-MM-YYYY"
                                               required>
                                    </div>
                                
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">
                                        Invoice Number 
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-hashtag"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="edit_no_invoice_vendors" 
                                               name="no_invoice_vendors" 
                                               placeholder="INV-2024-XXXX">
                                    </div>
                                    
                                </div>

                                <!-- BARIS 2 -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i data-feather="calendar" style="width:14px;height:14px;"></i>
                                        Invoice Date
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-check"></i>
                                        </span>
                                        <input type="date" 
                                               class="form-control date-picker" 
                                               id="edit_inv_date" 
                                               name="inv_date" placeholder="DD-MM-YYYY">
                                    </div>
                                    <small class="text-muted">Tanggal invoice dibuat</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i data-feather="x" style="width:14px;height:14px;"></i> Batal
                </button>
                <button type="submit" form="formEdit" class="btn btn-primary">
                    <i data-feather="save" style="width:14px;height:14px;"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>