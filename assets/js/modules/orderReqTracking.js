function initPageScripts() {
    console.log("orderTracking.js loaded");

    // ===== OPTIMIZED FLATPICKR INITIALIZATION =====
    function initDateTimePicker() {
        if (typeof flatpickr === "undefined") {
            console.error("Flatpickr belum ter-load");
            return;
        }

        // Destroy existing instances untuk menghindari duplikasi
        $(".date-picker").each(function() {
            if (this._flatpickr) {
                this._flatpickr.destroy();
            }
        });

        // Initialize date picker
        flatpickr(".date-picker", {
            dateFormat: "Y-m-d",
            allowInput: true,
            altInput: true,
            altFormat: "d-m-Y",
        });
    }

    // INIT SAAT PAGE LOAD
    initDateTimePicker();

    // Global flag untuk prevent event trigger saat initialization
let isInitializingDropdowns = false;

// ===== INISIALISASI DROPDOWN SELECT2 UNTUK EDIT MODAL =====
function initEditDropdowns(planFromValue = null, motValue = null, statusValue = null, typeShipmentValue = null, provinceValue = null, cityValue = null) {
    console.log(`🔄 Loading edit dropdowns: Plan=${planFromValue}, MOT=${motValue}, Status=${statusValue}, Province=${provinceValue}, City=${cityValue}`);
    
   
    isInitializingDropdowns = true;
    
    const independentPromises = [
        loadDropdownData($('#track_plan_from'), 'master_origin', 'origin_code', 'origin_code', '#editTrackingModal', planFromValue),
        loadDropdownData($('#track_mot'), 'master_mode_of_transport', 'mot_code', 'mot_code', '#editTrackingModal', motValue),
        loadDropdownData($('#track_status'), 'status_delivery', 'code', 'code', '#editTrackingModal', statusValue),
        loadDropdownData($('#track_type_shipment'), 'type_shipment', 'type_shipment', 'type_shipment', '#editTrackingModal', typeShipmentValue)
    ];
    
    const provincePromise = loadDropdownData(
        $('#track_destination_province'), 
        'province_city', 
        'province', 
        'province', 
        '#editTrackingModal', 
        provinceValue
    );
        
    return Promise.all([...independentPromises, provincePromise])
        .then((results) => {
            console.log("independent dropdowns loaded:", results);
            
            if (provinceValue && cityValue) {
                console.log(`Loading city for province: ${provinceValue}, target city: ${cityValue}`);
                
                return loadDropdownData(
                    $('#track_destination_city'),
                    'province_city',
                    'city',
                    'city',
                    '#editTrackingModal',
                    cityValue,
                    'province',
                    provinceValue
                ).then((cityResult) => {
                    console.log("city loaded:", cityResult);
                    
                    setTimeout(() => {
                        const actualCityValue = $('#track_destination_city').val();
                        console.log(`🔍 Verification - Expected: ${cityValue}, Actual: ${actualCityValue}`);
                        
                        if (actualCityValue !== cityValue) {
                            console.warn(`City value mismatch! Retrying...`);
                            $('#track_destination_city').val(cityValue).trigger('change');
                        }
                    
                        // reset flag setelah semua selesai
                        isInitializingDropdowns = false;
                    }, 200);
                    
                    return [...results, cityResult];
                });
            } else {
                const $cityDropdown = $('#track_destination_city');
                $cityDropdown.empty().append('<option value="">Pilih City</option>');
                
                if ($cityDropdown.hasClass('select2-hidden-accessible')) {
                    $cityDropdown.select2('destroy');
                }
                
                $cityDropdown.select2({
                    width: "100%",
                    dropdownParent: $('#editTrackingModal'),
                    allowClear: true,
                    placeholder: "Pilih City",
                });
                
                
                isInitializingDropdowns = false;
                
                return results;
            }
        })
        .catch(error => {
            console.error("error loading dropdowns:", error);
            isInitializingDropdowns = false;
            throw error;
        });
}


$(document).on('change', '#track_destination_province', function() {
    // skip jika sedang initialization
    if (isInitializingDropdowns) {
        console.log("Skipping province change event during initialization");
        return;
    }
    
    const selectedProvince = $(this).val();
    const $cityDropdown = $('#track_destination_city');
    
    console.log(`Province changed to: ${selectedProvince}`);
    
    $cityDropdown.empty().append('<option value="">Pilih City</option>');
    
    if (selectedProvince) {
        loadDropdownData(
            $cityDropdown, 
            'province_city', 
            'city', 
            'city', 
            '#editTrackingModal', 
            null, 
            'province', 
            selectedProvince
        );
    } else {
        if ($cityDropdown.hasClass('select2-hidden-accessible')) {
            $cityDropdown.select2('destroy');
        }
        $cityDropdown.select2({
            width: "100%",
            dropdownParent: $('#editTrackingModal'),
            allowClear: true,
            placeholder: "Pilih City",
        });
    }
});

// ===== KLIK EDIT TRACKING - DENGAN PROPER RACE CONDITION HANDLING =====
$(document).on("click", ".edit-tracking", function () {
    const id = $(this).data("id");
    showLoading("Loading data...");

    $.ajax({
        url: "modules/get_order_req_tracking",
        type: "GET",
        data: { id: id },
        dataType: "json",
        timeout: 15000, 
        success: function (res) {
            if (res.status === "success") {
                const d = res.data;

                // ===== UPDATE HEADER MODAL =====
                $("#editTrackingDnNumberHeader").text(d.dn_number || "N/A");

                // ===== POPULATE SEMUA FIELD NON-DROPDOWN DULU =====
                $("#track_id").val(d.id || "");
                $("#track_transaction_id").val(d.transaction_id || "");
                $("#track_date_request").val(d.date_request || "");
                $("#track_email_release_date").val(d.email_release_date || "");
                $("#track_dn_number").val(d.dn_number || "");
                $("#track_sub_project").val(d.sub_project || "");
                $("#track_site_id").val(d.site_id || "");
                $("#track_destination_address").val(d.destination_address || "");
                $("#track_rsd").val(d.rsd || "");
                $("#track_rad").val(d.rad || "");
                $("#track_sla").val(d.sla || "");
                $("#track_volume").val(d.volume || "");
                $("#track_gross_weight").val(d.gross_weight || "");
                $("#track_htm").val(d.htm || "");
                $("#track_driver_name").val(d.driver_name || "");
                $("#track_nopol").val(d.nopol || "");
                $("#track_phone").val(d.phone || "");
                $("#track_subcon").val(d.subcon || "");

                // ===== SPLIT DATETIME =====
                splitDateTime(d.truck_on_warehouse, 'track_truck_on_warehouse_date', 'track_truck_on_warehouse_time');
                splitDateTime(d.atd_whs_dispatch, 'track_atd_whs_dispatch_date', 'track_atd_whs_dispatch_time');
                splitDateTime(d.atd_pool_dispatch, 'track_atd_pool_dispatch_date', 'track_atd_pool_dispatch_time');
                splitDateTime(d.ata_mover_on_site, 'track_ata_mover_on_site_date', 'track_ata_mover_on_site_time');
                splitDateTime(d.receiver_on_site_datetime, 'track_receiver_on_site_date', 'track_receiver_on_site_time');
                splitDateTime(d.pod_datetime, 'track_pod_datetime_date', 'track_pod_datetime_time');

                $("#track_truck_on_warehouse").val(d.truck_on_warehouse || "");
                $("#track_atd_whs_dispatch").val(d.atd_whs_dispatch || "");
                $("#track_atd_pool_dispatch").val(d.atd_pool_dispatch || "");
                $("#track_ata_mover_on_site").val(d.ata_mover_on_site || "");
                $("#track_receiver_on_site_datetime").val(d.receiver_on_site_datetime || "");
                $("#track_pod_datetime").val(d.pod_datetime || "");
                $("#track_pod_type").val(d.pod_type || "");
                $("#track_pic_on_dn").val(d.pic_on_dn || "");
                $("#track_pic_mobile_no").val(d.pic_mobile_no || "");
                $("#track_receiver_on_site").val(d.receiver_on_site || "");
                $("#track_latitude").val(d.latitude || "");
                $("#track_longitude").val(d.longitude || "");
                $("#track_remarks").val(d.remarks || "");
                $("#track_nominal_add_cost").val(d.nominal_add_cost || "");
                $("#track_detail_add_cost").val(d.detail_add_cost || "");
                $("#track_approval_by_whatsapp").val(d.approval_by_whatsapp || "");
                $("#track_rise_up_by_email").val(d.rise_up_by_email || "");
                $("#track_approved_by_email").val(d.approved_by_email || "");
                $("#track_remarks_add_cost").val(d.remarks_add_cost || "");
                $("#track_overnight_day").val(d.overnight_day || "");

                // ===== GENERATE TIMELINE =====
                const existingLatestStatus = d.latest_status || "";
                const autoTimeline = generateTimeline();
                const timelinePattern = /^\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}(:\d{2})?\s*:\s*(Truck on Pickup Point|Done Pickup|OTW To Site|Mover Onsite|HO Done)$/;
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
                $("#track_latest_status").val(sortedContent);

                // Re-init datetime pickers
                initDateTimePicker();

               
                hideLoading();
                showLoading("Loading dropdown data...");

                initEditDropdowns(
                    d.plan_from || null,
                    d.mot || null,
                    d.status || null,
                    d.type_shipment || null,
                    d.destination_province || null,
                    d.destination_city || null
                )
                .then((results) => {
                    console.log("All dropdowns loaded successfully:", results);
                    hideLoading();
                    
                    // DOUBLE CHECK: Verifikasi semua value ter-set dengan benar
                    setTimeout(() => {
                        const verifications = {
                            'Plan From': { element: $('#track_plan_from'), expected: d.plan_from },
                            'MOT': { element: $('#track_mot'), expected: d.mot },
                            'Status': { element: $('#track_status'), expected: d.status },
                            'Type Shipment': { element: $('#track_type_shipment'), expected: d.type_shipment },
                            'Province': { element: $('#track_destination_province'), expected: d.destination_province },
                            'City': { element: $('#track_destination_city'), expected: d.destination_city }
                        };
                        
                        let hasIssue = false;
                        Object.entries(verifications).forEach(([name, {element, expected}]) => {
                            const actual = element.val();
                            if (expected && actual !== expected) {
                                console.warn(`${name} mismatch - Expected: ${expected}, Actual: ${actual}`);
                                element.val(expected).trigger('change');
                                hasIssue = true;
                            }
                        });
                        
                        if (hasIssue) {
                            console.log("🔧 Applied corrections to dropdown values");
                        }
                    }, 300);
                    
                    // Trigger change untuk status (validasi warning)
                    $("#track_status").trigger('change');
                    
                    // HOW MODAL HANYA SETELAH SEMUA SELESAI
                    setTimeout(() => {
                        $("#editTrackingModal").modal("show");
                        console.log("Modal opened successfully");
                    }, 400);
                })
                .catch(error => {
                    console.error("Error loading dropdowns:", error);
                    hideLoading();
                    showErrorToast("Error", "Gagal memuat dropdown: " + (error.message || "Unknown error"));
                });
                
            } else {
                hideLoading();
                showErrorToast("Error", res.message || "Gagal mengambil data");
            }
        },
        error: function (xhr, status, error) {
            hideLoading();
            console.error("AJAX Error:", { xhr, status, error });
            
            let errorMsg = "Gagal mengambil data";
            if (status === 'timeout') {
                errorMsg = "Request timeout - server terlalu lama merespon";
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            
            showErrorToast("Error", errorMsg);
        }
    });
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
    $(document).on('change', '#track_truck_on_warehouse_date, #track_truck_on_warehouse_time', function() {
        combineDateTime('track_truck_on_warehouse_date', 'track_truck_on_warehouse_time', 'track_truck_on_warehouse');
    });

    $(document).on('change', '#track_atd_whs_dispatch_date, #track_atd_whs_dispatch_time', function() {
        combineDateTime('track_atd_whs_dispatch_date', 'track_atd_whs_dispatch_time', 'track_atd_whs_dispatch');
    });

    $(document).on('change', '#track_atd_pool_dispatch_date, #track_atd_pool_dispatch_time', function() {
        combineDateTime('track_atd_pool_dispatch_date', 'track_atd_pool_dispatch_time', 'track_atd_pool_dispatch');
    });

    $(document).on('change', '#track_ata_mover_on_site_date, #track_ata_mover_on_site_time', function() {
        combineDateTime('track_ata_mover_on_site_date', 'track_ata_mover_on_site_time', 'track_ata_mover_on_site');
    });

    $(document).on('change', '#track_receiver_on_site_date, #track_receiver_on_site_time', function() {
        combineDateTime('track_receiver_on_site_date', 'track_receiver_on_site_time', 'track_receiver_on_site_datetime');
    });

    $(document).on('change', '#track_pod_datetime_date, #track_pod_datetime_time', function() {
        combineDateTime('track_pod_datetime_date', 'track_pod_datetime_time', 'track_pod_datetime');
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
    if (!$.fn.DataTable.isDataTable('#tabelOrderTracking')) {
        $("#tabelOrderTracking").DataTable({
            ajax: {
                url: "API/data_table_order_RequestTracking",
                dataSrc: "data",
                // beforeSend: showLoading,
                // complete: hideLoading,
            },
            columns: [
                { data: null, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
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
                { data: "date_request" },
                {
                    data: null,
                    orderable: false,
                    render: function (data) {
                        return `
                            <button class="btn btn-sm btn-info edit-tracking" data-id="${data.id}" title="Update Tracking">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn btn-sm btn-success copy-to-whatsapp" data-id="${data.id}" title="Copy to Clipboard">
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
    $("#exportExcelTracking").on("click", function () {
        window.location.href = "API/export_daily_report";
    });

    // ===== FUNGSI VALIDASI =====
    function validateTrackingForm() {
        const status = $('#track_status').val();
        const podDatetime = $('#track_pod_datetime').val();
        const ataMoverOnsite = $('#track_ata_mover_on_site').val();
        
        // Validasi untuk status "Onsite"
        if (status === "Onsite" && !ataMoverOnsite) {
            showErrorToast("Validasi Gagal", "ATA Mover on Site wajib diisi ketika status Onsite!");
            return false;
        }
        
        // Validasi untuk status "Handover Done"
        if (status === "Handover Done" && !podDatetime) {
            showErrorToast("Validasi Gagal", "POD DateTime wajib diisi ketika status Handover Done!");
            return false;
        }
        
        return true;
    }

    // ===== FUNGSI PARSE TANGGAL DARI BERBAGAI FORMAT =====
    function parseDateFromLine(line) {
        const pattern1 = /^(\d{2})\/(\d{2})\/(\d{2,4})\s+(\d{2}):(\d{2})/;
        const match = line.match(pattern1);
        
        if (match) {
            let [, day, month, year, hour, minute] = match;
            
            if (year.length === 2) {
                year = '20' + year;
            }
            
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
        
        linesWithDate.sort((a, b) => a.date - b.date);
        
        const sortedLines = linesWithDate.map(item => item.text);
        
        return [...sortedLines, ...linesWithoutDate].join('\n');
    }

    // ===== FUNGSI GENERATE TIMELINE =====
    function generateTimeline() {
        const timelineFields = [
            { id: 'track_truck_on_warehouse', label: 'Truck on Pickup Point' },
            { id: 'track_atd_whs_dispatch', label: 'Done Pickup' },
            { id: 'track_atd_pool_dispatch', label: 'OTW To Site' },
            { id: 'track_ata_mover_on_site', label: 'Mover Onsite' },
            { id: 'track_pod_datetime', label: 'HO Done' }
        ];

        let timelineData = [];
        
        timelineFields.forEach(field => {
            const value = $(`#${field.id}`).val();
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

        timelineData.sort((a, b) => a.datetime - b.datetime);

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


    // ===== LIVE PREVIEW TIMELINE SAAT DATETIME FIELDS BERUBAH =====
    $(document).on('change', 
        '#track_truck_on_warehouse_date, #track_truck_on_warehouse_time, ' +
        '#track_atd_whs_dispatch_date, #track_atd_whs_dispatch_time, ' +
        '#track_atd_pool_dispatch_date, #track_atd_pool_dispatch_time, ' +
        '#track_ata_mover_on_site_date, #track_ata_mover_on_site_time, ' +
        '#track_pod_datetime_date, #track_pod_datetime_time', 
        function() {
            const latestStatusTextarea = $('#track_latest_status');
            const currentValue = latestStatusTextarea.val();
        
            const autoTimelineLabels = [
                'Truck on Pickup Point',
                'Done Pickup', 
                'OTW To Site',
                'Mover Onsite',
                'HO Done'
            ];
            
            const timelinePattern = /^\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}(:\d{2})?\s*:\s*.+$/;
            
            const lines = currentValue.split('\n');
            const manualLogs = lines.filter(line => {
                const trimmed = line.trim();
                if (!trimmed) return false;
                
                if (!timelinePattern.test(trimmed)) {
                    return true;
                }
                
                const hasAutoLabel = autoTimelineLabels.some(label => trimmed.includes(`: ${label}`));
                return !hasAutoLabel;
            });
            
            const newTimeline = generateTimeline();
            
            let combinedContent = newTimeline;
            if (manualLogs.length > 0) {
                combinedContent = combinedContent ? combinedContent + '\n' + manualLogs.join('\n') : manualLogs.join('\n');
            }
            
            const sortedContent = sortAllLinesByDate(combinedContent);
            latestStatusTextarea.val(sortedContent);
    });

    // ===== SUBMIT EDIT TRACKING DENGAN VALIDASI =====
    $("#editTrackingForm").on("submit", function (e) {
        e.preventDefault();
        
        if (!validateTrackingForm()) {
            return false;
        }
        
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop("disabled", true);
        showLoading();

        $.ajax({
            url: "modules/proses_order_tracking_update",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (res) {
                hideLoading();
                $btn.prop("disabled", false);
                if (res.status === "success") {
                    $("#editTrackingModal").modal("hide");
                    $("#tabelOrderTracking").DataTable().ajax.reload();
                    showSuccessToast(res.message || "Tracking berhasil diupdate");
                } else {
                    showErrorToast("Gagal", res.message || "Terjadi kesalahan");
                }
            },
            error: function (xhr) {
                hideLoading();
                $btn.prop("disabled", false);
                let errorMsg = "Gagal mengupdate tracking";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showErrorToast("Error", errorMsg);
            }
        });
    });

    // Event listener untuk Status Change - Warning POD DateTime
    $(document).on('change', '#track_status', function() {
        const status = $(this).val();
        const podField = $('#track_pod_datetime');
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

        // Handle ATA Mover on Site untuk status "Onsite"
        const ataField = $('#track_ata_mover_on_site');
        const ataWrapper = ataField.closest('.col-md-6');
        
        if (status === "Onsite") {
            ataField.addClass('border-warning');
            ataField.attr('required', true);
            ataWrapper.find('.ata-warning-msg').remove();
            ataWrapper.append('<div class="ata-warning-msg"><small class="text-warning d-block mt-1"><i class="fas fa-exclamation-triangle me-1"></i>ATA Mover on Site wajib diisi untuk status Onsite</small></div>');
        } else {
            ataField.removeClass('border-warning');
            ataField.removeAttr('required');
            ataWrapper.find('.ata-warning-msg').remove();
        }
    });

    // Reset form saat modal ditutup
    $("#editTrackingModal").on("hidden.bs.modal", function () {
        $(this).find("form")[0].reset();
        $(this).find('.pod-warning-msg').remove();
        $(this).find('.ata-warning-msg').remove();
        $(this).find('input, select, textarea').removeClass('border-warning');
    });

    // Event untuk tombol Copy to WA
    $("#tabelOrderTracking").off("click.copywa").on("click.copywa", ".copy-to-whatsapp", function () {
        const rowData = $(this).closest("table").DataTable().row($(this).closest("tr")).data();
        
        if (!rowData) {
            showErrorToast("Error", "Data baris tidak ditemukan");
            return;
        }
        
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
        
        const text = `
DN : ${d.dn_number || '-'}
Project : ${d.sub_project || '-'}
Region : ${d.destination_province || '-'}
Site ID : ${d.site_id || '-'}
Receiver : ${d.receiver_on_site || '-'}
Subcont : ${d.subcon || '-'}
Phone Number : ${d.pic_mobile_no || '-'}
Delivery Adress : ${d.destination_address || '-'}
maps : ${mapsUrl}

Plan MOS : ${d.plan_mos || '-'}

Origin : ${d.plan_from || '-'}
Destination : ${d.destination_city || '-'}

Pickup Date  : ${d.pickup_date || '-'}
Unit Time Arrived : ${d.truck_on_warehouse || '-'}
Unit Time Dispatch : ${d.atd_whs_dispatch || '-'}
Type Unit / MOT : ${d.mot || '-'} ${d.type_shipment ? '| ' + d.type_shipment : ''}

No.pol : ${d.nopol || '-'}
Driver : ${d.driver_name || '-'} ${d.phone ? '| ' + d.phone : ''}
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
        const textarea = $("#track_latest_status");
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
        $("#editTrackingModal").on("hidden.bs.modal", function () {
            isManualEntryMode = false;
        });
    })();
}