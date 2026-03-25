function initPageScripts() {
    console.log("✅ billing_details.js loaded");

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

    // Daftar field finansial yang perlu auto-format
    const financialFields = [
        '#edit_unit_price',
        '#edit_btp_bta',
        '#edit_rooftop',
        '#edit_4wd',
        '#edit_langsir',
        '#edit_crane',
        '#edit_charter_boat'
    ];

    // Fungsi untuk format angka ke format Indonesia (1.234.567)
    function formatRupiah(angka) {
        if (!angka) return '';
        let number_string = angka.toString().replace(/[^,\d]/g, '');
        let split = number_string.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        if (split[1] !== undefined) {
            rupiah += ',' + split[1];
        }
        return rupiah;
    }

    // Fungsi untuk membersihkan format kembali ke angka murni (untuk submit)
    function cleanRupiah(value) {
        if (!value) return '';
        return value.toString().replace(/\./g, '');
    }

    // Event: Saat user mengetik di field finansial
    financialFields.forEach(selector => {
        $(document).on('keyup', selector, function(e) {
            let val = $(this).val();
            let cleaned = val.replace(/\./g, '');
            if (cleaned === '' || isNaN(cleaned)) {
                $(this).val('');
                return;
            }
            let formatted = formatRupiah(cleaned);
            $(this).val(formatted);
        });

        $(document).on('blur', selector, function(e) {
            let val = $(this).val();
            let cleaned = cleanRupiah(val);
            if (cleaned === '') {
                $(this).val('');
            } else {
                $(this).val(formatRupiah(cleaned));
            }
        });
    });

    // Inisialisasi DataTable
    if (!$.fn.DataTable.isDataTable('#tabelbilling')) {
        $("#tabelbilling").DataTable({
            ajax: {
                url: "API/data_billing_details",
                dataSrc: "data",
                // beforeSend: showLoading,
                // complete: hideLoading,
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseText);
                    showErrorToast("Gagal Load Data", "Gagal memuat data. Silakan cek console (F12) untuk detail error.");
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
                            <button class="btn btn-sm edit-btn" data-id="${row.id}" title="Update Data">
                                <i class="fas fa-pen text-dark"></i>
                            </button>
                        `;
                    }
                }
            ],
            order: [[0, "desc"]],
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

    $(document).on('draw.dt', '#tabelbilling', function () {
        feather.replace();
    });

    // Tombol Edit → Langsung buka modal tanpa GET data
    $('#tabelbilling tbody').on('click', '.edit-btn', function () {
        let recordId = $(this).data('id');
        
        // Reset form
        $('#formEdit')[0].reset();
        
        // Set ID record yang akan diupdate
        $('#edit_id').val(recordId);
        
        // Refresh feather icons
        setTimeout(() => feather.replace(), 100);
        
        // Tampilkan modal
        $('#editModal').modal('show');
    });

    // Submit form edit
    $('#formEdit').on('submit', function (e) {
        e.preventDefault();

        // Validasi required fields
        const dateSubmitPI = $('#edit_date_submit_pi').val();
        if (!dateSubmitPI) {
            showErrorToast("Validasi Gagal", "Date Submit PI wajib diisi!");
            $('#edit_date_submit_pi').focus();
            return;
        }

        // Bersihkan format rupiah sebelum kirim ke server
        let formData = $(this).serializeArray();
        let cleanedData = {};

        formData.forEach(item => {
            // Cek apakah field ini adalah field finansial
            let isFinancialField = false;
            financialFields.forEach(selector => {
                if (selector === '#edit_' + item.name) {
                    isFinancialField = true;
                }
            });

            if (isFinancialField) {
                cleanedData[item.name] = cleanRupiah(item.value);
            } else {
                cleanedData[item.name] = item.value;
            }
        });

        const $btn = $(this).find('button[type="submit"]');
        $btn.prop("disabled", true);

        $.ajax({
            url: "modules/update_billing_details",
            method: "POST",
            data: cleanedData,
            beforeSend: showLoading,
            complete: hideLoading,
            success: function (response) {
                $btn.prop("disabled", false);
                
                try {
                    let res = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (res.status === 'success') {
                        $('#editModal').modal('hide');
                        $('#tabelbilling').DataTable().ajax.reload(null, false);
                        
                        let message = 'Data berhasil diperbarui!';
                        
                        if (res.calculated) {
                            let details = [];
                            if (res.calculated.due_date) details.push(`Due Date: ${res.calculated.due_date}`);
                            if (res.calculated.aging_days !== null) details.push(`Aging Days: ${res.calculated.aging_days}`);
                            if (res.calculated.total_amount) details.push(`Total: Rp ${parseFloat(res.calculated.total_amount).toLocaleString('id-ID')}`);
                            if (res.calculated.grouping_aging_day) details.push(`Grouping: ${res.calculated.grouping_aging_day}`);
                            if (res.calculated.achieved_failed) details.push(`Status: ${res.calculated.achieved_failed}`);
                            
                            if (details.length > 0) {
                                message += '<br><small>' + details.join(' | ') + '</small>';
                            }
                        }
                        
                        showSuccessToast("Berhasil", message);
                    } else {
                        showErrorToast("Gagal Update", res.message || 'Terjadi kesalahan saat update data');
                    }
                } catch (err) {
                    console.error('Parse error:', err);
                    console.error('Response:', response);
                    showErrorToast("Error", "Response tidak valid dari server");
                }
            },
            error: function (xhr, status, error) {
                $btn.prop("disabled", false);
                
                let errorMsg = "Gagal terhubung ke server";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMsg = xhr.responseText;
                }
                
                console.error('AJAX Error:', xhr.responseText);
                showErrorToast("Error", errorMsg);
            }
        });
    });

    // Export Excel
    $("#exportExcelbilling").on("click", function () {
        window.location.href = "API/export_billing_excel";
    });

    // Reset form saat modal ditutup
    $("#editModal").on("hidden.bs.modal", function () {
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