function initPageScripts() {
    console.log("✅ invoice done.js loaded");

    // Inisialisasi DataTable
    if (!$.fn.DataTable.isDataTable('#invdone')) {
        $("#invdone").DataTable({
            ajax: {
                url: "API/table_invoice_done",
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
                { data: "invoice_send_to_customer" },
                { data: "no_invoice_vendors" },
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
                            <button class="btn btn-sm edit-btn" data-id="${row.id}" data-dn="${row.dn_number}" title="Action">
                                <i data-feather="edit-2"></i>
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
    $(document).on('draw.dt', '#invdone', function () {
        feather.replace();
    });
}