function initPageScripts() {
    console.log("✅ waiting_upload_scpod.js loaded");

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
    if (!$.fn.DataTable.isDataTable('#uploadscpod')) {
        $("#uploadscpod").DataTable({
            ajax: {
                url: "API/table_waiting_upload_scpod",
                dataSrc: "data",
                // beforeSend: showLoading,
                // complete: hideLoading,
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
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm edit-btn" data-id="${row.id}" data-dn="${row.dn_number}" title="Input Date">
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
    $(document).on('draw.dt', '#uploadscpod', function () {
        feather.replace();
    });

    // Event handler untuk tombol edit/submit
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        const dnNumber = $(this).data('dn');
        
        // Set data ke modal
        $('#modal_scpod_id').val(id);
        $('#modal_scpod_dn').text(dnNumber);
        
        // Reset form
        $('#form_submit_scpod')[0].reset();
        
        // Tampilkan modal
        $('#modalSubmitSCPOD').modal('show');
    });

    // Event handler untuk submit form
    $('#form_submit_scpod').on('submit', function(e) {
        e.preventDefault();
        
        const id = $('#modal_scpod_id').val();
        const dateSendScPod = $('#date_send_sc_pod').val();
        
        if (!dateSendScPod) {
            showErrorToast("Error", "Tanggal harus diisi!");
            return;
        }
        
        // Kirim data ke server
        $.ajax({
            url: 'modules/update_date_send_scpod', // Sesuaikan dengan endpoint Anda
            method: 'POST',
            data: {
                id: id,
                date_send_sc_pod: dateSendScPod
            },
            beforeSend: showLoading,
            success: function(response) {
                hideLoading();
                $('#modalSubmitSCPOD').modal('hide');
                showSuccessToast("Berhasil", "Data berhasil diupdate!");
                
                // Reload datatable
                $('#uploadscpod').DataTable().ajax.reload(null, false);
            },
            error: function(xhr) {
                hideLoading();
                console.error("Error:", xhr.responseText);
                showErrorToast("Error", "Gagal update data!");
            }
        });
    });
}