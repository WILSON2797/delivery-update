<?php
include '../php/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $province = trim($_POST['province']);
    $city = trim($_POST['city']);
    $created_by  = $_SESSION['nama']; // atau user_id

    try {

        // Cek apakah origin_code sudah ada
        $check_stmt = $conn->prepare(
            "SELECT COUNT(*) FROM province_city WHERE city = ?"
        );
        $check_stmt->bind_param("s", $city);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            throw new Exception("Kota $city sudah terdaftar!");
        }

        // Insert ke master_origin
        $sql = "INSERT INTO province_city (province, city, created_by) 
                VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $province, $city, $created_by);
        $stmt->execute();
        $stmt->close();

        echo json_encode([
            'status'  => 'success',
            'message' => 'City berhasil ditambahkan!'
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
