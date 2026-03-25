function initPageScripts() {
    console.log("✅ DailyReport.js loaded");

    // Inisialisasi DataTable
    if (!$.fn.DataTable.isDataTable('#tabelorderbtp')) {
        $("#tabelorderbtp").DataTable({
            ajax: {
                url: "API/data_table_order_btp",
                dataSrc: "data",
                beforeSend: showLoading,
                complete: hideLoading,
            },
            columns: [
                { data: null, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { 
                    data: "btp_datetime",
                    render: function(data, type, row) {
                        if (data && type === 'display') {
                            const date = new Date(data);
                            const day = String(date.getDate()).padStart(2, '0');
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const year = date.getFullYear();
                            const hours = String(date.getHours()).padStart(2, '0');
                            const minutes = String(date.getMinutes()).padStart(2, '0');
                            const seconds = String(date.getSeconds()).padStart(2, '0');
                            
                            return `${day}-${month}-${year} ${hours}:${minutes}:${seconds}`;
                        }
                        return data;
                    }
                },
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
                        else if (data === "Handover btp") color = "success";
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
    $("#exportExcelorderbtp").on("click", function () {
        window.location.href = "API/export_report_btp";
    });


    // Event untuk tombol Copy to WA - menggunakan namespace untuk mencegah duplikasi
$("#tabelorderbtp").off("click.copywa").on("click.copywa", ".copy-to-whatsapp", function () {
    const rowData = $(this).closest("table").DataTable().row($(this).closest("tr")).data();
    
    if (!rowData || !rowData.id) {
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
    
    // Generate teks format
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
${d.remarks || ''}
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
});
}