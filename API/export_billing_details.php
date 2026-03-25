<?php
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
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Shared\Date;

try {
    // Query untuk mengambil data - HANYA STATUS 'Handover Done'
    $sql = "SELECT 
                id,
                dn_number,
                sub_project,
                pod_date,
                month,
                year,
                weight,
                weight_chargeable,
                type_shipment,
                mot,
                destination_address,
                destination_city,
                date_send_sc_pod,
                date_approved_sc_pod,
                date_send_hc_pod,
                date_submit_pi,
                no_pi,
                unit_price,
                btp_bta,
                rooftop,
                `4wd`,
                langsir,
                crane,
                charter_boat,
                total_amount,
                grouping_aging_day,
                achieved_failed,
                date_confirm_vendors,
                status_var_vendors,
                invoice_send_to_customer,
                no_invoice_vendors,
                inv_date,
                nama_saf
            FROM billing_details 
            WHERE status = 'Handover Done'
            ORDER BY id ASC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query error: " . $conn->error);
    }
    
    // Buat spreadsheet baru
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Billing Details');
    
    // Define header dengan warna biru hijau tosca
    $headers = [
        'A1' => 'No',
        'B1' => 'MR/MRA',
        'C1' => 'Project',
        'D1' => 'Vendor',
        'E1' => 'HO Date',
        'F1' => 'Month',
        'G1' => 'Year',
        'H1' => 'Weight',
        'I1' => 'Weight Chargeable',
        'J1' => 'Type Shipment',
        'K1' => 'MOT',
        'L1' => 'Destination',
        'M1' => 'Kabupaten',
        'N1' => 'Date Send SCPOD',
        'O1' => 'Date Approved SCPOD',
        'P1' => 'Date Send HCPOD',
        'Q1' => 'Date Submit PI',
        'R1' => 'No PI',
        'S1' => 'Unit Price',
        'T1' => 'BTP / BTA',
        'U1' => 'Rooftop',
        'V1' => '4WD',
        'W1' => 'Langsir',
        'X1' => 'Crane',
        'Y1' => 'Charter Boat',
        'Z1' => 'Total Amount',
        'AA1' => 'Grouping Aging Day',
        'AB1' => 'Achieved/Failed',
        'AC1' => 'Date Confirm Vendors',
        'AD1' => 'Status VAR Vendors',
        'AE1' => 'Invoice Send To DHL',
        'AF1' => 'No Invoice Vendors',
        'AG1' => 'Inv Date',
        'AH1' => 'Nama SAF'
    ];
    
    // Set header values
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Style untuk header - Biru Hijau Tosca
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 11
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '20B2AA'] // Light Sea Green (Tosca)
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => false
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];
    
    // Apply header style
    $sheet->getStyle('A1:AH1')->applyFromArray($headerStyle);
    
    // Set column widths - Manual width untuk menghindari text terpotong
    $columnWidths = [
        'A' => 5,   // No
        'B' => 25,  // MR/MRA
        'C' => 50,  // Project
        'D' => 12,  // Vendor
        'E' => 15,  // HO Date
        'F' => 15,  // Month
        'G' => 8,   // Year
        'H' => 15,  // Weight
        'I' => 18,  // Weight Chargeable
        'J' => 15,  // Type Shipment
        'K' => 15,  // MOT
        'L' => 60,  // Destination
        'M' => 25,  // Kabupaten
        'N' => 18,  // Date Send SCPOD
        'O' => 20,  // Date Approved SCPOD
        'P' => 18,  // Date Send HCPOD
        'Q' => 18,  // Date Submit PI
        'R' => 25,  // No PI
        'S' => 15,  // Unit Price
        'T' => 15,  // BTP / BTA
        'U' => 12,  // Rooftop
        'V' => 12,  // 4WD
        'W' => 12,  // Langsir
        'X' => 12,  // Crane
        'Y' => 15,  // Charter Boat
        'Z' => 18,  // Total Amount
        'AA' => 20,  // Grouping Aging Day
        'AB' => 18, // Achieved/Failed
        'AC' => 22, // Date Confirm Vendors
        'AD' => 22, // Status VAR Vendors
        'AE' => 22, // Invoice Send To DHL
        'AF' => 22, // No Invoice Vendors
        'AG' => 15, // Inv Date
        'AH' => 18  // Nama SAF
    ];
    
    foreach ($columnWidths as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }
    
    // Set row height untuk header
    $sheet->getRowDimension(1)->setRowHeight(30);
    
    // Fill data dari database
    $rowNum = 2;
    $no = 1;
    
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowNum, $no);
        $sheet->setCellValue('B' . $rowNum, $row['dn_number']);
        $sheet->setCellValue('C' . $rowNum, $row['sub_project']);
        $sheet->setCellValue('D' . $rowNum, 'FIS'); // Default vendor
        
        // HO Date - Format tanggal DD/MM/YY
        if (!empty($row['pod_date'])) {
            $dt = new DateTime($row['pod_date'], new DateTimeZone('Asia/Jakarta'));
            $dateValue = Date::PHPToExcel($dt);
            $sheet->setCellValue('E' . $rowNum, $dateValue);
            $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY');
        } else {
            $sheet->setCellValue('E' . $rowNum, '-');
        }
        
        // Month - Extract month name saja dari format "December-2025" menjadi "December"
        $monthValue = $row['month'] ?: '-';
        if ($monthValue != '-' && strpos($monthValue, '-') !== false) {
            $monthValue = explode('-', $monthValue)[0];
        }
        $sheet->setCellValue('F' . $rowNum, $monthValue);
        $sheet->setCellValue('G' . $rowNum, $row['year'] ?: '-');
        $sheet->setCellValue('H' . $rowNum, $row['weight'] ?: 0);
        $sheet->setCellValue('I' . $rowNum, $row['weight_chargeable'] ?: 0);
        $sheet->setCellValue('J' . $rowNum, $row['type_shipment'] ?: '-');
        $sheet->setCellValue('K' . $rowNum, $row['mot'] ?: '-');
        $sheet->setCellValue('L' . $rowNum, $row['destination_address'] ?: '-');
        $sheet->setCellValue('M' . $rowNum, $row['destination_city'] ?: '-');
        
        // Date Send SCPOD - Format tanggal DD/MM/YY
        if (!empty($row['date_send_sc_pod'])) {
            $dt = new DateTime($row['date_send_sc_pod'], new DateTimeZone('Asia/Jakarta'));
            $dateValue = Date::PHPToExcel($dt);
            $sheet->setCellValue('N' . $rowNum, $dateValue);
            $sheet->getStyle('N' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY');
        } else {
            $sheet->setCellValue('N' . $rowNum, '');
        }
        
        // Date Approved SCPOD - Format tanggal DD/MM/YY
        if (!empty($row['date_approved_sc_pod'])) {
            $dt = new DateTime($row['date_approved_sc_pod'], new DateTimeZone('Asia/Jakarta'));
            $dateValue = Date::PHPToExcel($dt);
            $sheet->setCellValue('O' . $rowNum, $dateValue);
            $sheet->getStyle('O' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY');
        } else {
            $sheet->setCellValue('O' . $rowNum, '');
        }
        
        // Date Send HCPOD - Format tanggal DD/MM/YY
        if (!empty($row['date_send_hc_pod'])) {
            $dt = new DateTime($row['date_send_hc_pod'], new DateTimeZone('Asia/Jakarta'));
            $dateValue = Date::PHPToExcel($dt);
            $sheet->setCellValue('P' . $rowNum, $dateValue);
            $sheet->getStyle('P' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY');
        } else {
            $sheet->setCellValue('P' . $rowNum, '');
        }
        
        // Date Submit PI - Format tanggal DD/MM/YY
        if (!empty($row['date_submit_pi'])) {
            $dt = new DateTime($row['date_submit_pi'], new DateTimeZone('Asia/Jakarta'));
            $dateValue = Date::PHPToExcel($dt);
            $sheet->setCellValue('Q' . $rowNum, $dateValue);
            $sheet->getStyle('Q' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY');
        } else {
            $sheet->setCellValue('Q' . $rowNum, '');
        }
        
        $sheet->setCellValue('R' . $rowNum, $row['no_pi'] ?: '');
        $sheet->setCellValue('S' . $rowNum, $row['unit_price'] ?: '');
        $sheet->setCellValue('T' . $rowNum, $row['btp_bta'] ?: '');
        $sheet->setCellValue('U' . $rowNum, $row['rooftop'] ?: '');
        $sheet->setCellValue('V' . $rowNum, $row['4wd'] ?: '');
        $sheet->setCellValue('W' . $rowNum, $row['langsir'] ?: '');
        $sheet->setCellValue('X' . $rowNum, $row['crane'] ?: '');
        $sheet->setCellValue('Y' . $rowNum, $row['charter_boat'] ?: '');
        $sheet->setCellValue('Z' . $rowNum, $row['total_amount'] ?: '');
        $sheet->setCellValue('AA' . $rowNum, $row['grouping_aging_day'] ?: '');
        $sheet->setCellValue('AB' . $rowNum, $row['achieved_failed'] ?: '');
        
        // Date Confirm Vendors - Format tanggal DD/MM/YY
        if (!empty($row['date_confirm_vendors'])) {
            $dt = new DateTime($row['date_confirm_vendors'], new DateTimeZone('Asia/Jakarta'));
            $dateValue = Date::PHPToExcel($dt);
            $sheet->setCellValue('AC' . $rowNum, $dateValue);
            $sheet->getStyle('AC' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY');
        } else {
            $sheet->setCellValue('AC' . $rowNum, '');
        }
        
        $sheet->setCellValue('AD' . $rowNum, $row['status_var_vendors'] ?: '');
        
        // Invoice Send To DHL - Format tanggal DD/MM/YY
        if (!empty($row['invoice_send_to_customer'])) {
            $dt = new DateTime($row['invoice_send_to_customer'], new DateTimeZone('Asia/Jakarta'));
            $dateValue = Date::PHPToExcel($dt);
            $sheet->setCellValue('AE' . $rowNum, $dateValue);
            $sheet->getStyle('AE' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY');
        } else {
            $sheet->setCellValue('AE' . $rowNum, '');
        }
        
        $sheet->setCellValue('AF' . $rowNum, $row['no_invoice_vendors'] ?: '');
        
        // Inv Date - Format tanggal DD/MM/YY
        if (!empty($row['inv_date'])) {
            $dt = new DateTime($row['inv_date'], new DateTimeZone('Asia/Jakarta'));
            $dateValue = Date::PHPToExcel($dt);
            $sheet->setCellValue('AG' . $rowNum, $dateValue);
            $sheet->getStyle('AG' . $rowNum)->getNumberFormat()->setFormatCode('DD/MM/YY');
        } else {
            $sheet->setCellValue('AG' . $rowNum, '');
        }
        
        $sheet->setCellValue('AH' . $rowNum, $row['nama_saf'] ?: '');
        
        // Style untuk data rows
        $dataStyle = [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => false
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        
        $sheet->getStyle('A' . $rowNum . ':AH' . $rowNum)->applyFromArray($dataStyle);
        
        // Center align untuk kolom tertentu
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . $rowNum . ':G' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('N' . $rowNum . ':Q' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('AA' . $rowNum . ':AB' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('AC' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('AE' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('AG' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Right align untuk numeric columns
        $sheet->getStyle('H' . $rowNum . ':I' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('R' . $rowNum . ':Y' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Format number untuk kolom numeric
        $sheet->getStyle('H' . $rowNum . ':I' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('R' . $rowNum . ':Z' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
        
        // Alternate row colors untuk readability
        if ($no % 2 == 0) {
            $sheet->getStyle('A' . $rowNum . ':AH' . $rowNum)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F8F9FA');
        }
        
        $rowNum++;
        $no++;
    }
    
    // Auto-size untuk tinggi baris data
    for ($i = 2; $i < $rowNum; $i++) {
        $sheet->getRowDimension($i)->setRowHeight(-1);
    }
    
    // Freeze pane - Freeze header row DAN column D
    $sheet->freezePane('D2');
    
    $sheet->setAutoFilter('A1:AH' . ($rowNum - 1));

    $sheet->getSheetView()->setZoomScale(85);
    
    // Set active cell
    $sheet->setSelectedCell('A1');
    
    // Generate filename dengan timestamp
    $filename = 'Billing_Details_Report_' . date('YmdHis') . '.xlsx';
    
    // Set headers untuk download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');
    
    // Save to output
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
    // Clean up
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    
    exit;
    
} catch (Exception $e) {
    error_log("Export Error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Export failed: ' . $e->getMessage()
    ]);
    exit;
}