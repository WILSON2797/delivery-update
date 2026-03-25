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
                            <th>DN Number</th>
                            <th>Driver Name</th>
                            <th>Phone</th>
                            <th>Site ID</th>
                            <th>Sub Project</th>
                            <th>Plan From</th>
                            <th>Destination City</th>
                            <th>Destination Province</th>
                            <th>Subcon</th>
                            <th>MOT</th>
                            <th>Latest Status</th>
                            <th>Last Update</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        <tr class="table-search">
                            <th><input type="text" class="form-control form-control-sm"></th>
                            <th><input type="text" class="form-control form-control-sm"></th>
                            <th>
                                <div class="input-group input-group-sm input-group-joined">
                                    <input class="form-control pe-0" type="text" placeholder="Search" />
                                    <div class="input-group-text"><i data-feather="search"></i></div>
                                </div>
                            </th>
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
<!-- Modal ADD - MAKSIMAL 2 KOLOM PER BARIS -->
<!-- ======================================== -->
<div class="modal fade" id="addReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addReportModalLabel">
                    <i data-feather="plus-circle" style="width:20px;height:20px;"></i> 
                    Create New Order
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addReportForm">
                    <div class="card">
                        <div class="card-header bg-light">
                            <strong>📋 Informasi Create Order</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- BARIS 1 -->
                                <div class="col-md-6">
                                    <label class="form-label">Date Request <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" name="date_request" class="form-control date-picker" placeholder="YYYY-MM-DD" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Release Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" name="email_release_date" class="form-control date-picker" placeholder="YYYY-MM-DD" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">DN Number <span class="text-danger">*</span></label>
                                    <input type="text" name="dn_number" class="form-control" required>
                                </div>
                                
                                <!-- BARIS 2 -->
                                <div class="col-md-6">
                                    <label class="form-label">Sub Project <span class="text-danger">*</span></label>
                                    <input type="text" name="sub_project" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site ID <span class="text-danger">*</span></label>
                                    <input type="text" name="site_id" class="form-control" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Plan From <span class="text-danger">*</span>
                                    </label>
                                    <select 
                                        name="plan_from" 
                                        id="plan_from"
                                        class="form-control" 
                                        required
                                    >
                                        <option value="">Pilih Plan From</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Destination Province <span class="text-danger">*</span></label>
                                    <select name="destination_province" id="destination_province" class="form-control" required>
                                        <option value="">Pilih Province</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Destination City <span class="text-danger">*</span></label>
                                    <select name="destination_city" id="destination_city" class="form-control" required>
                                        <option value="">Pilih City</option>
                                    </select>
                                    <small class="text-warning">Harap Pilih Province Terlebih Dahulu</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">MOT <span class="text-danger">*</span></label>
                                    <select name="mot" id="mot" class="form-control" required>
                                        <option value="">Pilih MOT</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Type Shipment <span class="text-danger">*</span></label>
                                    <select name="type_shipment" id="type_shipment" class="form-control" required>
                                        <option value="">Pilih Type</option>
                                    </select>
                                </div>
                                <!-- BARIS 5 - FULL WIDTH -->
                                <div class="col-12">
                                    <label class="form-label">Destination Address <span class="text-danger">*</span></label>
                                    <textarea name="destination_address" class="form-control" rows="2" required></textarea>
                                </div>
                                
                                <!-- BARIS 6 -->
                                <div class="col-md-6">
                                    <label class="form-label">RSD (Requested Delivery Date)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" name="rsd" class="form-control date-picker" placeholder="YYYY-MM-DD" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">RAD (Actual Delivery Date)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" name="rad" class="form-control date-picker" placeholder="YYYY-MM-DD" required>
                                    </div>
                                </div>
                                
                                <!-- BARIS 7 -->
                                <div class="col-md-6">
                                    <label class="form-label">SLA (hari) <span class="text-danger">*</span></label>
                                    <input type="number" name="sla" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Volume (m³)</label>
                                    <input type="number" step="0.000001" name="volume" class="form-control" required>
                                </div>
                                
                                <!-- BARIS 8 -->
                                <div class="col-md-6">
                                    <label class="form-label">Gross Weight (kg)</label>
                                    <input type="number" step="0.000001" name="gross_weight" class="form-control" required>
                                </div>
                                
                                
                                <!-- BARIS 9 -->
                                <div class="col-md-6">
                                    <label class="form-label">HTM</label>
                                    <input type="text" name="htm" class="form-control">
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

<!-- ======================================== -->
<!-- Modal EDIT - MAKSIMAL 2 KOLOM PER BARIS -->
<!-- ======================================== -->
<div class="modal fade" id="editReportModal" tabindex="-1" aria-labelledby="editReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editReportModalLabel">
                    <i data-feather="edit" style="width:20px;height:20px;"></i> 
                    Update Status - <span id="editDnNumberHeader" class="fw-bold text-primary">DN-XXXXX</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editReportForm">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="transaction_id" id="edit_transaction_id">

                    <!-- SECTION 1: BASIC INFO (READ ONLY) -->
                    <div class="card mb-3">
                        
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- BARIS 1 -->
                                <div class="col-md-6">
                                    <label class="form-label">Date Request</label>
                                    <input type="text" name="date_request" id="edit_date_request" class="form-control bg-light" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">DN Number</label>
                                    <input type="text" name="dn_number" id="edit_dn_number" class="form-control bg-light" readonly>
                                </div>
                                
                               
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: DRIVER & TRACKING INFO -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>🚛 Informasi Driver & Tracking</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- BARIS 1 -->
                                <div class="col-md-6">
                                    <label class="form-label">Driver Name <span class="text-danger">*</span></label>
                                    <select name="driver_name" id="edit_driver_name" class="form-select" required>
                                        <option value="">Pilih Driver</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nopol <span class="text-danger">*</span></label>
                                    <input type="text" name="nopol" id="edit_nopol" class="form-control" required>
                                </div>
                                
                                <!-- BARIS 2 -->
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" id="edit_phone" class="form-control phone-input" pattern="[+\-0-9\s]+" placeholder="+62xxxxxxxxxx atau 08xxxxxxxxxx" required>
                                    
                                </div>
                                
                                
                                <!-- BARIS 3 -->
                                <div class="col-md-6">
                                    <label class="form-label">MOT <span class="text-danger">*</span></label>
                                    <select name="mot" id="edit_mot" class="form-select" required>
                                        <option value="">Pilih MOT</option>
                                    </select>
                                </div>
                                
                        </div>
                    </div>

                    <!-- SECTION 3: TIMELINE TRACKING -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>⏱️ Timeline Tracking</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- BARIS 1 -->
                                <div class="col-md-6">
                                    <label class="form-label">Truck on Pickup Point <span class="text-muted small">- Tiba Di Pickup Point</span></label>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="date" class="form-control date-picker" id="edit_truck_on_warehouse_date"
                                                placeholder="YYYY-MM-DD">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="edit_truck_on_warehouse_time"
                                                step="60" pattern="[0-9]{2}:[0-9]{2}" placeholder="23:59">
                                        </div>
                                    </div>
                                    <input type="hidden" name="truck_on_warehouse" id="edit_truck_on_warehouse">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Date Time Pickup Done
                                        <span class="text-muted small">- Done Pickup From Pickup Point</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="date" class="form-control date-picker" id="edit_atd_whs_dispatch_date">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="edit_atd_whs_dispatch_time" step="60">
                                        </div>
                                    </div>
                                    <input type="hidden" name="atd_whs_dispatch" id="edit_atd_whs_dispatch">
                                </div>

                                <!-- BARIS 2 -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Date Time Delivery
                                        <span class="text-muted small">- OTW To Destination / Site</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="date" class="form-control date-picker" id="edit_atd_pool_dispatch_date">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="edit_atd_pool_dispatch_time" step="60">
                                        </div>
                                    </div>
                                    <input type="hidden" name="atd_pool_dispatch" id="edit_atd_pool_dispatch">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Date Time Mover on Site
                                        <span class="text-muted small">- Mover Onsite At Destination</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="date" class="form-control date-picker" id="edit_ata_mover_on_site_date">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="edit_ata_mover_on_site_time" step="60">
                                        </div>
                                    </div>
                                    <input type="hidden" name="ata_mover_on_site" id="edit_ata_mover_on_site">
                                </div>

                                <!-- BARIS 3 -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Receiver on Site DateTime
                                        <span class="text-muted small">- PIC Onsite</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="date" class="form-control date-picker" id="edit_receiver_on_site_date">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="edit_receiver_on_site_time" step="60">
                                        </div>
                                    </div>
                                    <input type="hidden" name="receiver_on_site_datetime" id="edit_receiver_on_site_datetime">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        POD DateTime
                                        <span class="text-muted small">- Handover Date</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="date" class="form-control date-picker" id="edit_pod_datetime_date">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="edit_pod_datetime_time" step="60">
                                        </div>
                                    </div>
                                    <input type="hidden" name="pod_datetime" id="edit_pod_datetime">
                                </div>
                            </div>
                            
                            <div class="col-md-5">
                                    <label class="form-label">POD Type</label>
                                    <select name="pod_type" id="edit_pod_type" class="form-select">
                                        <option value="">Pilih POD Type</option>
                                        <option value="MPOD">MPOD</option>
                                        <option value="EPOD">EPOD</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="edit_status" class="form-select" required>
                                        
                                    </select>
                                </div>
                                <div class="col-md-6" id="btp_datetime_wrapper">
                                    <label class="form-label">
                                        Back To Pool DateTime
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="date" class="form-control date-picker" id="edit_btp_datetime_date">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="edit_btp_datetime_time" step="60">
                                        </div>
                                    </div>
                                    <input type="hidden" name="btp_datetime" id="edit_btp_datetime">
                                </div>
                        </div>
                    </div>

                    <!-- SECTION 4: PIC & RECEIVER -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>👥 PIC & Receiver</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- BARIS 1 -->
                                <div class="col-md-6">
                                    <label class="form-label">Subcon</label>
                                    <input type="text" name="subcon" id="edit_subcon" class="form-control" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">PIC on DN</label>
                                    <input type="text" name="pic_on_dn" id="edit_pic_on_dn" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PIC Mobile No</label>
                                    <input type="text" name="pic_mobile_no" id="edit_pic_mobile_no" class="form-control phone-input" pattern="[+\-0-9\s]+" placeholder="+62xxxxxxxxxx atau 08xxxxxxxxxx" required>
                                    <small class="text-muted">Dapat menggunakan + dan -</small>
                                </div>
                                
                                <!-- BARIS 2 -->
                                <div class="col-md-6">
                                    <label class="form-label">Receiver on Site (Actual Receiver)</label>
                                    <input type="text" name="receiver_on_site" id="edit_receiver_on_site" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="latitude" id="edit_latitude" class="form-control" placeholder="-6.200000" required>
                                </div>
                                
                                <!-- BARIS 3 -->
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="longitude" id="edit_longitude" class="form-control" placeholder="106.816666" required>
                                </div>
                                <div class="col-md-6">
                                    <!-- Kosong untuk menjaga simetri 2 kolom -->
                                </div>
                                
                                <!-- BARIS 4 - FULL WIDTH -->
                                <div class="col-12">
                                    <label class="form-label">Latest Status</label>
                                   <textarea 
                                        name="latest_status" 
                                        id="edit_latest_status" 
                                        class="form-control" 
                                        rows="10"
                                        placeholder="Update status pengiriman disini"
                                    ></textarea>
                                    <span class="text-muted small">
                                        Silakan isi update terbaru terkait status pengiriman
                                    </span>
                                </div>
                                
                                <!-- BARIS 5 - FULL WIDTH -->
                                <div class="col-12">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" id="edit_remarks" class="form-control" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 5: ADDITIONAL COST -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <strong>💰 Informasi Additional Cost</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- BARIS 1 -->
                                <div class="col-md-6">
                                    <label class="form-label">Nominal Add Cost</label>
                                    <input type="number" step="0.01" name="nominal_add_cost" id="edit_nominal_add_cost" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Detail Add Cost</label>
                                    <input type="text" name="detail_add_cost" id="edit_detail_add_cost" class="form-control">
                                </div>
                                
                                <!-- BARIS 2 -->
                                <div class="col-md-6">
                                    <label class="form-label">Approval by WhatsApp</label>
                                    <input type="text" name="approval_by_whatsapp" id="edit_approval_by_whatsapp" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rise Up by Email</label>
                                    <input type="text" name="rise_up_by_email" id="edit_rise_up_by_email" class="form-control">
                                </div>
                                
                                <!-- BARIS 3 -->
                                <div class="col-md-6">
                                    <label class="form-label">Approved by Email</label>
                                    <input type="text" name="approved_by_email" id="edit_approved_by_email" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Overnight Day</label>
                                    <input type="number" name="overnight_day" id="edit_overnight_day" class="form-control">
                                </div>
                                
                                <!-- BARIS 4 - FULL WIDTH -->
                                <div class="col-12">
                                    <label class="form-label">Remarks Add Cost</label>
                                    <textarea name="remarks_add_cost" id="edit_remarks_add_cost" class="form-control" rows="2"></textarea>
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
                <button type="button" id="btnSubmitEdit" class="btn btn-primary">
                    <i data-feather="save"></i> Update Data
                </button>
            </div>
        </div>
    </div>
</div>