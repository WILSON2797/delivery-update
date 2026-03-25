function initPageScripts() {
    console.log("✅ DailyReport.js loaded");

    // Inisialisasi DataTable
    if (!$.fn.DataTable.isDataTable('#tabelorderkeep')) {
        $("#tabelorderkeep").DataTable({
            ajax: {
                url: "API/data_table_order_keep",
                dataSrc: "data",
                // beforeSend: showLoading,
                // complete: hideLoading,
            },
            columns: [
                { data: null, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
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
                        else if (data === "Handover keep") color = "success";
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


   // Event untuk tombol Copy to WA - menggunakan namespace untuk mencegah duplikasi
$("#tabelorderkeep").off("click.copywa").on("click.copywa", ".copy-to-whatsapp", function () {
    const id = $(this).data("id");
    // Gunakan closest table untuk memastikan mengambil dari tabel yang benar
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