<?php
// API/get_invoice_detail.php
// Endpoint khusus untuk mendapatkan detail invoice satu record

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

// Query - fokus ke field yang dibutuhkan untuk invoice
$query = "SELECT 
    `id`,
    `dn_number`,
    `sub_project`,
    `customer`,
    `pod_date`,
    `invoice_send_to_customer`,
    `no_invoice_vendors`,
    `inv_date`
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

// Pastikan date fields dalam format yang benar
foreach (['pod_date', 'invoice_send_to_customer', 'inv_date'] as $field) {
    if (isset($data[$field]) && $data[$field] === null) {
        $data[$field] = '';
    }
}

ob_end_clean();
echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);

$conn->close();
exit;
?>