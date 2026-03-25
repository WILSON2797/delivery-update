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
                            <div class="page-header-icon"><i data-feather="map-pin"></i></div>
                            Order Request Tracking
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
                Order Tracking Table
                <div class="float-end">
                    <button class="btn btn-success btn-sm" id="exportExcelTracking">
                        <i data-feather="file-text" style="width:14px; height:14px;"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body" style="overflow-x: auto;">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-striped table-hover table-bordered compact-action" id="tabelOrderTracking"
                        style="min-width: 100%; white-space: nowrap;">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
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
                                <th>Status</th>
                                <th>Latest Status</th>
                                <th>Date Request</th>
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
<!-- Modal EDIT - SEMUA FIELD EDITABLE -->
<!-- ======================================== -->
<div class="modal fade" id="editTrackingModal" tabindex="-1" aria-labelledby="editTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="editTrackingModalLabel">
                    <i data-feather="edit-3" style="width:20px;height:20px;"></i>
                    Update Order Tracking - <span id="editTrackingDnNumberHeader" class="fw-bold">DN-XXXXX</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTrackingForm">
                    <input type="hidden" name="id" id="track_id">
                    <input type="hidden" name="transaction_id" id="track_transaction_id">

                    <!-- SECTION 1: BASIC ORDER INFO - SEMUA EDITABLE -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>📋 Informasi Order</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- BARIS 1 -->
                                <div class="col-md-6">
                                    <label class="form-label">Date Request <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" name="date_request" id="track_date_request"
                                            class="form-control date-picker" placeholder="YYYY-MM-DD" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Release Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" name="email_release_date" id="track_email_release_date"
                                            class="form-control date-picker" placeholder="YYYY-MM-DD" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">DN Number <span class="text-danger">*</span></label>
                                    <input type="text" name="dn_number" id="track_dn_number" class="form-control" required>
                                </div>

                                <!-- BARIS 2 -->
                                <div class="col-md-6">
                                    <label class="form-label">Sub Project <span class="text-danger">*</span></label>
                                    <input type="text" name="sub_project" id="track_sub_project" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site ID <span class="text-danger">*</span></label>
                                    <input type="text" name="site_id" id="track_site_id" class="form-control" required>
                                </div>

                                <!-- BARIS 3 -->
                                <div class="col-md-6">
                                    <label class="form-label">Plan From <span class="text-danger">*</span></label>
                                    <select name="plan_from" id="track_plan_from" class="form-control" required>
                                        <option value="">Pilih Plan From</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Destination Province <span class="text-danger">*</span></label>
                                    <select name="destination_province" id="track_destination_province" class="form-control" required>
                                        <option value="">Pilih Province</option>
                                    </select>
                                </div>

                                <!-- BARIS 4 -->
                                <div class="col-md-6">
                                    <label class="form-label">Destination City <span class="text-danger">*</span></label>
                                    <select name="destination_city" id="track_destination_city" class="form-control" required>
                                        <option value="">Pilih City</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">MOT <span class="text-danger">*</span></label>
                                    <select name="mot" id="track_mot" class="form-control" required>
                                        <option value="">Pilih MOT</option>
                                    </select>
                                </div>

                                <!-- BARIS 5 - FULL WIDTH -->
                                <div class="col-12">
                                    <label class="form-label">Destination Address <span class="text-danger">*</span></label>
                                    <textarea name="destination_address" id="track_destination_address" class="form-control" rows="2" required></textarea>
                                </div>

                                <!-- BARIS 6 -->
                                <div class="col-md-6">
                                    <label class="form-label">RSD (Requested Delivery Date)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" name="rsd" id="track_rsd" class="form-control date-picker" placeholder="YYYY-MM-DD">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">RAD (Actual Delivery Date)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" name="rad" id="track_rad" class="form-control date-picker" placeholder="YYYY-MM-DD">
                                    </div>
                                </div>

                                <!-- BARIS 7 -->
                                <div class="col-md-6">
                                    <label class="form-label">SLA (hari) <span class="text-danger">*</span></label>
                                    <input type="number" name="sla" id="track_sla" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Volume (m³)</label>
                                    <input type="number" step="0.000001" name="volume" id="track_volume" class="form-control">
                                </div>

                                <!-- BARIS 8 -->
                                <div class="col-md-6">
                                    <label class="form-label">Gross Weight (kg)</label>
                                    <input type="number" step="0.000001" name="gross_weight" id="track_gross_weight" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Type Shipment <span class="text-danger">*</span></label>
                                    <select name="type_shipment" id="track_type_shipment" class="form-control" required>
                                        <option value="">Pilih Type Shipment</option>
                                    </select>
                                </div>

                                <!-- BARIS 9 -->
                                <div class="col-md-6">
                                    <label class="form-label">HTM</label>
                                    <input type="text" name="htm" id="track_htm" class="form-control">
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
                                    <input type="text" name="driver_name" id="track_driver_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nopol <span class="text-danger">*</span></label>
                                    <input type="text" name="nopol" id="track_nopol" class="form-control" required>
                                </div>

                                <!-- BARIS 2 -->
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" id="track_phone" class="form-control phone-input"
                                        pattern="[+\-0-9\s]+" placeholder="+62xxxxxxxxxx atau 08xxxxxxxxxx" required>
                                    <small class="text-muted">Dapat menggunakan + dan -</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Subcon</label>
                                    <input type="text" name="subcon" id="track_subcon" class="form-control">
                                </div>

                                <!-- BARIS 3 -->
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" id="track_status" class="form-select">
                                        <option value="">Pilih Status</option>
                                    </select>
                                </div>
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
                                            <input type="date" class="form-control" id="track_truck_on_warehouse_date"
                                                placeholder="YYYY-MM-DD">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="track_truck_on_warehouse_time"
                                                step="60" pattern="[0-9]{2}:[0-9]{2}" placeholder="23:59">
                                        </div>
                                    </div>
                                    <input type="hidden" name="truck_on_warehouse" id="track_truck_on_warehouse">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Date Time Pickup Done
                                        <span class="text-muted small">- Done Pickup From Pickup Point</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="date" class="form-control" id="track_atd_whs_dispatch_date">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="track_atd_whs_dispatch_time" step="60">
                                        </div>
                                    </div>
                                    <input type="hidden" name="atd_whs_dispatch" id="track_atd_whs_dispatch">
                                </div>

                                <!-- BARIS 2 -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Date Time Delivery
                                        <span class="text-muted small">- OTW To Destination / Site</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="date" class="form-control" id="track_atd_pool_dispatch_date">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="track_atd_pool_dispatch_time" step="60">
                                        </div>
                                    </div>
                                    <input type="hidden" name="atd_pool_dispatch" id="track_atd_pool_dispatch">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Date Time Mover on Site
                                        <span class="text-muted small">- Mover Onsite At Destination</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="date" class="form-control" id="track_ata_mover_on_site_date">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="track_ata_mover_on_site_time" step="60">
                                        </div>
                                    </div>
                                    <input type="hidden" name="ata_mover_on_site" id="track_ata_mover_on_site">
                                </div>

                                <!-- BARIS 3 -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Receiver on Site DateTime
                                        <span class="text-muted small">- PIC Onsite</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="date" class="form-control" id="track_receiver_on_site_date">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="track_receiver_on_site_time" step="60">
                                        </div>
                                    </div>
                                    <input type="hidden" name="receiver_on_site_datetime" id="track_receiver_on_site_datetime">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        POD DateTime
                                        <span class="text-muted small">- Handover Date</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="date" class="form-control" id="track_pod_datetime_date">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control" id="track_pod_datetime_time" step="60">
                                        </div>
                                    </div>
                                    <input type="hidden" name="pod_datetime" id="track_pod_datetime">
                                </div>
                            </div>
                            
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label">POD Type</label>
                                    <select name="pod_type" id="track_pod_type" class="form-select">
                                        <option value="">Pilih POD Type</option>
                                        <option value="MPOD">MPOD</option>
                                        <option value="EPOD">EPOD</option>
                                    </select>
                                </div>
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
                                    <label class="form-label">PIC on DN</label>
                                    <input type="text" name="pic_on_dn" id="track_pic_on_dn" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PIC Mobile No</label>
                                    <input type="text" name="pic_mobile_no" id="track_pic_mobile_no"
                                        class="form-control phone-input" pattern="[+\-0-9\s]+"
                                        placeholder="+62xxxxxxxxxx atau 08xxxxxxxxxx">
                                    <small class="text-muted">Dapat menggunakan + dan -</small>
                                </div>

                                <!-- BARIS 2 -->
                                <div class="col-md-6">
                                    <label class="form-label">Receiver on Site</label>
                                    <input type="text" name="receiver_on_site" id="track_receiver_on_site" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="latitude" id="track_latitude" class="form-control" placeholder="-6.200000">
                                </div>

                                <!-- BARIS 3 -->
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="longitude" id="track_longitude" class="form-control" placeholder="106.816666">
                                </div>
                                <div class="col-md-6">
                                    <!-- Kosong untuk menjaga simetri 2 kolom -->
                                </div>

                                <!-- BARIS 4 - FULL WIDTH -->
                                <div class="col-12">
                                    <label class="form-label">Latest Status</label>
                                    <textarea 
                                        name="latest_status" 
                                        id="track_latest_status" 
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
                                    <textarea name="remarks" id="track_remarks" class="form-control" rows="2"></textarea>
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
                                    <input type="number" step="0.01" name="nominal_add_cost" id="track_nominal_add_cost" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Detail Add Cost</label>
                                    <input type="text" name="detail_add_cost" id="track_detail_add_cost" class="form-control">
                                </div>

                                <!-- BARIS 2 -->
                                <div class="col-md-6">
                                    <label class="form-label">Approval by WhatsApp</label>
                                    <input type="text" name="approval_by_whatsapp" id="track_approval_by_whatsapp" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rise Up by Email</label>
                                    <input type="text" name="rise_up_by_email" id="track_rise_up_by_email" class="form-control">
                                </div>

                                <!-- BARIS 3 -->
                                <div class="col-md-6">
                                    <label class="form-label">Approved by Email</label>
                                    <input type="text" name="approved_by_email" id="track_approved_by_email" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Overnight Day</label>
                                    <input type="number" name="overnight_day" id="track_overnight_day" class="form-control">
                                </div>

                                <!-- BARIS 4 - FULL WIDTH -->
                                <div class="col-12">
                                    <label class="form-label">Remarks Add Cost</label>
                                    <textarea name="remarks_add_cost" id="track_remarks_add_cost" class="form-control" rows="2"></textarea>
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
                <button type="submit" form="editTrackingForm" class="btn btn-info">
                    <i data-feather="save"></i> Update Tracking
                </button>
            </div>
        </div>
    </div>
</div>