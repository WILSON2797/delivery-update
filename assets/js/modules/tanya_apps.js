/**
 * tanya_apps.js  —  v2.0
 * Groq AI dengan akses database langsung (Tool / Function Calling)
 * Dibuat oleh Wilson Gurning
 */
function initPageScripts() {
    console.log("tanya_apps.js v2.0 loaded (Groq + DB Tool Calling)");
    feather.replace();

    let isProcessing = false;
    let chatHistory  = []; // konteks percakapan untuk Groq

    const $chatBody = $('#chatBody');
    const $input    = $('#chatInput');
    const $btnSend  = $('#btnSend');
    const $typing   = $('#typingIndicator');

    // =========================================================================
    // Kirim pesan ke groq_proxy.php
    // =========================================================================
    function askGroq(userText, onSuccess, onError) {
        $.ajax({
            url: 'API/groq_proxy',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ message: userText, history: chatHistory }),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    chatHistory.push({ role: 'user',      content: userText  });
                    chatHistory.push({ role: 'assistant', content: res.reply });
                    if (chatHistory.length > 20) chatHistory = chatHistory.slice(-20);
                    onSuccess(res);
                } else {
                    onError(res.message || 'Groq error');
                }
            },
            error: function(xhr) {
                onError(xhr.responseJSON?.message || 'Gagal terhubung ke server');
            }
        });
    }

    // =========================================================================
    // Format teks Groq → HTML
    // =========================================================================
    function formatGroqText(text) {
        return escHtml(text)
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g,     '<em>$1</em>')
            .replace(/\n/g,            '<br>');
    }

    // =========================================================================
    // Build driver card
    // =========================================================================
    function buildDriverCard(d, badgeHtml, avatarStyle) {
        const plainText = `Name  : ${d.driver_name}\nPhone : ${d.phone || '-'}\nNopol : ${d.nopol || '-'}`;
        const encoded   = encodeURIComponent(plainText);
        return `
            <div class="driver-card">
                <div class="driver-avatar" style="${avatarStyle}">${getInitials(d.driver_name)}</div>
                <div class="flex-grow-1">
                    <div class="fw-semibold" style="font-size:.88rem;">${escHtml(d.driver_name)}</div>
                    <div class="text-muted" style="font-size:.75rem;">
                        📞 ${escHtml(d.phone || '-')}
                        ${d.nopol       ? `&nbsp;·&nbsp; 🚛 ${escHtml(d.nopol)}`       : ''}
                        ${d.destination ? `&nbsp;·&nbsp; 📍 ${escHtml(d.destination)}` : ''}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    ${badgeHtml}
                    <button class="btn-copy-single" title="Copy" data-copy="${encoded}" onclick="handleCopyDriver(this)">
                        <i data-feather="copy" style="width:13px;height:13px;"></i>
                    </button>
                </div>
            </div>`;
    }

    function buildCopyAllBtn(drivers, label) {
        const all     = drivers.map(d => `Name  : ${d.driver_name}\nPhone : ${d.phone||'-'}\nNopol : ${d.nopol||'-'}`).join('\n\n');
        const encoded = encodeURIComponent(all);
        return `<button class="btn-copy-all mb-2" data-copy="${encoded}" onclick="handleCopyAll(this)">
                    <i data-feather="copy" style="width:13px;height:13px;"></i>
                    Copy Semua (${drivers.length} ${label})
                </button>`;
    }

    // =========================================================================
    // Build trip table
    // =========================================================================
    function buildTripTable(detail) {
        if (!detail || detail.length === 0) return '';
        const rows = detail.map(r => `
            <tr>
                <td>${escHtml(r.dn_number||'-')}</td>
                <td>${escHtml(r.date_request||'-')}</td>
                <td>${escHtml(r.destination_city||'-')}</td>
                <td><span class="ta-status-badge">${escHtml(r.status||'-')}</span></td>
            </tr>`).join('');
        return `<div class="ta-trip-table-wrap">
                    <table class="ta-trip-table">
                        <thead><tr><th>DN Number</th><th>Tanggal</th><th>Tujuan</th><th>Status</th></tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;
    }

    // =========================================================================
    // Build DN status table
    // =========================================================================
    function buildDnStatusTable(data) {
        if (!data || data.length === 0) return '';
        const rows = data.map((r,i) => `
            <tr>
                <td style="text-align:center;color:#94a3b8;">${i+1}</td>
                <td><strong>${escHtml(r.dn_number||'-')}</strong></td>
                <td>${escHtml(r.site_id||'-')}</td>
            </tr>`).join('');
        return `<div class="ta-trip-table-wrap">
                    <table class="ta-trip-table">
                        <thead><tr><th style="width:40px;">#</th><th>DN Number</th><th>Site ID</th></tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;
    }

    // =========================================================================
    // Render DB result (kartu/tabel) berdasarkan intent_id
    // =========================================================================
    function renderDbResult(dbResult) {
        if (!dbResult || !dbResult.success) return '';
        const intentId  = dbResult.intent_id || '';
        const colors    = ['#1a73e8','#0d9e6e','#d97706','#7c3aed','#dc2626','#0891b2'];
        let html        = '';
        const timestamp = new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});

        // ── TOTAL DN (statistik) ──────────────────────────────────────────────
        if (intentId === 'total_dn') {
            const d = dbResult.data || {};
            const perStatus = d.per_status || [];
            const statusColors = {
                'On Delivery' : ['#fef3c7','#92400e'],
                'Onsite'      : ['#ede9fe','#5b21b6'],
                'Standby'     : ['#d1fae5','#065f46'],
                'Pool mover'  : ['#e0f2fe','#0c4a6e'],
                'Cancelled'   : ['#fee2e2','#7f1d1d'],
                'Back To Pool': ['#fef3c7','#78350f'],
            };
            const rows = perStatus.map(s => {
                const [bg, tc] = statusColors[s.status] || ['#f1f5f9','#475569'];
                return `<tr>
                    <td><span class="badge" style="background:${bg};color:${tc};font-size:.75rem;">${escHtml(s.status||'-')}</span></td>
                    <td style="text-align:right;font-weight:700;color:#1e293b;">${s.jumlah}</td>
                </tr>`;
            }).join('');

            html = `
                <div class="d-flex align-items-center gap-2 mt-2 mb-3">
                    <i data-feather="file-text" style="width:15px;height:15px;color:#1a73e8;"></i>
                    <strong>Statistik DN — ${escHtml(d.period||'')}</strong>
                </div>
                <div class="d-flex gap-3 mb-3 flex-wrap">
                    <div class="ta-stat-card" style="background:#dbeafe;">
                        <div style="font-size:1.8rem;font-weight:800;color:#1a73e8;line-height:1;">${d.total||0}</div>
                        <div style="font-size:.72rem;color:#1e40af;">Total Semua DN</div>
                    </div>
                    <div class="ta-stat-card" style="background:#d1fae5;">
                        <div style="font-size:1.8rem;font-weight:800;color:#0d9e6e;line-height:1;">${d.bulan_ini||0}</div>
                        <div style="font-size:.72rem;color:#065f46;">Bulan Ini</div>
                    </div>
                    <div class="ta-stat-card" style="background:#fef3c7;">
                        <div style="font-size:1.8rem;font-weight:800;color:#d97706;line-height:1;">${d.hari_ini||0}</div>
                        <div style="font-size:.72rem;color:#92400e;">Hari Ini</div>
                    </div>
                </div>
                ${rows ? `
                <div class="ta-trip-table-wrap">
                    <table class="ta-trip-table">
                        <thead><tr><th>Status</th><th style="text-align:right;">Jumlah DN</th></tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>` : ''}`;

        // ── STANDBY ──────────────────────────────────────────────────────────
        } else if (intentId === 'driver_standby') {
            const drivers = dbResult.data || [];
            if (drivers.length === 0) {
                html = `<div class="d-flex align-items-center gap-2 mt-2">
                            <i data-feather="info" style="width:15px;height:15px;color:#f59e0b;"></i>
                            <span>Semua driver sedang On Delivery atau Onsite.</span>
                        </div>`;
            } else {
                html = `<div class="d-flex align-items-center gap-2 mt-2 mb-1">
                            <i data-feather="user-check" style="width:15px;height:15px;color:#0d9e6e;"></i>
                            <strong>Driver Standby (${drivers.length} orang)</strong>
                        </div>`;
                html += buildCopyAllBtn(drivers,'driver');
                drivers.forEach((d,i) => {
                    const bg = colors[i%colors.length];
                    html += buildDriverCard(d,`<span class="badge" style="background:#d1fae5;color:#065f46;font-size:.72rem;">Standby</span>`,`background:${bg}20;color:${bg};`);
                });
            }

        // ── ON DELIVERY ───────────────────────────────────────────────────────
        } else if (intentId === 'driver_on_delivery') {
            const drivers = dbResult.data || [];
            if (drivers.length === 0) {
                html = `<div class="d-flex align-items-center gap-2 mt-2"><i data-feather="info" style="width:15px;height:15px;"></i><span> Tidak ada driver On Delivery.</span></div>`;
            } else {
                html = `<div class="d-flex align-items-center gap-2 mt-2 mb-1">
                            <i data-feather="navigation" style="width:15px;height:15px;color:#d97706;"></i>
                            <strong>Driver On Delivery (${drivers.length} orang)</strong>
                        </div>`;
                html += buildCopyAllBtn(drivers,'driver');
                drivers.forEach(d => html += buildDriverCard(d,`<span class="badge" style="background:#fef3c7;color:#92400e;font-size:.72rem;">On Delivery</span>`,`background:#fef3c720;color:#d97706;`));
            }

        // ── ONSITE ────────────────────────────────────────────────────────────
        } else if (intentId === 'driver_onsite') {
            const drivers = dbResult.data || [];
            if (drivers.length === 0) {
                html = `<div class="d-flex align-items-center gap-2 mt-2"><i data-feather="info" style="width:15px;height:15px;"></i><span> Tidak ada driver Onsite.</span></div>`;
            } else {
                html = `<div class="d-flex align-items-center gap-2 mt-2 mb-1">
                            <i data-feather="map-pin" style="width:15px;height:15px;color:#7c3aed;"></i>
                            <strong>Driver Onsite / Waiting HO (${drivers.length} orang)</strong>
                        </div>`;
                html += buildCopyAllBtn(drivers,'driver');
                drivers.forEach(d => html += buildDriverCard(d,`<span class="badge" style="background:#ede9fe;color:#5b21b6;font-size:.72rem;">Onsite</span>`,`background:#ede9fe;color:#7c3aed;`));
            }

        // ── SEMUA DRIVER ──────────────────────────────────────────────────────
        } else if (intentId === 'total_driver') {
            const drivers = dbResult.data || [];
            html = `<div class="d-flex align-items-center gap-2 mt-2 mb-1">
                        <i data-feather="users" style="width:15px;height:15px;color:#1a73e8;"></i>
                        <strong>Semua Driver Terdaftar (${drivers.length} orang)</strong>
                    </div>`;
            html += buildCopyAllBtn(drivers,'driver');
            drivers.forEach((d,i) => {
                const bg = colors[i%colors.length];
                html += buildDriverCard(d, getStatusBadge(d.current_status), `background:${bg}20;color:${bg};`);
            });

        // ── TRIP COUNT ────────────────────────────────────────────────────────
        } else if (intentId === 'driver_trip_count') {
            if (!dbResult.found) {
                html = `<div class="d-flex align-items-center gap-2 mt-2">
                            <i data-feather="alert-circle" style="width:15px;height:15px;color:#f59e0b;"></i>
                            <span>Driver tidak ditemukan di database.</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.8rem;">Pastikan nama sesuai. Contoh: <em>Arnold</em>, <em>Budi</em></div>`;
            } else {
                const d   = dbResult.data;
                const clr = d.total > 0 ? '#0d9e6e' : '#94a3b8';
                html = `
                    <div class="ta-summary-card mt-2">
                        <div class="driver-avatar" style="background:#dbeafe;color:#1a73e8;width:48px;height:48px;font-size:1.1rem;">${getInitials(d.driver_name)}</div>
                        <div class="flex-grow-1">
                            <div class="fw-bold" style="font-size:.95rem;">${escHtml(d.driver_name)}</div>
                            <div class="text-muted" style="font-size:.76rem;">📅 ${escHtml(d.period)}</div>
                            <div class="text-muted" style="font-size:.76rem;">📞 ${escHtml(d.phone||'-')} &nbsp;·&nbsp; 🚛 ${escHtml(d.nopol||'-')}</div>
                        </div>
                        <div class="text-end">
                            <div style="font-size:2rem;font-weight:800;color:${clr};line-height:1;">${d.total}</div>
                            <div class="text-muted" style="font-size:.72rem;">DN / Trip</div>
                        </div>
                    </div>
                    ${d.total > 0
                        ? `<div class="text-muted mb-1 mt-2" style="font-size:.78rem;">Detail ${d.total} pengiriman bulan ini:</div>${buildTripTable(d.detail)}`
                        : `<div class="text-muted mt-2" style="font-size:.82rem;">Tidak ada pengiriman tercatat bulan ini.</div>`
                    }`;
            }

        // ── DN STATUS ─────────────────────────────────────────────────────────
        } else if (['dn_pool_mover','dn_cancelled','dn_back_to_pool'].includes(intentId)) {
            const styleMap = {
                dn_pool_mover   : { icon:'archive',     color:'#0891b2', bg:'#e0f2fe', tc:'#0c4a6e' },
                dn_cancelled    : { icon:'x-circle',    color:'#dc2626', bg:'#fee2e2', tc:'#7f1d1d' },
                dn_back_to_pool : { icon:'refresh-ccw', color:'#d97706', bg:'#fef3c7', tc:'#78350f' }
            };
            const st = styleMap[intentId];
            const statusLabel = dbResult.status || '';
            if ((dbResult.total||0) === 0) {
                html = `<div class="d-flex align-items-center gap-2 mt-2">
                            <i data-feather="info" style="width:15px;height:15px;color:${st.color};"></i>
                            <strong>Tidak ada DN dengan status "${escHtml(statusLabel)}".</strong>
                        </div>`;
            } else {
                html = `<div class="d-flex align-items-center gap-2 mt-2 mb-2">
                            <i data-feather="${st.icon}" style="width:15px;height:15px;color:${st.color};"></i>
                            <strong>DN Status "${escHtml(statusLabel)}"</strong>
                            <span class="badge" style="background:${st.bg};color:${st.tc};font-size:.72rem;">Total: ${dbResult.total}</span>
                        </div>
                        ${buildDnStatusTable(dbResult.data)}`;
            }
            
            // ── QUERY DAILY REPORT (filter fleksibel) ─────────────────────────────
        } else if (intentId === 'query_daily_report') {
            const total      = dbResult.total || 0;
            const filters    = dbResult.filter_desc || [];
            const perStatus  = dbResult.per_status  || [];
            const perCity    = dbResult.per_city    || [];
            const data       = dbResult.data        || [];

            const filterLabel = filters.length > 0
                ? filters.map(f => `<span class="badge" style="background:#e0f2fe;color:#0c4a6e;font-size:.72rem;">${escHtml(f)}</span>`).join(' ')
                : '<span class="badge" style="background:#f1f5f9;color:#475569;font-size:.72rem;">Semua Data</span>';

            const statusColors = {
                'On Delivery' : ['#fef3c7','#92400e'],
                'Onsite'      : ['#ede9fe','#5b21b6'],
                'Handover Done':['#d1fae5','#065f46'],
                'Pool mover'  : ['#e0f2fe','#0c4a6e'],
                'Cancelled'   : ['#fee2e2','#7f1d1d'],
                'Back To Pool': ['#fef3c7','#78350f'],
            };

            // Header + total count
            html = `
                <div class="d-flex align-items-center gap-2 mt-2 mb-1 flex-wrap">
                    <i data-feather="search" style="width:15px;height:15px;color:#1a73e8;"></i>
                    <strong>Hasil Pencarian DN</strong>
                    ${filterLabel}
                </div>
                <div class="ta-stat-card mb-3" style="background:#dbeafe;display:inline-flex;gap:12px;align-items:center;">
                    <div style="font-size:2rem;font-weight:800;color:#1a73e8;line-height:1;">${total}</div>
                    <div style="font-size:.75rem;color:#1e40af;">Total DN<br>ditemukan</div>
                </div>`;

            // Tabel rekap per status
            if (perStatus.length > 0) {
                const statusRows = perStatus.map(s => {
                    const [bg, tc] = statusColors[s.status] || ['#f1f5f9','#475569'];
                    return `<tr>
                        <td><span class="badge" style="background:${bg};color:${tc};font-size:.72rem;">${escHtml(s.status||'-')}</span></td>
                        <td style="text-align:right;font-weight:700;color:#1e293b;">${s.jumlah}</td>
                    </tr>`;
                }).join('');
                html += `<div class="text-muted mb-1" style="font-size:.78rem;">Rekap per Status:</div>
                         <div class="ta-trip-table-wrap mb-3">
                             <table class="ta-trip-table">
                                 <thead><tr><th>Status</th><th style="text-align:right;">Jumlah</th></tr></thead>
                                 <tbody>${statusRows}</tbody>
                             </table>
                         </div>`;
            }

            // Tabel rekap per kota (jika ada)
            if (perCity.length > 0) {
                const cityRows = perCity.map(c => `<tr>
                    <td>${escHtml(c.destination_city||'-')}</td>
                    <td style="text-align:right;font-weight:700;color:#1e293b;">${c.jumlah}</td>
                </tr>`).join('');
                html += `<div class="text-muted mb-1" style="font-size:.78rem;">Rekap per Kota (Top 10):</div>
                         <div class="ta-trip-table-wrap mb-3">
                             <table class="ta-trip-table">
                                 <thead><tr><th>Kota Tujuan</th><th style="text-align:right;">Jumlah</th></tr></thead>
                                 <tbody>${cityRows}</tbody>
                             </table>
                         </div>`;
            }

            // Tabel detail DN (max 20)
            if (data.length > 0) {
                const detailRows = data.map(r => {
                    const [bg, tc] = statusColors[r.status] || ['#f1f5f9','#475569'];
                    return `<tr>
                        <td><strong>${escHtml(r.dn_number||'-')}</strong></td>
                        <td>${escHtml(r.date_request||'-')}</td>
                        <td>${escHtml(r.destination_city||'-')}</td>
                        <td>${escHtml(r.driver_name||'-')}</td>
                        <td><span class="badge" style="background:${bg};color:${tc};font-size:.68rem;">${escHtml(r.status||'-')}</span></td>
                    </tr>`;
                }).join('');
                const moreNote = total > 20 ? `<div class="text-muted mt-1" style="font-size:.72rem;">Menampilkan 20 dari ${total} DN. Gunakan filter lebih spesifik untuk hasil lebih sempit.</div>` : '';
                html += `<div class="text-muted mb-1" style="font-size:.78rem;">Detail DN (maks. 20 data terbaru):</div>
                         <div class="ta-trip-table-wrap">
                             <table class="ta-trip-table">
                                 <thead><tr><th>DN Number</th><th>Tanggal</th><th>Kota</th><th>Driver</th><th>Status</th></tr></thead>
                                 <tbody>${detailRows}</tbody>
                             </table>
                         </div>${moreNote}`;
            } else if (total === 0) {
                html += `<div class="text-muted mt-1" style="font-size:.85rem;">Tidak ada DN yang sesuai dengan filter tersebut.</div>`;
            }
        }

        if (html) {
            html += `<div class="text-muted mt-2" style="font-size:.72rem;">🕐 Data diperbarui: ${timestamp}</div>`;
        }
        return html;
    }

    // =========================================================================
    // Send message — semua lewat Groq
    // =========================================================================
    function sendMessage(text) {
        text = text.trim();
        if (!text || isProcessing) return;

        appendUserBubble(text);
        $input.val('').css('height', 'auto');
        isProcessing = true;
        $btnSend.prop('disabled', true);
        showTyping();

        askGroq(text,
            function(res) {
                hideTyping();

                if (res.type === 'db_with_comment') {
                    // Groq akses DB → tampilkan komentar AI + kartu/tabel data
                    const commentHtml = `<div class="groq-reply mb-2">${formatGroqText(res.reply)}</div>`;
                    const dataHtml    = renderDbResult(res.db_result);
                    const badgeHtml   = `<div class="ta-groq-badge mt-2">
                                            <i data-feather="database" style="width:10px;height:10px;"></i>
                                            Data dari database
                                         </div>`;
                    appendBotBubble(commentHtml + dataHtml + badgeHtml, true);

                } else {
                    // Groq jawab langsung (tanpa DB)
                    const replyHtml = `<div class="groq-reply">${formatGroqText(res.reply)}</div>
                                       <div class="ta-groq-badge mt-2">
                                           <i data-feather="zap" style="width:10px;height:10px;"></i>
                                           Dijawab oleh AI
                                       </div>`;
                    appendBotBubble(replyHtml, true);
                }

                isProcessing = false;
                $btnSend.prop('disabled', false);
                feather.replace();
            },
            function(errMsg) {
                hideTyping();
                // Fallback jika Groq gagal total
                appendBotBubble(buildFallbackHTML(text), true);
                console.warn('Groq error:', errMsg);
                isProcessing = false;
                $btnSend.prop('disabled', false);
                feather.replace();
            }
        );
    }

    // =========================================================================
    // Fallback
    // =========================================================================
    function buildFallbackHTML(text) {
        return `Maaf, saya tidak bisa memproses pertanyaan "<em>${escHtml(text)}</em>" saat ini. Silakan coba lagi.<br><br>
                <div class="mt-2 d-flex flex-wrap gap-2">
                    <span class="suggestion-chip">driver standby</span>
                    <span class="suggestion-chip">driver on delivery</span>
                    <span class="suggestion-chip">driver onsite</span>
                    <span class="suggestion-chip">total driver</span>
                    <span class="suggestion-chip">Arnold berapa dn bulan ini</span>
                    <span class="suggestion-chip">berapa dn cancelled</span>
                </div>`;
    }

    // =========================================================================
    // Bubble helpers
    // =========================================================================
    function appendUserBubble(text) {
        $chatBody.append(`
            <div class="ta-msg-row-user">
                <div>
                    <div class="ta-bubble-user">${escHtml(text).replace(/\n/g,'<br>')}</div>
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
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function getInitials(name) {
        if (!name) return '?';
        const p = name.trim().split(/\s+/);
        return p.length === 1 ? p[0][0].toUpperCase() : (p[0][0]+p[p.length-1][0]).toUpperCase();
    }
    function getTimeNow() {
        return new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
    }
    function getStatusBadge(status) {
        const map = {
            standby     : ['#d1fae5','#065f46','Standby'],
            on_delivery : ['#fef3c7','#92400e','On Delivery'],
            onsite      : ['#ede9fe','#5b21b6','Onsite']
        };
        const s = (status||'standby').toLowerCase().replace(/\s+/g,'_');
        const [bg,color,label] = map[s]||['#f1f5f9','#475569',status||'Unknown'];
        return `<span class="badge" style="background:${bg};color:${color};font-size:.72rem;">${label}</span>`;
    }

    // =========================================================================
    // Events
    // =========================================================================
    $btnSend.on('click', () => sendMessage($input.val()));
    $input.on('keydown', function(e) {
        if (e.key==='Enter' && !e.shiftKey) { e.preventDefault(); sendMessage($input.val()); }
    });
    $chatBody.on('click', '.suggestion-chip', function() {
        const q = $(this).text(); $input.val(q); sendMessage(q);
    });
    $(document).on('click', '.suggestion-card', function() {
        const q = $(this).data('query'); if (q) { $input.val(q); sendMessage(q); }
    });
    $('#btnClearChat').on('click', function() {
        $chatBody.find('> div').not('#welcomeMsg').remove();
        chatHistory = [];
    });
    $input.focus();
}

// =============================================================================
// Global copy functions
// =============================================================================
function handleCopyDriver(btn) {
    const text = decodeURIComponent(btn.getAttribute('data-copy'));
    navigator.clipboard.writeText(text).then(() => {
        btn.innerHTML = '<i data-feather="check" style="width:13px;height:13px;color:#0d9e6e;"></i>';
        btn.style.color = '#0d9e6e'; feather.replace();
        setTimeout(() => { btn.innerHTML='<i data-feather="copy" style="width:13px;height:13px;"></i>'; btn.style.color=''; feather.replace(); }, 1500);
    }).catch(()=>alert('Gagal copy.'));
}
function handleCopyAll(btn) {
    const text = decodeURIComponent(btn.getAttribute('data-copy'));
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML='<i data-feather="check" style="width:13px;height:13px;"></i> Tersalin!';
        btn.style.background='#d1fae5'; btn.style.color='#065f46'; feather.replace();
        setTimeout(() => { btn.innerHTML=orig; btn.style.background=''; btn.style.color=''; feather.replace(); }, 2000);
    }).catch(()=>alert('Gagal copy.'));
}
function autoResize(el) {
    el.style.height='auto';
    el.style.height=Math.min(el.scrollHeight,120)+'px';
}