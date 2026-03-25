
function initPageScripts() {
    console.log("status_delivery.js loaded");

// driver
    $("#driverForm").on("submit", function (e) {
        e.preventDefault();
        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop("disabled", true);
        showLoading();
        $.ajax({
            url: "modules/proses_driver",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                hideLoading();
                $submitBtn.prop("disabled", false);
                if (response.status === "success") {
                    $("#driverModal").modal("hide");
                    $("#tabeldriver").DataTable().ajax.reload();
                    showSuccessToast(
                        response.message || "Data driver berhasil disimpan!",
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

    $("#driverModal").on("hidden.bs.modal", function () {
        $("#driverForm")[0].reset();
    });

    $(document).on('input', 'input[name="phone"]', function() {
        this.value = this.value.replace(/[^0-9+\-]/g, '');
    });

    if (!$.fn.DataTable.isDataTable('#tabeldriver')) {
        $("#tabeldriver").DataTable({
            ajax: {
                url: "API/get_driver",
                dataSrc: "data",
                beforeSend: showLoading,
                complete: hideLoading,
            },
            columns: [
                { data: null, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: "nama" },
                { data: "phone" },
                { data: "nopol" },
                { data: "create_at" },
                {
                    data: null,
                    render: (data, type, row) => `<button class="btn btn-sm edit-driver" data-id="${row.id}"data-bs-toggle="tooltip" data-bs-placement="top" title="Edit driver">
                    <i class="fas fa-pen text-dark"></i>
                    </button> 
                    `
                },
            ],
            order: [[0, "asc"]],
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
            initComplete: function () {
                initDataTableSearch(this.api());
            },
            destroy: true
        });
    }

    $("#exportExceldriver").on("click", () => window.location.href = "../modules/export_excel_driver");

}