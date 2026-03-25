<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Update session activity
$_SESSION['last_activity'] = time();

// Database connection
require_once '../php/config.php';

try {
    // Get parameters
    $table         = $_GET['table']        ?? '';
    $column        = $_GET['column']       ?? '';
    $display       = $_GET['display']      ?? '';
    $filter_column = $_GET['filter_column'] ?? '';
    $filter_value  = $_GET['filter_value'] ?? '';

    // Validasi input
    if (empty($table) || empty($column) || empty($display)) {
        throw new Exception('Parameter table, column, dan display wajib diisi');
    }

    // Whitelist tabel yang diizinkan
    $allowedTables = [
        'master_origin',
        'master_mode_of_transport',
        'status_delivery',
        'data_driver',
        'master_locator',
        'master_subcon',
        'province_city',
        'type_shipment',
    ];

    if (!in_array($table, $allowedTables)) {
        throw new Exception('Tabel tidak diizinkan');
    }

    // Whitelist kolom yang diizinkan
    $allowedColumns = [
        'origin_code', 'mot_code', 'mot_description', 'code', 'nama',
        'locator', 'subcon_name', 'province', 'city', 'id', 'type_shipment',
        'nopol', 'phone',
    ];

    if (!in_array($column, $allowedColumns) || !in_array($display, $allowedColumns)) {
        throw new Exception('Kolom tidak diizinkan');
    }

    if (!empty($filter_column) && !in_array($filter_column, $allowedColumns)) {
        throw new Exception('Filter column tidak diizinkan');
    }

    // ✅ Cek cache terlebih dahulu
    $cache_key = "dropdown_{$table}_{$column}_{$display}_{$filter_column}_{$filter_value}";

    if (isset($_SESSION[$cache_key]) &&
        (time() - ($_SESSION["{$cache_key}_time"] ?? 0)) < 300) { // Cache 5 menit
        echo json_encode([
            'status' => 'success',
            'data'   => $_SESSION[$cache_key],
            'count'  => count($_SESSION[$cache_key]),
            'cached' => true
        ]);
        exit;
    }

    // ✅ Build query — satu query saja, tidak duplikat
    $isDriverTable = ($table === 'data_driver');
    $isStatusDeliveryTable = ($table === 'status_delivery');

    if ($isDriverTable) {
        // Untuk data_driver: sertakan nopol & phone
        $sql = "SELECT nama AS id, nama AS text, nopol, phone
                FROM $table
                WHERE 1=1";
    } else {
        $sql = "SELECT $column AS id, $display AS text
                FROM $table
                WHERE 1=1";
    }
    
    // Untuk status_delivery: hanya tampilkan yang aktif
    if ($isStatusDeliveryTable) {
    $sql .= " AND is_active = 1";
}

    $params = [];
    $types  = '';

    // Tambahkan filter jika ada
    if (!empty($filter_column) && !empty($filter_value)) {
        $sql   .= " AND $filter_column = ?";
        $params[] = $filter_value;
        $types .= 's';
    }

    // GROUP BY & ORDER BY
    if ($isDriverTable) {
        $sql .= " GROUP BY nama, nopol, phone ORDER BY nama ASC";
    } else {
        $sql .= " GROUP BY $column, $display ORDER BY $display ASC";
    }

    // Prepare
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Query error: " . $conn->error);
    }

    // Bind params jika ada
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    // Execute
    if (!$stmt->execute()) {
        throw new Exception("Execute error: " . $stmt->error);
    }

    $result = $stmt->get_result();

    // Fetch hasil
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $item = [
            'id'   => $row['id'],
            'text' => $row['text'],
        ];
        // Sertakan nopol & phone khusus data_driver
        if ($isDriverTable) {
            $item['nopol'] = $row['nopol'] ?? '';
            $item['phone'] = $row['phone'] ?? '';
        }
        $data[] = $item;
    }

    $stmt->close();
    $conn->close();

    // Simpan ke cache session
    $_SESSION[$cache_key]              = $data;
    $_SESSION["{$cache_key}_time"]     = time();

    // Return response
    echo json_encode([
        'status' => 'success',
        'data'   => $data,
        'count'  => count($data),
        'cached' => false
    ]);

} catch (Exception $e) {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();

    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
?>