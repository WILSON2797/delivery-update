function initPageScripts() {
    console.log("✅ DailyReport.js loaded");

    function initDateTimePicker() {
        if (typeof flatpickr === "undefined") {
            console.error("❌ Flatpickr belum ter-load");
            return;
        }

        flatpickr(".datetime-picker", {
            enableTime: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i",
            allowInput: true,
            minuteIncrement: 5
        });

        flatpickr(".date-picker", {
        dateFormat: "Y-m-d",
        allowInput: true,
        // opsional: biar lebih rapi
        altInput: true,
        altFormat: "d-m-Y",
    });
    }

    // INIT SAAT PAGE LOAD
    initDateTimePicker();

    // Inisialisasi DataTable
    if (!$.fn.DataTable.isDataTable('#tabelDailyReport')) {
        $("#tabelDailyReport").DataTable({
            ajax: {
                url: "API/data_table_daily_report",
                dataSrc: "data",
                beforeSend: showLoading,
                complete: hideLoading,
            },
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1 },
                { data: "date_request" },
                { data: "driver_name" },
                { data: "phone" },
                { data: "nopol" },
                { data: "dn_number" },
                { data: "site_id" },
                { data: "sub_project" },
                { data: "plan_from" },
                { data: "destination_city" },
                { data: "destination_province" },
                { data: "subcon" },
                { data: "mot" },
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
                { data: "latest_status" },
                {
                    data: null,
                    orderable: false,
                    render: function (data) {
                        return `
                            <button class="btn btn-sm btn-warning edit-report" data-id="${data.id}" title="Edit">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn btn-sm btn-success copy-to-whatsapp" data-id="${data.id}" title="Copy to WA">
                                <i class="fas fa-copy"></i>
                            </button>
                        `;
                    }
                }
            ],
            order: [[0, "asc"]],
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
            scrollX: true,
            fixedColumns: { leftColumns: 0, rightColumns: 1 },
            initComplete: function () {
                initDataTableSearch(this.api());
            },
            destroy: true
        });
    }

    // Export Excel
    $("#exportExcelReport").on("click", function () {
        window.location.href = "API/export_daily_report";
    });

    // ===== FUNGSI VALIDASI =====
    function validateAddForm() {
        const status = $('select[name="status"]').val();
        const podDatetime = $('input[name="pod_datetime"]').val();
        
        if (status === "Handover Done" && !podDatetime) {
            showErrorToast("Validasi Gagal", "POD DateTime wajib diisi ketika status Handover Done!");
            return false;
        }
        return true;
    }

    function validateEditForm() {
        const status = $('#edit_status').val();
        const podDatetime = $('#edit_pod_datetime').val();
        
        if (status === "Handover Done" && !podDatetime) {
            showErrorToast("Validasi Gagal", "POD DateTime wajib diisi ketika status Handover Done!");
            return false;
        }
        return true;
    }

    // Submit Tambah Data dengan Validasi
    $("#addReportForm").on("submit", function (e) {
        e.preventDefault();
        
        // Validasi terlebih dahulu
        if (!validateAddForm()) {
            return false;
        }
        
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop("disabled", true);
        showLoading();

        $.ajax({
            url: "modules/proses_daily_report_add",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (res) {
                hideLoading();
                $btn.prop("disabled", false);
                if (res.status === "success") {
                    $("#addReportModal").modal("hide");
                    $("#tabelDailyReport").DataTable().ajax.reload();
                    showSuccessToast(res.message || "Data berhasil disimpan");
                } else {
                    showErrorToast("Gagal", res.message || "Terjadi kesalahan");
                }
            },
            error: function (xhr) {
                hideLoading();
                $btn.prop("disabled", false);
                let errorMsg = "Gagal menyimpan data";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showErrorToast("Error", errorMsg);
            }
        });
    });

    // Klik Edit - POPULATE SEMUA FIELD
    $(document).on("click", ".edit-report", function () {
        const id = $(this).data("id");
        showLoading();

        $.ajax({
            url: "modules/get_daily_report_detail",
            type: "GET",
            data: { id: id },
            dataType: "json",
            success: function (res) {
                hideLoading();
                if (res.status === "success") {
                    const d = res.data;

                    // Informasi Dasar
                    $("#edit_id").val(d.id || "");
                    $("#edit_date_request").val(d.date_request || "");
                    $("#edit_dn_number").val(d.dn_number || "");
                    $("#edit_sub_project").val(d.sub_project || "");
                    $("#edit_site_id").val(d.site_id || "");

                    // Lokasi & Tujuan
                    $("#edit_plan_from").val(d.plan_from || "");
                    $("#edit_destination_city").val(d.destination_city || "");
                    $("#edit_destination_province").val(d.destination_province || "");
                    $("#edit_destination_address").val(d.destination_address || "");
                    $("#edit_latitude").val(d.latitude || "");
                    $("#edit_longitude").val(d.longitude || "");

                    // Schedule & SLA
                    $("#edit_rsd").val(d.rsd || "");
                    $("#edit_rad").val(d.rad || "");
                    $("#edit_sla").val(d.sla || "");

                    // Timeline Tracking
                    if (d.truck_on_warehouse) $("#edit_truck_on_warehouse").val(d.truck_on_warehouse.replace(' ', 'T').substring(0, 16));
                    if (d.atd_whs_dispatch) $("#edit_atd_whs_dispatch").val(d.atd_whs_dispatch.replace(' ', 'T').substring(0, 16));
                    if (d.atd_pool_dispatch) $("#edit_atd_pool_dispatch").val(d.atd_pool_dispatch.replace(' ', 'T').substring(0, 16));
                    if (d.ata_mover_on_site) $("#edit_ata_mover_on_site").val(d.ata_mover_on_site.replace(' ', 'T').substring(0, 16));
                    if (d.receiver_on_site_datetime) $("#edit_receiver_on_site_datetime").val(d.receiver_on_site_datetime.replace(' ', 'T').substring(0, 16));
                    if (d.pod_datetime) $("#edit_pod_datetime").val(d.pod_datetime.replace(' ', 'T').substring(0, 16));

                    // Shipment Details
                    $("#edit_volume").val(d.volume || "");
                    $("#edit_gross_weight").val(d.gross_weight || "");
                    $("#edit_type_shipment").val(d.type_shipment || "");
                    $("#edit_status").val(d.status || "");
                    $("#edit_pod_type").val(d.pod_type || "");
                    $("#edit_mot").val(d.mot || "");

                    // Vendor & Driver
                    $("#edit_driver_name").val(d.driver_name || "");
                    $("#edit_nopol").val(d.nopol || "");
                    $("#edit_phone").val(d.phone || "");

                    // PIC & Receiver
                    $("#edit_pic_on_dn").val(d.pic_on_dn || "");
                    $("#edit_pic_mobile_no").val(d.pic_mobile_no || "");
                    $("#edit_receiver_on_site").val(d.receiver_on_site || "");
                    $("#edit_subcon").val(d.subcon || "");

                    // Additional Cost
                    $("#edit_nominal_add_cost").val(d.nominal_add_cost || "");
                    $("#edit_detail_add_cost").val(d.detail_add_cost || "");
                    $("#edit_approval_by_whatsapp").val(d.approval_by_whatsapp || "");
                    $("#edit_rise_up_by_email").val(d.rise_up_by_email || "");
                    $("#edit_approved_by_email").val(d.approved_by_email || "");
                    $("#edit_remarks_add_cost").val(d.remarks_add_cost || "");

                    // Status & Remarks
                    $("#edit_htm").val(d.htm || "");
                    $("#edit_overnight_day").val(d.overnight_day || "");
                    $("#edit_latest_status").val(d.latest_status || "");
                    $("#edit_remarks").val(d.remarks || "");

                    // Trigger change event untuk menampilkan warning jika status Handover Done
                    $("#edit_status").trigger('change');

                    $("#editReportModal").modal("show");
                } else {
                    showErrorToast("Error", res.message);
                }
            },
            error: function () {
                hideLoading();
                showErrorToast("Error", "Gagal mengambil data");
            }
        });
    });

    // Submit Edit Data dengan Validasi
    $("#editReportForm").on("submit", function (e) {
        e.preventDefault();
        
        // Validasi terlebih dahulu
        if (!validateEditForm()) {
            return false;
        }
        
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop("disabled", true);
        showLoading();

        $.ajax({
            url: "modules/proses_daily_report_update",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (res) {
                hideLoading();
                $btn.prop("disabled", false);
                if (res.status === "success") {
                    $("#editReportModal").modal("hide");
                    $("#tabelDailyReport").DataTable().ajax.reload();
                    showSuccessToast(res.message || "Data berhasil diupdate");
                } else {
                    showErrorToast("Gagal", res.message || "Terjadi kesalahan");
                }
            },
            error: function (xhr) {
                hideLoading();
                $btn.prop("disabled", false);
                let errorMsg = "Gagal mengupdate data";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showErrorToast("Error", errorMsg);
            }
        });
    });

    // Event listener untuk Status Change - Menampilkan warning POD DateTime
    $(document).on('change', 'select[name="status"], #edit_status', function() {
        const status = $(this).val();
        const isEdit = $(this).attr('id') === 'edit_status';
        const podField = isEdit ? '#edit_pod_datetime' : 'input[name="pod_datetime"]';
        
        if (status === "Handover Done") {
            $(podField).addClass('border-warning');
            $(podField).attr('required', true);
            
            // Hapus warning lama jika ada
            $(podField).next('.text-warning').remove();
            
            // Tampilkan peringatan
            $(podField).after('<small class="text-warning d-block mt-1">⚠️ POD DateTime wajib diisi untuk status Handover Done</small>');
        } else {
            $(podField).removeClass('border-warning');
            $(podField).removeAttr('required');
            $(podField).next('.text-warning').remove();
        }
    });

    // Reset form saat modal ditutup
    $("#addReportModal, #editReportModal").on("hidden.bs.modal", function () {
        $(this).find("form")[0].reset();
        // Hapus semua warning message
        $(this).find('.text-warning').remove();
        $(this).find('input, select, textarea').removeClass('border-warning');
    });

   // Event untuk tombol Copy to WA
$("#tabelDailyReport").off("click.copywa").on("click.copywa", ".copy-to-whatsapp", function () {
    const id = $(this).data("id");
    const rowData = $(this).closest("table").DataTable().row($(this).closest("tr")).data();
    
    if (!rowData) {
        showErrorToast("Error", "Data baris tidak ditemukan");
        return;
    }
    
    // Generate teks format
    const text = `
${rowData.dn_number || '-'}
Site ID : ${rowData.site_id || '-'}
Project : ${rowData.sub_project || '-'}
Region : ${rowData.destination_city || '-'}
Delivery Adress : ${rowData.destination_address || '-'}
maps : http://maps.google.com/?q=${rowData.latitude || ''},${rowData.longitude || ''}
Receiver : ${rowData.receiver_on_site || '-'}
Subcont : ${rowData.subcon || '-'}
Subcont Aktual : ${rowData.subcon || '-'}
PIC : ${rowData.pic_on_dn || '-'} ${rowData.pic_mobile_no ? '- ' + rowData.pic_mobile_no : ''}
Driver : ${rowData.driver_name || '-'}
No.pol : ${rowData.nopol || '-'}
TLP : ${rowData.phone || '-'}
Status : ${rowData.status || '-'}

${rowData.latest_status || 'Tidak ada status'}
    `.trim();
    
    // Copy ke clipboard
    navigator.clipboard.writeText(text).then(() => {
        showSuccessToast("Berhasil", "Teks berhasil dicopy ke clipboard! Silakan paste ke WA/Notepad.");
    }).catch(err => {
        showErrorToast("Gagal", "Gagal menyalin teks: " + err);
    });
});
}