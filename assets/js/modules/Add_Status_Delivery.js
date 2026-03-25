
function initPageScripts() {
    console.log("status_delivery.js loaded");

// statusdelivery
    $("#statusdeliveryForm").on("submit", function (e) {
        e.preventDefault();
        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop("disabled", true);
        showLoading();
        $.ajax({
            url: "modules/proses_statusdelivery",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                hideLoading();
                $submitBtn.prop("disabled", false);
                if (response.status === "success") {
                    $("#statusdeliveryModal").modal("hide");
                    $("#tabelstatusdelivery").DataTable().ajax.reload();
                    showSuccessToast(
                        response.message || "Data statusdelivery berhasil disimpan!",
                        "Berhasil"
                    );
                } else {
                    showErrorToast(
                        response.message || "Terjadi kesalahan saat menyimpan data!",
                        "Gagal"
                    );
                }
            },
            error: function (xhr) {
                hideLoading();
                $submitBtn.prop("disabled", false);
                const response = JSON.parse(xhr.responseText) || { status: "error", message: "Terjadi kesalahan tidak terduga!" };
                showErrorToast(
                    response.message || "Terjadi kesalahan Tak Terduga!",
                    "Gagal"
                );
            },
        });
    });

    $("#statusdeliveryModal").on("hidden.bs.modal", function () {
        $("#statusdeliveryForm")[0].reset();
    });

    if (!$.fn.DataTable.isDataTable('#tabelstatusdelivery')) {
        $("#tabelstatusdelivery").DataTable({
            ajax: {
                url: "API/get_statusdelivery",
                dataSrc: "data",
                beforeSend: showLoading,
                complete: hideLoading,
            },
            columns: [
                { data: null, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: "code" },
                { data: "created_by" },
                { data: "created_at" },
                {
                    data: null,
                    render: (data, type, row) => `<button class="btn btn-sm edit-statusdelivery" data-id="${row.id}"data-bs-toggle="tooltip" data-bs-placement="top" title="Edit statusdelivery">
                    <i class="fas fa-pen text-dark"></i>
                    </button> 
                    `
                },
            ],
            order: [[0, "asc"]],
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
            destroy: true
        });
    }

    $("#exportExcelstatusdelivery").on("click", () => window.location.href = "../modules/export_excel_statusdelivery");

}