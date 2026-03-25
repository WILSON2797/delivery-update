<?php
include '../php/config.php';
header('Content-Type: application/json');

// Disable output buffering untuk response lebih cepat
if (ob_get_level()) ob_end_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

try {
    $id = $_GET['id'] ?? null;
    
    // Validasi ID harus numeric
    if (empty($id) || !is_numeric($id)) {
        throw new Exception('ID tidak valid!');
    }
    
    // Cast ke integer untuk keamanan
    $id = (int) $id;
    
    // Query dengan SELECT kolom spesifik (termasuk id)
    $sql = "SELECT 
                id,
                transaction_id,
                date_request,
                dn_number,
                driver_name,
                nopol,
                phone,
                mot,
                subcon,
                truck_on_warehouse,
                atd_whs_dispatch,
                atd_pool_dispatch,
                ata_mover_on_site,
                receiver_on_site_datetime,
                pod_datetime,
                btp_datetime,
                pod_type,
                status,
                pic_on_dn,
                pic_mobile_no,
                receiver_on_site,
                latitude,
                longitude,
                latest_status,
                remarks,
                remarks_add_cost
            FROM daily_report 
            WHERE id = ? 
            LIMIT 1";
            
            /*
Tidak diambil dari query:

nominal_add_cost      -> Nominal biaya tambahan
approval_by_whatsapp  -> Approval via WhatsApp
rise_up_by_email      -> Raise up via Email
approved_by_email     -> Approval final via Email
overnight_day         -> Jumlah hari overnight
*/
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    // Execute dengan error handling
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        throw new Exception('Data tidak ditemukan!');
    }
    
    $data = $result->fetch_assoc();
    
    // Clean up resources immediately
    $stmt->close();
    $conn->close();
    
    // Send response
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Close connection on error
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