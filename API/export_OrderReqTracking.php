<?php
// API/export_daily_report.php

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

// Ambil data
$stmt = $conn->prepare("SELECT * FROM daily_report WHERE status != 'Back To Pool' ORDER BY created_at ASC");
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

// Buat spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- BARIS 1: Header Utama ---
// Kolom A-AE (merge 2 baris)
$mainHeaders = [
    'No', 'Date Request', 'Sub Project', 'DN Number', 'SITE ID', 'Plan From',
    'Destination Address', 'Destination (City)', 'DESTINATION (PROVINCE)',
    'Latest Status', 'RSD', 'RAD', 'SLA', 'VOLUME', 'Gross Weight',
    'Truck on Warehouse (Date & Time)', 'ATD (Date & Time mover dispatch from Whs)',
    'ATD (Date & Time mover dispatch from Pool)', 'ATA (Date & Time Mover on site)',
    '(Date & Time Receiver on site)', 'POD (Date & Time)', 'Status', 'SUBCON',
    'Driver Name', 'MOT', 'Type Shipment', 'PIC ON DN', 'PIC Mobile No',
    'Receiver on site', 'HTM', 'Remarks', 'MPOD/EPOD'
];

$col = 'A';
foreach ($mainHeaders as $header) {
    $sheet->setCellValue($col . '1', $header);
    // Merge baris 1 dan 2 untuk kolom A-AE
    $sheet->mergeCells($col . '1:' . $col . '2');
    $col++;
}

// COMMCASE (AF-AK merge horizontal di baris 1)
$sheet->setCellValue('AG1', 'COMMCASE');
$sheet->mergeCells('AG1:AL1');

// OVERNIGHT (AL-AO merge horizontal di baris 1)
$sheet->setCellValue('AM1', 'OVERNIGHT');
$sheet->mergeCells('AM1:AP1');

// Styling baris 1 (biru) untuk A-AE
$sheet->getStyle('A1:AF2')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);

// Styling COMMCASE (kuning)
$sheet->getStyle('AG1:AL2')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FF000000']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFC000']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);

// Styling OVERNIGHT (hijau muda atau sesuai kebutuhan)
$sheet->getStyle('AM1:AP2')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FF000000']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF92D050']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);

// --- BARIS 2: Sub-header untuk COMMCASE dan OVERNIGHT ---
// Sub-header COMMCASE (AF-AK)
$sheet->setCellValue('AG2', 'Nominal Add Cost');
$sheet->setCellValue('AH2', 'Detail Add Cost');
$sheet->setCellValue('AI2', 'Approval By Whatsapp');
$sheet->setCellValue('AJ2', 'Rise Up By Email');
$sheet->setCellValue('AK2', 'Approved By Email');
$sheet->setCellValue('AL2', 'Remarks Add Cost');

// Sub-header OVERNIGHT (AL-AO)
$sheet->setCellValue('AM2', 'Overnight / Day');
$sheet->setCellValue('AN2', 'Rise Up By Email');
$sheet->setCellValue('AO2', 'Approved By Email');
$sheet->setCellValue('AP2', 'Remarks Add Cost');

// --- Isi Data mulai baris 3 ---
$rowNum = 3;
$no = 1;
foreach ($data as $row) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValue('B' . $rowNum, $row['date_request'] ? date('d/m/y', strtotime($row['date_request'])) : '');
    $sheet->setCellValue('C' . $rowNum, $row['sub_project'] ?? '');
    $sheet->setCellValue('D' . $rowNum, $row['dn_number'] ?? '');
    $sheet->setCellValue('E' . $rowNum, $row['site_id'] ?? '');
    $sheet->setCellValue('F' . $rowNum, $row['plan_from'] ?? '');
    $sheet->setCellValue('G' . $rowNum, $row['destination_address'] ?? '');
    $sheet->setCellValue('H' . $rowNum, $row['destination_city'] ?? '');
    $sheet->setCellValue('I' . $rowNum, $row['destination_province'] ?? '');
    $sheet->setCellValue('J' . $rowNum, $row['latest_status'] ?? '');
    $sheet->setCellValue('K' . $rowNum, $row['rsd'] ? date('d/m/y', strtotime($row['rsd'])) : '');
    $sheet->setCellValue('L' . $rowNum, $row['rad'] ? date('d/m/y', strtotime($row['rad'])) : '');
    $sheet->setCellValue('M' . $rowNum, $row['sla'] ?? '');
    $sheet->setCellValue('N' . $rowNum, $row['volume'] ?? '');
    $sheet->setCellValue('O' . $rowNum, $row['gross_weight'] ?? '');
    $sheet->setCellValue('P' . $rowNum, $row['truck_on_warehouse'] ?? '');
    $sheet->setCellValue('Q' . $rowNum, $row['atd_whs_dispatch'] ?? '');
    $sheet->setCellValue('R' . $rowNum, $row['atd_pool_dispatch'] ?? '');
    $sheet->setCellValue('S' . $rowNum, $row['ata_mover_on_site'] ?? '');
    $sheet->setCellValue('T' . $rowNum, $row['receiver_on_site_datetime'] ?? '');
    $sheet->setCellValue('U' . $rowNum, $row['pod_datetime'] ?? '');
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
    
    // COMMCASE columns
    $sheet->setCellValue('AG' . $rowNum, $row['nominal_add_cost'] ?? '');
    $sheet->setCellValue('AH' . $rowNum, $row['detail_add_cost'] ?? '');
    $sheet->setCellValue('AI' . $rowNum, $row['approval_by_whatsapp'] ?? '');
    $sheet->setCellValue('AJ' . $rowNum, $row['rise_up_by_email'] ?? '');
    $sheet->setCellValue('AK' . $rowNum, $row['approved_by_email'] ?? '');
    $sheet->setCellValue('AL' . $rowNum, $row['remarks_add_cost'] ?? '');
    
    // OVERNIGHT columns
    $sheet->setCellValue('AM' . $rowNum, $row['overnight_day'] ?? '');
    // Kolom AM, AN, AO untuk OVERNIGHT (sesuaikan dengan field database Anda)
    $sheet->setCellValue('AN' . $rowNum, $row['overnight_rise_up_email'] ?? '');
    $sheet->setCellValue('AO' . $rowNum, $row['overnight_approved_email'] ?? '');
    $sheet->setCellValue('AP' . $rowNum, $row['overnight_remarks'] ?? '');

    $rowNum++;
}

// === PEWARNAAN KOLOM STATUS (Kolom V) ===
for ($i = 2; $i < $rowNum; $i++) {
    $statusValue = $sheet->getCell('V' . $i)->getValue();
    
    switch ($statusValue) {
        case 'Handover Done':
            // Hijau
            $sheet->getStyle('V' . $i)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF00B050'] // Hijau
                ],
                'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true]
            ]);
            break;
            
        case 'Onsite':
            // Kuning (Warning)
            $sheet->getStyle('V' . $i)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFFF00'] // Kuning
                ],
                'font' => ['color' => ['argb' => 'FF000000'], 'bold' => true]
            ]);
            break;
            
        case 'On Delivery':
            // Primary (Biru)
            $sheet->getStyle('V' . $i)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0070C0'] // Biru
                ],
                'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true]
            ]);
            break;
            
        case 'Back To Pool':
            // Merah (seharusnya tidak muncul karena sudah difilter)
            $sheet->getStyle('V' . $i)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFF0000'] // Merah
                ],
                'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true]
            ]);
            break;
            
        case 'Pool Mover':
            // Oranye (warna relevan untuk pool/staging)
            $sheet->getStyle('V' . $i)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFF9900'] // Oranye
                ],
                'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true]
            ]);
            break;
    }
}

// Border untuk semua sel
$sheet->getStyle('A1:AO' . ($rowNum - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
]);

// Auto size kolom
for ($colIndex = 1; $colIndex <= 41; $colIndex++) {  // A sampai AO = 41 kolom
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
}



// Download
$writer = new Xlsx($spreadsheet);
$filename = 'Daily_Report_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');
header('Expires: 0');

$writer->save('php://output');
exit;
?>