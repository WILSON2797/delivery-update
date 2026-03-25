<?php
session_start();
include '../php/config.php';

header('Content-Type: application/json');

// ===============================
// CEK LOGIN
// ===============================
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'User tidak terautentikasi'
    ]);
    exit();
}

try {
    // ===============================
    // QUERY DASHBOARD DATA - BILLING TRACKING
    // ===============================
    
    // 1. Total Shipment (semua DN)
    $queryTotalShipment = "SELECT COUNT(*) as total FROM billing_details WHERE status != 'Back To Pool'";
    $resultTotalShipment = $conn->query($queryTotalShipment);
    $totalShipment = $resultTotalShipment->fetch_assoc()['total'];
    
    // 2. Handover Done
    $queryHandoverDone = "SELECT COUNT(*) as total FROM billing_details WHERE status = 'Handover Done'";
    $resultHandoverDone = $conn->query($queryHandoverDone);
    $totalHandoverDone = $resultHandoverDone->fetch_assoc()['total'];
    
    // 3. SCPOD Submit (date_send_sc_pod sudah ada)
    $querySCPODSubmit = "SELECT COUNT(*) as total FROM billing_details WHERE date_send_sc_pod IS NOT NULL";
    $resultSCPODSubmit = $conn->query($querySCPODSubmit);
    $totalSCPODSubmit = $resultSCPODSubmit->fetch_assoc()['total'];
    
    // 4. NY Submit SCPOD (date_send_sc_pod null tapi status Handover Done)
    $queryNYSCPOD = "SELECT COUNT(*) as total FROM billing_details WHERE date_send_sc_pod IS NULL AND status = 'Handover Done'";
    $resultNYSCPOD = $conn->query($queryNYSCPOD);
    $totalNYSCPOD = $resultNYSCPOD->fetch_assoc()['total'];
    
    // 5. Total PI Submit (date_submit_pi sudah ada)
    $queryPISubmit = "SELECT COUNT(*) as total FROM billing_details WHERE date_submit_pi IS NOT NULL";
    $resultPISubmit = $conn->query($queryPISubmit);
    $totalPISubmit = $resultPISubmit->fetch_assoc()['total'];
    
    // 6. NY PI Submit (date_submit_pi null tapi date_approved_sc_pod sudah ada)
    $queryNYPI = "SELECT COUNT(*) as total FROM billing_details WHERE date_submit_pi IS NULL AND date_approved_sc_pod IS NOT NULL";
    $resultNYPI = $conn->query($queryNYPI);
    $totalNYPI = $resultNYPI->fetch_assoc()['total'];
    
    // 7. PI Approved (date_confirm_vendors sudah ada)
    $queryPIApproved = "SELECT COUNT(*) as total FROM billing_details WHERE date_confirm_vendors IS NOT NULL";
    $resultPIApproved = $conn->query($queryPIApproved);
    $totalPIApproved = $resultPIApproved->fetch_assoc()['total'];
    
    // 8. Total Submit INV (invoice_send_to_customer sudah ada)
    $querySubmitINV = "SELECT COUNT(*) as total FROM billing_details WHERE invoice_send_to_customer IS NOT NULL";
    $resultSubmitINV = $conn->query($querySubmitINV);
    $totalSubmitINV = $resultSubmitINV->fetch_assoc()['total'];
    
    // ===============================
    // RESPONSE
    // ===============================
    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_shipment' => (int)$totalShipment,
            'total_handover_done' => (int)$totalHandoverDone,
            'total_scpod_submit' => (int)$totalSCPODSubmit,
            'total_ny_scpod' => (int)$totalNYSCPOD,
            'total_pi_submit' => (int)$totalPISubmit,
            'total_ny_pi' => (int)$totalNYPI,
            'total_pi_approved' => (int)$totalPIApproved,
            'total_submit_inv' => (int)$totalSubmitINV
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>