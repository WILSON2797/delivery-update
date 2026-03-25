<?php
session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$_SESSION['last_activity'] = time();
date_default_timezone_set('Asia/Jakarta');
?>

<style>
/* ── Stat card (total DN) ──────────────────────────────────────────── */
.ta-stat-card {
    border-radius: 12px;
    padding: 10px 18px;
    text-align: center;
    min-width: 90px;
    flex: 1;
}

/* ── Groq AI Badge ─────────────────────────────────────────────────── */
.ta-groq-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: .70rem;
    color: #6366f1;
    background: #eef2ff;
    border: 1px solid #c7d2fe;
    border-radius: 20px;
    padding: 2px 8px;
    font-weight: 500;
}

/* ── Groq reply text ───────────────────────────────────────────────── */
.groq-reply {
    line-height: 1.65;
    font-size: .875rem;
}

/* ── Online badge ──────────────────────────────────────────────────── */
.ta-badge-online {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: .75rem;
    color: #0d9e6e;
    background: #d1fae5;
    border-radius: 20px;
    padding: 4px 12px;
    font-weight: 600;
}
</style>

<main>
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-xl px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="message-circle"></i></div>
                            Tanya Apps
                        </h1>
                    </div>
                    <div class="col-auto mb-3">
                        <span class="ta-badge-online">
                            <i data-feather="zap" style="width:13px;height:13px;"></i>
                            Smart Assistant · Groq AI
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid px-4 mt-2">

        <!-- Chat Window -->
        <div class="ta-card">

            <!-- Chat Header -->
            <div class="ta-chat-header">
                <div class="ta-avatar-lg">
                    <i data-feather="cpu" style="width:20px;height:20px;color:#1a73e8;"></i>
                </div>
                <div>
                    <div class="fw-bold text-white" style="font-size:1rem;">Apps Assistant</div>
                    <div class="text-white-50" style="font-size:0.78rem;">
                        <span class="me-1">●</span>Online · Powered by Groq AI
                    </div>
                </div>
                <div class="ms-auto">
                    <button class="btn btn-sm btn-outline-light" id="btnClearChat" title="Hapus percakapan">
                        <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Body -->
            <div id="chatBody" class="ta-chat-body">

                <!-- Welcome message -->
                <div class="ta-msg-row" id="welcomeMsg">
                    <div class="ta-avatar-sm">
                        <i data-feather="cpu" style="width:16px;height:16px;color:#fff;"></i>
                    </div>
                    <div>
                        <div class="ta-bubble-bot">
                            Halo! 👋  Saya <strong>Apps Assistant</strong>, dibuat oleh <strong>Wilson Gurning</strong>.<br>
                            Saya siap menjawab pertanyaan Anda — baik data real-time dari database maupun pertanyaan umum seputar aplikasi. Misalnya:
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                <span class="suggestion-chip">driver standby</span>
                                <span class="suggestion-chip">mover standby</span>
                                <span class="suggestion-chip">apa itu status onsite?</span>
                                <span class="suggestion-chip">siapa driver available?</span>
                            </div>
                        </div>
                        <div class="ta-timestamp">Apps Assistant</div>
                    </div>
                </div>

            </div>

            <!-- Typing Indicator -->
            <div id="typingIndicator" class="ta-typing-wrap" style="display:none;">
                <div class="d-flex align-items-center gap-2">
                    <div class="ta-avatar-xs">
                        <i data-feather="cpu" style="width:13px;height:13px;color:#fff;"></i>
                    </div>
                    <div class="typing-dots px-3 py-2 rounded-pill bg-white shadow-sm">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="ta-input-wrap">
                <div class="d-flex gap-2 align-items-end">
                    <div class="flex-grow-1">
                        <textarea id="chatInput"
                            class="ta-textarea"
                            placeholder="Tanya apa saja... (contoh: driver standby / apa itu BTP?)"
                            rows="1"
                            onInput="autoResize(this)"></textarea>
                    </div>
                    <button id="btnSend" class="ta-btn-send">
                        <i data-feather="send" style="width:18px;height:18px;"></i>
                    </button>
                </div>
                <div class="ta-input-hint">
                    Tekan <kbd>Enter</kbd> untuk kirim · <kbd>Shift+Enter</kbd> untuk baris baru
                </div>
            </div>
        </div>

        <!-- Quick Suggestion Cards -->
        <div class="row g-3 mt-2" style="max-width:1400px; margin-left:auto; margin-right:auto;">
            <div class="col-md-4">
                <div class="suggestion-card p-3 d-flex align-items-center gap-3"
                     data-query="driver standby">
                    <div class="ta-chip-icon bg-success-soft">
                        <i data-feather="user-check" style="width:18px;height:18px;color:#0d9e6e;"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="font-size:.85rem;">Driver Standby</div>
                        <div class="text-muted" style="font-size:.75rem;">Lihat driver yang available</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="suggestion-card p-3 d-flex align-items-center gap-3"
                     data-query="mover standby">
                    <div class="ta-chip-icon bg-primary-soft">
                        <i data-feather="truck" style="width:18px;height:18px;color:#1a73e8;"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="font-size:.85rem;">Mover Standby</div>
                        <div class="text-muted" style="font-size:.75rem;">Lihat mover yang available</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="suggestion-card p-3 d-flex align-items-center gap-3"
                     data-query="berapa driver on delivery sekarang">
                    <div class="ta-chip-icon bg-warning-soft">
                        <i data-feather="navigation" style="width:18px;height:18px;color:#d97706;"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="font-size:.85rem;">Driver On Delivery</div>
                        <div class="text-muted" style="font-size:.75rem;">Cek driver sedang bertugas</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>