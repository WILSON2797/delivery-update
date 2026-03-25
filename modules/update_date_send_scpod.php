<?php
// API/update_date_send_scpod.php
header('Content-Type: application/json');

// Koneksi database (sesuaikan dengan konfigurasi Anda)
require_once '../php/config.php'; // Sesuaikan path

try {
    // Validasi input
    if (!isset($_POST['id']) || !isset($_POST['date_send_sc_pod'])) {
        throw new Exception('Data tidak lengkap');
    }
    
    $id = intval($_POST['id']);
    $date_send_sc_pod = $_POST['date_send_sc_pod'];
    
    // Validasi format tanggal
    $date = DateTime::createFromFormat('Y-m-d', $date_send_sc_pod);
    if (!$date || $date->format('Y-m-d') !== $date_send_sc_pod) {
        throw new Exception('Format tanggal tidak valid');
    }
    
    // Hitung KPI Uploaded
    // Ambil pod_date terlebih dahulu
    $stmt = $conn->prepare("SELECT pod_date FROM billing_details WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row) {
        throw new Exception('Data tidak ditemukan');
    }
    
    $kpi_uploaded = 'NY.Uploaded';
    if ($row['pod_date']) {
        $pod_date = new DateTime($row['pod_date']);
        $send_date = new DateTime($date_send_sc_pod);
        $diff = $pod_date->diff($send_date)->days;
        
        // Logika KPI: Jika dikirim dalam 2 hari = ONTIME, lebih dari 2 hari = LATE
        if ($diff <= 2) {
            $kpi_uploaded = 'ONTIME';
        } else {
            $kpi_uploaded = 'LATE';
        }
    }
    
    // Update database
    $stmt = $conn->prepare("
        UPDATE billing_details 
        SET date_send_sc_pod = ?,
            kpi_uploaded = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssi", $date_send_sc_pod, $kpi_uploaded, $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Data berhasil diupdate',
            'data' => [
                'id' => $id,
                'date_send_sc_pod' => $date_send_sc_pod,
                'kpi_uploaded' => $kpi_uploaded
            ]
        ]);
    } else {
        throw new Exception('Gagal update data: ' . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>