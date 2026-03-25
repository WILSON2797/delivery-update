
function initPageScripts() {
    console.log("✅ master_masterMOT.js loaded");

// masterMOT
    $("#masterMOTForm").on("submit", function (e) {
        e.preventDefault();
        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop("disabled", true);
        showLoading();
        $.ajax({
            url: "modules/proses_masterMOT",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                hideLoading();
                $submitBtn.prop("disabled", false);
                if (response.status === "success") {
                    $("#masterMOTModal").modal("hide");
                    $("#tabelmasterMOT").DataTable().ajax.reload();
                    showSuccessToast(
                        response.message || "Data masterMOT berhasil disimpan!",
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

    $("#masterMOTModal").on("hidden.bs.modal", function () {
        $("#masterMOTForm")[0].reset();
    });

    if (!$.fn.DataTable.isDataTable('#tabelmasterMOT')) {
        $("#tabelmasterMOT").DataTable({
            ajax: {
                url: "API/get_masterMOT",
                dataSrc: "data",
                beforeSend: showLoading,
                complete: hideLoading,
            },
            columns: [
                { data: null, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: "mot_code" },
                { data: "mot_description" },
                { data: "created_by" },
                { data: "created_at" },
                {
                    data: null,
                    render: (data, type, row) => `<button class="btn btn-sm edit-masterMOT" data-id="${row.id}"data-bs-toggle="tooltip" data-bs-placement="top" title="Edit masterMOT">
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

    $("#exportExcelmasterMOT").on("click", () => window.location.href = "../modules/export_excel_masterMOT");

}