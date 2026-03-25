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

    // ===== VALIDASI PLAN FROM (DYNAMIC DARI DATABASE) =====
    $plan_from = trim($_POST['plan_from']);
    if (!empty($plan_from)) {
        $stmt_check_origin = $conn->prepare("SELECT COUNT(*) as count FROM master_origin WHERE origin_code = ?");
        $stmt_check_origin->bind_param("s", $plan_from);
        $stmt_check_origin->execute();
        $result_origin = $stmt_check_origin->get_result()->fetch_assoc();
        $stmt_check_origin->close();
        
        if ($result_origin['count'] == 0) {
            throw new Exception('Plan From tidak valid! Origin tidak ditemukan di master data.');
        }
    }

    // ===== VALIDASI MOT (DYNAMIC DARI DATABASE) =====
    $mot = $_POST['mot'];
    if (!empty($mot)) {
        $stmt_check_mot = $conn->prepare("SELECT COUNT(*) as count FROM master_mode_of_transport WHERE mot_code = ?");
        $stmt_check_mot->bind_param("s", $mot);
        $stmt_check_mot->execute();
        $result_mot = $stmt_check_mot->get_result()->fetch_assoc();
        $stmt_check_mot->close();
        
        if ($result_mot['count'] == 0) {
            throw new Exception('MOT tidak valid! MOT tidak ditemukan di master data.');
        }
    }

    // ===== VALIDASI STATUS (DYNAMIC DARI DATABASE) =====
    if (!empty($status)) {
        $stmt_check_status = $conn->prepare("SELECT COUNT(*) as count FROM status_delivery WHERE code = ?");
        $stmt_check_status->bind_param("s", $status);
        $stmt_check_status->execute();
        $result_status = $stmt_check_status->get_result()->fetch_assoc();
        $stmt_check_status->close();
        
        if ($result_status['count'] == 0) {
            throw new Exception('Status tidak valid! Status tidak ditemukan di master data.');
        }
    }

    // ===== VALIDASI PROVINCE & CITY (DYNAMIC DARI DATABASE) =====
    $destination_province = $_POST['destination_province'];
    $destination_city = $_POST['destination_city'];
    
    if (!empty($destination_province) && !empty($destination_city)) {
        $stmt_check_location = $conn->prepare("SELECT COUNT(*) as count FROM province_city WHERE province = ? AND city = ?");
        $stmt_check_location->bind_param("ss", $destination_province, $destination_city);
        $stmt_check_location->execute();
        $result_location = $stmt_check_location->get_result()->fetch_assoc();
        $stmt_check_location->close();
        
        if ($result_location['count'] == 0) {
            throw new Exception('Kombinasi Province dan City tidak valid! Tidak ditemukan di master data.');
        }
    }

    // ===== MULAI TRANSACTION =====
    $conn->begin_transaction();

    try {
        // ===== 1. LOCK ROW DAN AMBIL DN_NUMBER =====
        $lockCheck = $conn->prepare("SELECT dn_number FROM daily_report WHERE id = ? FOR UPDATE");
        $lockCheck->bind_param("i", $id);
        $lockCheck->execute();
        $lockResult = $lockCheck->get_result();
        
        if ($lockResult->num_rows === 0) {
            $lockCheck->close();
            throw new Exception('Data tidak ditemukan!');
        }
        
        $row = $lockResult->fetch_assoc();
        $old_dn_number = $row['dn_number'];
        $lockCheck->close();

        // ===== 2. UPDATE DAILY_REPORT =====
        $sql_daily = "UPDATE daily_report SET
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
            update_by = ?,
            updated_at = NOW()
        WHERE id = ?";

        $stmt_daily = $conn->prepare($sql_daily);
        if (!$stmt_daily) {
            throw new Exception("Prepare daily_report failed: " . $conn->error);
        }

        // NULL handling untuk kolom datetime
        $truck_on_warehouse = !empty($_POST['truck_on_warehouse']) ? $_POST['truck_on_warehouse'] : null;
        $atd_whs_dispatch   = !empty($_POST['atd_whs_dispatch']) ? $_POST['atd_whs_dispatch'] : null;
        $atd_pool_dispatch  = !empty($_POST['atd_pool_dispatch']) ? $_POST['atd_pool_dispatch'] : null;
        $ata_mover_on_site  = !empty($_POST['ata_mover_on_site']) ? $_POST['ata_mover_on_site'] : null;
        $receiver_on_site_datetime = !empty($_POST['receiver_on_site_datetime']) ? $_POST['receiver_on_site_datetime'] : null;

        // Parameter untuk daily_report (total 44 field + 1 WHERE id = 45)
        $params_daily = [
            $date_request,                          // 1
            $email_release_date,                    // 2
            $_POST['dn_number'],                    // 3
            $_POST['sub_project'],                  // 4
            $_POST['site_id'],                      // 5
            $plan_from,                             // 6
            $_POST['destination_city'],             // 7
            $_POST['destination_province'],         // 8
            $_POST['destination_address'],          // 9
            $mot,                                   // 10
            $rsd,                                   // 11
            $rad,                                   // 12
            $sla,                                   // 13
            $volume,                                // 14
            $gross_weight,                          // 15
            $_POST['type_shipment'] ?? '',          // 16
            $_POST['htm'] ?? '',                    // 17
            $_POST['driver_name'],                  // 18
            $_POST['nopol'],                        // 19
            $phone,                                 // 20
            $_POST['subcon'] ?? '',                 // 21
            $latitude,                              // 22
            $longitude,                             // 23
            $_POST['latest_status'] ?? '',          // 24
            $_POST['remarks'] ?? '',                // 25
            $truck_on_warehouse,                    // 26
            $atd_whs_dispatch,                      // 27
            $atd_pool_dispatch,                     // 28
            $ata_mover_on_site,                     // 29
            $receiver_on_site_datetime,             // 30
            $pod_datetime_raw,                      // 31
            $status,                                // 32
            $pod_type,                              // 33
            $_POST['pic_on_dn'] ?? '',              // 34
            $pic_mobile_no,                         // 35
            $_POST['receiver_on_site'] ?? '',       // 36
            $nominal_add_cost,                      // 37
            $_POST['detail_add_cost'] ?? '',        // 38
            $_POST['approval_by_whatsapp'] ?? '',   // 39
            $_POST['rise_up_by_email'] ?? '',       // 40
            $_POST['approved_by_email'] ?? '',      // 41
            $_POST['remarks_add_cost'] ?? '',       // 42
            $overnight_day,                         // 43
            $update_by,                             // 44
            $id                                     // 45 (WHERE)
        ];

        $types_daily = str_repeat('s', count($params_daily));
        $stmt_daily->bind_param($types_daily, ...$params_daily);

        if (!$stmt_daily->execute()) {
            throw new Exception('Gagal update daily_report: ' . $stmt_daily->error);
        }

        $affected_rows = $stmt_daily->affected_rows;
        $stmt_daily->close();

        // ===== 3. UPDATE/INSERT BILLING_DETAILS =====
        $new_dn_number = $_POST['dn_number'];
        
        // Cek apakah billing_details sudah ada (cek dengan DN number LAMA)
        $check_billing = $conn->prepare("SELECT id FROM billing_details WHERE dn_number = ?");
        $check_billing->bind_param("s", $old_dn_number);
        $check_billing->execute();
        $check_result = $check_billing->get_result();
        $billing_exists = $check_result->num_rows > 0;
        $check_billing->close();

        // Konversi pod_datetime ke pod_date dan year - ASSIGN KE VARIABLE
        $pod_date = null;
        $year = null;
        if (!empty($pod_datetime_raw)) {
            $pod_date = date('Y-m-d', strtotime($pod_datetime_raw));
            $year = (int) date('Y', strtotime($pod_datetime_raw));
        }

        // Prepare variabel untuk billing details
        $billing_sub_project = $_POST['sub_project'];
        $billing_type_shipment = $_POST['type_shipment'] ?? '';
        $billing_destination_address = $_POST['destination_address'];
        $billing_destination_city = $_POST['destination_city'];
        $billing_destination_province = $_POST['destination_province'];

        if ($billing_exists) {
            // ===== UPDATE BILLING_DETAILS =====
            $sql_billing = "UPDATE billing_details SET
                dn_number = ?,
                sub_project = ?,
                pod_date = ?,
                status = ?,
                year = ?,
                weight = ?,
                weight_chargeable = ?,
                type_shipment = ?,
                mot = ?,
                plan_from = ?,
                destination_province = ?,
                destination_city = ?,
                destination_address = ?
            WHERE dn_number = ?";

            $stmt_billing = $conn->prepare($sql_billing);
            if (!$stmt_billing) {
                throw new Exception("Prepare billing_details update failed: " . $conn->error);
            }

            $stmt_billing->bind_param(
                "ssssisssssssss",
                $new_dn_number,                     // 1 - dn_number baru
                $billing_sub_project,               // 2 - sub_project
                $pod_date,                          // 3 - pod_date
                $status,                            // 4 - status
                $year,                              // 5 - year (integer)
                $gross_weight,                      // 6 - weight
                $gross_weight,                      // 7 - weight_chargeable
                $billing_type_shipment,             // 8 - type_shipment
                $mot,                               // 9 - mot
                $plan_from,                         // 10 - plan_from
                $billing_destination_province,      // 11 - destination_province
                $billing_destination_city,          // 12 - destination_city
                $billing_destination_address,       // 13 - destination_address
                $old_dn_number                      // 14 - WHERE dn_number lama
            );

            if (!$stmt_billing->execute()) {
                throw new Exception('Gagal update billing_details: ' . $stmt_billing->error);
            }

            $stmt_billing->close();

        } else {
            // ===== INSERT BILLING_DETAILS (jika belum ada) =====
            $sql_billing_insert = "INSERT INTO billing_details (
                dn_number,
                sub_project,
                pod_date,
                status,
                year,
                weight,
                weight_chargeable,
                type_shipment,
                mot,
                plan_from,
                destination_province,
                destination_city,
                destination_address
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt_billing_insert = $conn->prepare($sql_billing_insert);
            if (!$stmt_billing_insert) {
                throw new Exception("Prepare billing_details insert failed: " . $conn->error);
            }

            $stmt_billing_insert->bind_param(
                "ssssissssssss",
                $new_dn_number,                     // 1 - dn_number
                $billing_sub_project,               // 2 - sub_project
                $pod_date,                          // 3 - pod_date
                $status,                            // 4 - status
                $year,                              // 5 - year
                $gross_weight,                      // 6 - weight
                $gross_weight,                      // 7 - weight_chargeable
                $billing_type_shipment,             // 8 - type_shipment
                $mot,                               // 9 - mot
                $plan_from,                         // 10 - plan_from
                $billing_destination_province,      // 11 - destination_province
                $billing_destination_city,          // 12 - destination_city
                $billing_destination_address        // 13 - destination_address
            );

            if (!$stmt_billing_insert->execute()) {
                throw new Exception('Gagal insert billing_details: ' . $stmt_billing_insert->error);
            }

            $stmt_billing_insert->close();
        }

        // ===== 4. COMMIT TRANSACTION =====
        $conn->commit();
        
        // Cek apakah ada perubahan
        if ($affected_rows === 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Tidak ada perubahan data',
                'affected_rows' => 0
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Order tracking berhasil diupdate dan disinkronkan ke billing!',
                'affected_rows' => $affected_rows,
                'dn_number' => $new_dn_number
            ]);
        }

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