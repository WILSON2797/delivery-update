<?php
include '../php/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mot_code = trim($_POST['mot_code']);
    $mot_description = trim($_POST['mot_description']);
    $created_by  = $_SESSION['nama']; // atau user_id

    try {

        // Cek apakah origin_code sudah ada
        $check_stmt = $conn->prepare(
            "SELECT COUNT(*) FROM master_mode_of_transport WHERE mot_code = ?"
        );
        $check_stmt->bind_param("s", $mot_code);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            throw new Exception("$mot_code sudah terdaftar!");
        }

        // Insert ke master_origin
        $sql = "INSERT INTO master_mode_of_transport (mot_code, mot_description, created_by) 
                VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $mot_code, $mot_description, $created_by);
        $stmt->execute();
        $stmt->close();

        echo json_encode([
            'status'  => 'success',
            'message' => 'MOT berhasil ditambahkan!'
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
