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

// ===== FUNGSI UNTUK MEMBERSIHKAN INPUT =====
function cleanInput($value) {
    if ($value === null || $value === '') {
        return null;
    }
    
    // Trim spasi biasa
    $value = trim($value);
    
    // Hapus unicode whitespace yang tidak terlihat
    $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00A0}\x{2000}-\x{200F}\x{202F}\x{205F}\x{3000}]/u', '', $value);
    
    // Trim lagi setelah pembersihan unicode
    $value = trim($value);
    
    // Hapus multiple spaces menjadi single space
    $value = preg_replace('/\s+/', ' ', $value);
    
    return $value !== '' ? $value : null;
}

try {
    
    // Validasi session untuk created_by
    if (!isset($_SESSION['nama']) || empty($_SESSION['nama'])) {
        throw new Exception('Session tidak valid! Silakan login kembali.');
    }
    
    $created_by = $_SESSION['nama'];
    
    // Validasi field required dengan pembersihan input
    $date_request = cleanInput($_POST['date_request'] ?? null);
    $dn_number = cleanInput($_POST['dn_number'] ?? null);
    $sub_project = cleanInput($_POST['sub_project'] ?? null);
    $site_id = cleanInput($_POST['site_id'] ?? null);
    $plan_from = cleanInput($_POST['plan_from'] ?? null);
    $destination_city = cleanInput($_POST['destination_city'] ?? null);
    $destination_province = cleanInput($_POST['destination_province'] ?? null);
    $destination_address = cleanInput($_POST['destination_address'] ?? null);
    $mot = cleanInput($_POST['mot'] ?? null);
    $sla = cleanInput($_POST['sla'] ?? null);
    $email_release_date = cleanInput($_POST['email_release_date'] ?? null);

    if (empty($date_request) || empty($dn_number) || empty($sub_project) || empty($site_id) || 
        empty($plan_from) || empty($destination_city) || empty($destination_province) || 
        empty($destination_address) || empty($mot) || empty($sla)) {
        throw new Exception('Semua field yang bertanda * wajib diisi!');
    }

    // ===== MULAI TRANSACTION =====
    $conn->begin_transaction();

    try {
        // ===== 1. CEK DUPLIKASI DN_NUMBER =====
        $check = $conn->prepare("SELECT id FROM daily_report WHERE dn_number = ? FOR UPDATE");
        $check->bind_param("s", $dn_number);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $check->close();
            throw new Exception("DN Number $dn_number sudah terdaftar!");
        }
        $check->close();

        // ===== 2. INSERT KE TABEL DAILY_REPORT =====
        $sql_daily = "INSERT INTO daily_report (
            date_request, 
            dn_number, 
            sub_project, 
            site_id, 
            plan_from,
            destination_city, 
            destination_province, 
            destination_address,
            mot,
            rsd, 
            rad, 
            sla, 
            volume, 
            gross_weight,
            type_shipment, 
            htm,
            email_release_date,
            created_by,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt_daily = $conn->prepare($sql_daily);
        if (!$stmt_daily) {
            throw new Exception("Prepare daily_report failed: " . $conn->error);
        }

        // NULL handling untuk field optional
        $rsd = cleanInput($_POST['rsd'] ?? null);
        $rad = cleanInput($_POST['rad'] ?? null);
        $volume = cleanInput($_POST['volume'] ?? null);
        $gross_weight = cleanInput($_POST['gross_weight'] ?? null);
        $type_shipment = cleanInput($_POST['type_shipment'] ?? null);
        $htm = cleanInput($_POST['htm'] ?? null);

        // Bind parameters untuk daily_report
        $stmt_daily->bind_param(
            "ssssssssssssssssss",
            $date_request,
            $dn_number,
            $sub_project,
            $site_id,
            $plan_from,
            $destination_city,
            $destination_province,
            $destination_address,
            $mot,
            $rsd,
            $rad,
            $sla,
            $volume,
            $gross_weight,
            $type_shipment,
            $htm,
            $email_release_date,
            $created_by
        );

        if (!$stmt_daily->execute()) {
            throw new Exception('Gagal insert ke daily_report: ' . $stmt_daily->error);
        }

        $insert_id = $conn->insert_id;
        $stmt_daily->close();

        // ===== 3. INSERT KE TABEL BILLING_DETAILS =====
        $sql_billing = "INSERT INTO billing_details (
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
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_billing = $conn->prepare($sql_billing);
        if (!$stmt_billing) {
            throw new Exception("Prepare billing_details failed: " . $conn->error);
        }

        // Status awal untuk billing
        $initial_status = 'Pending'; // atau bisa NULL jika mau

        // Bind parameters untuk billing_details
        $stmt_billing->bind_param(
            "ssssssddsss",
            $dn_number,              // dn_number
            $sub_project,            // sub_project
            $plan_from,              // plan_from
            $destination_address,    // destination_address
            $destination_city,       // destination_city
            $destination_province,   // destination_province
            $gross_weight,           // weight (d = double)
            $gross_weight,           // weight_chargeable (d = double)
            $type_shipment,          // type_shipment
            $mot,                    // mot
            $initial_status          // status
        );

        if (!$stmt_billing->execute()) {
            throw new Exception('Gagal insert ke billing_details: ' . $stmt_billing->error);
        }

        $stmt_billing->close();

        // ===== 4. COMMIT TRANSACTION =====
        $conn->commit();
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Data berhasil ditambahkan ke daily_report dan billing_details!',
            'insert_id' => $insert_id
        ]);

    } catch (Exception $e) {
        // ===== ROLLBACK JIKA ADA ERROR =====
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>