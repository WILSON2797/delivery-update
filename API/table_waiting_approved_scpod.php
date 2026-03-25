<?php
// API/table_waiting_approved_scpod.php
header('Content-Type: application/json');

// Koneksi database
require_once '../php/config.php'; // Sesuaikan path

try {
    // Query untuk mengambil data yang sudah upload SCPOD tapi belum approved
    // Kondisi: date_send_sc_pod NOT NULL AND date_approved_sc_pod IS NULL
    $query = "
        SELECT 
            id,
            dn_number,
            sub_project,
            DATE_FORMAT(pod_date, '%Y-%m-%d') as pod_date,
            type_shipment,
            mot,
            DATE_FORMAT(date_send_sc_pod, '%Y-%m-%d') as date_send_sc_pod,
            kpi_uploaded,
            DATE_FORMAT(date_approved_sc_pod, '%Y-%m-%d') as date_approved_sc_pod,
            DATE_FORMAT(date_send_hc_pod, '%Y-%m-%d') as date_send_hc_pod,
            DATE_FORMAT(date_submit_pi, '%Y-%m-%d') as date_submit_pi,
            DATE_FORMAT(due_date, '%Y-%m-%d') as due_date,
            status
        FROM billing_details
        WHERE status = 'Handover Done'
          AND date_send_sc_pod IS NOT NULL
          AND date_approved_sc_pod IS NULL
        ORDER BY date_send_sc_pod DESC
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('Query error: ' . $conn->error);
    }
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'recordsTotal' => count($data),
        'recordsFiltered' => count($data)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => []
    ]);
}

$conn->close();
?>