<?php
include '../php/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code = trim($_POST['code']);
    $created_by  = $_SESSION['nama']; // atau user_id

    try {

        // Cek apakah origin_code sudah ada
        $check_stmt = $conn->prepare(
            "SELECT COUNT(*) FROM status_delivery WHERE code = ?"
        );
        $check_stmt->bind_param("s", $code);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            throw new Exception("$code sudah terdaftar!");
        }

        // Insert ke master_origin
        $sql = "INSERT INTO status_delivery (code, created_by) 
                VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $code, $created_by);
        $stmt->execute();
        $stmt->close();

        echo json_encode([
            'status'  => 'success',
            'message' => 'Origin berhasil ditambahkan!'
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
