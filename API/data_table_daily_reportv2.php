<?php
session_start();
include '../php/config.php'; // sesuaikan path koneksi

header('Content-Type: application/json');

// ===============================
// CEK LOGIN
// ===============================
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'User tidak terautentikasi'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

try {

    // ===============================
    // QUERY: EXCLUDE HANDOVER DONE
    // ===============================
    $query = "SELECT * FROM daily_report WHERE status <> ?";
    $stmt  = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $excludeStatus = 'Handover Done';
    $stmt->bind_param("s", $excludeStatus);

    // ===============================
    // EXECUTE
    // ===============================
    $stmt->execute();
    $result = $stmt->get_result();

    // ===============================
    // FETCH DATA
    // ===============================
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // ===============================
    // RESPONSE
    // ===============================
    echo json_encode([
        'status' => 'success',
        'data'   => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    $stmt->close();

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

}

$conn->close();
