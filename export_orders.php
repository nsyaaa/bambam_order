<?php
// ===============================
// Bambam Burger - Excel Export
// ===============================
include 'db.php';

// Include the library via Composer
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

// Sync date filters with admin dashboard
$reportStart = $_GET['report_start'] ?? date('Y-m-01');
$reportEnd = $_GET['report_end'] ?? date('Y-m-d');

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Header Row
    $headers = ['Order ID', 'Date', 'Customer', 'Branch', 'Type', 'Total (RM)', 'Status', 'Method'];
    $column = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($column . '1', $header);
        $sheet->getStyle($column . '1')->getFont()->setBold(true);
        $column++;
    }

    // Fetch Data matching the date range
    $stmt = $pdo->prepare("SELECT id, created_at, customer_name, branch, order_type, total_amount, status, payment_method 
                           FROM orders 
                           WHERE created_at BETWEEN ? AND ? 
                           ORDER BY created_at DESC");
    $stmt->execute([$reportStart . " 00:00:00", $reportEnd . " 23:59:59"]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fill Data
    $rowIndex = 2;
    $totalSum = 0;

    foreach ($rows as $row) {
        $sheet->setCellValue('A' . $rowIndex, $row['id']);
        
        $sheet->setCellValue('B' . $rowIndex, date('d/m/y H:i', strtotime($row['created_at'])));

        $sheet->setCellValue('C' . $rowIndex, $row['customer_name']);
        $sheet->setCellValue('D' . $rowIndex, $row['branch']);
        $sheet->setCellValue('E' . $rowIndex, $row['order_type']);
        
        $amount = (float)$row['total_amount'];
        $sheet->setCellValue('F' . $rowIndex, $amount);
        $sheet->getStyle('F' . $rowIndex)
              ->getNumberFormat()
              ->setFormatCode('[$RM-409] #,##0.00');

        $sheet->setCellValue('G' . $rowIndex, $row['status']);
        $sheet->setCellValue('H' . $rowIndex, $row['payment_method']);
        
        $totalSum += $amount;
        $rowIndex++;
    }

    // Add TOTAL row
    $sheet->setCellValue('E' . $rowIndex, 'TOTAL');
    $sheet->setCellValue('F' . $rowIndex, $totalSum);
    $sheet->getStyle('F' . $rowIndex)
          ->getNumberFormat()
          ->setFormatCode('[$RM-409] #,##0.00');
    $sheet->getStyle('E' . $rowIndex . ':F' . $rowIndex)->getFont()->setBold(true);

    // Auto-size columns for better readability
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Bambam_Orders_Report_' . $reportStart . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    die("Error generating spreadsheet: " . $e->getMessage());
}