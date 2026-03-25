<?php
// API/data_billing_inv_filled.php
// API khusus: Hanya tampilkan data yang inv_date sudah terisi (NOT NULL)

ob_start();
header('Content-Type: application/json');

// Koneksi DB
require_once '../php/config.php';

// Cek koneksi
if (!isset($conn) || $conn->connect_error) {
    ob_end_clean();
    echo json_encode(['data' => [], 'error' => 'DB connection failed']);
    exit;
}

// Query: Hanya record yang inv_date TERISI (bukan NULL)
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
WHERE `inv_date` IS NOT NULL
ORDER BY `inv_date` DESC";

$result = $conn->query($query);

if (!$result) {
    ob_end_clean();
    echo json_encode(['data' => [], 'error' => 'Query error: ' . $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    // Field finansial tetap angka murni (float)
    foreach (['unit_price', 'btp_bta', 'rooftop', '4wd', 'langsir', 'crane', 'charter_boat', 'total_amount'] as $field) {
        if (isset($row[$field]) && $row[$field] !== null) {
            $row[$field] = floatval($row[$field]);
        }
    }
    
    // Ubah NULL jadi string kosong untuk field non-angka (biar aman di JS/DataTables)
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