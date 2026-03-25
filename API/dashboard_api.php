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
    // QUERY DASHBOARD DATA
    // ===============================
    
    // Total DN Number (semua record)
    $queryTotalDN = "SELECT COUNT(*) as total FROM daily_report WHERE status != 'Back To Pool'";
    $resultTotalDN = $conn->query($queryTotalDN);
    $totalDN = $resultTotalDN->fetch_assoc()['total'];
    
    // Total On Delivery
    $queryOnDelivery = "SELECT COUNT(*) as total FROM daily_report WHERE status = 'On Delivery'";
    $resultOnDelivery = $conn->query($queryOnDelivery);
    $totalOnDelivery = $resultOnDelivery->fetch_assoc()['total'];
    
    // Total Onsite
    $queryOnsite = "SELECT COUNT(*) as total FROM daily_report WHERE status = 'Onsite'";
    $resultOnsite = $conn->query($queryOnsite);
    $totalOnsite = $resultOnsite->fetch_assoc()['total'];
    
    // Total Back To Pool
    $queryBTP = "SELECT COUNT(*) as total FROM daily_report WHERE status = 'Back To Pool'";
    $resultBTP = $conn->query($queryBTP);
    $totalBTP = $resultBTP->fetch_assoc()['total'];
    
    // Total Pool Mover
    $queryPoolMover = "SELECT COUNT(*) as total FROM daily_report WHERE status = 'Pool Mover'";
    $resultPoolMover = $conn->query($queryPoolMover);
    $totalPoolMover = $resultPoolMover->fetch_assoc()['total'];
    
    // Total Handover Done (untuk chart)
    $queryHandoverDone = "SELECT COUNT(*) as total FROM daily_report WHERE status = 'Handover Done'";
    $resultHandoverDone = $conn->query($queryHandoverDone);
    $totalHandoverDone = $resultHandoverDone->fetch_assoc()['total'];
    
    // ===============================
    // RESPONSE
    // ===============================
    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_dn_number' => (int)$totalDN,
            'total_on_delivery' => (int)$totalOnDelivery,
            'total_onsite' => (int)$totalOnsite,
            'total_btp' => (int)$totalBTP,
            'total_pool_mover' => (int)$totalPoolMover,
            'total_handover_done' => (int)$totalHandoverDone
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