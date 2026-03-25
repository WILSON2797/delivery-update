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
    $table = $_GET['table'] ?? '';
    $column = $_GET['column'] ?? '';
    $display = $_GET['display'] ?? '';
    $filter_column = $_GET['filter_column'] ?? '';
    $filter_value = $_GET['filter_value'] ?? '';
    
    // Validasi input
    if (empty($table) || empty($column) || empty($display)) {
        throw new Exception('Parameter table, column, dan display wajib diisi');
    }
    
    // Whitelist tabel yang diizinkan
    $allowedTables = [
        'master_origin',
        'master_mode_of_transport',
        'status_delivery',
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
        'origin_code', 'mot_code', 'mot_description', 'code',
        'locator', 'subcon_name', 'province', 'city', 'id', 'type_shipment',
    ];
    
    if (!in_array($column, $allowedColumns) || !in_array($display, $allowedColumns)) {
        throw new Exception('Kolom tidak diizinkan');
    }
    
    if (!empty($filter_column) && !in_array($filter_column, $allowedColumns)) {
        throw new Exception('Filter column tidak diizinkan');
    }
    
    // ✅ OPTIMASI 1: Cek cache terlebih dahulu
    $cache_key = "dropdown_{$table}_{$column}_{$display}_{$filter_column}_{$filter_value}";
    
    if (isset($_SESSION[$cache_key]) && 
        (time() - ($_SESSION["{$cache_key}_time"] ?? 0)) < 300) { // Cache 5 menit
        echo json_encode([
            'status' => 'success',
            'data' => $_SESSION[$cache_key],
            'count' => count($_SESSION[$cache_key]),
            'cached' => true
        ]);
        exit;
    }
    
    // ✅ OPTIMASI 2: Query yang lebih efisien
    $sql = "SELECT $column as id, $display as text 
            FROM $table 
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Filter jika ada
    if (!empty($filter_column) && !empty($filter_value)) {
        $sql .= " AND $filter_column = ?";
        $params[] = $filter_value;
        $types .= 's';
    }
    
    // ✅ OPTIMASI 3: GROUP BY lebih efisien dari DISTINCT
    $sql .= " GROUP BY $column, $display";
    
    // Order by
    $sql .= " ORDER BY $display ASC";
    
    // ✅ OPTIMASI 4: Limit hasil (opsional, sesuaikan kebutuhan)
    // $sql .= " LIMIT 500";
    
    // Prepare dan execute
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Query error: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute error: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['id'],
            'text' => $row['text']
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    
    $_SESSION[$cache_key] = $data;
    $_SESSION["{$cache_key}_time"] = time();
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'count' => count($data),
        'cached' => false
    ]);
    
} catch (Exception $e) {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>