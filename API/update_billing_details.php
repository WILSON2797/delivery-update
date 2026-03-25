<?php
// API/update_billing_details.php
// Khusus update field billing utama (finansial, submit PI, dll) dari halaman Billing Details

ob_start();
header('Content-Type: application/json');

require_once '../php/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = $_POST;

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

// 4. Hitung GROUPING_AGING_DAY = date_confirm_vendors - date_submit_pi
if (!empty($input['date_confirm_vendors']) && !empty($input['date_submit_pi'])) {
    $date_confirm = new DateTime($input['date_confirm_vendors']);
    $date_submit = new DateTime($input['date_submit_pi']);
    $interval = $date_submit->diff($date_confirm);
    $grouping_days = $interval->days;
    
    $input['grouping_aging_day'] = $grouping_days . ' days';
    
    // 5. Hitung ACHIEVED/FAILED = Jika grouping_aging_day > 2 days → Failed, else → Achieved
    $input['achieved_failed'] = $grouping_days > 2 ? 'Failed' : 'Achieved';
} else {
    $input['grouping_aging_day'] = NULL;
    $input['achieved_failed'] = NULL;
}

// 6. LOGIC BARU: Set status_var_vendors berdasarkan date_confirm_vendors
if (!empty($input['date_confirm_vendors'])) {
    $input['status_var_vendors'] = 'Confirmed';
} else {
    $input['status_var_vendors'] = 'Waiting Confirm PI';
}

// 7. Hitung KPI_UPLOADED
$pod_date = null;
if (isset($input['id']) && !empty($input['id'])) {
    $id = intval($input['id']);
    $result = $conn->query("SELECT pod_date FROM billing_details WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        $pod_date = $row['pod_date'];
    }
}

if (isset($input['pod_date']) && !empty($input['pod_date'])) {
    $pod_date = $input['pod_date'];
}

if (empty($pod_date)) {
    $input['kpi_uploaded'] = 'NY HO';
} elseif (empty($input['date_send_sc_pod'])) {
    $input['kpi_uploaded'] = 'NY.Uploaded';
} else {
    $pod_date_obj = new DateTime($pod_date);
    $date_send_sc = new DateTime($input['date_send_sc_pod']);
    $pod_date_obj->modify('+3 days');
    
    if ($date_send_sc <= $pod_date_obj) {
        $input['kpi_uploaded'] = 'ONTIME';
    } else {
        $input['kpi_uploaded'] = 'LATE';
    }
}

// ==================== UPDATE / INSERT DATABASE ====================

// Field yang diizinkan untuk di-update/insert
$allowed_fields = [
    'dn_number',
    'sub_project',
    'customer',
    'pod_date',
    'type_shipment',
    'mot',
    'date_send_sc_pod',
    'kpi_uploaded',
    'date_approved_sc_pod',
    'date_send_hc_pod',
    'date_submit_pi',
    'due_date',
    'aging_days',
    'no_pi',
    'unit_price',
    'btp_bta',
    'rooftop',
    '4wd',
    'langsir',
    'crane',
    'charter_boat',
    'total_amount',
    'grouping_aging_day',
    'achieved_failed',
    'date_confirm_vendors',
    'status_var_vendors', // IMPORTANT: Tambahkan field ini
    'nama_saf'
];

$response = ['status' => 'error', 'message' => 'Unknown error'];

if (isset($input['id']) && !empty($input['id'])) {
    // MODE UPDATE
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
                    'grouping_aging_day' => $input['grouping_aging_day'],
                    'achieved_failed' => $input['achieved_failed'],
                    'kpi_uploaded' => $input['kpi_uploaded'],
                    'status_var_vendors' => $input['status_var_vendors'] // Tambahkan ke response
                ]
            ];
        } else {
            $response['message'] = 'Gagal update: ' . $conn->error;
        }
    } else {
        $response['message'] = 'Tidak ada data yang diubah';
    }

} else {
    // MODE INSERT (jika perlu, tapi asumsikan halaman billing tidak punya insert baru)
    $response['message'] = 'ID wajib untuk update';
}

ob_end_clean();
echo json_encode($response);
$conn->close();
exit;
?>