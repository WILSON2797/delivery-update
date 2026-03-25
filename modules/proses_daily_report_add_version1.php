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
    
    // Hapus unicode whitespace yang tidak terlihat (zero-width space, non-breaking space, dll)
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

    // ===== MENCEGAH RACE CONDITION DENGAN TRANSACTION & LOCK =====
    $conn->begin_transaction();

    try {
        // Lock table untuk mencegah insert bersamaan
        $check = $conn->prepare("SELECT id FROM daily_report WHERE dn_number = ? FOR UPDATE");
        $check->bind_param("s", $dn_number);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $check->close();
            $conn->rollback();
            throw new Exception("DN Number $dn_number sudah terdaftar!");
        }
        $check->close();

        // Query INSERT - HANYA FIELD YANG ADA DI FORM ADD
        $sql = "INSERT INTO daily_report (
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

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $conn->rollback();
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // NULL handling untuk field optional dengan pembersihan input
        $rsd = cleanInput($_POST['rsd'] ?? null);
        $rad = cleanInput($_POST['rad'] ?? null);
        $volume = cleanInput($_POST['volume'] ?? null);
        $gross_weight = cleanInput($_POST['gross_weight'] ?? null);
        $type_shipment = cleanInput($_POST['type_shipment'] ?? null);
        $htm = cleanInput($_POST['htm'] ?? null);

        // Bind parameters (total 16 field)
        $stmt->bind_param(
            "ssssssssssssssssss",
            $date_request,      // 1
            $dn_number,         // 2
            $sub_project,       // 3
            $site_id,           // 4
            $plan_from,         // 5
            $destination_city,  // 6
            $destination_province, // 7
            $destination_address,  // 8
            $mot,               // 9
            $rsd,               // 10
            $rad,               // 11
            $sla,               // 12
            $volume,            // 13
            $gross_weight,      // 14
            $type_shipment,     // 15
            $htm,                // 16
            $email_release_date,
            $created_by
        );

        if ($stmt->execute()) {
            $conn->commit();
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Data berhasil ditambahkan!',
                'insert_id' => $conn->insert_id
            ]);
        } else {
            $conn->rollback();
            throw new Exception('Gagal menyimpan: ' . $stmt->error);
        }

        $stmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>