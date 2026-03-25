<?php
// API/update_billing_details.php
// Khusus update field billing dari modal Submit PI

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
if (!isset($input['id']) || empty($input['id'])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'ID wajib untuk update']);
    exit;
}

// ==================== KALKULASI OTOMATIS ====================

// 1. Hitung DUE_DATE = date_submit_pi + 2 days
if (!empty($input['date_submit_pi'])) {
    $date_submit_pi = new DateTime($input['date_submit_pi']);
    $date_submit_pi->modify('+2 days');
    $input['due_date'] = $date_submit_pi->format('Y-m-d');
} else {
    $input['due_date'] = NULL;
}

// 2. Hitung AGING_DAYS
// Formula: IF(due_date="", TODAY()-date_submit_pi, due_date-date_submit_pi)
if (!empty($input['date_submit_pi'])) {
    $date_submit = new DateTime($input['date_submit_pi']);
    
    if (!empty($input['due_date'])) {
        $due_date = new DateTime($input['due_date']);
        $interval = $date_submit->diff($due_date);
        $input['aging_days'] = $interval->days;
    } else {
        $today = new DateTime();
        $interval = $date_submit->diff($today);
        $input['aging_days'] = $interval->days;
    }
} else {
    $input['aging_days'] = NULL;
}

// 3. Hitung TOTAL_AMOUNT = unit_price + btp_bta + rooftop + 4wd + langsir + crane + charter_boat
$total_amount = 0;
$amount_fields = ['unit_price', 'btp_bta', 'rooftop', '4wd', 'langsir', 'crane', 'charter_boat'];

foreach ($amount_fields as $field) {
    if (!empty($input[$field]) && is_numeric($input[$field])) {
        $total_amount += floatval($input[$field]);
    }
}
$input['total_amount'] = $total_amount > 0 ? $total_amount : NULL;

// 4. AUTO UPDATE STATUS_VAR_VENDORS
// Jika date_submit_pi terisi, maka status_var_vendors = "Waiting Confirm PI"
if (!empty($input['date_submit_pi'])) {
    $input['status_var_vendors'] = 'Waiting Confirm PI';
}

// ==================== UPDATE DATABASE ====================

// Field yang diizinkan untuk di-update (hanya yang ada di modal)
$allowed_fields = [
    'no_pi',
    'date_submit_pi',
    'date_send_hc_pod',
    'unit_price',
    'btp_bta',
    'rooftop',
    '4wd',
    'langsir',
    'crane',
    'charter_boat',
    // Field hasil kalkulasi otomatis
    'due_date',
    'aging_days',
    'total_amount',
    'status_var_vendors'  // Tambahkan field status
];

$response = ['status' => 'error', 'message' => 'Unknown error'];

$id = intval($input['id']);

$set_parts = [];
foreach ($allowed_fields as $field) {
    if (array_key_exists($field, $input)) {
        $value = $input[$field];
        
        if ($value === '' || $value === NULL) {
            $set_parts[] = "`$field` = NULL";
        } else {
            $escaped = $conn->real_escape_string($value);
            $set_parts[] = "`$field` = '$escaped'";
        }
    }
}

if (!empty($set_parts)) {
    $sql = "UPDATE `billing_details` SET " . implode(', ', $set_parts) . " WHERE `id` = $id";

    if ($conn->query($sql)) {
        $response = [
            'status' => 'success', 
            'message' => 'Data berhasil diperbarui',
            'calculated' => [
                'due_date' => $input['due_date'],
                'aging_days' => $input['aging_days'],
                'total_amount' => $input['total_amount'],
                'status_var_vendors' => $input['status_var_vendors'] ?? null
            ]
        ];
    } else {
        $response['message'] = 'Gagal update: ' . $conn->error;
    }
} else {
    $response['message'] = 'Tidak ada data yang diubah';
}

ob_end_clean();
echo json_encode($response);
$conn->close();
exit;
?>