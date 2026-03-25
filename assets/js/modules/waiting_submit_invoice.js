function initPageScripts() {
    console.log("✅ waiting_submit_invoice.js loaded");

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
    if (!$.fn.DataTable.isDataTable('#tabelinvoice')) {
        $("#tabelinvoice").DataTable({
            ajax: {
                url: "API/data_waiting_invoice",
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
                    data: "total_amount",
                    render: function(data) {
                        return data ? parseFloat(data).toLocaleString('id-ID', {minimumFractionDigits: 2}) : '-';
                    }
                },
                { data: "date_confirm_vendors" },
                { data: "status_var_vendors" },
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
    $(document).on('draw.dt', '#tabelinvoice', function () {
        feather.replace();
    });

    // Tombol Edit/Tambah → LANGSUNG buka modal dengan ID saja
    $('#tabelinvoice tbody').on('click', '.edit-btn', function () {
        let table = $('#tabelinvoice').DataTable();
        let rowData = table.row($(this).closest('tr')).data();

        // Reset form
        $('#formEdit')[0].reset();

        // Hanya isi ID dan data yang sudah ada (jika ada)
        $('#edit_id').val(rowData.id);
        $('#edit_invoice_send_to_customer').val(rowData.invoice_send_to_customer || '');
        $('#edit_no_invoice_vendors').val(rowData.no_invoice_vendors || '');
        $('#edit_inv_date').val(rowData.inv_date || '');

        // Update title modal sesuai kondisi
        let hasInvoice = rowData.invoice_send_to_customer ? true : false;
        $('#editModalLabel').html(
            `<i data-feather="${hasInvoice ? 'edit' : 'plus-circle'}"></i> ${hasInvoice ? 'Edit' : 'Tambah'} Invoice Information`
        );

        // Refresh icons
        setTimeout(() => feather.replace(), 100);

        $('#editModal').modal('show');
    });

    // Submit form edit
    $('#formEdit').on('submit', function (e) {
        e.preventDefault();

        // Validasi wajib: Invoice Send To Customer
        if (!$('#edit_invoice_send_to_customer').val()) {
            showErrorToast("Validasi Gagal", "Invoice Send To Customer wajib diisi!");
            $('#edit_invoice_send_to_customer').focus();
            return;
        }

        const $btn = $(this).find('button[type="submit"]');
        $btn.prop("disabled", true);

        $.ajax({
            url: "modules/update_invoice_details",
            method: "POST",
            data: $(this).serialize(),
            beforeSend: showLoading,
            complete: function() {
                hideLoading();
                $btn.prop("disabled", false);
            },
            success: function (response) {
                try {
                    let res = typeof response === 'string' ? JSON.parse(response) : response;

                    if (res.status === 'success') {
                        $('#editModal').modal('hide');
                        $('#tabelinvoice').DataTable().ajax.reload(null, false);
                        showSuccessToast("Berhasil", "Data invoice berhasil disimpan!");
                    } else {
                        showErrorToast("Gagal", res.message || "Terjadi kesalahan saat menyimpan.");
                    }
                } catch (err) {
                    console.error("Parse error:", err);
                    showErrorToast("Error", "Response server tidak valid.");
                }
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || xhr.responseText || "Gagal terhubung ke server";
                showErrorToast("Error", msg);
            }
        });
    });

    // Export Excel
    $("#exportExcelinvoice").on("click", function () {
        window.location.href = "API/export_billing_excel";
    });

    // Reset form saat modal ditutup
    $("#editModal").on("hidden.bs.modal", function () {
        $(this).find("form")[0].reset();
    });
}

// Helper functions fallback
if (typeof showLoading === 'undefined') {
    window.showLoading = () => console.log('Loading...');
    window.hideLoading = () => console.log('Loaded');
}

if (typeof showSuccessToast === 'undefined') {
    window.showSuccessToast = (title, message) => alert(title + '\n' + message);
}

if (typeof showErrorToast === 'undefined') {
    window.showErrorToast = (title, message) => alert(title + '\n' + message);
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