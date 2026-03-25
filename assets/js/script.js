// ======================================================
// GLOBAL FUNCTIONS - LOAD ONCE (SPA READY)
// ======================================================
// File ini di-load SEKALI di index.html dan tidak perlu reload
(function() {
    'use strict';

    // Cegah double initialization
    if (window.FIS_GLOBAL_LOADED) {
        console.warn("⚠️ Global functions already loaded, skipping re-initialization");
        return;
    }

    console.log("🚀 Loading global functions...");

    // ======================================================
    // DROPDOWN CACHE SYSTEM
    // ======================================================
    window.dropdownCache = {};

    /**
     * Clear dropdown cache
     * @param {string|null} table - Nama tabel yang cachenya mau dihapus, atau null untuk hapus semua
     */
    window.clearDropdownCache = function(table = null) {
        if (table) {
            // Clear cache untuk table tertentu
            Object.keys(window.dropdownCache).forEach(key => {
                if (key.startsWith(table + '_')) {
                    delete window.dropdownCache[key];
                }
            });
            console.log(`🗑️ Cache cleared for table: ${table}`);
        } else {
            // Clear semua cache
            window.dropdownCache = {};
            console.log(`🗑️ All dropdown cache cleared`);
        }
    };

    // ======================================================
    // SESSION MANAGEMENT
    // ======================================================
    const SESSION_CONFIG = {
        idleTimeout: 1200000,    // 20 menit (client-side idle timer)
        warningTimeout: 1140000, // 19 menit (warning muncul)
        checkInterval: 60000,    // tidak terpakai lagi
        updateInterval: 30000    // update ke server setiap 30 detik
    };

    let idleTimer, warningTimer, sessionExtended = false;
    let lastServerUpdate = Date.now(); // waktu terakhir update ke server

    // Update activity ke server secara periodik
    function updateLastActivity() {
        try {
            // Simpan di temp storage (opsional, untuk fallback)
            window.tempStorage = window.tempStorage || {};
            window.tempStorage.lastActivity = Date.now();

            // Update ke server setiap 30 detik (throttling benar)
            if (Date.now() - lastServerUpdate >= SESSION_CONFIG.updateInterval) {
                $.ajax({
                    url: "update_activity.php",
                    type: "POST",
                    dataType: "json",
                    cache: false,
                    timeout: 10000, // tambahan: cegah hanging
                    success: function(response) {
                        if (response.status === "updated") {
                            lastServerUpdate = Date.now();
                            console.log("✅ Activity updated on server:", new Date().toLocaleTimeString());
                        }
                    },
                    error: function(xhr, status, error) {
                        console.warn("⚠️ Failed to update activity on server:", status);
                        // Optional: retry logic bisa ditambahkan di sini
                    }
                });
            }

            // Selalu reset timer idle di client
            resetIdleTimer();

        } catch (e) {
            console.warn('Storage not available:', e);
        }
    }

    function showWarning() {
        Swal.fire({
            icon: "warning",
            title: "Session About to Expire",
            text: "Your session will expire in 1 minute. Do you want to extend your session?",
            showCancelButton: true,
            confirmButtonText: "Yes, Extend Session",
            cancelButtonText: "No, Logout",
            timer: 60000,
            timerProgressBar: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "extend_session.php",
                    type: "POST",
                    success: function (response) {
                        if (response.status === "extended") {
                            sessionExtended = true;
                            lastServerUpdate = Date.now();
                            updateLastActivity();
                            Swal.fire({
                                icon: "success",
                                title: "Session Extended",
                                text: "Your session has been extended.",
                                timer: 2000,
                                showConfirmButton: false,
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Failed to extend session. Please login again.",
                        }).then(() => {
                            window.location.href = "/login?message=session_expired";
                        });
                    },
                });
            } else {
                clearTimeout(idleTimer);
                Swal.fire({
                    icon: "warning",
                    title: "Session Time Expired",
                    text: "Please login again.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: true,
                    confirmButtonText: "Login Again",
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "/login?message=session_expired";
                    }
                });
            }
        });
    }

    function resetIdleTimer() {
        clearTimeout(idleTimer);
        clearTimeout(warningTimer);
        sessionExtended = false;

        warningTimer = setTimeout(() => {
            if (!sessionExtended) showWarning();
        }, SESSION_CONFIG.warningTimeout);

        idleTimer = setTimeout(() => {
            if (!sessionExtended) {
                clearTimeout(warningTimer);
                Swal.fire({
                    icon: "warning",
                    title: "Session Time Expired",
                    text: "Please login again.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: true,
                    confirmButtonText: "Login Again",
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "/login?message=session_expired";
                    }
                });
            }
        }, SESSION_CONFIG.idleTimeout);
    }

    // Event delegation untuk activity tracking (SPA friendly)
    $(document).on("mousemove keydown click", updateLastActivity);

    // Server-side session check - lebih jarang (setiap 2 menit)
    setInterval(() => {
        $.ajax({
            url: "check_session.php",
            type: "GET",
            dataType: "json",
            cache: false,
            success: function (response) {
                if (response.status === "expired") {
                    clearTimeout(idleTimer);
                    clearTimeout(warningTimer);
                    Swal.fire({
                        icon: "warning",
                        title: "Session Time Expired",
                        text: "Please login again.",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: true,
                        confirmButtonText: "Login Again",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "/login.php?message=session_expired";
                        }
                    });
                }
            },
            error: function () {
                console.error("Failed to check session on server");
            },
        });
    }, 120000); // Setiap 2 menit

    // Initialize idle timer
    resetIdleTimer();

    // ======================================================
    // DATATABLE SEARCH BOX
    // ======================================================
    window.initDataTableSearch = function(api) {
    // Fungsi debounce untuk menunda eksekusi sampai user berhenti mengetik
    function debounce(fn, delay = 400) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    api.columns().eq(0).each(function (colIdx) {
        if (colIdx === api.columns().count() - 1) return; // skip kolom terakhir (misal "Action")

        const $input = $('input', api.column(colIdx).header());
        if ($input.length && !$input.data('init-search')) {

            // Tandai agar tidak di-bind ulang
            $input.data('init-search', true);

            // Handle Ctrl + A
            $input.on('keydown', function (e) {
                if (e.ctrlKey && e.keyCode === 65) {
                    e.preventDefault();
                    $(this).select();
                }
            });

            // Event ketik dengan debounce agar tidak draw setiap karakter
            $input.on('keyup change clear', debounce(function (e) {
                const value = this.value;
                api.column(colIdx).search(value).draw();
            }, 400));

            // Klik fokus + select text
            $input.on('click', function (e) {
                e.stopPropagation();
                $(this).focus().select();
            });
        }
    });
};

    // ======================================================
    // TOOLTIP INITIALIZATION (Event Delegation untuk SPA)
    // ======================================================
    // Inisialisasi tooltip untuk elemen dengan attribute [title]
    $(document).ready(function() {
        initializeTooltips();
    });

    // Function untuk inisialisasi semua tooltip
    function initializeTooltips() {
        $('button[title], a[title], [data-bs-toggle="tooltip"]').each(function() {
            if (!$(this).data('bs-tooltip-initialized')) {
                new bootstrap.Tooltip(this, {
                    container: 'body',
                    boundary: 'viewport',
                    placement: $(this).data('bs-placement') || 'top',
                    trigger: 'hover'
                });
                $(this).data('bs-tooltip-initialized', true);
            }
        });
    }
    
    // Re-initialize tooltip setelah konten dinamis dimuat (untuk SPA)
    $(document).on('DOMNodeInserted', function() {
        // Debounce untuk performance
        clearTimeout(window.tooltipTimeout);
        window.tooltipTimeout = setTimeout(function() {
            initializeTooltips();
        }, 100);
    });
    
    // Event delegation untuk mouseenter (backup untuk elemen dinamis)
    $(document).on('mouseenter', '[data-bs-toggle="tooltip"], button[title], a[title]', function () {
        if (!$(this).data('bs-tooltip-initialized')) {
            if ($(this).is(':visible')) {
                const tooltip = new bootstrap.Tooltip(this, {
                    container: 'body',
                    boundary: 'viewport',
                    placement: $(this).data('bs-placement') || 'top',
                    trigger: 'hover'
                });
                $(this).data('bs-tooltip-initialized', true);
                tooltip.show();
            }
        } else {
            const existingTooltip = bootstrap.Tooltip.getInstance(this);
            if (existingTooltip) {
                existingTooltip.show();
            }
        }
    });
    
    // Event untuk hide tooltip
    $(document).on('mouseleave', '[data-bs-toggle="tooltip"], button[title], a[title]', function () {
        const tooltipInstance = bootstrap.Tooltip.getInstance(this);
        if (tooltipInstance) {
            tooltipInstance.hide();
        }
    });
    
    // Dispose tooltip ketika elemen akan dihapus (cleanup)
    // $(document).on('DOMNodeRemoved', function(e) {
    //     const tooltipInstance = bootstrap.Tooltip.getInstance(e.target);
    //     if (tooltipInstance) {
    //         tooltipInstance.dispose();
    //     }
    // });
    
    //Hide tooltip saat tombol action diklik (fix tooltip stuck)
    $(document).on('click', 'button, a', function () {
    const tooltipInstance = bootstrap.Tooltip.getInstance(this);
    if (tooltipInstance) {
        tooltipInstance.hide();
    }
    // Bersihkan tooltip orphan yang tertinggal di body
    setTimeout(() => $('.tooltip').remove(), 150);
});
    
    // DROPDOWN DRIVER (dengan auto-fill nopol & phone)
    // ======================================================
    window.loadDriverDropdown = function($select, modalId, defaultValue = null) {
        return $.ajax({
            url: "API/dropdownlist",
            type: "GET",
            data: {
                table  : "data_driver",
                column : "id",
                display: "nama",
            },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $select.empty().append('<option value="">Pilih Driver</option>');

                    // Simpan nopol & phone ke dalam option sebagai data attribute
                    response.data.forEach(function(item) {
                        const $opt = $(new Option(item.text, item.id));
                        $opt.data("nopol", item.nopol || "");
                        $opt.data("phone", item.phone || "");
                        $select.append($opt);
                    });

                    if ($select.hasClass("select2-hidden-accessible")) {
                        $select.select2("destroy");
                    }

                    $select.select2({
                        width         : "100%",
                        dropdownParent: modalId ? $(modalId) : null,
                        allowClear    : true,
                        placeholder   : "Pilih Driver",
                        language: {
                            noResults : () => "Driver tidak ditemukan",
                            searching : () => "Mencari..."
                        },
                        templateResult: function(state) {
                            if (!state.id) return state.text;
                            const $opt = $select.find('option[value="' + CSS.escape(state.id) + '"]');
                            const nopol = $opt.data("nopol") || "-";
                            return $('<span>' + state.text + ' <small class="text-muted">(' + nopol + ')</small></span>');
                        }
                    });

                    if (defaultValue !== null && defaultValue !== '') {
                        setTimeout(() => {
                            $select.val(defaultValue).trigger("change");
                        }, 100);
                    }
                } else {
                    console.error("Failed to load Driver dropdown:", response.message);
                }
            },
            error: function (xhr) {
                console.error("Error loading Driver dropdown:", xhr.responseText);
            },
        });
    };

    // ======================================================
    // SPECIFIC DROPDOWN LOADERS (LEGACY - OPTIONAL)
    // ======================================================
    window.loadOriginDropdown = function ($select, modalId, defaultValue = null) {
        return $.ajax({
            url: "API/dropdownlist",
            type: "GET",
            data: { table: "master_origin", column: "origin_code", display: "origin_code" },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $select.empty().append('<option value="">Pilih Origin</option>');
                    response.data.forEach(item => {
                        $select.append(new Option(item.text, item.id));
                    });
                    
                    if ($select.hasClass('select2-hidden-accessible')) {
                        $select.select2('destroy');
                    }
                    
                    $select.select2({
                        width: "100%",
                        dropdownParent: modalId ? $(modalId) : null,
                        allowClear: true,
                        placeholder: "Pilih Origin",
                        language: {
                            noResults: () => "Data origin tidak ditemukan",
                            searching: () => "Mencari..."
                        },
                    });
                    
                    if (defaultValue !== null) {
                        setTimeout(() => {
                            $select.val(defaultValue).trigger('change');
                        }, 100);
                    }
                } else {
                    console.error("Failed to load Origin:", response.message || "Data kosong");
                    $select.empty().append('<option value="">Pilih Origin</option>');
                }
            },
            error: function (xhr) {
                console.error("Error loading Origin:", xhr.responseText);
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: "Gagal memuat data origin. Silakan periksa koneksi atau server.",
                });
                $select.empty().append('<option value="">Pilih Origin</option>');
            },
        });
    };

    window.loadModeOfTransportDropdown = function ($select, modalId, defaultValue = null) {
        return $.ajax({
            url: "API/dropdownlist",
            type: "GET",
            data: { table: "master_mode_of_transport", column: "mot_code", display: "mot_code" },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $select.empty().append('<option value="">Pilih Mode of Transport</option>');
                    response.data.forEach(item => {
                        $select.append(new Option(item.text, item.id));
                    });
                    
                    if ($select.hasClass('select2-hidden-accessible')) {
                        $select.select2('destroy');
                    }
                    
                    $select.select2({
                        width: "100%",
                        dropdownParent: modalId ? $(modalId) : null,
                        allowClear: true,
                        placeholder: "Pilih Mode of Transport",
                        language: {
                            noResults: () => "Data MOT tidak ditemukan",
                            searching: () => "Mencari..."
                        },
                    });
                    
                    if (defaultValue !== null) {
                        setTimeout(() => {
                            $select.val(defaultValue).trigger('change');
                        }, 100);
                    }
                } else {
                    console.error("Failed to load MOT:", response.message || "Data kosong");
                    $select.empty().append('<option value="">Pilih Mode of Transport</option>');
                }
            },
            error: function (xhr) {
                console.error("Error loading MOT:", xhr.responseText);
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: "Gagal memuat data mode of transport. Silakan periksa koneksi atau server.",
                });
                $select.empty().append('<option value="">Pilih Mode of Transport</option>');
            },
        });
    };

    window.loadLocatorDropdown = function ($select, modalId, defaultValue = null) {
        return $.ajax({
            url: "API/dropdownlist",
            type: "GET",
            data: { table: "master_locator", column: "locator", display: "locator" },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $select.empty().append('<option value="">Pilih Locator</option>');
                    response.data.forEach(item => {
                        $select.append(new Option(item.text, item.id));
                    });
                    
                    if ($select.hasClass('select2-hidden-accessible')) {
                        $select.select2('destroy');
                    }
                    
                    $select.select2({
                        width: "100%",
                        dropdownParent: modalId ? $(modalId) : null,
                        allowClear: true,
                        placeholder: "Pilih Locator",
                        language: {
                            noResults: () => "Data locator tidak ditemukan",
                            searching: () => "Mencari..."
                        },
                    });
                    
                    if (defaultValue !== null) {
                        setTimeout(() => {
                            $select.val(defaultValue).trigger('change');
                        }, 100);
                    }
                } else {
                    console.error("Failed to load Locator:", response.message || "Data kosong");
                    $select.empty().append('<option value="">Pilih Locator</option>');
                }
            },
            error: function (xhr) {
                console.error("Error loading Locator:", xhr.responseText);
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: "Gagal memuat data locator. Silakan periksa koneksi atau server.",
                });
                $select.empty().append('<option value="">Pilih Locator</option>');
            },
        });
    };

    // ======================================================
    // GENERIC DROPDOWN DATA LOADER WITH CACHING
    // ======================================================
    /**
     * Helper function untuk populate dropdown
     */
    function populateDropdown($select, data, display, modalId, defaultValue) {
        $select.empty().append(`<option value="">Pilih ${display.charAt(0).toUpperCase() + display.slice(1)}</option>`);
        data.forEach(item => {
            $select.append(new Option(item.text, item.id));
        });
        
        // Destroy select2 jika sudah ada
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        
        $select.select2({
            width: "100%",
            dropdownParent: modalId ? $(modalId) : null,
            allowClear: true,
            placeholder: `Pilih ${display.charAt(0).toUpperCase() + display.slice(1)}`,
            language: {
                noResults: () => "Data tidak ditemukan",
                searching: () => "Mencari..."
            },
        });
        
        // Set default value jika diberikan
        if (defaultValue !== null && defaultValue !== '') {
            setTimeout(() => {
                $select.val(defaultValue).trigger('change');
                console.log(`✅ Set default value: ${defaultValue} for ${display}`);
            }, 100);
        }
    }

    /**
     * Load dropdown data with caching support
     * @param {jQuery} $select - jQuery element dari select
     * @param {string} table - Nama tabel
     * @param {string} column - Nama kolom untuk value
     * @param {string} display - Nama kolom untuk display text
     * @param {string} modalId - ID modal untuk dropdownParent (optional)
     * @param {string|null} defaultValue - Default value yang akan dipilih (optional)
     * @param {string|null} filterColumn - Kolom untuk filter (optional)
     * @param {string|null} filterValue - Value untuk filter (optional)
     * @returns {Promise}
     */
    window.loadDropdownData = function($select, table, column, display, modalId, defaultValue = null, filterColumn = null, filterValue = null) {
        const cacheKey = `${table}_${column}_${display}_${filterColumn || ''}_${filterValue || ''}`;
        
        // ✅ Cek cache dulu
        if (window.dropdownCache[cacheKey]) {
            console.log(`✅ Using cached data for ${table}`);
            populateDropdown($select, window.dropdownCache[cacheKey], display, modalId, defaultValue);
            return Promise.resolve({ status: 'success', data: window.dropdownCache[cacheKey], cached: true });
        }
        
        const ajaxData = { table, column, display };
        
        // ✅ Tambahkan filter jika ada
        if (filterColumn && filterValue) {
            ajaxData.filter_column = filterColumn;
            ajaxData.filter_value = filterValue;
        }
        
        return $.ajax({
            url: "API/dropdownlist",
            type: "GET",
            data: ajaxData,
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    // ✅ Simpan ke cache
                    window.dropdownCache[cacheKey] = response.data;
                    console.log(`💾 Cached data for ${table} (${response.data.length} items)`);
                    
                    populateDropdown($select, response.data, display, modalId, defaultValue);
                } else {
                    console.error("Failed to load data:", response.message);
                    $select.empty().append(`<option value="">Pilih ${display.charAt(0).toUpperCase() + display.slice(1)}</option>`);
                }
            },
            error: function (xhr) {
                console.error("Error loading dropdown:", xhr.responseText);
                showErrorToast("Error", `Gagal memuat data ${display}`);
                $select.empty().append(`<option value="">Pilih ${display.charAt(0).toUpperCase() + display.slice(1)}</option>`);
            },
        });
    };

    // ======================================================
    // CLEANUP FUNCTION (untuk destroy DataTable, Select2, dll saat page change)
    // ======================================================
    window.cleanupPage = function() {
        // Destroy all DataTables
        $.fn.dataTable.tables({ visible: true, api: true }).destroy();

        // Destroy all Select2
        $('.select2-hidden-accessible').select2('destroy');

        // Hide all tooltips
        $('.tooltip').remove();

        // Remove all SweetAlert
        Swal.close();

        console.log("🧹 Page cleanup completed");
    };
    
    // ======================================================
    // TOOLTIP MANAGEMENT
    // ======================================================
    // Nonaktifkan tooltip Bootstrap untuk tombol X di Select2
    $(document).on('mouseenter', '.select2-selection__clear', function(e) {
        e.stopPropagation();
        $(this).removeAttr('title');
        $(this).removeAttr('data-original-title');
        $(this).removeAttr('data-bs-original-title');
        
        // Dispose tooltip Bootstrap jika ada
        const tooltipInstance = bootstrap.Tooltip.getInstance(this);
        if (tooltipInstance) {
            tooltipInstance.dispose();
        }
    });

    // Cegah inisialisasi tooltip untuk elemen Select2 clear button
    window.initializeTooltips = function() {
        $('button[title], a[title], [data-bs-toggle="tooltip"]').not('.select2-selection__clear').each(function() {
            if (!$(this).data('bs-tooltip-initialized')) {
                new bootstrap.Tooltip(this, {
                    container: 'body',
                    boundary: 'viewport',
                    placement: $(this).data('bs-placement') || 'top',
                    trigger: 'hover'
                });
                $(this).data('bs-tooltip-initialized', true);
            }
        });
    };

    // ======================================================
    // REALTIME CLOCK
    // ======================================================
    window.initRealtimeClock = function() {
        function updateClock() {
            const now = new Date();
            
            // Convert ke WIB (GMT+7) menggunakan toLocaleString
            const options = {
                timeZone: 'Asia/Jakarta',
                year: 'numeric',
                month: 'long',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };
            
            const formatter = new Intl.DateTimeFormat('en-US', options);
            const parts = formatter.formatToParts(now);
            
            // Parse hasil format
            let day, month, year, hour, minute, second;
            
            parts.forEach(part => {
                if (part.type === 'day') day = part.value;
                if (part.type === 'month') month = part.value;
                if (part.type === 'year') year = part.value;
                if (part.type === 'hour') hour = part.value;
                if (part.type === 'minute') minute = part.value;
                if (part.type === 'second') second = part.value;
            });
            
            // Update setiap button
            $('#dayBtn').text(day);
            $('#monthBtn').text(month);
            $('#yearBtn').text(year);
            $('#timeBtn').text(`${hour}:${minute}:${second} WIB`);
        }

        // Jalankan saat pertama kali
        updateClock();

        // Update setiap 1 detik (1000 millisecond)
        setInterval(updateClock, 1000);
    };

    // Panggil fungsi saat DOM siap
    $(document).ready(function() {
        window.initRealtimeClock();
    });

    // Mark as loaded
    window.FIS_GLOBAL_LOADED = true;
    console.log("✅ Global functions loaded successfully with caching support");

})();