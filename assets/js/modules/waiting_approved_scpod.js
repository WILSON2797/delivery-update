function initPageScripts() {
    console.log("✅ waiting_approved_scpod.js loaded");


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
    if (!$.fn.DataTable.isDataTable('#approvedscpod')) {
        $("#approvedscpod").DataTable({
            ajax: {
                url: "API/table_waiting_approved_scpod",
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
                {
                    data: "status",
                    render: function (data) {
                        let color = "secondary";
                        if (data === "On Delivery") color = "primary";
                        else if (data === "Onsite") color = "warning";
                        else if (data === "Handover Done") color = "success";
                        else if (data === "Back To Pool") color = "dark";
                        return `<span class="badge bg-${color}">${data}</span>`;
                    }
                },
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
                {
                    data: "date_approved_sc_pod",
                    render: function (data) {
                        if (!data) {
                            return `<span class="badge bg-warning">Waiting Approval</span>`;
                        }
                        return `<span class="badge bg-success">Approved</span>`;
                    }
                },
                
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm approve-btn" data-id="${row.id}" data-dn="${row.dn_number}" title="Input Date">
                                <i class="fa-solid fa-calendar"></i>
                            </button>
                        `;
                    }
                }
            ],
            order: [[4, "desc"]],
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

    // Refresh feather icons
    $(document).on('draw.dt', '#approvedscpod', function () {
        feather.replace();
    });

    // Event handler untuk tombol approve
    $(document).on('click', '.approve-btn', function() {
        const id = $(this).data('id');
        const dnNumber = $(this).data('dn');
        
        // Set data ke modal
        $('#modal_approved_id').val(id);
        $('#modal_approved_dn').text(dnNumber);
        
        // Reset form
        $('#form_approve_scpod')[0].reset();
        
        // Set default date to today
        const today = new Date().toISOString().split('T')[0];
        // $('#date_approved_sc_pod').val(today);
        
        // Tampilkan modal
        $('#modalApproveSCPOD').modal('show');
    });

    // Event handler untuk submit form
    $('#form_approve_scpod').on('submit', function(e) {
        e.preventDefault();
        
        const id = $('#modal_approved_id').val();
        const dateApprovedScPod = $('#date_approved_sc_pod').val();
        
        if (!dateApprovedScPod) {
            showErrorToast("Error", "Tanggal harus diisi!");
            return;
        }
        
        // Kirim data ke server
        $.ajax({
            url: 'modules/update_approved_scpod.php',
            method: 'POST',
            data: {
                id: id,
                date_approved_sc_pod: dateApprovedScPod
            },
            beforeSend: showLoading,
            success: function(response) {
                hideLoading();
                $('#modalApproveSCPOD').modal('hide');
                
                if (response.success) {
                    showSuccessToast("Berhasil", response.message || "Data berhasil diupdate!");
                    
                    // Reload datatable
                    $('#approvedscpod').DataTable().ajax.reload(null, false);
                } else {
                    showErrorToast("Error", response.message || "Gagal update data!");
                }
            },
            error: function(xhr) {
                hideLoading();
                console.error("Error:", xhr.responseText);
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    showErrorToast("Error", response.message || "Gagal update data!");
                } catch (e) {
                    showErrorToast("Error", "Gagal update data!");
                }
            }
        });
    });
}