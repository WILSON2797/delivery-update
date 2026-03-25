<?php
include '../php/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama       = trim($_POST['nama'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $nopol      = trim($_POST['nopol'] ?? '');
    $created_by = $_SESSION['nama'] ?? 'system';

    // Validasi field kosong
    if (empty($nama) || empty($phone) || empty($nopol)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Semua field wajib diisi!'
        ]);
        exit;
    }

    try {

        // Cek apakah nama driver sudah ada
        $check_stmt = $conn->prepare(
            "SELECT COUNT(*) FROM data_driver WHERE nama = ?"
        );
        $check_stmt->bind_param("s", $nama);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            throw new Exception("Driver '$nama' sudah terdaftar!");
        }

        // Insert ke data_driver
        $sql = "INSERT INTO data_driver (nama, phone, nopol) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nama, $phone, $nopol);
        $stmt->execute();
        $stmt->close();

        echo json_encode([
            'status'  => 'success',
            'message' => 'Driver berhasil ditambahkan!'
        ]);

    } catch (Exception $e) {

        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => $e->getMessage()
        ]);
    }

} else {

    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Method Not Allowed'
    ]);
}

$conn->close();