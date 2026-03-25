<?php
// API/export_daily_report.php

// Set timezone ke Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// Bersihkan semua output buffer
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Load PhpSpreadsheet
require_once __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../php/config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// Set timezone di MySQL connection
$conn->query("SET time_zone = '+07:00'");

// Ambil data
$stmt = $conn->prepare("
    SELECT *
    FROM daily_report
    WHERE status = ?
    ORDER BY pod_datetime ASC
");

$status = 'Handover Done';
$stmt->bind_param("s", $status);

$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

// Buat spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- BARIS 1: Header (Satu baris saja, tanpa merge) ---
$headers = [
    'No', 'Date Request', 'Sub Project', 'DN Number', 'SITE ID', 'Plan From',
    'Destination Address', 'Destination (City)', 'DESTINATION (PROVINCE)',
    'Latest Status', 'RSD', 'RAD', 'SLA', 'VOLUME', 'Gross Weight',
    'Truck on Pickup Point', 'Date Pickup',
    'Date Delivery', 'Onsite Date',
    '(Date & Time Receiver on site)', 'POD (Date & Time)', 'Status', 'SUBCON',
    'Driver Name', 'MOT', 'Type Shipment', 'PIC ON DN', 'PIC Mobile No',
    'Receiver on site', 'HTM', 'Remarks', 'MPOD/EPOD',
    'Nominal Add Cost (AC)', 'Detail Add Cost (AC)', 'Approval By Whatsapp (AC)', 
    'Rise Up By Email (AC)', 'Approved By Email (AC)', 'Remarks Add Cost (AC)',
    'Overnight / Day (OT)', 'Rise Up By Email (OT)', 'Approved By Email (OT)', 'Remarks Add Cost (OT)'
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Styling header baris 1 (A-AF: biru)
$sheet->getStyle('A1:AF1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10, 'name' => 'Arial'],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);

// Styling kolom COMMCASE (AG-AL: kuning)
$sheet->getStyle('AG1:AL1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FF000000'], 'size' => 10, 'name' => 'Arial'],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFC000']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);

// Styling kolom OVERNIGHT (AM-AP: hijau muda)
$sheet->getStyle('AM1:AP1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FF000000'], 'size' => 10, 'name' => 'Arial'],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF92D050']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);

$sheet->getRowDimension(1)->setRowHeight(30);

// --- Isi Data mulai baris 2 ---
$rowNum = 2;
$no = 1;
foreach ($data as $row) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    
    // Date Request - Format Date Only
    if (!empty($row['date_request'])) {
        $dt = new DateTime($row['date_request'], new DateTimeZone('Asia/Jakarta'));
        $dateValue = Date::PHPToExcel($dt);
        $sheet->setCellValue('B' . $rowNum, $dateValue);
        $sheet->getStyle('B' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY');
    }
    
    $sheet->setCellValue('C' . $rowNum, $row['sub_project'] ?? '');
    $sheet->setCellValue('D' . $rowNum, $row['dn_number'] ?? '');
    $sheet->setCellValue('E' . $rowNum, $row['site_id'] ?? '');
    $sheet->setCellValue('F' . $rowNum, $row['plan_from'] ?? '');
    $sheet->setCellValue('G' . $rowNum, $row['destination_address'] ?? '');
    $sheet->setCellValue('H' . $rowNum, $row['destination_city'] ?? '');
    $sheet->setCellValue('I' . $rowNum, $row['destination_province'] ?? '');
    $sheet->setCellValue('J' . $rowNum, $row['latest_status'] ?? '');
    
    // RSD - Format Date Only
    if (!empty($row['rsd'])) {
        $dt = new DateTime($row['rsd'], new DateTimeZone('Asia/Jakarta'));
        $dateValue = Date::PHPToExcel($dt);
        $sheet->setCellValue('K' . $rowNum, $dateValue);
        $sheet->getStyle('K' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY');
    }
    
    // RAD - Format Date Only
    if (!empty($row['rad'])) {
        $dt = new DateTime($row['rad'], new DateTimeZone('Asia/Jakarta'));
        $dateValue = Date::PHPToExcel($dt);
        $sheet->setCellValue('L' . $rowNum, $dateValue);
        $sheet->getStyle('L' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY');
    }
    
    $sheet->setCellValue('M' . $rowNum, $row['sla'] ?? '');
    $sheet->setCellValue('N' . $rowNum, $row['volume'] ?? '');
    $sheet->setCellValue('O' . $rowNum, $row['gross_weight'] ?? '');
    
    // Truck on Warehouse - Format DateTime tanpa detik
    if (!empty($row['truck_on_warehouse'])) {
        $dt = new DateTime($row['truck_on_warehouse'], new DateTimeZone('Asia/Jakarta'));
        $dateValue = Date::PHPToExcel($dt);
        $sheet->setCellValue('P' . $rowNum, $dateValue);
        $sheet->getStyle('P' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY HH:MM');
    }
    
    // ATD Whs Dispatch - Format DateTime tanpa detik
    if (!empty($row['atd_whs_dispatch'])) {
        $dt = new DateTime($row['atd_whs_dispatch'], new DateTimeZone('Asia/Jakarta'));
        $dateValue = Date::PHPToExcel($dt);
        $sheet->setCellValue('Q' . $rowNum, $dateValue);
        $sheet->getStyle('Q' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY HH:MM');
    }
    
    // ATD Pool Dispatch - Format DateTime tanpa detik
    if (!empty($row['atd_pool_dispatch'])) {
        $dt = new DateTime($row['atd_pool_dispatch'], new DateTimeZone('Asia/Jakarta'));
        $dateValue = Date::PHPToExcel($dt);
        $sheet->setCellValue('R' . $rowNum, $dateValue);
        $sheet->getStyle('R' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY HH:MM');
    }
    
    // ATA Mover on Site - Format DateTime tanpa detik
    if (!empty($row['ata_mover_on_site'])) {
        $dt = new DateTime($row['ata_mover_on_site'], new DateTimeZone('Asia/Jakarta'));
        $dateValue = Date::PHPToExcel($dt);
        $sheet->setCellValue('S' . $rowNum, $dateValue);
        $sheet->getStyle('S' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY HH:MM');
    }
    
    // Receiver on Site DateTime - Format DateTime tanpa detik
    if (!empty($row['receiver_on_site_datetime'])) {
        $dt = new DateTime($row['receiver_on_site_datetime'], new DateTimeZone('Asia/Jakarta'));
        $dateValue = Date::PHPToExcel($dt);
        $sheet->setCellValue('T' . $rowNum, $dateValue);
        $sheet->getStyle('T' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY HH:MM');
    }
    
    // POD DateTime - Format DateTime tanpa detik
    if (!empty($row['pod_datetime'])) {
        $dt = new DateTime($row['pod_datetime'], new DateTimeZone('Asia/Jakarta'));
        $dateValue = Date::PHPToExcel($dt);
        $sheet->setCellValue('U' . $rowNum, $dateValue);
        $sheet->getStyle('U' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY HH:MM');
    }
    
    $sheet->setCellValue('V' . $rowNum, $row['status'] ?? '');
    $sheet->setCellValue('W' . $rowNum, $row['subcon'] ?? '');
    $sheet->setCellValue('X' . $rowNum, $row['driver_name'] ?? '');
    $sheet->setCellValue('Y' . $rowNum, $row['mot'] ?? '');
    $sheet->setCellValue('Z' . $rowNum, $row['type_shipment'] ?? '');
    $sheet->setCellValue('AA' . $rowNum, $row['pic_on_dn'] ?? '');
    $sheet->setCellValue('AB' . $rowNum, $row['pic_mobile_no'] ?? '');
    $sheet->setCellValue('AC' . $rowNum, $row['receiver_on_site'] ?? '');
    $sheet->setCellValue('AD' . $rowNum, $row['htm'] ?? '');
    $sheet->setCellValue('AE' . $rowNum, $row['remarks'] ?? '');
    $sheet->setCellValue('AF' . $rowNum, $row['pod_type'] ?? '');
    
    // COMMCASE columns (tanpa merge, langsung ke kolom)
    $sheet->setCellValue('AG' . $rowNum, $row['nominal_add_cost'] ?? '');
    $sheet->setCellValue('AH' . $rowNum, $row['detail_add_cost'] ?? '');
    $sheet->setCellValue('AI' . $rowNum, $row['approval_by_whatsapp'] ?? '');
    $sheet->setCellValue('AJ' . $rowNum, $row['rise_up_by_email'] ?? '');
    $sheet->setCellValue('AK' . $rowNum, $row['approved_by_email'] ?? '');
    $sheet->setCellValue('AL' . $rowNum, $row['remarks_add_cost'] ?? '');
    
    // OVERNIGHT columns (tanpa merge, langsung ke kolom)
    $sheet->setCellValue('AM' . $rowNum, $row['overnight_day'] ?? '');
    $sheet->setCellValue('AN' . $rowNum, $row['overnight_rise_up_email'] ?? '');
    $sheet->setCellValue('AO' . $rowNum, $row['overnight_approved_email'] ?? '');
    $sheet->setCellValue('AP' . $rowNum, $row['overnight_remarks'] ?? '');

    $rowNum++;
}

// Set font Arial size 10 untuk semua data (dari baris 2 sampai akhir)
$sheet->getStyle('A2:AP' . ($rowNum - 1))->getFont()->setName('Arial')->setSize(10);


// Rata tengah untuk kolom tertentu
$sheet->getStyle('A2:A' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('B2:B' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('K2:K' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('L2:L' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('M2:M' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('N2:N' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('O2:O' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('P2:P' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('Q2:Q' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('R2:R' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('S2:S' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('T2:T' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('U2:U' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('V2:V' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('Y2:Z' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('Z2:Z' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Border untuk semua sel
$sheet->getStyle('A1:AP' . ($rowNum - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
]);

// Set lebar kolom tertentu secara manual
$sheet->getColumnDimension('C')->setWidth(30);  // Sub Project
$sheet->getColumnDimension('F')->setWidth(13);  // Plan From
$sheet->getColumnDimension('G')->setWidth(39);  // Destination Address
$sheet->getColumnDimension('J')->setWidth(50);  // Latest Status

// Auto size untuk kolom lainnya
$manualColumns = ['C', 'F', 'G', 'J']; // Kolom yang sudah diset manual
for ($colIndex = 1; $colIndex <= 42; $colIndex++) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
    
    // Skip kolom yang sudah diset manual
    if (!in_array($colLetter, $manualColumns)) {
        $sheet->getColumnDimension($colLetter)->setAutoSize(true);
    }
}

$sheet->freezePane('E2');
$sheet->setAutoFilter('A1:AP' . ($rowNum - 1));

$sheet->getSheetView()->setZoomScale(85);

// Download
$writer = new Xlsx($spreadsheet);
$filename = 'Handover_Done_Report_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');
header('Expires: 0');

$writer->save('php://output');
exit;
?>