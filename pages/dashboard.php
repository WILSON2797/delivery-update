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
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" onclick="location.reload()">
            <i data-feather="refresh-cw" class="feather-sm"></i> Refresh Data
        </button>
    </div>

    <!-- Content Row - Summary Cards (4 per row) -->
    <div class="row">

        <!-- Total DN Number -->
        <div class="col-xl-3 col-md-6 mb-4 dashboard-card-animate dashboard-card-1">
            <div class="card stat-card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="card-title-stat text-primary text-uppercase mb-1">Total DN Number</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalDnNumber">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary-soft">
                                <i data-feather="file-text" class="feather-icon text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Planned -->
        <div class="col-xl-3 col-md-6 mb-4 dashboard-card-animate dashboard-card-2">
            <div class="card stat-card border-left-green shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="card-title-stat text-green text-uppercase mb-1">Planned</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalPlanned">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-green-soft">
                                <i data-feather="calendar" class="feather-icon text-green"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Handover Done -->
        <div class="col-xl-3 col-md-6 mb-4 dashboard-card-animate dashboard-card-3">
            <div class="card stat-card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="card-title-stat text-success text-uppercase mb-1">Handover Done</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalHandover">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success-soft">
                                <i data-feather="check-circle" class="feather-icon text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total On Delivery -->
        <div class="col-xl-3 col-md-6 mb-4 dashboard-card-animate dashboard-card-4">
            <div class="card stat-card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="card-title-stat text-primary text-uppercase mb-1">On Delivery</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalOnDelivery">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-teal-soft">
                                <i data-feather="truck" class="feather-icon text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Onsite -->
        <div class="col-xl-3 col-md-6 mb-4 dashboard-card-animate dashboard-card-4">
            <div class="card stat-card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="card-title-stat text-warning text-uppercase mb-1">Onsite</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalOnsite">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-warning-soft">
                                <i data-feather="map-pin" class="feather-icon text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Back To Pool -->
        <div class="col-xl-3 col-md-6 mb-4 dashboard-card-animate dashboard-card-4">
            <div class="card stat-card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="card-title-stat text-info text-uppercase mb-1">Back To Pool</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBTP">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-info-soft">
                                <i data-feather="refresh-cw" class="feather-icon text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pool Mover -->
        <div class="col-xl-3 col-md-6 mb-4 dashboard-card-animate dashboard-card-5">
            <div class="card stat-card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="card-title-stat text-dark text-uppercase mb-1">Pool Mover</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalPoolMover">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-dark-soft">
                                <i data-feather="package" class="feather-icon text-dark"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Waiting Inbound -->
        <div class="col-xl-3 col-md-6 mb-4 dashboard-card-animate dashboard-card-5">
            <div class="card stat-card border-left-purple shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="card-title-stat text-purple text-uppercase mb-1">Waiting Inbound</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalWaitingInbound">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-purple-soft">
                                <i data-feather="download" class="feather-icon text-purple"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back To WH -->
        <div class="col-xl-3 col-md-6 mb-4 dashboard-card-animate dashboard-card-5">
            <div class="card stat-card border-left-orange shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="card-title-stat text-orange text-uppercase mb-1">Back To WH</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBackToWH">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-orange-soft">
                                <i data-feather="home" class="feather-icon text-orange"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Waiting Pickup -->
        <div class="col-xl-3 col-md-6 mb-4 dashboard-card-animate dashboard-card-5">
            <div class="card stat-card border-left-teal shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="card-title-stat text-teal text-uppercase mb-1">Waiting Pickup</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalWaitingPickup">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-teal-soft">
                                <i data-feather="clock" class="feather-icon text-teal"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancelled -->
        <div class="col-xl-3 col-md-6 mb-4 dashboard-card-animate dashboard-card-5">
            <div class="card stat-card border-left-red shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="card-title-stat text-red text-uppercase mb-1">Cancelled</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalCancelled">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-red-soft">
                                <i data-feather="x-circle" class="feather-icon text-red"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        

    </div>
    <!-- END Content Row - Summary Cards -->

    <!-- Content Row - Charts -->
    <div class="row">
        <!-- Bar Chart -->
        <div class="col-xl-8 col-lg-7 dashboard-card-animate dashboard-chart-1">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i data-feather="bar-chart-2" class="feather-sm"></i>
                        Daily Report Overview
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
                        <canvas id="inventoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie Chart -->
        <div class="col-xl-4 col-lg-5 dashboard-card-animate dashboard-chart-2">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i data-feather="pie-chart" class="feather-sm"></i>
                        Status Distribution
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
                        <canvas id="stockPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2"><i class="fas fa-circle text-danger"></i> Handover Done</span>
                        <span class="mr-2"><i class="fas fa-circle text-success"></i> On Delivery</span>
                        <span class="mr-2"><i class="fas fa-circle text-warning"></i> Onsite</span>
                        <span class="mr-2"><i class="fas fa-circle text-info"></i> Back To Pool</span>
                        <span class="mr-2"><i class="fas fa-circle text-secondary"></i> Pool Mover</span>
                        <br>
                        <span class="mr-2"><i class="fas fa-circle" style="color:#6f42c1;"></i> Waiting Inbound</span>
                        <span class="mr-2"><i class="fas fa-circle" style="color:#ff8000;"></i> Back To WH</span>
                        <span class="mr-2"><i class="fas fa-circle" style="color:#17a2b8;"></i> Waiting Pickup</span>
                        <span class="mr-2"><i class="fas fa-circle" style="color:#dc3545;"></i> Cancelled</span>
                        <span class="mr-2"><i class="fas fa-circle" style="color:#20c997;"></i> Planned</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<link rel="stylesheet" href="dashboard.css">