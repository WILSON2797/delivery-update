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
    
    // Transaction ID wajib ada
    $transaction_id = $_POST['transaction_id'] ?? null;
    
    if (empty($transaction_id)) {
        throw new Exception('Transaction ID wajib diisi!');
    }
    
    // Ambil status
    $status = $_POST['status'] ?? '';
    
    // ===== VALIDASI STATUS HANDOVER DONE =====
    $pod_datetime_raw = !empty($_POST['pod_datetime']) ? $_POST['pod_datetime'] : null;
    $btp_datetime_raw = !empty($_POST['btp_datetime']) ? $_POST['btp_datetime'] : null;
    $atd_pool_dispatch_raw   = !empty($_POST['atd_pool_dispatch']) ? $_POST['atd_pool_dispatch'] : null;
    $ata_mover_on_site_raw   = !empty($_POST['ata_mover_on_site']) ? $_POST['ata_mover_on_site'] : null;
    
    // ===== VALIDASI STATUS HANDOVER DONE =====
    if ($status === 'Handover Done' && empty($pod_datetime_raw)) {
        throw new Exception('POD DateTime wajib diisi ketika status Handover Done!');
    }
    
    // ===== VALIDASI STATUS BACK TO POOL =====
    if ($status === 'Back To Pool' && empty($btp_datetime_raw)) {
        throw new Exception('BTP DateTime wajib diisi ketika status Back To Pool!');
    }
    
    //  Validasi 2 Sisi 

    $status_order = [
        'Pending'       => 0,
        'On Process'    => 1,
        'On Delivery'   => 2,
        'Onsite'        => 3,
        'Handover Done' => 4,
        'Back To Pool'  => 5,
    ];
    
    $current_level = $status_order[$status] ?? -1;
    
    // --- ARAH 1: Field terisi → Status harus minimal sekian ---
    if (!empty($atd_pool_dispatch_raw) && $current_level < $status_order['On Delivery']) {
        throw new Exception('Date Time Delivery sudah terisi, status minimal harus On Delivery!');
    }
    
    if (!empty($ata_mover_on_site_raw) && $current_level < $status_order['Onsite']) {
        throw new Exception('Date Time Mover OnSite sudah terisi, status minimal harus Onsite!');
    }
    
    // --- ARAH 2: Status → Field wajib terisi ---
    if ($status === 'On Delivery' && empty($atd_pool_dispatch_raw)) {
        throw new Exception('Date Time Delivery wajib diisi!');
    }
    
    if ($status === 'Onsite') {
        if (empty($atd_pool_dispatch_raw)) {
            throw new Exception('Date Time Delivery wajib diisi!');
        }
        if (empty($ata_mover_on_site_raw)) {
            throw new Exception('Date Time Mover OnSite wajib diisi!');
        }
    }
    
    if ($status === 'Handover Done') {
        if (empty($atd_pool_dispatch_raw)) {
            throw new Exception('Date Time Delivery wajib diisi!');
        }
        if (empty($ata_mover_on_site_raw)) {
            throw new Exception('Date Time Mover OnSite wajib diisi!');
        }
    }
    
    if ($status === 'Back To Pool') {
        if (empty($atd_pool_dispatch_raw)) {
            throw new Exception('Date Time Delivery wajib diisi!');
        }
    }
    
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
    
    // ===== MULAI TRANSACTION =====
    $conn->begin_transaction();
    
    try {
        // ===== 1. LOCK ROW DAN AMBIL DATA YANG DIPERLUKAN =====
        $lockCheck = $conn->prepare("SELECT id, dn_number, type_shipment, gross_weight FROM daily_report WHERE transaction_id = ? FOR UPDATE");
        $lockCheck->bind_param("s", $transaction_id);
        $lockCheck->execute();
        $lockResult = $lockCheck->get_result();
        
        if ($lockResult->num_rows === 0) {
            $lockCheck->close();
            throw new Exception('Data dengan Transaction ID tersebut tidak ditemukan!');
        }
        
        $row = $lockResult->fetch_assoc();
        $id = $row['id'];
        $dn_number = $row['dn_number'];
        $existing_type_shipment = $row['type_shipment'];
        $existing_gross_weight = $row['gross_weight'];
        $lockCheck->close();
        
        // ===== 2. UPDATE DAILY_REPORT =====
        $sql_daily = "UPDATE daily_report SET
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
            btp_datetime = ?,
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
        WHERE transaction_id = ?";
        
        $stmt_daily = $conn->prepare($sql_daily);
        if (!$stmt_daily) {
            throw new Exception("Prepare daily_report failed: " . $conn->error);
        }
        
        // NULL handling untuk kolom numeric/datetime
        $truck_on_warehouse = !empty($_POST['truck_on_warehouse']) ? $_POST['truck_on_warehouse'] : null;
        $atd_whs_dispatch   = !empty($_POST['atd_whs_dispatch']) ? $_POST['atd_whs_dispatch'] : null;
        $atd_pool_dispatch  = !empty($_POST['atd_pool_dispatch']) ? $_POST['atd_pool_dispatch'] : null;
        $ata_mover_on_site  = !empty($_POST['ata_mover_on_site']) ? $_POST['ata_mover_on_site'] : null;
        $receiver_on_site_datetime = !empty($_POST['receiver_on_site_datetime']) ? $_POST['receiver_on_site_datetime'] : null;
        $pod_datetime       = $pod_datetime_raw;
        $btp_datetime       = $btp_datetime_raw;
        $nominal_add_cost   = !empty($_POST['nominal_add_cost']) ? floatval($_POST['nominal_add_cost']) : null;
        $overnight_day      = !empty($_POST['overnight_day']) ? intval($_POST['overnight_day']) : null;
        
        // NULL handling untuk kolom DECIMAL (latitude, longitude)
        $latitude  = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
        $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
        
        // Parameter untuk daily_report
        $params_daily = [
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
            $btp_datetime,
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
            $transaction_id
        ];
        
        // Tipe data
        $types_daily = "sssssddssssssssssssssdssssssss";
        
        // PENTING: Bind parameter sebelum execute!
        $stmt_daily->bind_param($types_daily, ...$params_daily);
        
        if (!$stmt_daily->execute()) {
            throw new Exception('Gagal update daily_report: ' . $stmt_daily->error);
        }
        
        $stmt_daily->close();
        
        // ===== 3. UPDATE BILLING_DETAILS =====
        $check_billing = $conn->prepare("SELECT id FROM billing_details WHERE transaction_id = ?");
        $check_billing->bind_param("s", $transaction_id);
        $check_billing->execute();
        $check_result = $check_billing->get_result();
        $billing_exists = $check_result->num_rows > 0;
        $check_billing->close();
        
        if ($billing_exists) {
            // UPDATE billing_details jika sudah ada
            $sql_billing = "UPDATE billing_details SET
                status = ?,
                pod_date = ?,
                mot = ?
            WHERE transaction_id = ?";
            
            $stmt_billing = $conn->prepare($sql_billing);
            if (!$stmt_billing) {
                throw new Exception("Prepare billing_details failed: " . $conn->error);
            }
            
            // Konversi pod_datetime ke pod_date (hanya tanggal)
            $pod_date = null;
            if (!empty($pod_datetime)) {
                $pod_date = date('Y-m-d', strtotime($pod_datetime));
            }
            
            $mot_billing = $_POST['mot'] ?? null;
            
            $stmt_billing->bind_param(
                "ssss",
                $status,
                $pod_date,
                $mot_billing,
                $transaction_id
            );
            
            if (!$stmt_billing->execute()) {
                throw new Exception('Gagal update billing_details: ' . $stmt_billing->error);
            }
            
            $stmt_billing->close();
            
        } else {
            // INSERT billing_details jika belum ada
            $get_data = $conn->prepare("SELECT 
                sub_project, 
                plan_from, 
                destination_address, 
                destination_city, 
                destination_province
            FROM daily_report WHERE transaction_id = ?");
            
            $get_data->bind_param("s", $transaction_id);
            $get_data->execute();
            $data_result = $get_data->get_result();
            $data = $data_result->fetch_assoc();
            $get_data->close();
            
            if ($data) {
                $sql_billing_insert = "INSERT INTO billing_details (
                    transaction_id,
                    dn_number,
                    sub_project,
                    plan_from,
                    destination_address,
                    destination_city,
                    destination_province,
                    weight,
                    weight_chargeable,
                    type_shipment,
                    mot,
                    pod_date,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt_billing_insert = $conn->prepare($sql_billing_insert);
                if (!$stmt_billing_insert) {
                    throw new Exception("Prepare billing_details insert failed: " . $conn->error);
                }
                
                // Konversi pod_datetime ke pod_date
                $pod_date = null;
                if (!empty($pod_datetime)) {
                    $pod_date = date('Y-m-d', strtotime($pod_datetime));
                }
                
                $mot_billing = $_POST['mot'] ?? null;
                
                $stmt_billing_insert->bind_param(
                    "sssssssddssss",
                    $transaction_id,
                    $dn_number,
                    $data['sub_project'],
                    $data['plan_from'],
                    $data['destination_address'],
                    $data['destination_city'],
                    $data['destination_province'],
                    $existing_gross_weight,
                    $existing_gross_weight,
                    $existing_type_shipment,
                    $mot_billing,
                    $pod_date,
                    $status
                );
                
                if (!$stmt_billing_insert->execute()) {
                    throw new Exception('Gagal insert billing_details: ' . $stmt_billing_insert->error);
                }
                
                $stmt_billing_insert->close();
            }
        }
        
        // ===== 4. COMMIT TRANSACTION =====
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Data has been successfully updated!',
            'transaction_id' => $transaction_id,
            'dn_number' => $dn_number
        ]);
        
    } catch (Exception $e) {
        // ===== ROLLBACK JIKA ADA ERROR =====
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