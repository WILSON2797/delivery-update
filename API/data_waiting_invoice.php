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

// Query - backtick untuk kolom 4wd
// FILTER: 
// 1. status_var_vendors = 'confirmed'
// 2. invoice_send_to_customer IS NULL (belum ada invoice)
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
WHERE `status_var_vendors` = 'confirmed'
  AND `invoice_send_to_customer` IS NULL
ORDER BY `pod_date` DESC";

$result = $conn->query($query);

if (!$result) {
    ob_end_clean();
    echo json_encode(['data' => [], 'error' => 'Query error: ' . $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    // PENTING: Field finansial tetap dalam format ANGKA MURNI
    // Jangan gunakan number_format() untuk field yang akan diedit
    foreach (['unit_price', 'btp_bta', 'rooftop', '4wd', 'langsir', 'crane', 'charter_boat', 'total_amount'] as $field) {
        if (isset($row[$field]) && $row[$field] !== null) {
            // Konversi ke float untuk konsistensi
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

ob_end_clean(); // Buang output tak sengaja sebelum JSON
echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);

$conn->close();
exit;
?>