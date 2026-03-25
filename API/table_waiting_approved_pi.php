<?php
// API/table_waiting_approved_pi.php
header('Content-Type: application/json');

require_once '../php/config.php';

try {
    // Query untuk mengambil data yang sudah submit PI tapi belum approved PI
    // Kondisi: date_submit_pi NOT NULL AND date_confirm_vendors IS NULL
    $query = "
        SELECT 
            id,
            dn_number,
            sub_project,
            customer,
            DATE_FORMAT(pod_date, '%Y-%m-%d') as pod_date,
            type_shipment,
            mot,
            DATE_FORMAT(date_send_sc_pod, '%Y-%m-%d') as date_send_sc_pod,
            kpi_uploaded,
            DATE_FORMAT(date_approved_sc_pod, '%Y-%m-%d') as date_approved_sc_pod,
            DATE_FORMAT(date_send_hc_pod, '%Y-%m-%d') as date_send_hc_pod,
            DATE_FORMAT(date_submit_pi, '%Y-%m-%d') as date_submit_pi,
            DATE_FORMAT(due_date, '%Y-%m-%d') as due_date,
            aging_days,
            no_pi,
            unit_price,
            btp_bta,
            rooftop,
            4wd,
            langsir,
            crane,
            charter_boat,
            total_amount,
            status,
            DATE_FORMAT(date_confirm_vendors, '%Y-%m-%d') as date_confirm_vendors,
            grouping_aging_day,
            achieved_failed,
            status_var_vendors
        FROM billing_details
        WHERE date_submit_pi IS NOT NULL
          AND date_confirm_vendors IS NULL
        ORDER BY pod_date ASC
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