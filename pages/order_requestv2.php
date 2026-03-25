<?php
session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$_SESSION['last_activity'] = time();

$role = $_SESSION['role'] ?? 'user';
date_default_timezone_set('Asia/Jakarta');
?>

<main>
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-xl px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="truck"></i></div>
                            Daily Delivery Report
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
                Daily Delivery Report Table
                <div class="float-end">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addReportModal">
                        <i data-feather="plus" style="width: 14px; height: 14px;"></i> Add New
                    </button>
                    <button class="btn btn-success btn-sm" id="exportExcelReport">
                        <i data-feather="file-text" style="width:14px; height:14px;"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body" style="overflow-x: auto;">
                <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-striped table-hover table-bordered compact-action" id="tabelDailyReport"
                    style="min-width: 100%; white-space: nowrap;">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Date Request</th>
                            <th>Driver Name</th>
                            <th>Phone</th>
                            <th>Nopol</th>
                            <th>DN Number</th>
                            <th>Site ID</th>
                            <th>Sub Project</th>
                            <th>Plan From</th>
                            <th>Destination City</th>
                            <th>Destination Province</th>
                            <th>Subcon</th>
                            <th>MOT</th>
                            
                            <th>Latest Status</th>
                            <th>Status</th>
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
                            
                            <th class="action-column"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
</main>

<!-- ======================================== -->
<!-- Modal Tambah Daily Report (LENGKAP SEMUA FIELD) -->
<!-- ======================================== -->
<div class="modal fade" id="addReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addReportModalLabel">
                    <i data-feather="plus-circle" style="width:20px;height:20px;"></i> 
                    Tambah Daily Delivery Report
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addReportForm">
                    <!-- SECTION 1: INFORMASI DASAR -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>📋 Informasi Dasar</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Date Request <span class="text-danger">*</span></label>
                                    <input type="date" name="date_request" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">DN Number <span class="text-danger">*</span></label>
                                    <input type="text" name="dn_number" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sub Project</label>
                                    <input type="text" name="sub_project" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site ID</label>
                                    <input type="text" name="site_id" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: LOKASI & TUJUAN -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>📍 Lokasi & Tujuan</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Plan From</label>
                                    <select name="plan_from" class="form-control" required>
                                        <option value="">-- Pilih Plan --</option>
                                        <option value="JKBS">JKBS</option>
                                        <option value="TCL">TLC</option>
                                        <option value="PT.Fanah Jaya Maindo">PT. Fanah Jaya Maindo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Destination City</label>
                                    <input type="text" name="destination_city" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Destination Province</label>
                                    <input type="text" name="destination_province" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">latitude</label>
                                    <input type="text" name="latitude" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">longitude</label>
                                    <input type="text" name="longitude" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Destination Address</label>
                                    <textarea name="destination_address" class="form-control" rows="2" placeholder="Alamat lengkap tujuan" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: SCHEDULE & SLA -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>📅 Schedule & SLA</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">RSD (Requested Delivery Date)</label>
                                    <input type="date" name="rsd" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">RAD (Actual Delivery Date)</label>
                                    <input type="date" name="rad" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">SLA (hari)</label>
                                    <input type="number" name="sla" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: TIMELINE TRACKING -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>⏱️ Timeline Tracking</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">Truck on Warehouse</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="truck_on_warehouse"
                                            class="form-control datetime-picker"
                                            placeholder="YYYY-MM-DD HH:mm"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">(Date & Time mover dispatch from Whs)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="atd_whs_dispatch"
                                            class="form-control datetime-picker"
                                            placeholder="YYYY-MM-DD HH:mm"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">(Date & Time mover dispatch from pool)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="atd_pool_dispatch"
                                            class="form-control datetime-picker"
                                            placeholder="YYYY-MM-DD HH:mm"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">ATA Mover on Site</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="ata_mover_on_site"
                                            class="form-control datetime-picker"
                                            placeholder="YYYY-MM-DD HH:mm"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Receiver on Site DateTime</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="receiver_on_site_datetime"
                                            class="form-control datetime-picker"
                                            placeholder="YYYY-MM-DD HH:mm"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">POD DateTime</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="pod_datetime"
                                            class="form-control datetime-picker"
                                            placeholder="YYYY-MM-DD HH:mm"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>


                    <!-- SECTION 5: SHIPMENT DETAILS -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>📦 Shipment Details</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Volume (m³)</label>
                                    <input type="number" step="0.00001" name="volume" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gross Weight (kg)</label>
                                    <input type="number" step="0.00001" name="gross_weight" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Type Shipment</label>
                                    <input type="text" name="type_shipment" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">Pilih Status</option>
                                        <option value="Done Pickup At WH">Done Pickup At WH</option>
                                        <option value="Pool Mover">Pool Mover</option>
                                        <option value="On Delivery">On Delivery</option>
                                        <option value="Onsite">Onsite</option>
                                        <option value="Back To Pool">Back To Pool</option>
                                        <option value="Handover Done">Handover Done</option>
                                        
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">POD Type</label>
                                    <select name="pod_type" class="form-select">
                                        <option value="">Pilih POD Type</option>
                                        <option value="MPOD">MPOD</option>
                                        <option value="EPOD">EPOD</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">MOT (Mode of Transport)</label>
                                    <input type="text" name="mot" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 6: VENDOR & DRIVER -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>🚛 Vendor & Driver</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                
                                <div class="col-md-6">
                                    <label class="form-label">Driver Name</label>
                                    <input type="text" name="driver_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nopol</label>
                                    <input type="text" name="nopol" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="number" name="phone" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 7: PIC & RECEIVER -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>👥 PIC & Receiver</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">PIC on DN</label>
                                    <input type="text" name="pic_on_dn" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PIC Mobile No</label>
                                    <input type="text" name="pic_mobile_no" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Receiver on Site</label>
                                    <input type="text" name="receiver_on_site" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Subcon</label>
                                    <input type="text" name="subcon" class="form-control" required>
                                </div>


                            </div>
                        </div>
                    </div>

                    <!-- SECTION 8: ADDITIONAL COST -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>💰 Additional Cost</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nominal Add Cost</label>
                                    <input type="number" step="0.01" name="nominal_add_cost" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Detail Add Cost</label>
                                    <input type="text" name="detail_add_cost" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Approval by WhatsApp</label>
                                    <input type="text" name="approval_by_whatsapp" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Rise Up by Email</label>
                                    <input type="text" name="rise_up_by_email" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Approved by Email</label>
                                    <input type="text" name="approved_by_email" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Remarks Add Cost</label>
                                    <textarea name="remarks_add_cost" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 9: STATUS & REMARKS -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>📝 Status & Remarks</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">HTM</label>
                                    <input type="text" name="htm" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Overnight Day</label>
                                    <input type="number" name="overnight_day" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Latest Status</label>
                                    <textarea name="latest_status" class="form-control" rows="3" placeholder="Status terbaru pengiriman"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="3" placeholder="Catatan tambahan"></textarea>
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
                <button type="submit" form="addReportForm" class="btn btn-primary">
                    <i data-feather="save" style="width:14px;height:14px;"></i> Simpan Data
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editReportModal" tabindex="-1" aria-labelledby="editReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editReportModalLabel">
                    <i data-feather="edit" style="width:20px;height:20px;"></i> 
                    Edit Daily Delivery Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editReportForm">
                    <input type="hidden" name="id" id="edit_id">

                    <!-- SECTION 1: INFORMASI DASAR -->
                    <div class="card mb-3">
                        <div class="card-header bg-light"><strong>📋 Informasi Dasar</strong></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Date Request <span class="text-danger">*</span></label>
                                    <input type="date" name="date_request" id="edit_date_request" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">DN Number <span class="text-danger">*</span></label>
                                    <input type="text" name="dn_number" id="edit_dn_number" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sub Project</label>
                                    <input type="text" name="sub_project" id="edit_sub_project" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site ID</label>
                                    <input type="text" name="site_id" id="edit_site_id" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: LOKASI & TUJUAN -->
                    <div class="card mb-3">
                        <div class="card-header bg-light"><strong>📍 Lokasi & Tujuan</strong></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Plan From <span class="text-danger">*</span></label>
                                    <select name="plan_from" id="edit_plan_from" class="form-control" required>
                                        <option value="">-- Pilih Plan --</option>
                                        <option value="JKBS">JKBS</option>
                                        <option value="TCL">TLC</option>
                                        <option value="PT.Fanah Jaya Maindo">PT. Fanah Jaya Maindo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Destination City <span class="text-danger">*</span></label>
                                    <input type="text" name="destination_city" id="edit_destination_city" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Destination Province <span class="text-danger">*</span></label>
                                    <input type="text" name="destination_province" id="edit_destination_province" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="latitude" id="edit_latitude" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="longitude" id="edit_longitude" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Destination Address</label>
                                    <textarea name="destination_address" id="edit_destination_address" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: SCHEDULE & SLA -->
                    <div class="card mb-3">
                        <div class="card-header bg-light"><strong>📅 Schedule & SLA</strong></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">RSD</label>
                                    <input type="date" name="rsd" id="edit_rsd" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">RAD</label>
                                    <input type="date" name="rad" id="edit_rad" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">SLA (Hari)</label>
                                    <input type="number" name="sla" id="edit_sla" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: TIMELINE TRACKING -->
                    <div class="card mb-3">
                        <div class="card-header bg-light"><strong>⏱️ Timeline Tracking</strong></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Truck on Warehouse</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="truck_on_warehouse"
                                            id="edit_truck_on_warehouse"
                                            class="form-control datetime-picker"
                                            placeholder="YYYY-MM-DD HH:mm"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ATD (Date & Time mover dispatch from Whs)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="atd_whs_dispatch"
                                            id="edit_atd_whs_dispatch"
                                            class="form-control datetime-picker"
                                            placeholder="YYYY-MM-DD HH:mm"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ATD (Date & Time mover dispatch from Pool)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="atd_pool_dispatch"
                                            id="edit_atd_pool_dispatch"
                                            class="form-control datetime-picker"
                                            placeholder="YYYY-MM-DD HH:mm"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ATA Mover on Site</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="ata_mover_on_site"
                                            id="edit_ata_mover_on_site"
                                            class="form-control datetime-picker"
                                            placeholder="YYYY-MM-DD HH:mm"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Receiver on Site DateTime</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="receiver_on_site_datetime"
                                            id="edit_receiver_on_site_datetime"
                                            class="form-control datetime-picker"
                                            placeholder="YYYY-MM-DD HH:mm"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">POD DateTime</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="pod_datetime"
                                            id="edit_pod_datetime"
                                            class="form-control datetime-picker"
                                            placeholder="YYYY-MM-DD HH:mm"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 5: SHIPMENT DETAILS -->
                    <div class="card mb-3">
                        <div class="card-header bg-light"><strong>📦 Shipment Details</strong></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Volume (m³)</label>
                                    <input type="number" step="0.00001" name="volume" id="edit_volume" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gross Weight (kg)</label>
                                    <input type="number" step="0.00001" name="gross_weight" id="edit_gross_weight" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Type Shipment</label>
                                    <input type="text" name="type_shipment" id="edit_type_shipment" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" id="edit_status" class="form-select">
                                        <option value="">Pilih Status</option>
                                        <option value="Done Pickup At WH">Done Pickup At WH</option>
                                        <option value="Pool Mover">Pool Mover</option>
                                        <option value="On Delivery">On Delivery</option>
                                        <option value="Onsite">Onsite</option>
                                        <option value="Back To Pool">Back To Pool</option>
                                        <option value="Handover Done">Handover Done</option>
                                        
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">POD Type</label>
                                    <select name="pod_type" id="edit_pod_type" class="form-select">
                                        <option value="">Pilih POD Type</option>
                                        <option value="MPOD">MPOD</option>
                                        <option value="EPOD">EPOD</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">MOT (Mode of Transport)</label>
                                    <input type="text" name="mot" id="edit_mot" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 6: VENDOR & DRIVER -->
                    <div class="card mb-3">
                        <div class="card-header bg-light"><strong>🚛 Driver</strong></div>
                        <div class="card-body">
                            <div class="row g-3">
                                
                                <div class="col-md-6">
                                    <label class="form-label">Driver Name</label>
                                    <input type="text" name="driver_name" id="edit_driver_name" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nopol</label>
                                    <input type="text" name="nopol" id="edit_nopol" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" id="edit_phone" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 7: PIC & RECEIVER -->
                    <div class="card mb-3">
                        <div class="card-header bg-light"><strong>👥 PIC & Receiver</strong></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">PIC on DN</label>
                                    <input type="text" name="pic_on_dn" id="edit_pic_on_dn" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PIC Mobile No</label>
                                    <input type="text" name="pic_mobile_no" id="edit_pic_mobile_no" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Receiver on Site</label>
                                    <input type="text" name="receiver_on_site" id="edit_receiver_on_site" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Subcon</label>
                                    <input type="text" name="subcon" id="edit_subcon" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 8: ADDITIONAL COST -->
                    <div class="card mb-3">
                        <div class="card-header bg-light"><strong>💰 Additional Cost</strong></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nominal Add Cost</label>
                                    <input type="number" step="0.01" name="nominal_add_cost" id="edit_nominal_add_cost" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Detail Add Cost</label>
                                    <input type="text" name="detail_add_cost" id="edit_detail_add_cost" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Approval by WhatsApp</label>
                                    <input type="text" name="approval_by_whatsapp" id="edit_approval_by_whatsapp" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Rise Up by Email</label>
                                    <input type="text" name="rise_up_by_email" id="edit_rise_up_by_email" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Approved by Email</label>
                                    <input type="text" name="approved_by_email" id="edit_approved_by_email" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Remarks Add Cost</label>
                                    <textarea name="remarks_add_cost" id="edit_remarks_add_cost" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 9: STATUS & REMARKS -->
                    <div class="card mb-3">
                        <div class="card-header bg-light"><strong>📝 Status & Remarks</strong></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">HTM</label>
                                    <input type="text" name="htm" id="edit_htm" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Overnight Day</label>
                                    <input type="number" name="overnight_day" id="edit_overnight_day" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Latest Status</label>
                                    <textarea name="latest_status" id="edit_latest_status" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" id="edit_remarks" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i data-feather="x"></i> Batal
                </button>
                <button type="submit" form="editReportForm" class="btn btn-primary">
                    <i data-feather="save"></i> Update Data
                </button>
            </div>
        </div>
    </div>
</div>