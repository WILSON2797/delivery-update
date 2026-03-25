<main>
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-xl px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="truck"></i></div>
                            Invoice Done
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
                Invoice Done - Billing Details
                <div class="float-end">
                    <button class="btn btn-success btn-sm" id="exportExcelinvdone">
                        <i data-feather="file-text" style="width:14px; height:14px;"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body" style="overflow-x: auto;">
                <table class="table table-striped table-hover table-bordered compact-action" id="invdone"
                    style="min-width: 100%; white-space: nowrap;">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>DN Number</th>
                            <th>Sub Project</th>
                            <th>POD Date</th>
                            <th>Type Shipment</th>
                            <th>MOT</th>
                            <th>Date Send SCPOD</th>
                            <th>KPI Uploaded</th>
                            <th>Date Approved SCPOD</th>
                            <th>Date Send HCPOD</th>
                            <th>Date Submit PI</th>
                            <th>Due Date</th>
                            <th>Date Submit Invoice</th>
                            <th>No Invoice</th>
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
                            <th class="action-column"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</main>