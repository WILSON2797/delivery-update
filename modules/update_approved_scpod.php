<?php
// modules/update_approved_scpod.php
header('Content-Type: application/json');

// Koneksi database (sesuaikan dengan konfigurasi Anda)
require_once '../php/config.php'; // Sesuaikan path

try {
    // Validasi input
    if (!isset($_POST['id']) || !isset($_POST['date_approved_sc_pod'])) {
        throw new Exception('Data tidak lengkap');
    }
    
    $id = intval($_POST['id']);
    $date_approved_sc_pod = $_POST['date_approved_sc_pod'];
    
    // Validasi format tanggal
    $date = DateTime::createFromFormat('Y-m-d', $date_approved_sc_pod);
    if (!$date || $date->format('Y-m-d') !== $date_approved_sc_pod) {
        throw new Exception('Format tanggal tidak valid');
    }
    
    // Cek apakah data ada dan date_send_sc_pod sudah terisi
    $stmt = $conn->prepare("
        SELECT id, dn_number, date_send_sc_pod 
        FROM billing_details 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row) {
        throw new Exception('Data tidak ditemukan');
    }
    
    if (empty($row['date_send_sc_pod'])) {
        throw new Exception('Date Send SCPOD belum terisi. Harap isi terlebih dahulu.');
    }
    
    // Validasi: date_approved_sc_pod tidak boleh lebih kecil dari date_send_sc_pod
    $date_send = new DateTime($row['date_send_sc_pod']);
    $date_approved = new DateTime($date_approved_sc_pod);
    
    if ($date_approved < $date_send) {
        throw new Exception('Tanggal approval tidak boleh lebih awal dari tanggal pengiriman SCPOD');
    }
    
    // Update database
    $stmt = $conn->prepare("
        UPDATE billing_details 
        SET date_approved_sc_pod = ?
        WHERE id = ?
    ");
    $stmt->bind_param("si", $date_approved_sc_pod, $id);
    
    if ($stmt->execute()) {
        // Hitung selisih hari untuk info
        $diff_days = $date_send->diff($date_approved)->days;
        
        echo json_encode([
            'success' => true,
            'message' => 'SCPOD berhasil di-approve!',
            'data' => [
                'id' => $id,
                'dn_number' => $row['dn_number'],
                'date_approved_sc_pod' => $date_approved_sc_pod,
                'date_send_sc_pod' => $row['date_send_sc_pod'],
                'processing_days' => $diff_days
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