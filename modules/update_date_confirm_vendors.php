<?php
// API/update_date_confirm_vendors.php
ob_start();
header('Content-Type: application/json');

require_once '../php/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Validasi input
    if (!isset($_POST['id']) || !isset($_POST['date_confirm_vendors'])) {
        throw new Exception('Data tidak lengkap');
    }
    
    $id = intval($_POST['id']);
    $date_confirm_vendors = $_POST['date_confirm_vendors'];
    
    // Validasi format tanggal
    $date = DateTime::createFromFormat('Y-m-d', $date_confirm_vendors);
    if (!$date || $date->format('Y-m-d') !== $date_confirm_vendors) {
        throw new Exception('Format tanggal tidak valid');
    }
    
    // Ambil date_submit_pi untuk kalkulasi
    $stmt = $conn->prepare("SELECT date_submit_pi FROM billing_details WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row) {
        throw new Exception('Data tidak ditemukan');
    }
    
    // ==================== KALKULASI OTOMATIS ====================
    
    $grouping_aging_day = NULL;
    $achieved_failed = NULL;
    $status_var_vendors = 'Confirmed';
    
    // Hitung GROUPING_AGING_DAY = date_confirm_vendors - date_submit_pi
    if (!empty($row['date_submit_pi']) && !empty($date_confirm_vendors)) {
        $date_submit = new DateTime($row['date_submit_pi']);
        $date_confirm = new DateTime($date_confirm_vendors);
        $interval = $date_submit->diff($date_confirm);
        $grouping_days = $interval->days;
        
        $grouping_aging_day = $grouping_days . ' days';
        
        // Hitung ACHIEVED/FAILED = Jika grouping_aging_day > 2 days → Failed, else → Achieved
        $achieved_failed = $grouping_days > 2 ? 'Failed' : 'Achieved';
    }
    
    // Update database
    $stmt = $conn->prepare("
        UPDATE billing_details 
        SET date_confirm_vendors = ?,
            grouping_aging_day = ?,
            achieved_failed = ?,
            status_var_vendors = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssssi", 
        $date_confirm_vendors, 
        $grouping_aging_day, 
        $achieved_failed, 
        $status_var_vendors,
        $id
    );
    
    if ($stmt->execute()) {
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Data berhasil diupdate',
            'calculated' => [
                'date_confirm_vendors' => $date_confirm_vendors,
                'grouping_aging_day' => $grouping_aging_day,
                'achieved_failed' => $achieved_failed,
                'status_var_vendors' => $status_var_vendors
            ]
        ]);
    } else {
        throw new Exception('Gagal update data: ' . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
exit;
?>