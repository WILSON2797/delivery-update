<?php
session_start();
include '../php/config.php';
header('Content-Type: application/json');

// Disable output buffering
if (ob_get_level()) ob_end_clean();

// Cek login
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'User tidak terautentikasi'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

try {
    $id = $_GET['id'] ?? null;
    
    // Validasi ID
    if (empty($id) || !is_numeric($id)) {
        throw new Exception('ID tidak valid!');
    }
    
    $id = (int) $id;
    
    // Query hanya kolom yang dibutuhkan untuk Copy WA
    $sql = "SELECT 
                dn_number,
                site_id,
                sub_project,
                destination_province,
                destination_address,
                latitude,
                longitude,
                receiver_on_site,
                subcon,
                pic_on_dn,
                pic_mobile_no,
                driver_name,
                nopol,
                mot,
                phone,
                status,
                latest_status,
                detail_add_cost,
                remarks,
                plan_from,
                destination_city,
                type_shipment,
                DATE_FORMAT(truck_on_warehouse, '%d-%m-%Y %H:%i') as truck_on_warehouse,
                DATE_FORMAT(atd_whs_dispatch, '%d-%m-%Y %H:%i') as atd_whs_dispatch,
                DATE_FORMAT(atd_pool_dispatch, '%d-%m-%Y %H:%i') as atd_pool_dispatch,
                DATE_FORMAT(atd_pool_dispatch, '%d-%m-%Y') as plan_mos,
                DATE_FORMAT(atd_whs_dispatch, '%d-%m-%Y') as pickup_date
            FROM daily_report 
            WHERE id = ? 
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        throw new Exception('Data tidak ditemukan!');
    }
    
    $data = $result->fetch_assoc();
    
    // Clean up
    $stmt->close();
    $conn->close();
    
    // Response
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>