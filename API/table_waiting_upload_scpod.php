<?php
// API/table_waiting_upload_scpod.php

ob_start(); // Tangkap output tak sengaja (warning, notice)
header('Content-Type: application/json');

// Koneksi DB - pastikan path benar!
require_once '../php/config.php';

// Cek koneksi
if (!isset($conn) || $conn->connect_error) {
    ob_end_clean();
    echo json_encode(['data' => [], 'error' => 'DB connection failed']);
    exit;
}

$query = "SELECT 
    `id`,
    `dn_number`,
    `sub_project`,
    `status`,
    `pod_date`,
    `type_shipment`,
    `mot`,
    `date_send_sc_pod`,
    `kpi_uploaded`
FROM `billing_details`
WHERE `date_send_sc_pod` IS NULL
  AND `pod_date` IS NOT NULL
ORDER BY `pod_date` DESC";

$result = $conn->query($query);

if (!$result) {
    ob_end_clean();
    echo json_encode(['data' => [], 'error' => 'Query error: ' . $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    // Field finansial tetap dalam format ANGKA MURNI (untuk editing di frontend)
    foreach (['unit_price', 'btp_bta', 'rooftop', '4wd', 'langsir', 'crane', 'charter_boat', 'total_amount'] as $field) {
        if (isset($row[$field]) && $row[$field] !== null) {
            $row[$field] = floatval($row[$field]);
        }
    }
    
    // Ubah NULL jadi string kosong untuk field non-angka agar aman di JavaScript
    foreach ($row as $key => $value) {
        if ($value === null && !in_array($key, ['unit_price', 'btp_bta', 'rooftop', '4wd', 'langsir', 'crane', 'charter_boat', 'total_amount'])) {
            $row[$key] = '';
        }
    }
    
    $data[] = $row;
}

ob_end_clean();
echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);

$conn->close();
exit;
?>