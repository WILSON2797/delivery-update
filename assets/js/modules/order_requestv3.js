function initPageScripts() {
    console.log("✅ DailyReport.js loaded");

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

    // ===== INISIALISASI DROPDOWN SELECT2 START=====
    function initDropdowns() {
        const promises = [
            loadDropdownData($('#destination_province'), 'province_city', 'province', 'province', '#addReportModal'),
            loadDropdownData($('#plan_from'), 'master_origin', 'origin_code', 'origin_code', '#addReportModal'),
            loadDropdownData($('#type_shipment'), 'type_shipment', 'type_shipment', 'type_shipment', '#addReportModal'),
            loadDropdownData($('#mot'), 'master_mode_of_transport', 'mot_code', 'mot_code', '#addReportModal')
        ];
        
        return Promise.all(promises);
    }

    function initEditDropdowns(motValue = null, statusValue = null) {
        console.log(`🔄 Loading edit dropdowns: MOT=${motValue}, Status=${statusValue}`);
        
        const promises = [
            loadDropdownData($('#edit_mot'), 'master_mode_of_transport', 'mot_code', 'mot_code', '#editReportModal', motValue),
            loadDropdownData($('#edit_status'), 'status_delivery', 'code', 'code', '#editReportModal', statusValue)
        ];
        
        return Promise.all(promises);
    }
    
    // ✅ EVENT LISTENER: Saat Province dipilih di ADD MODAL
    $(document).on('change', '#destination_province', function() {
        const selectedProvince = $(this).val();
        const $cityDropdown = $('#destination_city');
        
        // Reset city dropdown
        $cityDropdown.empty().append('<option value="">Pilih City</option>');
        
        if (selectedProvince) {
            // ✅ Load city dengan FILTER province
            loadDropdownData(
                $cityDropdown,           // element
                'province_city',         // table
                'city',                  // column
                'city',                  // display
                '#addReportModal',       // modalId
                null,                    // defaultValue
                'province',              // ✅ filterColumn
                selectedProvince         // ✅ filterValue
            );
        } else {
            // Jika province dikosongkan, reinit select2 untuk city
            if ($cityDropdown.hasClass('select2-hidden-accessible')) {
                $cityDropdown.select2('destroy');
            }
            $cityDropdown.select2({
                width: "100%",
                dropdownParent: $('#addReportModal'),
                allowClear: true,
                placeholder: "Pilih City",
            });
        }
    });
    
    // ✅ EVENT LISTENER: Saat Province dipilih di EDIT MODAL (jika ada)
    $(document).on('change', '#edit_destination_province', function() {
        const selectedProvince = $(this).val();
        const $cityDropdown = $('#edit_destination_city');
        
        $cityDropdown.empty().append('<option value="">Pilih City</option>');
        
        if (selectedProvince) {
            loadDropdownData(
                $cityDropdown,
                'province_city',
                'city',
                'city',
                '#editReportModal',
                null,
                'province',              // ✅ filterColumn
                selectedProvince         // ✅ filterValue
            );
        }
    });
    
    // EVENT: Add Modal show
    $('#addReportModal').on('show.bs.modal', function() {
        $("#addReportForm")[0].reset();
        initDropdowns();
    });

    

    // ===== FUNGSI HELPER UNTUK SPLIT & COMBINE DATETIME =====
    function splitDateTime(datetime, dateFieldId, timeFieldId) {
        if (!datetime) {
            $(`#${dateFieldId}`).val('');
            $(`#${timeFieldId}`).val('');
            return;
        }
        
        const parts = datetime.split(' ');
        if (parts.length === 2) {
            $(`#${dateFieldId}`).val(parts[0]); // YYYY-MM-DD
            $(`#${timeFieldId}`).val(parts[1].substring(0, 5)); // HH:mm (tanpa detik)
        }
    }

    function combineDateTime(dateFieldId, timeFieldId, hiddenFieldId) {
        const date = $(`#${dateFieldId}`).val();
        const time = $(`#${timeFieldId}`).val();
        
        if (date && time) {
            $(`#${hiddenFieldId}`).val(`${date} ${time}`);
        } else {
            $(`#${hiddenFieldId}`).val('');
        }
    }

    // ===== AUTO COMBINE DATE + TIME KE HIDDEN FIELD =====
    $(document).on('change', '#edit_truck_on_warehouse_date, #edit_truck_on_warehouse_time', function() {
        combineDateTime('edit_truck_on_warehouse_date', 'edit_truck_on_warehouse_time', 'edit_truck_on_warehouse');
    });

    $(document).on('change', '#edit_atd_whs_dispatch_date, #edit_atd_whs_dispatch_time', function() {
        combineDateTime('edit_atd_whs_dispatch_date', 'edit_atd_whs_dispatch_time', 'edit_atd_whs_dispatch');
    });

    $(document).on('change', '#edit_atd_pool_dispatch_date, #edit_atd_pool_dispatch_time', function() {
        combineDateTime('edit_atd_pool_dispatch_date', 'edit_atd_pool_dispatch_time', 'edit_atd_pool_dispatch');
    });

    $(document).on('change', '#edit_ata_mover_on_site_date, #edit_ata_mover_on_site_time', function() {
        combineDateTime('edit_ata_mover_on_site_date', 'edit_ata_mover_on_site_time', 'edit_ata_mover_on_site');
    });

    $(document).on('change', '#edit_receiver_on_site_date, #edit_receiver_on_site_time', function() {
        combineDateTime('edit_receiver_on_site_date', 'edit_receiver_on_site_time', 'edit_receiver_on_site_datetime');
    });

    $(document).on('change', '#edit_pod_datetime_date, #edit_pod_datetime_time', function() {
        combineDateTime('edit_pod_datetime_date', 'edit_pod_datetime_time', 'edit_pod_datetime');
    });

    // Validasi phone number input - hanya izinkan angka, +, -, dan spasi
    $(document).on('input', '.phone-input', function() {
        const start = this.selectionStart;
        const end = this.selectionEnd;
        
        const cleanedValue = this.value.replace(/[^0-9+\-\s]/g, '');
        
        if (this.value !== cleanedValue) {
            this.value = cleanedValue;
            this.setSelectionRange(start - 1, end - 1);
        }
    });

    // Inisialisasi DataTable
    if (!$.fn.DataTable.isDataTable('#tabelDailyReport')) {
        $("#tabelDailyReport").DataTable({
            ajax: {
                url: "API/data_table_daily_report",
                dataSrc: "data",
                // beforeSend: showLoading,
                // complete: hideLoading,
            },
            columns: [
                { data: null, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: "date_request" },
                { data: "dn_number" },
                { data: "driver_name" },
                { data: "phone" },
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
                { data: "updated_at" },
                {
                    data: null,
                    orderable: false,
                    render: function (data) {
                        return `
                            <button class="btn btn-sm btn-warning edit-report" data-id="${data.id}" title="Update Report">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn btn-sm btn-success copy-to-whatsapp" data-id="${data.id}" title="Copied to Clipboard">
                                <i class="fas fa-copy"></i>
                            </button>
                        `;
                    }
                }
            ],
            order: [[15, "desc"]],
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
    function validateEditForm() {
    const status = $('#edit_status').val();
    const podDatetime = $('#edit_pod_datetime').val();
    const podType = $('#edit_pod_type').val();
    const ataMoverOnSite = $('#edit_ata_mover_on_site').val();
    
    // Validasi 1: Jika status "Handover Done", POD DateTime wajib diisi
    if (status === "Handover Done" && !podDatetime) {
        showErrorToast("Validasi Gagal", "POD DateTime wajib diisi ketika status Handover Done!");
        return false;
    }
    
    // Validasi 2: Jika status "Handover Done", POD Type wajib diisi
    if (status === "Handover Done" && !podType) {
        showErrorToast("Validasi Gagal", "POD Type (MPOD/EPOD) wajib diisi ketika status Handover Done!");
        return false;
    }
    
    // Validasi 3: Jika status "Handover Done", ATA Mover on Site wajib diisi
    if (status === "Handover Done" && !ataMoverOnSite) {
        showErrorToast("Validasi Gagal", "ATA Mover on Site wajib diisi ketika status Handover Done!");
        return false;
    }
    
    // Validasi 4: Jika POD DateTime diisi, status harus "Handover Done"
    if (podDatetime && status !== "Handover Done") {
        showErrorToast("Validasi Gagal", "Status harus 'Handover Done' jika POD DateTime diisi!");
        return false;
    }
    
    return true;
}

    // ===== SUBMIT ADD DATA =====
    $("#addReportForm").on("submit", function (e) {
        e.preventDefault();
        
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

    // ===== FUNGSI PARSE TANGGAL DARI BERBAGAI FORMAT =====
    function parseDateFromLine(line) {
        // Pattern: DD/MM/YYYY HH:mm atau DD/MM/YY HH:mm
        const pattern1 = /^(\d{2})\/(\d{2})\/(\d{2,4})\s+(\d{2}):(\d{2})/;
        const match = line.match(pattern1);
        
        if (match) {
            let [, day, month, year, hour, minute] = match;
            
            // Konversi YY ke YYYY
            if (year.length === 2) {
                year = '20' + year;
            }
            
            // Create date object (month - 1 karena JS month 0-indexed)
            return new Date(year, month - 1, day, hour, minute);
        }
        
        return null;
    }

    // ===== FUNGSI SORT SEMUA BARIS BERDASARKAN TANGGAL =====
    function sortAllLinesByDate(textContent) {
        if (!textContent || !textContent.trim()) return '';
        
        const lines = textContent.split('\n');
        const linesWithDate = [];
        const linesWithoutDate = [];
        
        lines.forEach(line => {
            const trimmed = line.trim();
            if (!trimmed) return;
            
            const date = parseDateFromLine(trimmed);
            
            if (date && !isNaN(date.getTime())) {
                linesWithDate.push({
                    date: date,
                    text: trimmed
                });
            } else {
                linesWithoutDate.push(trimmed);
            }
        });
        
        // Sort baris yang ada tanggalnya (ascending)
        linesWithDate.sort((a, b) => a.date - b.date);
        
        // Gabungkan: baris dengan tanggal (sorted) + baris tanpa tanggal
        const sortedLines = linesWithDate.map(item => item.text);
        
        return [...sortedLines, ...linesWithoutDate].join('\n');
    }

    // ===== FUNGSI GENERATE TIMELINE =====
    function generateTimeline() {
        const timelineFields = [
            { id: 'edit_truck_on_warehouse', label: 'Truck on Warehouse' },
            { id: 'edit_atd_whs_dispatch', label: 'Dispatch From WH' },
            { id: 'edit_atd_pool_dispatch', label: 'Dispatch From Pool' },
            { id: 'edit_ata_mover_on_site', label: 'Mover Onsite' },
            { id: 'edit_pod_datetime', label: 'HO Done' }
        ];

        let timelineData = [];
        
        timelineFields.forEach(field => {
            const value = $(`#${field.id}`).val(); // ✅ FIXED: Tambah backtick
            if (value) {
                const formatted = formatDateTimeToTimeline(value);
                if (formatted) {
                    timelineData.push({
                        datetime: new Date(value.replace(' ', 'T')),
                        formatted: formatted,
                        label: field.label
                    });
                }
            }
        });

        // Sort berdasarkan datetime (ascending)
        timelineData.sort((a, b) => a.datetime - b.datetime);

        // Convert ke format string
        return timelineData.map(item => `${item.formatted} : ${item.label}`).join('\n');
    }

    // ===== FORMAT DATETIME =====
    function formatDateTimeToTimeline(datetime) {
        if (!datetime) return '';
        
        const parts = datetime.split(' ');
        if (parts.length !== 2) return '';
        
        const dateParts = parts[0].split('-');
        if (dateParts.length !== 3) return '';
        
        const [year, month, day] = dateParts;
        
        const timeParts = parts[1].split(':');
        const time = `${timeParts[0]}:${timeParts[1]}`;
        
        return `${day}/${month}/${year} ${time}`;
    }

    // ===== KLIK EDIT - POPULATE SEMUA FIELD + UPDATE HEADER =====
    $(document).on("click", ".edit-report", function () {
        const id = $(this).data("id");
        // showLoading();

        $.ajax({
            url: "modules/get_daily_report_detail",
            type: "GET",
            data: { id: id },
            dataType: "json",
            success: function (res) {
                if (res.status === "success") {
                    const d = res.data;

                    // Set header dan ID
                    $("#editDnNumberHeader").text(d.dn_number || "N/A");
                    $("#edit_id").val(d.id || "");
                    $("#edit_transaction_id").val(d.transaction_id || "");

                    // Set semua field
                    $("#edit_date_request").val(d.date_request || "");
                    $("#edit_dn_number").val(d.dn_number || "");
                    $("#edit_driver_name").val(d.driver_name || "");
                    $("#edit_nopol").val(d.nopol || "");
                    $("#edit_phone").val(d.phone || "");
                    $("#edit_subcon").val(d.subcon || "");
                    $("#edit_latitude").val(d.latitude || "");
                    $("#edit_longitude").val(d.longitude || "");
                    $("#edit_remarks").val(d.remarks || "");

                    // Timeline fields
                    splitDateTime(d.truck_on_warehouse, 'edit_truck_on_warehouse_date', 'edit_truck_on_warehouse_time');
                    splitDateTime(d.atd_whs_dispatch, 'edit_atd_whs_dispatch_date', 'edit_atd_whs_dispatch_time');
                    splitDateTime(d.atd_pool_dispatch, 'edit_atd_pool_dispatch_date', 'edit_atd_pool_dispatch_time');
                    splitDateTime(d.ata_mover_on_site, 'edit_ata_mover_on_site_date', 'edit_ata_mover_on_site_time');
                    splitDateTime(d.receiver_on_site_datetime, 'edit_receiver_on_site_date', 'edit_receiver_on_site_time');
                    splitDateTime(d.pod_datetime, 'edit_pod_datetime_date', 'edit_pod_datetime_time');
                    
                    $("#edit_truck_on_warehouse").val(d.truck_on_warehouse || "");
                    $("#edit_atd_whs_dispatch").val(d.atd_whs_dispatch || "");
                    $("#edit_atd_pool_dispatch").val(d.atd_pool_dispatch || "");
                    $("#edit_ata_mover_on_site").val(d.ata_mover_on_site || "");
                    $("#edit_receiver_on_site_datetime").val(d.receiver_on_site_datetime || "");
                    $("#edit_pod_datetime").val(d.pod_datetime || "");

                    $("#edit_pod_type").val(d.pod_type || "");
                    $("#edit_pic_on_dn").val(d.pic_on_dn || "");
                    $("#edit_pic_mobile_no").val(d.pic_mobile_no || "");
                    $("#edit_receiver_on_site").val(d.receiver_on_site || "");
                    $("#edit_nominal_add_cost").val(d.nominal_add_cost || "");
                    $("#edit_detail_add_cost").val(d.detail_add_cost || "");
                    $("#edit_approval_by_whatsapp").val(d.approval_by_whatsapp || "");
                    $("#edit_rise_up_by_email").val(d.rise_up_by_email || "");
                    $("#edit_approved_by_email").val(d.approved_by_email || "");
                    $("#edit_remarks_add_cost").val(d.remarks_add_cost || "");
                    $("#edit_overnight_day").val(d.overnight_day || "");

                    // Generate timeline
                    const existingLatestStatus = d.latest_status || "";
                    const autoTimeline = generateTimeline();
                    const timelinePattern = /^\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}(:\d{2})?\s*:\s*(Truck on Warehouse|Dispatch From WH|Dispatch From Pool|Mover Onsite|HO Done)$/;
                    const existingLines = existingLatestStatus.split('\n');
                    const manualLogs = existingLines.filter(line => {
                        const trimmed = line.trim();
                        return trimmed && !timelinePattern.test(trimmed);
                    });
                    let combinedContent = autoTimeline;
                    if (manualLogs.length > 0) {
                        combinedContent = combinedContent ? combinedContent + '\n' + manualLogs.join('\n') : manualLogs.join('\n');
                    }
                    const sortedContent = sortAllLinesByDate(combinedContent);
                    $("#edit_latest_status").val(sortedContent);

                    initDateTimePicker();

                    // ✅ KUNCI: Load dropdown DENGAN nilai, tunggu selesai
                    hideLoading();
                    showLoading("Loading dropdown data...");

                    initEditDropdowns(d.mot || "", d.status || "")
                        .then((results) => {
                            console.log("✅ Dropdowns loaded:", results);
                            hideLoading();
                            
                            $("#edit_status").trigger('change');
                            $("#editReportModal").modal("show");
                        })
                        .catch(error => {
                            console.error("❌ Error loading dropdowns:", error);
                            hideLoading();
                            showErrorToast("Error", "Gagal memuat dropdown: " + error.message);
                        });
                        
                } else {
                    hideLoading();
                    showErrorToast("Error", res.message);
                }
            },
            error: function () {
                hideLoading();
                showErrorToast("Error", "Gagal mengambil data");
            }
        });
    });


    // ===== LIVE PREVIEW TIMELINE SAAT DATETIME FIELDS BERUBAH =====
    $(document).on('change', 
        '#edit_truck_on_warehouse_date, #edit_truck_on_warehouse_time, ' +
        '#edit_atd_whs_dispatch_date, #edit_atd_whs_dispatch_time, ' +
        '#edit_atd_pool_dispatch_date, #edit_atd_pool_dispatch_time, ' +
        '#edit_ata_mover_on_site_date, #edit_ata_mover_on_site_time, ' +
        '#edit_pod_datetime_date, #edit_pod_datetime_time', 
        function() {
            const latestStatusTextarea = $('#edit_latest_status');
            const currentValue = latestStatusTextarea.val();
        
        // Daftar label yang digunakan untuk auto-timeline
        const autoTimelineLabels = [
            'Truck on Warehouse',
            'Dispatch From WH', 
            'Dispatch From Pool',
            'Mover Onsite',
            'HO Done'
        ];
        
        // Pattern untuk deteksi datetime line
        const timelinePattern = /^\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}(:\d{2})?\s*:\s*.+$/;
        
        // Pisahkan manual logs dari auto timeline
        const lines = currentValue.split('\n');
        const manualLogs = lines.filter(line => {
            const trimmed = line.trim();
            if (!trimmed) return false;
            
            // Cek apakah baris ini match pattern datetime
            if (!timelinePattern.test(trimmed)) {
                return true; // Bukan format datetime, anggap manual log
            }
            
            // Cek apakah ada label auto-timeline di baris ini
            const hasAutoLabel = autoTimelineLabels.some(label => trimmed.includes(`: ${label}`));
            
            // Jika ada label auto-timeline, buang (return false)
            // Jika tidak ada, anggap manual log (return true)
            return !hasAutoLabel;
        });
        
        // Generate timeline baru dari fields
        const newTimeline = generateTimeline();
        
        // Gabungkan timeline baru + manual logs
        let combinedContent = newTimeline;
        if (manualLogs.length > 0) {
            combinedContent = combinedContent ? combinedContent + '\n' + manualLogs.join('\n') : manualLogs.join('\n');
        }
        
        // 🔥 SORT SEMUA BARIS BERDASARKAN TANGGAL
        const sortedContent = sortAllLinesByDate(combinedContent);
        
        latestStatusTextarea.val(sortedContent);
    });

    // ===== SUBMIT EDIT DATA DENGAN VALIDASI =====
    $("#editReportForm").on("submit", function (e) {
        e.preventDefault();
        
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

    // Event listener untuk Status Change - Warning POD DateTime
    $(document).on('change', '#edit_status', function() {
        const status = $(this).val();
        const podField = $('#edit_pod_datetime');
        const podWrapper = podField.closest('.col-md-6');
        
        if (status === "Handover Done") {
            podField.addClass('border-warning');
            podField.attr('required', true);
            podWrapper.find('.pod-warning-msg').remove();
            podWrapper.append('<div class="pod-warning-msg"><small class="text-warning d-block mt-1"><i class="fas fa-exclamation-triangle me-1"></i>POD DateTime wajib diisi untuk status Handover Done</small></div>');
        } else {
            podField.removeClass('border-warning');
            podField.removeAttr('required');
            podWrapper.find('.pod-warning-msg').remove();
        }
    });

    // Reset form saat modal ditutup
    $("#addReportModal, #editReportModal").on("hidden.bs.modal", function () {
        $(this).find("form")[0].reset();
        $(this).find('.pod-warning-msg').remove();
        $(this).find('input, select, textarea').removeClass('border-warning');
    });

//     // Event untuk tombol Copy to WA
//     $("#tabelDailyReport").off("click.copywa").on("click.copywa", ".copy-to-whatsapp", function () {
//         const rowData = $(this).closest("table").DataTable().row($(this).closest("tr")).data();
        
//         if (!rowData) {
//             showErrorToast("Error", "Data baris tidak ditemukan");
//             return;
//         }
        
//         const mapsUrl = (rowData.latitude && rowData.longitude) 
//             ? `http://maps.google.com/?q=${rowData.latitude},${rowData.longitude}`
//             : '-';
        
//         const text = `
// ${rowData.dn_number || '-'}
// Site ID : ${rowData.site_id || '-'}
// Project : ${rowData.sub_project || '-'}
// Region : ${rowData.destination_city || '-'}
// Delivery Adress : ${rowData.destination_address || '-'}
// maps : ${mapsUrl}
// Receiver : ${rowData.receiver_on_site || '-'}
// Subcont : ${rowData.subcon || '-'}
// Subcont Aktual : ${rowData.subcon || '-'}
// PIC : ${rowData.pic_on_dn || '-'} ${rowData.pic_mobile_no ? '- ' + rowData.pic_mobile_no : ''}
// Driver : ${rowData.driver_name || '-'}
// No.pol : ${rowData.nopol || '-'}
// TLP : ${rowData.phone || '-'}
// Status : ${rowData.status || '-'}

// ${rowData.latest_status || 'Tidak ada status'}
//         `.trim();
        
//         navigator.clipboard.writeText(text).then(() => {
//             showSuccessToast("Berhasil", "Teks berhasil dicopy ke clipboard! Silakan paste ke WA/Notepad.");
//         }).catch(err => {
//             showErrorToast("Gagal", "Gagal menyalin teks: " + err);
//         });
//     });

// New code

$("#tabelDailyReport").off("click.copywa").on("click.copywa", ".copy-to-whatsapp", function () {
    const rowData = $(this).closest("table").DataTable().row($(this).closest("tr")).data();
    
    if (!rowData || !rowData.id) {
        showErrorToast("Error", "Data baris tidak ditemukan");
        return;
    }
    
    // // Show loading
    // showLoading("Memuat data...");
    
    // Fetch data lengkap via API
    $.ajax({
        url: "API/get_copy_whatsapp_data",
        type: "GET",
        data: { id: rowData.id },
        dataType: "json",
        success: function (res) {
            hideLoading();
            
            if (res.status === "success") {
                const d = res.data;
                
                // Generate maps URL
                const mapsUrl = (d.latitude && d.longitude) 
                    ? `http://maps.google.com/?q=${d.latitude},${d.longitude}`
                    : '-';
                
                // Format text untuk WhatsApp
                const text = `
${d.dn_number || '-'}
Site ID : ${d.site_id || '-'}
Project : ${d.sub_project || '-'}
Region : ${d.destination_city || '-'}
Delivery Adress : ${d.destination_address || '-'}
maps : ${mapsUrl}
Receiver : ${d.receiver_on_site || '-'}
Subcont : ${d.subcon || '-'}
Subcont Aktual : ${d.subcon || '-'}
PIC : ${d.pic_on_dn || '-'} ${d.pic_mobile_no ? '- ' + d.pic_mobile_no : ''}
Driver : ${d.driver_name || '-'}
No.pol : ${d.nopol || '-'}
TLP : ${d.phone || '-'}
Status : ${d.status || '-'}

${d.latest_status || 'Tidak ada status'}
                `.trim();
                
                // Copy to clipboard
                navigator.clipboard.writeText(text).then(() => {
                    showSuccessToast("Berhasil", "Teks berhasil dicopy ke clipboard! Silakan paste ke WA/Notepad.");
                }).catch(err => {
                    showErrorToast("Gagal", "Gagal menyalin teks: " + err);
                });
            } else {
                showErrorToast("Error", res.message || "Gagal mengambil data");
            }
        },
        error: function (xhr) {
            hideLoading();
            let errorMsg = "Gagal mengambil data";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            showErrorToast("Error", errorMsg);
        }
    });
}); // New Code End

    // ===== AUTO TIMESTAMP UNTUK LATEST STATUS (MANUAL ENTRY) =====
    (function () {
        const textarea = $("#edit_latest_status");
        let isManualEntryMode = false;

        function getCurrentTimestamp() {
            const now = new Date();
            const pad = n => n.toString().padStart(2, "0");

            const day = pad(now.getDate());
            const month = pad(now.getMonth() + 1);
            const year = now.getFullYear();
            const hours = pad(now.getHours());
            const minutes = pad(now.getMinutes());

            return `${day}/${month}/${year} ${hours}:${minutes} : `;
        }

        // Saat tekan ENTER - tambah timestamp manual
        textarea.on("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();

                const currentValue = $(this).val();
                const newTimestamp = getCurrentTimestamp();
                
                if (!currentValue.trim()) {
                    $(this).val(newTimestamp);
                } else {
                    $(this).val(currentValue + "\n" + newTimestamp);
                }

                this.selectionStart = this.selectionEnd = this.value.length;
                
                isManualEntryMode = true;
            }
        });

        // Reset flag saat modal ditutup
        $("#editReportModal").on("hidden.bs.modal", function () {
            isManualEntryMode = false;
        });
    })();
}