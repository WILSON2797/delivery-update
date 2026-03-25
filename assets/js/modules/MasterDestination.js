
function initPageScripts() {
    console.log("✅ master_destination.js loaded");

// destination
    $("#destinationForm").on("submit", function (e) {
        e.preventDefault();
        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop("disabled", true);
        showLoading();
        $.ajax({
            url: "modules/proses_destination",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                hideLoading();
                $submitBtn.prop("disabled", false);
                if (response.status === "success") {
                    $("#destinationModal").modal("hide");
                    $("#tabeldestination").DataTable().ajax.reload();
                    showSuccessToast(
                        response.message || "Data destination berhasil disimpan!",
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

    $("#destinationModal").on("hidden.bs.modal", function () {
        $("#destinationForm")[0].reset();
    });

    if (!$.fn.DataTable.isDataTable('#tabeldestination')) {
        $("#tabeldestination").DataTable({
            ajax: {
                url: "API/get_destination",
                dataSrc: "data",
                beforeSend: showLoading,
                complete: hideLoading,
            },
            columns: [
                { data: null, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: "province" },
                { data: "city" },
                { data: "created_by" },
                { data: "created_at" },
                {
                    data: null,
                    render: (data, type, row) => `<button class="btn btn-sm edit-destination" data-id="${row.id}"data-bs-toggle="tooltip" data-bs-placement="top" title="Edit destination">
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

    $("#exportExceldestination").on("click", () => window.location.href = "../modules/export_excel_destination");

}