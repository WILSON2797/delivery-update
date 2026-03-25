<?php
// API/data_billing_details.php

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

// Query yang dimodifikasi
// Sekarang hanya menampilkan record dengan:
// - status_var_vendors = 'Waiting Confirm PI' ATAU NULL
// - DAN date_submit_pi IS NULL
$query = "SELECT 
    `id`,
    `dn_number`,
    `sub_project`,
    `customer`,
    `pod_date`,
    `type_shipment`,
    `mot`,
    `date_send_sc_pod`,
    `kpi_uploaded`,
    `date_approved_sc_pod`,
    `date_send_hc_pod`,
    `date_submit_pi`,
    `due_date`,
    `aging_days`,
    `no_pi`,
    `total_amount`,
    `unit_price`,
    `btp_bta`,
    `rooftop`,
    `4wd`,
    `langsir`,
    `crane`,
    `charter_boat`,
    `grouping_aging_day`,
    `achieved_failed`,
    `date_confirm_vendors`,
    `status_var_vendors`,
    `invoice_send_to_customer`,
    `no_invoice_vendors`,
    `inv_date`,
    `nama_saf`
FROM `billing_details`
WHERE `date_approved_sc_pod` IS NOT NULL
  AND `date_submit_pi` IS NULL
ORDER BY `pod_date` ASC";

$result = $conn->query($query);

if (!$result) {
    ob_end_clean();
    echo json_encode(['data' => [], 'error' => 'Query error: ' . $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    // PENTING: Field finansial tetap dalam format ANGKA MURNI
    foreach (['unit_price', 'btp_bta', 'rooftop', '4wd', 'langsir', 'crane', 'charter_boat', 'total_amount'] as $field) {
        if (isset($row[$field]) && $row[$field] !== null) {
            $row[$field] = floatval($row[$field]);
        }
    }
    
    // Ubah NULL jadi string kosong biar aman di JS (kecuali field angka)
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