<?php
session_start();
include '../php/config.php'; // Sesuaikan dengan path file koneksi database Anda

// Set header keamanan
header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User tidak terautentikasi'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

// Tampilkan semua data KECUALI yang statusnya 'Handover Done'
    // Tampilkan semua data KECUALI yang statusnya 'Handover Done Dan Back To Pool'
   $query = "
    SELECT 
        id,
        date_request,
        driver_name,
        phone,
        nopol,
        dn_number,
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
       OR status != ?
    ORDER BY date_request ASC, id DESC
";
    
    $stmt = $conn->prepare($query);
if (!$stmt) {
    throw new Exception('Prepare failed: ' . $conn->error);
}

$excludeStatus = 'Back To Pool';
$stmt->bind_param("s", $excludeStatus);
    
   

// Eksekusi query
$stmt->execute();
$result = $stmt->get_result();

// Siapkan array untuk menyimpan data
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Tutup statement dan koneksi database
$stmt->close();
$conn->close();

// Kirim data sebagai JSON
echo json_encode(['status' => 'success', 'data' => $data], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>