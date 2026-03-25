<?php
session_start(); // Mulai sesi
// Generate CSRF token jika belum ada
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if (isset($_SESSION["username"])) {
    // Jika pengguna sudah login, arahkan ke NewTask.php
    header('Location: /pages/dashboard');
    exit();
}

// Fungsi untuk mendapatkan URL dengan cache busting
function getAssetUrl($filePath) {
    $fullPath = __DIR__ . '/' . $filePath;
    if (file_exists($fullPath)) {
        $mtime = filemtime($fullPath);
        return $filePath . '?v=' . $mtime;
    }
    return $filePath; // Fallback jika file tidak ditemukan
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Meta tags untuk kontrol cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>Delivery Management</title>
    <link rel="stylesheet" href="<?php echo getAssetUrl('assets/css/login.css'); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/toastr-custom.css?v=<?php echo getAssetUrl('css/toastr-custom.css'); ?>">
    
</head>
<body>
    <div class="light"></div>
    <div class="light"></div>
    <div class="light"></div>

    <div class="container" id="container">
        <!-- Form Login -->
        <div class="form-container sign-in-container">
            <form id="loginForm">
            <h1>Delivery Management</h1>
                <!-- <div class="social-container">
                    <a href="#"><span>Apps</span></a>
                    <a href="#"><span>G</span></a>
                    <a href="#"><span>in</span></a>
                </div> -->
                <p>Silahkan Login</p>
                <div class="form-group has-icon">
                    <span class="input-icon input-icon-left">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <input type="text" id="login-username" name="username" placeholder=" " required>
                    <!--<label for="login-username">Username</label>-->
                </div>
                <div class="form-group has-icon has-icon-right">
                    <span class="input-icon input-icon-left">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <input type="password" id="login-password" name="password" placeholder=" " required>
                    <!--<label for="login-password">Password</label>-->
                    <button type="button" class="toggle-password" id="togglePassword" tabindex="-1" aria-label="Toggle password visibility">
                        <!-- Eye icon (visible when password is hidden) -->
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        <!-- Eye-off icon (visible when password is shown) -->
                        <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                <div class="forgot-password">
                    <a href="#">Lupa password?</a>
                </div>
                <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="<?php echo getAssetUrl('assets/js/toastr-init.js'); ?>"></script>
    <script src="<?php echo getAssetUrl('assets/js/auth.js'); ?>"></script>
    <script src="<?php echo getAssetUrl('assets/js/login.js'); ?>"></script>
</body>
</html>