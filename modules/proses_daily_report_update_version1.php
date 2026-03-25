<?php
include '../php/config.php';

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

try {
    
    // Validasi session untuk update_by
    if (!isset($_SESSION['nama']) || empty($_SESSION['nama'])) {
        throw new Exception('Session tidak valid! Silakan login kembali.');
    }
    
    $update_by = $_SESSION['nama'];
    
    $id = $_POST['id'] ?? null;

    if (empty($id)) {
        throw new Exception('ID wajib diisi!');
    }

    // ===== VALIDASI STATUS HANDOVER DONE =====
    $status = $_POST['status'] ?? '';
    $pod_datetime_raw = !empty($_POST['pod_datetime']) ? $_POST['pod_datetime'] : null;

    if ($status === 'Handover Done' && empty($pod_datetime_raw)) {
        throw new Exception('POD DateTime wajib diisi ketika status Handover Done!');
    }
    // =========================================

    // Validasi pod_type - HANYA MPOD atau EPOD, selainnya NULL
    $pod_type = null;
    if (isset($_POST['pod_type']) && in_array($_POST['pod_type'], ['MPOD', 'EPOD'])) {
        $pod_type = $_POST['pod_type'];
    }

    // Validasi phone number - hanya izinkan angka, +, -, dan spasi
    $phone = $_POST['phone'] ?? '';
    if (!empty($phone) && !preg_match('/^[0-9+\-\s]+$/', $phone)) {
        throw new Exception('Phone number hanya boleh berisi angka, +, -, dan spasi!');
    }

    $pic_mobile_no = $_POST['pic_mobile_no'] ?? '';
    if (!empty($pic_mobile_no) && !preg_match('/^[0-9+\-\s]+$/', $pic_mobile_no)) {
        throw new Exception('PIC Mobile No hanya boleh berisi angka, +, -, dan spasi!');
    }

    // ===== MENCEGAH RACE CONDITION DENGAN TRANSACTION & ROW LOCK =====
    $conn->begin_transaction();

    try {
        // Lock row yang akan diupdate dengan FOR UPDATE
        // Ini mencegah user lain mengupdate row yang sama secara bersamaan
        $lockCheck = $conn->prepare("SELECT id FROM daily_report WHERE id = ? FOR UPDATE");
        $lockCheck->bind_param("i", $id);
        $lockCheck->execute();
        $lockResult = $lockCheck->get_result();
        
        if ($lockResult->num_rows === 0) {
            $lockCheck->close();
            $conn->rollback();
            throw new Exception('Data tidak ditemukan!');
        }
        $lockCheck->close();

        // Query UPDATE - HANYA UPDATE FIELD YANG EDITABLE
        $sql = "UPDATE daily_report SET
            driver_name = ?,
            nopol = ?,
            phone = ?,
            subcon = ?,
            mot = ?,
            latitude = ?,
            longitude = ?,
            latest_status = ?,
            remarks = ?,
            truck_on_warehouse = ?,
            atd_whs_dispatch = ?,
            atd_pool_dispatch = ?,
            ata_mover_on_site = ?,
            receiver_on_site_datetime = ?,
            pod_datetime = ?,
            status = ?,
            pod_type = ?,
            pic_on_dn = ?,
            pic_mobile_no = ?,
            receiver_on_site = ?,
            nominal_add_cost = ?,
            detail_add_cost = ?,
            approval_by_whatsapp = ?,
            rise_up_by_email = ?,
            approved_by_email = ?,
            remarks_add_cost = ?,
            overnight_day = ?,
            update_by = ?,
            updated_at = NOW()
        WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $conn->rollback();
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // NULL handling untuk kolom numeric/datetime
        $truck_on_warehouse = !empty($_POST['truck_on_warehouse']) ? $_POST['truck_on_warehouse'] : null;
        $atd_whs_dispatch   = !empty($_POST['atd_whs_dispatch']) ? $_POST['atd_whs_dispatch'] : null;
        $atd_pool_dispatch  = !empty($_POST['atd_pool_dispatch']) ? $_POST['atd_pool_dispatch'] : null;
        $ata_mover_on_site  = !empty($_POST['ata_mover_on_site']) ? $_POST['ata_mover_on_site'] : null;
        $receiver_on_site_datetime = !empty($_POST['receiver_on_site_datetime']) ? $_POST['receiver_on_site_datetime'] : null;
        $pod_datetime       = $pod_datetime_raw;
        $nominal_add_cost   = !empty($_POST['nominal_add_cost']) ? $_POST['nominal_add_cost'] : null;
        $overnight_day      = !empty($_POST['overnight_day']) ? $_POST['overnight_day'] : null;
        
        // NULL handling untuk kolom DECIMAL (latitude, longitude)
        $latitude  = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
        $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

        // Parameter dalam array (total 27 field + 1 WHERE id = 28)
        $params = [
            $_POST['driver_name'] ?? '',
            $_POST['nopol'] ?? '',
            $phone,
            $_POST['subcon'] ?? '',
            $_POST['mot'] ?? '',
            $latitude,
            $longitude,
            $_POST['latest_status'] ?? '',
            $_POST['remarks'] ?? '',
            $truck_on_warehouse,
            $atd_whs_dispatch,
            $atd_pool_dispatch,
            $ata_mover_on_site,
            $receiver_on_site_datetime,
            $pod_datetime,
            $status,
            $pod_type,
            $_POST['pic_on_dn'] ?? '',
            $pic_mobile_no,
            $_POST['receiver_on_site'] ?? '',
            $nominal_add_cost,
            $_POST['detail_add_cost'] ?? '',
            $_POST['approval_by_whatsapp'] ?? '',
            $_POST['rise_up_by_email'] ?? '',
            $_POST['approved_by_email'] ?? '',
            $_POST['remarks_add_cost'] ?? '',
            $overnight_day,
            $update_by,
            $id
        ];

        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            // Cek apakah ada row yang ter-update
            if ($stmt->affected_rows === 0) {
                $conn->rollback();
                throw new Exception('Tidak ada perubahan data atau data tidak ditemukan!');
            }
            
            // Commit transaction jika berhasil
            $conn->commit();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Data delivery report berhasil diupdate!',
                'affected_rows' => $stmt->affected_rows
            ]);
        } else {
            $conn->rollback();
            throw new Exception('Gagal mengupdate data: ' . $stmt->error);
        }

        $stmt->close();

    } catch (Exception $e) {
        // Rollback jika ada error dalam transaction
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>