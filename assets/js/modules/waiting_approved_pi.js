function initPageScripts() {
    console.log("✅ waiting_approved_pi.js loaded");


    function initDateTimePicker() {
        if (typeof flatpickr === "undefined") {
            console.error("❌ Flatpickr belum ter-load");
            return;
        }

        // Hanya untuk date-picker di form ADD
        flatpickr(".date-picker", {
            dateFormat: "Y-m-d",
            allowInput: true,
            altInput: true,
            altFormat: "d-m-Y",
        });
    }

    // INIT SAAT PAGE LOAD
    initDateTimePicker();


    // Inisialisasi DataTable
    if (!$.fn.DataTable.isDataTable('#tabelApprovedPI')) {
        $("#tabelApprovedPI").DataTable({
            ajax: {
                url: "API/table_waiting_approved_pi",
                dataSrc: "data",
                beforeSend: showLoading,
                complete: hideLoading,
                error: function(xhr) {
                    console.error("AJAX Error:", xhr.responseText);
                    showErrorToast("Gagal Load Data", "Gagal memuat data. Cek console untuk detail.");
                }
            },
            columns: [
                { 
                    data: null, 
                    render: (data, type, row, meta) => 
                        meta.row + meta.settings._iDisplayStart + 1 
                },
                { data: "dn_number" },
                { data: "sub_project" },
                { data: "status" },
                { data: "pod_date" },
                { data: "type_shipment" },
                { data: "mot" },
                { data: "date_send_sc_pod" },
                { 
                    data: "kpi_uploaded",
                    render: function(data) {
                        if (!data) return '-';
                        let color = data === "ONTIME" ? "success" : "danger";
                        return `<span class="badge bg-${color}">${data}</span>`;
                    }
                },
                { data: "date_approved_sc_pod" },
                { data: "date_send_hc_pod" },
                { data: "date_submit_pi" },
                { data: "due_date" },
                { data: "aging_days" },
                { data: "no_pi" },
                { 
                    data: "unit_price",
                    render: function(data) {
                        return data ? parseFloat(data).toLocaleString('id-ID', {minimumFractionDigits: 2}) : '-';
                    }
                },
                { 
                    data: "btp_bta",
                    render: function(data) {
                        return data ? parseFloat(data).toLocaleString('id-ID', {minimumFractionDigits: 2}) : '-';
                    }
                },
                { 
                    data: "rooftop",
                    render: function(data) {
                        return data ? parseFloat(data).toLocaleString('id-ID', {minimumFractionDigits: 2}) : '-';
                    }
                },
                { 
                    data: "4wd",
                    render: function(data) {
                        return data ? parseFloat(data).toLocaleString('id-ID', {minimumFractionDigits: 2}) : '-';
                    }
                },
                { 
                    data: "langsir",
                    render: function(data) {
                        return data ? parseFloat(data).toLocaleString('id-ID', {minimumFractionDigits: 2}) : '-';
                    }
                },
                { 
                    data: "crane",
                    render: function(data) {
                        return data ? parseFloat(data).toLocaleString('id-ID', {minimumFractionDigits: 2}) : '-';
                    }
                },
                { 
                    data: "charter_boat",
                    render: function(data) {
                        return data ? parseFloat(data).toLocaleString('id-ID', {minimumFractionDigits: 2}) : '-';
                    }
                },
                
                { 
                    data: "total_amount",
                    render: function(data) {
                        return data ? parseFloat(data).toLocaleString('id-ID', {minimumFractionDigits: 2}) : '-';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm submit-approved-btn" 
                                    data-id="${row.id}" 
                                    data-dn="${row.dn_number}" 
                                    title="Input Date Approval">
                                <i class="fa-solid fa-calendar"></i>
                            </button>
                        `;
                    }
                }
            ],
            order: [[0, "asc"]],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            },
            scrollX: true,
            fixedColumns: {
                leftColumns: 0,
                rightColumns: 1
            },
            initComplete: function () {
                initDataTableSearch(this.api());
                feather.replace();
            },
            destroy: true
        });
    }

    // Refresh feather icons setelah draw
    $(document).on('draw.dt', '#tabelApprovedPI', function () {
        feather.replace();
    });

    // Event handler untuk tombol submit approved PI
    $(document).on('click', '.submit-approved-btn', function() {
        const id = $(this).data('id');
        const dnNumber = $(this).data('dn');
        
        // Set data ke modal
        $('#approved_pi_id').val(id);
        $('#approved_pi_dn').text(dnNumber);
        
        // Reset form
        $('#formApprovedPI')[0].reset();
        $('#approved_pi_id').val(id); // Set ulang ID setelah reset
        
        // Tampilkan modal
        $('#modalApprovedPI').modal('show');
    });

    // Event handler untuk submit form
    $('#formApprovedPI').on('submit', function(e) {
        e.preventDefault();
        
        const id = $('#approved_pi_id').val();
        const dateConfirmVendors = $('#date_confirm_vendors').val();
        
        if (!dateConfirmVendors) {
            showErrorToast("Error", "Tanggal harus diisi!");
            return;
        }
        
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop("disabled", true);
        
        // Kirim data ke server
        $.ajax({
            url: 'modules/update_date_confirm_vendors',
            method: 'POST',
            data: {
                id: id,
                date_confirm_vendors: dateConfirmVendors
            },
            beforeSend: showLoading,
            success: function(response) {
                hideLoading();
                $btn.prop("disabled", false);
                
                try {
                    let res = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (res.success) {
                        $('#modalApprovedPI').modal('hide');
                        $('#tabelApprovedPI').DataTable().ajax.reload(null, false);
                        
                        let message = 'Data berhasil diupdate!';
                        if (res.calculated) {
                            let details = [];
                            if (res.calculated.grouping_aging_day) details.push(`Grouping: ${res.calculated.grouping_aging_day}`);
                            if (res.calculated.achieved_failed) details.push(`Status: ${res.calculated.achieved_failed}`);
                            if (res.calculated.status_var_vendors) details.push(`Status Vendors: ${res.calculated.status_var_vendors}`);
                            
                            if (details.length > 0) {
                                message += '<br><small>' + details.join(' | ') + '</small>';
                            }
                        }
                        
                        showSuccessToast("Berhasil", message);
                    } else {
                        showErrorToast("Gagal", res.message || "Terjadi kesalahan!");
                    }
                } catch (err) {
                    console.error('Parse error:', err);
                    showErrorToast("Error", "Response tidak valid dari server");
                }
            },
            error: function(xhr) {
                hideLoading();
                $btn.prop("disabled", false);
                console.error("Error:", xhr.responseText);
                
                let errorMsg = "Gagal update data!";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                showErrorToast("Error", errorMsg);
            }
        });
    });

    // Export Excel
    $("#exportExcelApprovedPI").on("click", function () {
        window.location.href = "API/export_approved_pi_excel";
    });

    // Reset form saat modal ditutup
    $("#modalApprovedPI").on("hidden.bs.modal", function () {
        $(this).find("form")[0].reset();
    });
}

// Auto-init jika fungsi helper belum ada
if (typeof showLoading === 'undefined') {
    window.showLoading = function() { console.log('Loading...'); };
    window.hideLoading = function() { console.log('Loaded'); };
}

if (typeof showSuccessToast === 'undefined') {
    window.showSuccessToast = function(title, message) {
        console.log('SUCCESS:', title, message);
        alert(title + '\n' + message);
    };
}

if (typeof showErrorToast === 'undefined') {
    window.showErrorToast = function(title, message) {
        console.error('ERROR:', title, message);
        alert(title + '\n' + message);
    };
}

if (typeof initDataTableSearch === 'undefined') {
    window.initDataTableSearch = function(api) {
        api.columns().every(function() {
            let column = this;
            $('input', this.header()).on('keyup change', function() {
                if (column.search() !== this.value) {
                    column.search(this.value).draw();
                }
            });
        });
    };
}