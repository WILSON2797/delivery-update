<?php
session_start();

// Jika sudah login, tolak akses
if (isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sudah login']);
    exit;
}

include 'config.php';

// Hanya tampilkan error di mode development
// Ganti false → true jika sedang development lokal
define('IS_DEV', false);
if (IS_DEV) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ─── CSRF Protection ────────────────────────────────────────────────────────
// Pastikan CSRF token dikirim dan cocok
if (
    !isset($_POST['csrf_token']) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

// ─── Rate Limiting (simple, session-based) ──────────────────────────────────
// Maksimal 5 percobaan dalam 5 menit
$maxAttempts  = 5;
$lockDuration = 300; // detik (5 menit)
$now          = time();

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts']  = 0;
    $_SESSION['login_first_try'] = $now;
}

// Reset counter jika window sudah lewat
if (($now - $_SESSION['login_first_try']) > $lockDuration) {
    $_SESSION['login_attempts']  = 0;
    $_SESSION['login_first_try'] = $now;
}

if ($_SESSION['login_attempts'] >= $maxAttempts) {
    $sisaDetik = $lockDuration - ($now - $_SESSION['login_first_try']);
    $sisaMenit = ceil($sisaDetik / 60);
    http_response_code(429);
    echo json_encode([
        'status'  => 'error',
        'message' => "Terlalu banyak percobaan login. Coba lagi dalam {$sisaMenit} menit."
    ]);
    exit;
}

// ─── Validasi Method ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
    exit;
}

// ─── Ambil & Sanitasi Input ─────────────────────────────────────────────────
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    echo json_encode(['status' => 'error', 'message' => 'Username dan password wajib diisi']);
    exit;
}

// Batasi panjang input untuk mencegah abuse
if (strlen($username) > 100 || strlen($password) > 200) {
    echo json_encode(['status' => 'error', 'message' => 'Input tidak valid']);
    exit;
}

// ─── Cek Database ────────────────────────────────────────────────────────────
try {
    $stmt = $conn->prepare("
        SELECT nama, username, password, role, profile_picture
        FROM data_username
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row    = $result->fetch_assoc();

    // Selalu jalankan password_verify meski user tidak ada
    // → mencegah timing attack (attacker mengukur waktu respons)
    $dummyHash    = '$2y$10$invalidhashfortimingprotectiononly00000000000000000000';
    $passwordHash = $row ? $row['password'] : $dummyHash;
    $isValid      = $row && password_verify($password, $passwordHash);

    if ($isValid) {
        // Reset counter percobaan
        $_SESSION['login_attempts']  = 0;
        $_SESSION['login_first_try'] = $now;

        // ── Session Fixation Protection ──────────────────────────────────
        // Regenerate session ID setelah login berhasil
        session_regenerate_id(true);

        $_SESSION['nama']            = $row['nama'];
        $_SESSION['username']        = $row['username'];
        $_SESSION['role']            = $row['role'];
        $_SESSION['profile_picture'] = $row['profile_picture'] ?? '';
        $_SESSION['login_time']      = $now;

        error_log("Login berhasil - Username: {$row['username']} | Role: {$row['role']}");

        // Buat CSRF token baru setelah login
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        echo json_encode([
            'status'  => 'success',
            'message' => 'Login berhasil',
            'role'    => $row['role'],
        ]);

    } else {
        // Catat percobaan gagal
        $_SESSION['login_attempts']++;

        error_log("Login gagal - Username: $username | IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

        // Pesan generik → tidak memberi tahu apakah username atau password yang salah
        echo json_encode([
            'status'  => 'error',
            'message' => 'Username atau password salah'
        ]);
    }

} catch (Exception $e) {
    error_log("Error login: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan server']);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}