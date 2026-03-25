<?php
/**
 * tanya_apps_api.php
 * Letakkan di: api/tanya_apps_api.php
 *
 * Tabel:
 *   - data_driver  : id, nama, nopol, phone         (utf8mb4_unicode_ci)
 *   - daily_report : id, driver_name, nopol, phone,
 *                    status, destination_city,
 *                    dn_number, date_request         (utf8mb4_0900_ai_ci)
 */

session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Jakarta');

require_once '../php/config.php'; // $pdo

$action = $_GET['action'] ?? '';

switch ($action) {

    // =========================================================================
    // Driver STANDBY
    // =========================================================================
    case 'get_available_drivers':
        try {
            // Driver yang sedang busy — paksa collation sama untuk perbandingan
            $busyStmt = $pdo->query("
                SELECT DISTINCT driver_name COLLATE utf8mb4_unicode_ci
                FROM daily_report
                WHERE status IN ('On Delivery', 'Onsite')
                  AND driver_name IS NOT NULL
                  AND driver_name != ''
            ");
            $busyDrivers = $busyStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($busyDrivers)) {
                $ph   = implode(',', array_fill(0, count($busyDrivers), '?'));
                $stmt = $pdo->prepare("
                    SELECT nama AS driver_name, nopol, phone
                    FROM data_driver
                    WHERE nama NOT IN ($ph)
                    ORDER BY nama ASC
                ");
                $stmt->execute($busyDrivers);
            } else {
                $stmt = $pdo->query("
                    SELECT nama AS driver_name, nopol, phone
                    FROM data_driver
                    ORDER BY nama ASC
                ");
            }

            echo json_encode([
                'success' => true,
                'data'    => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // =========================================================================
    // Driver ON DELIVERY
    // =========================================================================
    case 'get_on_delivery_drivers':
        try {
            $stmt = $pdo->query("
                SELECT DISTINCT
                    dr.driver_name,
                    dr.nopol,
                    dr.phone,
                    dr.destination_city AS destination
                FROM daily_report dr
                WHERE dr.status = 'On Delivery'
                  AND dr.driver_name IS NOT NULL
                  AND dr.driver_name != ''
                ORDER BY dr.driver_name ASC
            ");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // =========================================================================
    // Driver ONSITE
    // =========================================================================
    case 'get_onsite_drivers':
        try {
            $stmt = $pdo->query("
                SELECT DISTINCT
                    dr.driver_name,
                    dr.nopol,
                    dr.phone,
                    dr.destination_city AS destination
                FROM daily_report dr
                WHERE dr.status = 'Onsite'
                  AND dr.driver_name IS NOT NULL
                  AND dr.driver_name != ''
                ORDER BY dr.driver_name ASC
            ");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // =========================================================================
    // Semua driver + status
    // =========================================================================
    case 'get_all_drivers':
        try {
            $stmt = $pdo->query("
                SELECT
                    d.nama AS driver_name,
                    d.nopol,
                    d.phone,
                    COALESCE(
                        (SELECT dr.status
                         FROM daily_report dr
                         WHERE dr.driver_name COLLATE utf8mb4_unicode_ci = d.nama
                           AND dr.status IN ('On Delivery','Onsite')
                         ORDER BY dr.id DESC LIMIT 1),
                        'standby'
                    ) AS current_status
                FROM data_driver d
                ORDER BY d.nama ASC
            ");
            $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($drivers as &$drv) {
                $s = strtolower($drv['current_status']);
                if ($s === 'ondelivery')  $drv['current_status'] = 'on_delivery';
                elseif ($s === 'onsite')  $drv['current_status'] = 'onsite';
                else                      $drv['current_status'] = 'standby';
            }

            echo json_encode(['success' => true, 'data' => $drivers]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // =========================================================================
    // Trip count — berapa DN mover X dalam bulan ini
    // =========================================================================
    case 'get_driver_trip_count':
        try {
            $nama = trim($_GET['nama'] ?? '');
            if (!$nama) {
                echo json_encode(['success' => false, 'message' => 'Nama tidak boleh kosong']);
                break;
            }

            // Cari nama asli di data_driver (fuzzy LIKE)
            $stmtCari = $pdo->prepare("
                SELECT nama, nopol, phone
                FROM data_driver
                WHERE nama LIKE CONCAT('%', ?, '%')
                ORDER BY nama ASC
                LIMIT 1
            ");
            $stmtCari->execute([$nama]);
            $driver = $stmtCari->fetch(PDO::FETCH_ASSOC);

            if (!$driver) {
                // Coba cari di daily_report langsung
                $stmtCari2 = $pdo->prepare("
                    SELECT DISTINCT driver_name AS nama, nopol, phone
                    FROM daily_report
                    WHERE driver_name LIKE CONCAT('%', ?, '%')
                    LIMIT 1
                ");
                $stmtCari2->execute([$nama]);
                $driver = $stmtCari2->fetch(PDO::FETCH_ASSOC);
            }

            if (!$driver) {
                echo json_encode([
                    'success' => true,
                    'found'   => false,
                    'message' => "Driver/mover dengan nama \"$nama\" tidak ditemukan di database."
                ]);
                break;
            }

            $namaAsli = $driver['nama'];

            // Hitung total DN bulan ini
            $stmtCount = $pdo->prepare("
                SELECT COUNT(*) AS total
                FROM daily_report
                WHERE driver_name COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', ?, '%')
                  AND MONTH(date_request) = MONTH(CURDATE())
                  AND YEAR(date_request)  = YEAR(CURDATE())
            ");
            $stmtCount->execute([$namaAsli]);
            $total = (int)$stmtCount->fetchColumn();

            // Ambil detail DN bulan ini
            $stmtDetail = $pdo->prepare("
                SELECT
                    dn_number,
                    DATE_FORMAT(date_request, '%d/%m/%Y') AS date_request,
                    destination_city,
                    status
                FROM daily_report
                WHERE driver_name COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', ?, '%')
                  AND MONTH(date_request) = MONTH(CURDATE())
                  AND YEAR(date_request)  = YEAR(CURDATE())
                ORDER BY date_request ASC
            ");
            $stmtDetail->execute([$namaAsli]);
            $detail = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'found'   => true,
                'data'    => [
                    'driver_name' => $namaAsli,
                    'nopol'       => $driver['nopol'] ?? '-',
                    'phone'       => $driver['phone'] ?? '-',
                    'total'       => $total,
                    'period'      => date('01 M Y') . ' — ' . date('d M Y'),
                    'detail'      => $detail
                ]
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
        // DN list by status (Pool mover / Cancelled / Back To Pool)
        // =========================================================================
        case 'get_dn_by_status':
            try {
                $status = trim($_GET['status'] ?? '');
    
                $allowedStatus = ['Pool mover', 'Cancelled', 'Back To Pool'];
                if (!in_array($status, $allowedStatus)) {
                    echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
                    break;
                }
    
                $stmt = $pdo->prepare("
                    SELECT dn_number, site_id
                    FROM daily_report
                    WHERE status = ?
                    ORDER BY date_request DESC
                ");
                $stmt->execute([$status]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                echo json_encode([
                    'success' => true,
                    'status'  => $status,
                    'total'   => count($data),
                    'data'    => $data
                ]);
    
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Unknown action: ' . htmlspecialchars($action)
        ]);
        break;
}