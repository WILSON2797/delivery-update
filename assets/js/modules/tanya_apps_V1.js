/**
 * tanya_apps.js
 * Chat Assistant — Keyword Detection, Driver Availability & Trip Count
 */
function initPageScripts() {
    console.log("tanya_apps.js loaded");
    feather.replace();
    let isProcessing = false;
    const $chatBody = $('#chatBody');
    const $input    = $('#chatInput');
    const $btnSend  = $('#btnSend');
    const $typing   = $('#typingIndicator');
    // =========================================================================
    // INTENTS — tambah keyword di sini
    // =========================================================================
    const INTENTS = [
        {
            id: 'driver_standby',
            keywords: [
                'driver standby','driver available','driver siapa','siapa driver',
                'driver kosong','driver bebas','cek driver','driver yang ada',
                'list driver','driver tersedia','mover standby','mover available',
                'mover siapa','siapa mover','mover kosong','mover bebas',
                'cek mover','driver on standby','siapa yang standby',
                'berapa driver yang standby','driver yg standby','driver standby sekarang',
                'ada driver gak','driver free','driver nganggur'
            ],
            action: 'get_available_drivers'
        },
        {
            id: 'driver_on_delivery',
            keywords: [
                'driver on delivery','driver sedang delivery','driver bertugas',
                'driver berangkat','driver di jalan','on delivery','berapa driver delivery',
                'driver yang delivery','driver aktif','driver jalan','driver kirim'
            ],
            action: 'get_on_delivery_drivers'
        },
        {
            id: 'driver_onsite',
            keywords: [
                'driver onsite','driver di site','driver on site','driver di lokasi',
                'driver waiting ho','waiting handover','driver menunggu'
            ],
            action: 'get_onsite_drivers'
        },
        {
            id: 'total_driver',
            keywords: [
                'total driver','jumlah driver','semua driver',
                'daftar driver','list semua driver'
            ],
            action: 'get_all_drivers'
        },
        // ── DN BY STATUS — harus di atas driver_trip_count ───────────────────
        // supaya keyword 'dn pool', 'dn cancel', 'btp' tidak nyangkut di trip count
        {
            id: 'dn_pool_mover',
            keywords: [
                'dn pool mover','dn pool','berapa dn pool','list dn pool',
                'cek dn pool','dn yang pool','dn status pool','pool mover'
            ],
            action: 'get_dn_by_status',
            statusParam: 'Pool mover'
        },
        {
            id: 'dn_cancelled',
            keywords: [
                'dn cancelled','dn cancel','berapa dn cancel','berapa dn cancelled',
                'list dn cancel','cek dn cancel','dn yang cancel','dn dibatalkan',
                'dn yg cancel','dn yg cancelled'
            ],
            action: 'get_dn_by_status',
            statusParam: 'Cancelled'
        },
        {
            id: 'dn_back_to_pool',
            keywords: [
                'back to pool','berapa btp','dn btp','dn back to pool',
                'list btp','cek btp','berapa dn btp','dn yang btp','dn kembali pool',
                'dn yg btp'
            ],
            action: 'get_dn_by_status',
            statusParam: 'Back To Pool'
        },
        // ── TRIP COUNT — di bawah dn_by_status ───────────────────────────────
        {
            id: 'driver_trip_count',
            keywords: [
                'berapa kali','berapa pengiriman','berapa trip',
                'sudah berapa','total pengiriman','total trip',
                'rekap pengiriman','pengiriman bulan','dn bulan','trip bulan',
                'kirim berapa','berapa delivery','rekap dn','history pengiriman',
                'riwayat pengiriman','berapa shipment','berapa dn'
            ],
            action: 'get_driver_trip_count'
        }
    ];
    // =========================================================================
    // Detect intent
    // =========================================================================
    function detectIntent(text) {
        const lower = text.toLowerCase().trim();
        for (const intent of INTENTS) {
            for (const kw of intent.keywords) {
                if (lower.includes(kw)) return intent;
            }
        }
        return null;
    }
    // =========================================================================
    // Extract nama driver dari kalimat bebas
    // =========================================================================
    function extractDriverName(text) {
        const stopwords = [
            'ada','berapa','kali','mover','driver','mengirim','kirim','dalam',
            'satu','dua','tiga','bulan','ini','minggu','hari','pengiriman','total',
            'dn','trip','sudah','untuk','siapa','cek','rekap','delivery','sekarang',
            'apakah','history','riwayat','shipment','lakukan','sudah','pernah',
            'sampai','bulanini','mingguini','hariini','yang'
        ];
        let lower = text.toLowerCase().trim().replace(/[?.,!]/g, '');
        stopwords.forEach(sw => {
            lower = lower.replace(new RegExp('\\b' + sw + '\\b', 'g'), ' ');
        });
        const cleaned = lower.replace(/\s+/g, ' ').trim();
        if (!cleaned) return null;
        const words = cleaned.split(' ').filter(w => w.length > 1);
        if (words.length === 0) return null;
        return words.map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    }
    // =========================================================================
    // Build driver card
    // =========================================================================
    function buildDriverCard(d, badgeHtml, avatarStyle) {
        const plainText = `Name  : ${d.driver_name}\nPhone : ${d.phone || '-'}\nNopol : ${d.nopol || '-'}`;
        const encoded   = encodeURIComponent(plainText);
        const initials  = getInitials(d.driver_name);
        return `
            <div class="driver-card">
                <div class="driver-avatar" style="${avatarStyle}">${initials}</div>
                <div class="flex-grow-1">
                    <div class="fw-semibold" style="font-size:.88rem;">${escHtml(d.driver_name)}</div>
                    <div class="text-muted" style="font-size:.75rem;">
                        📞 ${escHtml(d.phone || '-')}
                        ${d.nopol       ? `&nbsp;·&nbsp; 🚛 ${escHtml(d.nopol)}`        : ''}
                        ${d.destination ? `&nbsp;·&nbsp; 📍 ${escHtml(d.destination)}`  : ''}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    ${badgeHtml}
                    <button class="btn-copy-single" title="Copy ke clipboard"
                            data-copy="${encoded}" onclick="handleCopyDriver(this)">
                        <i data-feather="copy" style="width:13px;height:13px;"></i>
                    </button>
                </div>
            </div>`;
    }
    function buildCopyAllBtn(drivers, label) {
        const all = drivers.map(d =>
            `Name  : ${d.driver_name}\nPhone : ${d.phone || '-'}\nNopol : ${d.nopol || '-'}`
        ).join('\n\n');
        const encoded = encodeURIComponent(all);
        return `
            <button class="btn-copy-all mb-2" data-copy="${encoded}" onclick="handleCopyAll(this)">
                <i data-feather="copy" style="width:13px;height:13px;"></i>
                Copy Semua (${drivers.length} ${label})
            </button>`;
    }
    // =========================================================================
    // Build trip detail table
    // =========================================================================
    function buildTripTable(detail) {
        if (!detail || detail.length === 0) return '';
        const rows = detail.map(r => `
            <tr>
                <td>${escHtml(r.dn_number    || '-')}</td>
                <td>${escHtml(r.date_request || '-')}</td>
                <td>${escHtml(r.destination_city || '-')}</td>
                <td>
                    <span class="ta-status-badge">${escHtml(r.status || '-')}</span>
                </td>
            </tr>`).join('');
        return `
            <div class="ta-trip-table-wrap">
                <table class="ta-trip-table">
                    <thead>
                        <tr>
                            <th>DN Number</th>
                            <th>Tanggal</th>
                            <th>Tujuan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
    }
    // =========================================================================
    // Build DN status table (untuk Pool mover / Cancelled / Back To Pool)
    // =========================================================================
    function buildDnStatusTable(data) {
        if (!data || data.length === 0) return '';
        const rows = data.map((r, i) => `
            <tr>
                <td style="text-align:center;color:#94a3b8;">${i + 1}</td>
                <td><strong>${escHtml(r.dn_number || '-')}</strong></td>
                <td>${escHtml(r.site_id || '-')}</td>
            </tr>`).join('');
        return `
            <div class="ta-trip-table-wrap">
                <table class="ta-trip-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>DN Number</th>
                            <th>Site ID</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
    }
    // =========================================================================
    // Send message
    // =========================================================================
    function sendMessage(text) {
        text = text.trim();
        if (!text || isProcessing) return;
        appendUserBubble(text);
        $input.val('').css('height', 'auto');
        isProcessing = true;
        $btnSend.prop('disabled', true);
        showTyping();
        const intent = detectIntent(text);
        if (intent) {
            // ── DN by status: langsung panggil tanpa nama ─────────────────────
            if (intent.action === 'get_dn_by_status') {
                setTimeout(() => {
                    $.ajax({
                        url: 'API/tanya_apps_api',
                        method: 'GET',
                        data: { action: 'get_dn_by_status', status: intent.statusParam },
                        dataType: 'json',
                        success: function (res) {
                            hideTyping();
                            renderIntentResponse(intent.id, res, intent.statusParam);
                            isProcessing = false;
                            $btnSend.prop('disabled', false);
                            feather.replace();
                        },
                        error: function () {
                            hideTyping();
                            appendBotBubble('⚠️ Maaf, terjadi kesalahan. Silakan coba lagi.');
                            isProcessing = false;
                            $btnSend.prop('disabled', false);
                        }
                    });
                }, 600);
                return;
            }
            // ── Trip count: wajib ada nama ────────────────────────────────────
            if (intent.id === 'driver_trip_count') {
                const nama = extractDriverName(text);
                if (!nama) {
                    hideTyping();
                    appendBotBubble(
                        `Mohon sertakan nama mover/driver-nya. Contoh:<br>
                        <em>"Arnold berapa dn bulan ini?"</em><br>
                        <em>"rekap pengiriman Budi bulan ini"</em>`, true);
                    isProcessing = false;
                    $btnSend.prop('disabled', false);
                    feather.replace();
                    return;
                }
                setTimeout(() => {
                    $.ajax({
                        url: 'API/tanya_apps_api',
                        method: 'GET',
                        data: { action: intent.action, nama: nama },
                        dataType: 'json',
                        success: function (res) {
                            hideTyping();
                            renderIntentResponse(intent.id, res);
                            isProcessing = false;
                            $btnSend.prop('disabled', false);
                            feather.replace();
                        },
                        error: function () {
                            hideTyping();
                            appendBotBubble('⚠️ Maaf, terjadi kesalahan. Silakan coba lagi.');
                            isProcessing = false;
                            $btnSend.prop('disabled', false);
                        }
                    });
                }, 600);
                return;
            }
            // ── Intent lain (standby, on delivery, onsite, total) ─────────────
            setTimeout(() => {
                $.ajax({
                    url: 'API/tanya_apps_api',
                    method: 'GET',
                    data: { action: intent.action },
                    dataType: 'json',
                    success: function (res) {
                        hideTyping();
                        renderIntentResponse(intent.id, res);
                        isProcessing = false;
                        $btnSend.prop('disabled', false);
                        feather.replace();
                    },
                    error: function () {
                        hideTyping();
                        appendBotBubble('⚠️ Maaf, terjadi kesalahan. Silakan coba lagi.');
                        isProcessing = false;
                        $btnSend.prop('disabled', false);
                    }
                });
            }, 600);
        } else {
            setTimeout(() => {
                hideTyping();
                appendBotBubble(buildFallbackHTML(text), true);
                isProcessing = false;
                $btnSend.prop('disabled', false);
                feather.replace();
            }, 500);
        }
    }
    // =========================================================================
    // Render response berdasarkan intent
    // =========================================================================
    function renderIntentResponse(intentId, res, statusParam) {
        if (!res.success) {
            appendBotBubble('⚠️ ' + (res.message || 'Gagal memuat data.'));
            return;
        }
        const timestamp = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const colors    = ['#1a73e8','#0d9e6e','#d97706','#7c3aed','#dc2626','#0891b2'];
        let html        = '';

        // ── STANDBY ──────────────────────────────────────────────────────────
        if (intentId === 'driver_standby') {
            const drivers = res.data || [];
            if (drivers.length === 0) {
                html = `<div class="d-flex align-items-center gap-2">
                            <i data-feather="info" style="width:15px;height:15px;color:#f59e0b;"></i>
                            <strong>Tidak ada driver yang standby saat ini.</strong>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.82rem;">
                            Semua driver sedang On Delivery atau Onsite.
                        </div>`;
            } else {
                html = `<div class="d-flex align-items-center gap-2 mb-1">
                            <i data-feather="user-check" style="width:15px;height:15px;color:#0d9e6e;"></i>
                            <strong>Driver Standby (${drivers.length} orang)</strong>
                        </div>
                        <div class="text-muted mb-2" style="font-size:.78rem;">
                            Driver berikut <strong>tidak</strong> sedang On Delivery maupun Onsite:
                        </div>`;
                html += buildCopyAllBtn(drivers, 'driver');
                drivers.forEach((d, i) => {
                    const bg    = colors[i % colors.length];
                    const badge = `<span class="badge" style="background:#d1fae5;color:#065f46;font-size:.72rem;">Standby</span>`;
                    html += buildDriverCard(d, badge, `background:${bg}20;color:${bg};`);
                });
            }
        // ── ON DELIVERY ───────────────────────────────────────────────────────
        } else if (intentId === 'driver_on_delivery') {
            const drivers = res.data || [];
            if (drivers.length === 0) {
                html = `<i data-feather="info" style="width:15px;height:15px;"></i>
                        <strong> Tidak ada driver yang sedang On Delivery.</strong>`;
            } else {
                html = `<div class="d-flex align-items-center gap-2 mb-1">
                            <i data-feather="navigation" style="width:15px;height:15px;color:#d97706;"></i>
                            <strong>Driver On Delivery (${drivers.length} orang)</strong>
                        </div>`;
                html += buildCopyAllBtn(drivers, 'driver');
                drivers.forEach(d => {
                    const badge = `<span class="badge" style="background:#fef3c7;color:#92400e;font-size:.72rem;">On Delivery</span>`;
                    html += buildDriverCard(d, badge, `background:#fef3c720;color:#d97706;`);
                });
            }
        // ── ONSITE ────────────────────────────────────────────────────────────
        } else if (intentId === 'driver_onsite') {
            const drivers = res.data || [];
            if (drivers.length === 0) {
                html = `<i data-feather="info" style="width:15px;height:15px;"></i>
                        <strong> Tidak ada driver yang sedang Onsite.</strong>`;
            } else {
                html = `<div class="d-flex align-items-center gap-2 mb-1">
                            <i data-feather="map-pin" style="width:15px;height:15px;color:#7c3aed;"></i>
                            <strong>Driver Onsite / Waiting HO (${drivers.length} orang)</strong>
                        </div>`;
                html += buildCopyAllBtn(drivers, 'driver');
                drivers.forEach(d => {
                    const badge = `<span class="badge" style="background:#ede9fe;color:#5b21b6;font-size:.72rem;">Onsite</span>`;
                    html += buildDriverCard(d, badge, `background:#ede9fe;color:#7c3aed;`);
                });
            }
        // ── SEMUA DRIVER ──────────────────────────────────────────────────────
        } else if (intentId === 'total_driver') {
            const drivers = res.data || [];
            html = `<div class="d-flex align-items-center gap-2 mb-1">
                        <i data-feather="users" style="width:15px;height:15px;color:#1a73e8;"></i>
                        <strong>Semua Driver Terdaftar (${drivers.length} orang)</strong>
                    </div>`;
            html += buildCopyAllBtn(drivers, 'driver');
            drivers.forEach((d, i) => {
                const bg = colors[i % colors.length];
                html += buildDriverCard(d, getStatusBadge(d.current_status), `background:${bg}20;color:${bg};`);
            });
        // ── TRIP COUNT ────────────────────────────────────────────────────────
        } else if (intentId === 'driver_trip_count') {
            if (!res.found) {
                html = `<div class="d-flex align-items-center gap-2">
                            <i data-feather="alert-circle" style="width:15px;height:15px;color:#f59e0b;"></i>
                            <span>${escHtml(res.message || 'Nama driver tidak ditemukan di database.')}</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.8rem;">
                            Pastikan nama sesuai data. Contoh: <em>Arnold</em>, <em>Budi</em>, <em>Harry</em>
                        </div>`;
            } else {
                const d   = res.data;
                const clr = d.total > 0 ? '#0d9e6e' : '#94a3b8';
                html = `
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i data-feather="bar-chart-2" style="width:15px;height:15px;color:#1a73e8;"></i>
                        <strong>Rekap Pengiriman — ${escHtml(d.driver_name)}</strong>
                    </div>
                    <div class="ta-summary-card">
                        <div class="driver-avatar" style="background:#dbeafe;color:#1a73e8;width:48px;height:48px;font-size:1.1rem;">
                            ${getInitials(d.driver_name)}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold" style="font-size:.95rem;">${escHtml(d.driver_name)}</div>
                            <div class="text-muted" style="font-size:.76rem;">📅 ${escHtml(d.period)}</div>
                            <div class="text-muted" style="font-size:.76rem;">📞 ${escHtml(d.phone || '-')} &nbsp;·&nbsp; 🚛 ${escHtml(d.nopol || '-')}</div>
                        </div>
                        <div class="text-end">
                            <div style="font-size:2rem;font-weight:800;color:${clr};line-height:1;">${d.total}</div>
                            <div class="text-muted" style="font-size:.72rem;">DN / Trip</div>
                        </div>
                    </div>
                    ${d.total > 0
                        ? `<div class="text-muted mb-2 mt-1" style="font-size:.78rem;">
                                Detail ${d.total} pengiriman bulan ini:
                           </div>
                           ${buildTripTable(d.detail)}`
                        : `<div class="text-muted mt-2" style="font-size:.82rem;">
                                Tidak ada pengiriman tercatat bulan ini.
                           </div>`
                    }`;
            }
        // ── DN POOL MOVER ─────────────────────────────────────────────────────
        } else if (intentId === 'dn_pool_mover') {
            const statusLabel = statusParam || 'Pool mover';
            const iconColor   = '#0891b2';
            const badgeBg     = '#e0f2fe';
            const badgeColor  = '#0c4a6e';
            if (res.total === 0) {
                html = `<div class="d-flex align-items-center gap-2">
                            <i data-feather="info" style="width:15px;height:15px;color:${iconColor};"></i>
                            <strong>Tidak ada DN dengan status "${escHtml(statusLabel)}" saat ini.</strong>
                        </div>`;
            } else {
                html = `<div class="d-flex align-items-center gap-2 mb-2">
                            <i data-feather="archive" style="width:15px;height:15px;color:${iconColor};"></i>
                            <strong>DN Status "${escHtml(statusLabel)}"</strong>
                            <span class="badge" style="background:${badgeBg};color:${badgeColor};font-size:.72rem;">
                                Total: ${res.total}
                            </span>
                        </div>
                        ${buildDnStatusTable(res.data)}`;
            }
        // ── DN CANCELLED ──────────────────────────────────────────────────────
        } else if (intentId === 'dn_cancelled') {
            const statusLabel = statusParam || 'Cancelled';
            const iconColor   = '#dc2626';
            const badgeBg     = '#fee2e2';
            const badgeColor  = '#7f1d1d';
            if (res.total === 0) {
                html = `<div class="d-flex align-items-center gap-2">
                            <i data-feather="info" style="width:15px;height:15px;color:${iconColor};"></i>
                            <strong>Tidak ada DN dengan status "${escHtml(statusLabel)}" saat ini.</strong>
                        </div>`;
            } else {
                html = `<div class="d-flex align-items-center gap-2 mb-2">
                            <i data-feather="x-circle" style="width:15px;height:15px;color:${iconColor};"></i>
                            <strong>DN Status "${escHtml(statusLabel)}"</strong>
                            <span class="badge" style="background:${badgeBg};color:${badgeColor};font-size:.72rem;">
                                Total: ${res.total}
                            </span>
                        </div>
                        ${buildDnStatusTable(res.data)}`;
            }
        // ── DN BACK TO POOL ───────────────────────────────────────────────────
        } else if (intentId === 'dn_back_to_pool') {
            const statusLabel = statusParam || 'Back To Pool';
            const iconColor   = '#d97706';
            const badgeBg     = '#fef3c7';
            const badgeColor  = '#78350f';
            if (res.total === 0) {
                html = `<div class="d-flex align-items-center gap-2">
                            <i data-feather="info" style="width:15px;height:15px;color:${iconColor};"></i>
                            <strong>Tidak ada DN dengan status "${escHtml(statusLabel)}" saat ini.</strong>
                        </div>`;
            } else {
                html = `<div class="d-flex align-items-center gap-2 mb-2">
                            <i data-feather="refresh-ccw" style="width:15px;height:15px;color:${iconColor};"></i>
                            <strong>DN Status "${escHtml(statusLabel)}"</strong>
                            <span class="badge" style="background:${badgeBg};color:${badgeColor};font-size:.72rem;">
                                Total: ${res.total}
                            </span>
                        </div>
                        ${buildDnStatusTable(res.data)}`;
            }
        }

        html += `<div class="text-muted mt-3" style="font-size:.72rem;">
                    🕐 Data diperbarui: ${timestamp}
                 </div>`;
        appendBotBubble(html, true);
        feather.replace();
    }
    // =========================================================================
    // Fallback
    // =========================================================================
    function buildFallbackHTML(text) {
        return `Maaf, saya belum mengerti pertanyaan "<em>${escHtml(text)}</em>".<br><br>
                Anda bisa mencoba:
                <div class="mt-2 d-flex flex-wrap gap-2">
                    <span class="suggestion-chip">driver standby</span>
                    <span class="suggestion-chip">mover standby</span>
                    <span class="suggestion-chip">driver on delivery</span>
                    <span class="suggestion-chip">driver onsite</span>
                    <span class="suggestion-chip">total driver</span>
                    <span class="suggestion-chip">Arnold berapa dn bulan ini</span>
                    <span class="suggestion-chip">berapa dn pool mover</span>
                    <span class="suggestion-chip">berapa dn cancelled</span>
                    <span class="suggestion-chip">berapa dn btp</span>
                </div>`;
    }
    // =========================================================================
    // Bubble helpers
    // =========================================================================
    function appendUserBubble(text) {
        const escaped = escHtml(text).replace(/\n/g, '<br>');
        $chatBody.append(`
            <div class="ta-msg-row-user">
                <div>
                    <div class="ta-bubble-user">${escaped}</div>
                    <div class="ta-timestamp-right">${getTimeNow()}</div>
                </div>
            </div>`);
        scrollBottom();
    }
    function appendBotBubble(content, isHTML = false) {
        const inner = isHTML ? content : escHtml(content);
        $chatBody.append(`
            <div class="ta-msg-row">
                <div class="ta-avatar-sm">
                    <i data-feather="cpu" style="width:16px;height:16px;color:#fff;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="ta-bubble-bot">${inner}</div>
                    <div class="ta-timestamp">Apps Assistant · ${getTimeNow()}</div>
                </div>
            </div>`);
        scrollBottom();
    }
    function showTyping()   { $typing.show();  scrollBottom(); }
    function hideTyping()   { $typing.hide(); }
    function scrollBottom() { $chatBody.animate({ scrollTop: $chatBody[0].scrollHeight }, 250); }
    // =========================================================================
    // Utilities
    // =========================================================================
    function escHtml(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function getInitials(name) {
        if (!name) return '?';
        const parts = name.trim().split(/\s+/);
        return parts.length === 1
            ? parts[0].charAt(0).toUpperCase()
            : (parts[0].charAt(0) + parts[parts.length-1].charAt(0)).toUpperCase();
    }
    function getTimeNow() {
        return new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
    }
    function getStatusBadge(status) {
        const map = {
            standby     : ['#d1fae5','#065f46','Standby'],
            on_delivery : ['#fef3c7','#92400e','On Delivery'],
            onsite      : ['#ede9fe','#5b21b6','Onsite'],
        };
        const s = (status||'standby').toLowerCase().replace(/\s+/g,'_');
        const [bg, color, label] = map[s] || ['#f1f5f9','#475569', status||'Unknown'];
        return `<span class="badge" style="background:${bg};color:${color};font-size:.72rem;">${label}</span>`;
    }
    // =========================================================================
    // Events
    // =========================================================================
    $btnSend.on('click', () => sendMessage($input.val()));
    $input.on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage($input.val()); }
    });
    $chatBody.on('click', '.suggestion-chip', function() {
        const q = $(this).text(); $input.val(q); sendMessage(q);
    });
    $(document).on('click', '.suggestion-card', function() {
        const q = $(this).data('query');
        if (q) { $input.val(q); sendMessage(q); }
    });
    $('#btnClearChat').on('click', function() {
        $chatBody.find('> div').not('#welcomeMsg').remove();
    });
    $input.focus();
}
// =============================================================================
// Global functions (dipanggil dari onclick HTML)
// =============================================================================
function handleCopyDriver(btn) {
    const text = decodeURIComponent(btn.getAttribute('data-copy'));
    navigator.clipboard.writeText(text).then(() => {
        btn.innerHTML = '<i data-feather="check" style="width:13px;height:13px;color:#0d9e6e;"></i>';
        btn.style.color = '#0d9e6e';
        feather.replace();
        setTimeout(() => {
            btn.innerHTML = '<i data-feather="copy" style="width:13px;height:13px;"></i>';
            btn.style.color = '';
            feather.replace();
        }, 1500);
    }).catch(() => alert('Gagal copy. Pastikan browser mengizinkan akses clipboard.'));
}
function handleCopyAll(btn) {
    const text = decodeURIComponent(btn.getAttribute('data-copy'));
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML    = '<i data-feather="check" style="width:13px;height:13px;"></i> Tersalin!';
        btn.style.background = '#d1fae5';
        btn.style.color      = '#065f46';
        feather.replace();
        setTimeout(() => {
            btn.innerHTML        = orig;
            btn.style.background = '';
            btn.style.color      = '';
            feather.replace();
        }, 2000);
    }).catch(() => alert('Gagal copy. Pastikan browser mengizinkan akses clipboard.'));
}
function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}