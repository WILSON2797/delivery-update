<?php
// API/update_invoice_details.php
// Khusus update field invoice dari halaman Waiting Submit Invoice

ob_start();
header('Content-Type: application/json');

require_once '../php/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = $_POST;

// Validasi ID wajib ada
if (empty($input['id'])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'ID data tidak ditemukan']);
    exit;
}

$id = intval($input['id']);

// Validasi wajib: invoice_send_to_customer
if (empty($input['invoice_send_to_customer'])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Invoice Send To Customer wajib diisi!']);
    exit;
}

// Field yang diizinkan di halaman ini (hanya 3 ini!)
$allowed_fields = [
    'invoice_send_to_customer',
    'no_invoice_vendors',
    'inv_date'
];

$set_parts = [];

foreach ($allowed_fields as $field) {
    if (array_key_exists($field, $input)) {
        $value = trim($input[$field]);
        
        if ($value === '') {
            $set_parts[] = "`$field` = NULL";
        } else {
            $escaped = $conn->real_escape_string($value);
            $set_parts[] = "`$field` = '$escaped'";
        }
    }
}

if (empty($set_parts)) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang diubah']);
    exit;
}

// Query update hanya field invoice
$sql = "UPDATE `billing_details` SET " . implode(', ', $set_parts) . " WHERE `id` = $id";

if ($conn->query($sql)) {
    $response = [
        'status' => 'success',
        'message' => 'Data invoice berhasil diperbarui',
        'updated_fields' => $allowed_fields
    ];
} else {
    $response = [
        'status' => 'error',
        'message' => 'Gagal update database: ' . $conn->error
    ];
}

ob_end_clean();
echo json_encode($response);
$conn->close();
exit;
?>