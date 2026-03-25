<?php
// API/get_billing_detail.php
// Endpoint khusus untuk mendapatkan detail satu record untuk modal edit

ob_start();
header('Content-Type: application/json');

require_once '../php/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Ambil ID dari GET atau POST
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

if ($id <= 0) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
    exit;
}

// Query dengan backtick untuk kolom 4wd
$query = "SELECT 
    `id`,
    `dn_number`,
    `sub_project`,
    `customer`,
    `pod_date`,
    `date_send_sc_pod`,
    `date_approved_sc_pod`,
    `date_send_hc_pod`,
    `date_submit_pi`,
    `due_date`,
    `aging_days`,
    `no_pi`,
    `unit_price`,
    `btp_bta`,
    `rooftop`,
    `4wd`,
    `langsir`,
    `crane`,
    `charter_boat`,
    `total_amount`,
    `grouping_aging_day`,
    `achieved_failed`,
    `date_confirm_vendors`,
    `status_var_vendors`,
    `invoice_send_to_customer`,
    `no_invoice_vendors`,
    `inv_date`,
    `nama_saf`
FROM `billing_details` 
WHERE `id` = $id 
LIMIT 1";

$result = $conn->query($query);

if (!$result) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $conn->error]);
    exit;
}

$data = $result->fetch_assoc();

if (!$data) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    exit;
}

// PENTING: Pastikan field finansial tetap dalam format ANGKA MURNI (tanpa format)
// Jangan gunakan number_format() di sini!
foreach (['unit_price', 'btp_bta', 'rooftop', '4wd', 'langsir', 'crane', 'charter_boat', 'total_amount'] as $field) {
    if (isset($data[$field]) && $data[$field] !== null) {
        // Konversi ke float untuk menghilangkan .00 jika tidak perlu
        $data[$field] = floatval($data[$field]);
    }
}

ob_end_clean();
echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);

$conn->close();
exit;
?>