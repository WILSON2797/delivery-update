<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../login?message=session_expired");
    exit();
}
?>

<div class="container-fluid px-4">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard - Billing Tracking</h1>
        <div class="float-end">
        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" onclick="location.reload()">
            <i data-feather="refresh-cw" class="feather-sm"></i> Refresh Data
        </button>
        <button class="d-none d-sm-inline-block btn btn-success btn-sm" id="exportExcelBillingDashboard">
            <i data-feather="file-text" style="width:14px; height:14px;"></i> Export Excel
        </button>
        </div>
    </div>

    <!-- Content Row - Summary Cards -->
    <div class="row">
        <!-- Total Shipment -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Shipment</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBillingShipment">0</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="package" class="feather-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Handover Done -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Handover Done</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBillingHandover">0</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="check-circle" class="feather-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SCPOD Submit -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                SCPOD Submit</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBillingSCPODSubmit">0</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="file-text" class="feather-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NY Submit SCPOD -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                NY Submit SCPOD</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBillingNYSCPOD">0</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="alert-circle" class="feather-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total PI Submit -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total PI Submit</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBillingPISubmit">0</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="send" class="feather-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NY PI Submit -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                NY PI Submit</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBillingNYPI">0</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="x-circle" class="feather-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PI Approved -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                PI Approved</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBillingPIApproved">0</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="check-square" class="feather-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Submit INV -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                Total Submit INV</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBillingSubmitINV">0</div>
                        </div>
                        <div class="col-auto">
                            <i data-feather="dollar-sign" class="feather-2x text-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Total Amount</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBillingAmount">Rp 0</div>
                </div>
                <div class="col-auto">
                    <i data-feather="dollar-sign" class="feather-2x text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Content Row - Charts -->
    <div class="row">
        <!-- Bar Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i data-feather="bar-chart-2" class="feather-sm"></i>
                        Billing Process Overview
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i data-feather="more-vertical" class="feather-sm text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Chart Options:</div>
                            <a class="dropdown-item" href="#" onclick="location.reload()">
                                <i data-feather="refresh-cw" class="feather-sm mr-2"></i>Refresh
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="position: relative; height: 320px;">
                        <canvas id="billingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i data-feather="pie-chart" class="feather-sm"></i>
                        Process Distribution
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink2"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i data-feather="more-vertical" class="feather-sm text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink2">
                            <div class="dropdown-header">Chart Options:</div>
                            <a class="dropdown-item" href="#" onclick="location.reload()">
                                <i data-feather="refresh-cw" class="feather-sm mr-2"></i>Refresh
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2" style="position: relative; height: 245px;">
                        <canvas id="billingPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Handover Done
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> SCPOD Submit
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> PI Submit
                        </span>
                        <br class="d-sm-none">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> PI Approved
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-dark"></i> Submit INV
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    /* Custom styles for dashboard */
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    
    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }
    
    .border-left-dark {
        border-left: 0.25rem solid #858796 !important;
    }
    
    .text-primary-300 {
        color: #4e73df !important;
    }
    
    .text-success-300 {
        color: #1cc88a !important;
    }
    
    .text-info-300 {
        color: #36b9cc !important;
    }
    
    .text-warning-300 {
        color: #f6c23e !important;
    }
    
    .text-danger {
        color: #e74a3b !important;
    }
    
    .text-dark-300 {
        color: #858796 !important;
    }
    
    .feather-2x {
        width: 2rem;
        height: 2rem;
    }
    
    .feather-sm {
        width: 1rem;
        height: 1rem;
    }
    
    .card {
        border: none;
        transition