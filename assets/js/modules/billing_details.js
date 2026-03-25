function initPageScripts() {
    console.log("✅ billing_details.js loaded");

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
        // Hapus semua karakter non-digit
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
            // Hapus titik lama, lalu format ulang
            let cleaned = val.replace(/\./g, '');
            if (cleaned === '' || isNaN(cleaned)) {
                $(this).val('');
                return;
            }
            let formatted = formatRupiah(cleaned);
            $(this).val(formatted);
        });

        // Pastikan saat fokus keluar (blur), tetap terformat rapi
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

    // Inisialisasi DataTable hanya jika belum ada
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
                            <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id}" title="Edit">
                                <i data-feather="edit-2"></i>
                            </button>
                        `;
                    }
                }
            ],
            order: [[4, "desc"]], // Urutkan berdasarkan pod_date terbaru
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

    // Refresh icon Feather jika ada yang baru ditambahkan
    $(document).on('draw.dt', '#tabelbilling', function () {
        feather.replace();
    });

    // Tombol Edit → Ambil data LANGSUNG dari server (bukan dari DataTable)
    $('#tabelbilling tbody').on('click', '.edit-btn', function () {
        let recordId = $(this).data('id');
        
        // Reset form
        $('#formEdit')[0].reset();
        
        // Tampilkan loading
        showLoading();

        // PENTING: Ambil data fresh dari server
        $.ajax({
            url: "API/get_billing_detail.php",
            method: "GET",
            data: { id: recordId },
            dataType: "json",
            success: function(response) {
                hideLoading();
                
                if (response.status === 'success' && response.data) {
                    let rowData = response.data;
                    
                    // Isi field biasa
                    $('#edit_id').val(rowData.id);
                    $('#edit_date_send_sc_pod').val(rowData.date_send_sc_pod || '');
                    $('#edit_date_approved_sc_pod').val(rowData.date_approved_sc_pod || '');
                    $('#edit_date_send_hc_pod').val(rowData.date_send_hc_pod || '');
                    $('#edit_date_submit_pi').val(rowData.date_submit_pi || '');
                    $('#edit_date_confirm_vendors').val(rowData.date_confirm_vendors || '');
                    $('#edit_no_pi').val(rowData.no_pi || '');

                    // PENTING: Isi field finansial - data dari server sudah ANGKA MURNI
                    // Langsung format tanpa perlu clean lagi
                    $('#edit_unit_price').val(rowData.unit_price ? formatRupiah(rowData.unit_price.toString()) : '');
                    $('#edit_btp_bta').val(rowData.btp_bta ? formatRupiah(rowData.btp_bta.toString()) : '');
                    $('#edit_rooftop').val(rowData.rooftop ? formatRupiah(rowData.rooftop.toString()) : '');
                    $('#edit_4wd').val(rowData['4wd'] ? formatRupiah(rowData['4wd'].toString()) : '');
                    $('#edit_langsir').val(rowData.langsir ? formatRupiah(rowData.langsir.toString()) : '');
                    $('#edit_crane').val(rowData.crane ? formatRupiah(rowData.crane.toString()) : '');
                    $('#edit_charter_boat').val(rowData.charter_boat ? formatRupiah(rowData.charter_boat.toString()) : '');

                    // Refresh feather icons di modal
                    setTimeout(() => feather.replace(), 100);

                    $('#editModal').modal('show');
                } else {
                    showErrorToast("Error", response.message || "Gagal mengambil data");
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.error('AJAX Error:', xhr.responseText);
                showErrorToast("Error", "Gagal mengambil data dari server");
            }
        });
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
            url: "API/update_billing_details",
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