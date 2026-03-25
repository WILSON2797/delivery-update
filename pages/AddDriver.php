<?php
session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$_SESSION['last_activity'] = time();

$role         = isset($_SESSION['role'])         ? $_SESSION['role']         : 'user';
$wh_name      = isset($_SESSION['wh_name'])      ? $_SESSION['wh_name']      : '';
$wh_id        = isset($_SESSION['wh_id'])        ? $_SESSION['wh_id']        : '';
$project_name = isset($_SESSION['project_name']) ? $_SESSION['project_name'] : '';

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
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <button class="btn btn-sm btn-light text-primary active me-2"><?php echo date('d'); ?></button>
                        <button class="btn btn-sm btn-light text-primary me-2"><?php echo date('F'); ?></button>
                        <button class="btn btn-sm btn-light text-primary"><?php echo date('Y'); ?></button>
                        <button class="btn btn-sm btn-light text-primary"><?php echo date('H:i  T'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main page content-->
    <div class="container-fluid px-4 mt-4">
        <div class="card mb-4">
            <div class="card-header">
                <div class="float-end">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#driverModal">
                        <i data-feather="plus" style="width: 14px; height: 14px;"></i> Add New
                    </button>
                    <button class="btn btn-success btn-sm" id="exportExceldriver">
                        <i data-feather="file-text" style="width:14px; height:14px;"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body" style="overflow-x: auto;">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-striped table-hover table-bordered compact-action" id="tabeldriver"
                        style="min-width: 100%; white-space: nowrap;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">No</th>
                                <th><strong>Driver Name</strong></th>
                                <th><strong>Phone</strong></th>
                                <th><strong>Nopol</strong></th>
                                <th><strong>Created Date</strong></th>
                                <th><strong>Action</strong></th>
                            </tr>
                            <tr class="table-search">
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


<!-- Modal Tambah Driver -->
<div class="modal fade" id="driverModal" tabindex="-1" aria-labelledby="driverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="driverModalLabel">Tambah Driver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="driverForm" action="../modules/proses_driver.php" method="POST">
                    <div class="row mb-2">
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Driver Name</label>
                            <input type="text" name="nama" class="form-control"
                                placeholder="Contoh: Budi Santoso"
                                required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                placeholder="Contoh: +628123456789"
                                title="Hanya angka, boleh diawali + atau -"
                                required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Nopol</label>
                            <input type="text" name="nopol" class="form-control"
                                placeholder="Contoh: B 1234 ABC"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>