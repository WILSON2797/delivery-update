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

    // ===== VALIDASI FIELD WAJIB =====
    $required_fields = [
        'date_request' => 'Date Request',
        'email_release_date' => 'Email Release Date',
        'dn_number' => 'DN Number',
        'sub_project' => 'Sub Project',
        'site_id' => 'Site ID',
        'plan_from' => 'Plan From',
        'destination_city' => 'Destination City',
        'destination_province' => 'Destination Province',
        'destination_address' => 'Destination Address',
        'mot' => 'MOT',
        'sla' => 'SLA',
        'driver_name' => 'Driver Name',
        'nopol' => 'Nopol',
        'phone' => 'Phone Number'
    ];

    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field])) {
            throw new Exception("$label wajib diisi!");
        }
    }

    // ===== VALIDASI STATUS HANDOVER DONE =====
    $status = $_POST['status'] ?? '';
    $pod_datetime_raw = !empty($_POST['pod_datetime']) ? $_POST['pod_datetime'] : null;

    if ($status === 'Handover Done' && empty($pod_datetime_raw)) {
        throw new Exception('POD DateTime wajib diisi ketika status Handover Done!');
    }

    // ===== VALIDASI DATE FORMAT =====
    $date_request = $_POST['date_request'];
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_request)) {
        throw new Exception('Format Date Request tidak valid! Gunakan YYYY-MM-DD');
    }
    $email_release_date = $_POST['email_release_date'];
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $email_release_date)) {
        throw new Exception('Format Date Email Release tidak valid! Gunakan YYYY-MM-DD');
    }

    // Validasi RSD dan RAD jika diisi
    $rsd = !empty($_POST['rsd']) ? $_POST['rsd'] : null;
    if ($rsd && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $rsd)) {
        throw new Exception('Format RSD tidak valid! Gunakan YYYY-MM-DD');
    }

    $rad = !empty($_POST['rad']) ? $_POST['rad'] : null;
    if ($rad && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $rad)) {
        throw new Exception('Format RAD tidak valid! Gunakan YYYY-MM-DD');
    }

    // ===== VALIDASI NUMERIC FIELDS =====
    $sla = $_POST['sla'];
    if (!is_numeric($sla) || $sla < 0) {
        throw new Exception('SLA harus berupa angka positif!');
    }

    $volume = !empty($_POST['volume']) ? $_POST['volume'] : null;
    if ($volume !== null && (!is_numeric($volume) || $volume < 0)) {
        throw new Exception('Volume harus berupa angka positif!');
    }

    $gross_weight = !empty($_POST['gross_weight']) ? $_POST['gross_weight'] : null;
    if ($gross_weight !== null && (!is_numeric($gross_weight) || $gross_weight < 0)) {
        throw new Exception('Gross Weight harus berupa angka positif!');
    }

    $nominal_add_cost = !empty($_POST['nominal_add_cost']) ? $_POST['nominal_add_cost'] : null;
    if ($nominal_add_cost !== null && !is_numeric($nominal_add_cost)) {
        throw new Exception('Nominal Add Cost harus berupa angka!');
    }

    $overnight_day = !empty($_POST['overnight_day']) ? $_POST['overnight_day'] : null;
    if ($overnight_day !== null && (!is_numeric($overnight_day) || $overnight_day < 0)) {
        throw new Exception('Overnight Day harus berupa angka positif!');
    }

    // ===== VALIDASI LATITUDE & LONGITUDE =====
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    if ($latitude !== null && (!is_numeric($latitude) || $latitude < -90 || $latitude > 90)) {
        throw new Exception('Latitude harus berupa angka antara -90 hingga 90!');
    }

    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
    if ($longitude !== null && (!is_numeric($longitude) || $longitude < -180 || $longitude > 180)) {
        throw new Exception('Longitude harus berupa angka antara -180 hingga 180!');
    }

    // ===== VALIDASI POD TYPE =====
    $pod_type = null;
    if (isset($_POST['pod_type']) && in_array($_POST['pod_type'], ['MPOD', 'EPOD'])) {
        $pod_type = $_POST['pod_type'];
    }

    // ===== VALIDASI PHONE NUMBER =====
    $phone = $_POST['phone'];
    if (!preg_match('/^[0-9+\-\s]+$/', $phone)) {
        throw new Exception('Phone number hanya boleh berisi angka, +, -, dan spasi!');
    }

    $pic_mobile_no = $_POST['pic_mobile_no'] ?? '';
    if (!empty($pic_mobile_no) && !preg_match('/^[0-9+\-\s]+$/', $pic_mobile_no)) {
        throw new Exception('PIC Mobile No hanya boleh berisi angka, +, -, dan spasi!');
    }

   // ===== VALIDASI PLAN FROM =====
    $plan_from = trim($_POST['plan_from']);
    if (empty($plan_from)) {
        throw new Exception('Plan From wajib diisi!');
    }

    // ===== VALIDASI MOT =====
    $valid_mot = ['PICKUP', 'CD4', 'CD6', 'WINGBOX', '2-WINGBOX', '4-WINGBOX', '6-WINGBOX', '9-WINGBOX'];
    $mot = $_POST['mot'];
    if (!in_array($mot, $valid_mot)) {
        throw new Exception('MOT tidak valid!');
    }

    // ===== VALIDASI STATUS =====
    $valid_status = ['Done Pickup At WH', 'Pool Mover', 'On Delivery', 'Onsite', 'Back To Pool', 'Handover Done'];
    if (!empty($status) && !in_array($status, $valid_status)) {
        throw new Exception('Status tidak valid!');
    }

    // ===== MENCEGAH RACE CONDITION DENGAN TRANSACTION & ROW LOCK =====
    $conn->begin_transaction();

    try {
        // Lock row yang akan diupdate dengan FOR UPDATE
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

        // Query UPDATE - UPDATE SEMUA FIELD (EDITABLE)
        $sql = "UPDATE daily_report SET
            date_request = ?,
            email_release_date = ?,
            dn_number = ?,
            sub_project = ?,
            site_id = ?,
            plan_from = ?,
            destination_city = ?,
            destination_province = ?,
            destination_address = ?,
            mot = ?,
            rsd = ?,
            rad = ?,
            sla = ?,
            volume = ?,
            gross_weight = ?,
            type_shipment = ?,
            htm = ?,
            driver_name = ?,
            nopol = ?,
            phone = ?,
            subcon = ?,
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
            update_by = ?
        WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $conn->rollback();
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // NULL handling untuk kolom datetime
        $truck_on_warehouse = !empty($_POST['truck_on_warehouse']) ? $_POST['truck_on_warehouse'] : null;
        $atd_whs_dispatch   = !empty($_POST['atd_whs_dispatch']) ? $_POST['atd_whs_dispatch'] : null;
        $atd_pool_dispatch  = !empty($_POST['atd_pool_dispatch']) ? $_POST['atd_pool_dispatch'] : null;
        $ata_mover_on_site  = !empty($_POST['ata_mover_on_site']) ? $_POST['ata_mover_on_site'] : null;
        $receiver_on_site_datetime = !empty($_POST['receiver_on_site_datetime']) ? $_POST['receiver_on_site_datetime'] : null;

        // Parameter dalam array (total 42 field + 1 WHERE id = 43)
        $params = [
            $date_request,                          // 1
            $email_release_date,                          // 1
            $_POST['dn_number'],                    // 2
            $_POST['sub_project'],                  // 3
            $_POST['site_id'],                      // 4
            $plan_from,                             // 5
            $_POST['destination_city'],             // 6
            $_POST['destination_province'],         // 7
            $_POST['destination_address'],          // 8
            $mot,                                   // 9
            $rsd,                                   // 10
            $rad,                                   // 11
            $sla,                                   // 12
            $volume,                                // 13
            $gross_weight,                          // 14
            $_POST['type_shipment'] ?? '',          // 15
            $_POST['htm'] ?? '',                    // 16
            $_POST['driver_name'],                  // 17
            $_POST['nopol'],                        // 18
            $phone,                                 // 19
            $_POST['subcon'] ?? '',                 // 20
            $latitude,                              // 21
            $longitude,                             // 22
            $_POST['latest_status'] ?? '',          // 23
            $_POST['remarks'] ?? '',                // 24
            $truck_on_warehouse,                    // 25
            $atd_whs_dispatch,                      // 26
            $atd_pool_dispatch,                     // 27
            $ata_mover_on_site,                     // 28
            $receiver_on_site_datetime,             // 29
            $pod_datetime_raw,                      // 30
            $status,                                // 31
            $pod_type,                              // 32
            $_POST['pic_on_dn'] ?? '',              // 33
            $pic_mobile_no,                         // 34
            $_POST['receiver_on_site'] ?? '',       // 35
            $nominal_add_cost,                      // 36
            $_POST['detail_add_cost'] ?? '',        // 37
            $_POST['approval_by_whatsapp'] ?? '',   // 38
            $_POST['rise_up_by_email'] ?? '',       // 39
            $_POST['approved_by_email'] ?? '',      // 40
            $_POST['remarks_add_cost'] ?? '',       // 41
            $overnight_day,                         // 42
            $update_by,                             // 43
            $id                                     // 44 (WHERE)
        ];

        // Bind parameters - semua sebagai string untuk handling NULL otomatis
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            // Cek apakah ada row yang ter-update
            if ($stmt->affected_rows === 0) {
                // Bisa jadi tidak ada perubahan data atau ID tidak ditemukan
                // Kita cek apakah ID memang ada
                $checkStmt = $conn->prepare("SELECT id FROM daily_report WHERE id = ?");
                $checkStmt->bind_param("i", $id);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                $checkStmt->close();
                
                if ($checkResult->num_rows === 0) {
                    $conn->rollback();
                    throw new Exception('Data tidak ditemukan!');
                }
                
                // ID ada tapi tidak ada perubahan
                $conn->commit();
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Tidak ada perubahan data',
                    'affected_rows' => 0
                ]);
            } else {
                // Commit transaction jika berhasil
                $conn->commit();
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Order tracking berhasil diupdate!',
                    'affected_rows' => $stmt->affected_rows
                ]);
            }
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