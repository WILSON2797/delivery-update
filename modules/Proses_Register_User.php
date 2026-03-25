<?php
session_start();
include '../php/config.php';

header('Content-Type: application/json');

// ===============================
// CEK LOGIN
// ===============================
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Anda harus login untuk mengakses halaman ini.'
    ]);
    exit();
}

try {

    // ===============================
    // AMBIL INPUT
    // ===============================
    $nama     = trim($_POST['nama'] ?? '');
    $region   = trim($_POST['region'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // ===============================
    // VALIDASI
    // ===============================
    if (
        empty($nama) ||
        empty($region) ||
        empty($username) ||
        empty($password)
    ) {
        throw new Exception('Nama, Region, Username, dan Password wajib diisi');
    }

    // ===============================
    // HASH PASSWORD
    // ===============================
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // ===============================
    // CEK DUPLIKAT USERNAME
    // ===============================
    $check = $conn->prepare("
        SELECT id FROM data_username 
        WHERE username = ?
    ");

    if (!$check) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        throw new Exception('Username sudah digunakan!');
    }
    $check->close();

    // ===============================
    // INSERT USER BARU
    // ===============================
    $insert = $conn->prepare("
        INSERT INTO data_username 
        (nama, region, username, password) 
        VALUES (?, ?, ?, ?)
    ");

    if (!$insert) {
        throw new Exception('Prepare insert failed: ' . $conn->error);
    }

    $insert->bind_param(
        "ssss",
        $nama,
        $region,
        $username,
        $passwordHash
    );

    if (!$insert->execute()) {
        throw new Exception('Gagal menyimpan data: ' . $insert->error);
    }

    echo json_encode([
        'status'  => 'success',
        'message' => 'User berhasil didaftarkan'
    ]);

    $insert->close();

} catch (Exception $e) {

    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
