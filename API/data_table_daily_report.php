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

    
    // Tampilkan semua data KECUALI yang statusnya 'Handover Done , Cancelled , Back To Pool & Back To WH'
   $query = "
    SELECT 
        id,
        date_request,
        dn_number,
        driver_name,
        phone,
        site_id,
        sub_project,
        plan_from,
        destination_city,
        destination_province,
        subcon,
        mot,
        status,
        latest_status,
        updated_at
    FROM daily_report
    WHERE status IS NULL
       OR status NOT IN (?, ?, ?, ?)
    ORDER BY updated_at DESC, id DESC
";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $excludeStatus1 = 'Handover Done';
    $excludeStatus2 = 'Back To Pool';
    $excludeStatus3 = 'Cancelled';
    $excludeStatus4 = 'Back To WH';
    $stmt->bind_param("ssss", $excludeStatus1, $excludeStatus2, $excludeStatus3, $excludeStatus4);

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
        'data'   => $data,
        'total'  => count($data)
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
?>