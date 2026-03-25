// ======================================================
// assets/js/modules/dashboard.js
// Dashboard untuk Daily Delivery Report
// ======================================================

function initPageScripts() {
    console.log("✅ dashboard.js loaded");

    // ======================================================
    // 1️⃣ Variabel Global
    // ======================================================
    let inventoryChart = null;
    let stockPieChart = null;

    // ======================================================
    // 2️⃣ Load Dashboard Data
    // ======================================================
    function loadDashboardData() {
        console.log("🔄 Loading dashboard data...");
        
        if (typeof window.showLoading === "function") {
            window.showLoading('Loading dashboard data...');
        }

        $.ajax({
            url: 'API/dashboard_api',
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
                    updateInventoryChart(data);
                    updateStockPieChart(data);
                    
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
        $('#totalDnNumber').text(formatNumber(data.total_dn_number || 0));
        $('#totalHandover').text(formatNumber(data.total_handover_done || 0));
        $('#totalOnDelivery').text(formatNumber(data.total_on_delivery || 0));
        $('#totalOnsite').text(formatNumber(data.total_onsite || 0));
        $('#totalBTP').text(formatNumber(data.total_btp || 0));
        $('#totalPoolMover').text(formatNumber(data.total_pool_mover || 0));
        
        console.log("✅ Summary cards updated");
    }

    // ======================================================
    // 4️⃣ Update Inventory Chart (Bar Chart)
    // ======================================================
    function updateInventoryChart(data) {
        const ctx = document.getElementById('inventoryChart');
        if (!ctx) {
            console.warn("⚠️ inventoryChart canvas not found");
            return;
        }

        // Destroy existing chart
        if (inventoryChart) {
            inventoryChart.destroy();
        }

        // Create new chart
        inventoryChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Total DN', 'Handover Done', 'On Delivery', 'Onsite', 'Back To Pool', 'Pool Mover'],
                datasets: [{
                    label: 'Quantity',
                    data: [
                        data.total_dn_number || 0,
                        data.total_handover_done || 0,
                        data.total_on_delivery || 0,
                        data.total_onsite || 0,
                        data.total_btp || 0,
                        data.total_pool_mover || 0
                    ],
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.8)',
                        'rgba(231, 74, 59, 0.8)',
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(54, 185, 204, 0.8)',
                        'rgba(133, 135, 150, 0.8)'
                    ],
                    borderColor: [
                        'rgb(78, 115, 223)',
                        'rgb(231, 74, 59)',
                        'rgb(28, 200, 138)',
                        'rgb(246, 194, 62)',
                        'rgb(54, 185, 204)',
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

        console.log("✅ Inventory chart updated");
    }

    // ======================================================
    // 5️⃣ Update Stock Pie Chart
    // ======================================================
    function updateStockPieChart(data) {
        const ctx = document.getElementById('stockPieChart');
        if (!ctx) {
            console.warn("⚠️ stockPieChart canvas not found");
            return;
        }

        // Destroy existing chart
        if (stockPieChart) {
            stockPieChart.destroy();
        }

        // Data untuk pie chart (exclude Total DN Number)
        const handoverDone = data.total_handover_done || 0;
        const onDelivery = data.total_on_delivery || 0;
        const onsite = data.total_onsite || 0;
        const btp = data.total_btp || 0;
        const poolMover = data.total_pool_mover || 0;
        const total = handoverDone + onDelivery + onsite + btp + poolMover;

        // Create new chart
        stockPieChart = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Handover Done', 'On Delivery', 'Onsite', 'Back To Pool', 'Pool Mover'],
                datasets: [{
                    data: [handoverDone, onDelivery, onsite, btp, poolMover],
                    backgroundColor: [
                        'rgba(231, 74, 59, 0.8)',
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(54, 185, 204, 0.8)',
                        'rgba(133, 135, 150, 0.8)'
                    ],
                    borderColor: [
                        'rgb(231, 74, 59)',
                        'rgb(28, 200, 138)',
                        'rgb(246, 194, 62)',
                        'rgb(54, 185, 204)',
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

        console.log("✅ Stock pie chart updated");
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
        if (inventoryChart) {
            inventoryChart.destroy();
            inventoryChart = null;
        }
        if (stockPieChart) {
            stockPieChart.destroy();
            stockPieChart = null;
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