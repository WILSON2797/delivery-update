// ======================================================
// assets/js/modules/dashboard.js
// Dashboard untuk Billing Tracking
// ======================================================

function initPageScripts() {
    console.log("✅ dashboard.js loaded");


        $("#exportExcelBillingDashboard").on("click", function () {
            // Langsung redirect ke API export
            window.location.href = "API/export_billing_details.php";
        });
    // ======================================================
    // 1️⃣ Variabel Global
    // ======================================================
    let billingChart = null;
    let billingPieChart = null;

    // ======================================================
    // 2️⃣ Load Dashboard Data
    // ======================================================
    function loadDashboardData() {
        console.log("🔄 Loading dashboard data...");
        
        if (typeof window.showLoading === "function") {
            window.showLoading('Loading dashboard data...');
        }

        $.ajax({
            url: 'API/DashboarBilling',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (typeof window.hideLoading === "function") {
                    window.hideLoading();
                }

                if (response.status === 'success' && response.data) {
                    const data = response.data;
                    console.log("✅ Dashboard data loaded:", data);
                    
                    // Update cards
                    updateSummaryCards(data);
                    
                    // Update charts
                    updateBillingChart(data);
                    updateBillingPieChart(data);
                    
                } else {
                    console.error("❌ No data received from server");
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Data',
                        text: 'Tidak ada data untuk ditampilkan',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr, status, error) {
                if (typeof window.hideLoading === "function") {
                    window.hideLoading();
                }
                
                console.error("❌ Failed to load dashboard data:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memuat data dashboard. Silakan refresh halaman.'
                });
            }
        });
    }

    // ======================================================
    // 3️⃣ Update Summary Cards
    // ======================================================
    function updateSummaryCards(data) {
        $('#totalBillingShipment').text(formatNumber(data.total_shipment || 0));
        $('#totalBillingHandover').text(formatNumber(data.total_handover_done || 0));
        $('#totalBillingSCPODSubmit').text(formatNumber(data.total_scpod_submit || 0));
        $('#totalBillingNYSCPOD').text(formatNumber(data.total_ny_scpod || 0));
        $('#totalBillingPISubmit').text(formatNumber(data.total_pi_submit || 0));
        $('#totalBillingNYPI').text(formatNumber(data.total_ny_pi || 0));
        $('#totalBillingPIApproved').text(formatNumber(data.total_pi_approved || 0));
        $('#totalBillingSubmitINV').text(formatNumber(data.total_submit_inv || 0));
        
        console.log("✅ Summary cards updated");
    }

    // ======================================================
    // 4️⃣ Update Billing Chart (Bar Chart)
    // ======================================================
    function updateBillingChart(data) {
        const ctx = document.getElementById('billingChart');
        if (!ctx) {
            console.warn("⚠️ billingChart canvas not found");
            return;
        }

        // Destroy existing chart
        if (billingChart) {
            billingChart.destroy();
        }

        // Create new chart
        billingChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: [
                    'Total Shipment', 
                    'Handover Done', 
                    'SCPOD Submit', 
                    'NY SCPOD', 
                    'PI Submit', 
                    'NY PI', 
                    'PI Approved', 
                    'Submit INV'
                ],
                datasets: [{
                    label: 'Quantity',
                    data: [
                        data.total_shipment || 0,
                        data.total_handover_done || 0,
                        data.total_scpod_submit || 0,
                        data.total_ny_scpod || 0,
                        data.total_pi_submit || 0,
                        data.total_ny_pi || 0,
                        data.total_pi_approved || 0,
                        data.total_submit_inv || 0
                    ],
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.8)',   // Primary - Total Shipment
                        'rgba(28, 200, 138, 0.8)',   // Success - Handover Done
                        'rgba(54, 185, 204, 0.8)',   // Info - SCPOD Submit
                        'rgba(246, 194, 62, 0.8)',   // Warning - NY SCPOD
                        'rgba(78, 115, 223, 0.8)',   // Primary - PI Submit
                        'rgba(231, 74, 59, 0.8)',    // Danger - NY PI
                        'rgba(28, 200, 138, 0.8)',   // Success - PI Approved
                        'rgba(133, 135, 150, 0.8)'   // Dark - Submit INV
                    ],
                    borderColor: [
                        'rgb(78, 115, 223)',
                        'rgb(28, 200, 138)',
                        'rgb(54, 185, 204)',
                        'rgb(246, 194, 62)',
                        'rgb(78, 115, 223)',
                        'rgb(231, 74, 59)',
                        'rgb(28, 200, 138)',
                        'rgb(133, 135, 150)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Total: ' + formatNumber(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value);
                            },
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        console.log("✅ Billing chart updated");
    }

    // ======================================================
    // 5️⃣ Update Billing Pie Chart
    // ======================================================
    function updateBillingPieChart(data) {
        const ctx = document.getElementById('billingPieChart');
        if (!ctx) {
            console.warn("⚠️ billingPieChart canvas not found");
            return;
        }

        // Destroy existing chart
        if (billingPieChart) {
            billingPieChart.destroy();
        }

        // Data untuk pie chart (exclude Total Shipment)
        const handoverDone = data.total_handover_done || 0;
        const scpodSubmit = data.total_scpod_submit || 0;
        const piSubmit = data.total_pi_submit || 0;
        const piApproved = data.total_pi_approved || 0;
        const submitInv = data.total_submit_inv || 0;
        const total = handoverDone + scpodSubmit + piSubmit + piApproved + submitInv;

        // Create new chart
        billingPieChart = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Handover Done', 'SCPOD Submit', 'PI Submit', 'PI Approved', 'Submit INV'],
                datasets: [{
                    data: [handoverDone, scpodSubmit, piSubmit, piApproved, submitInv],
                    backgroundColor: [
                        'rgba(28, 200, 138, 0.8)',   // Success - Handover Done
                        'rgba(54, 185, 204, 0.8)',   // Info - SCPOD Submit
                        'rgba(78, 115, 223, 0.8)',   // Primary - PI Submit
                        'rgba(28, 200, 138, 0.8)',   // Success - PI Approved
                        'rgba(133, 135, 150, 0.8)'   // Dark - Submit INV
                    ],
                    borderColor: [
                        'rgb(28, 200, 138)',
                        'rgb(54, 185, 204)',
                        'rgb(78, 115, 223)',
                        'rgb(28, 200, 138)',
                        'rgb(133, 135, 150)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + formatNumber(value) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        console.log("✅ Billing pie chart updated");
    }

    // ======================================================
    // 6️⃣ Helper Function - Format Number
    // ======================================================
    function formatNumber(num) {
        if (num === null || num === undefined) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // ======================================================
    // 7️⃣ Cleanup Function
    // ======================================================
    window.cleanupDashboard = function() {
        // Destroy charts
        if (billingChart) {
            billingChart.destroy();
            billingChart = null;
        }
        if (billingPieChart) {
            billingPieChart.destroy();
            billingPieChart = null;
        }
        
        console.log("🧹 Dashboard cleanup completed");
    };

    // ======================================================
    // 8️⃣ Initialize Dashboard
    // ======================================================
    function initDashboard() {
        console.log("🚀 Initializing dashboard...");
        loadDashboardData();
    }

    // ======================================================
    // 🔟 Auto Initialize on Page Load
    // ======================================================
    setTimeout(function() {
        if (typeof Chart === 'undefined') {
            console.error("❌ Chart.js library not loaded!");
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Chart.js library tidak ditemukan. Silakan periksa koneksi internet Anda.'
            });
        } else {
            initDashboard();
        }
    }, 100);

    console.log("✅ All dashboard event handlers initialized");
}

// Export function untuk SPA
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { initPageScripts };
}