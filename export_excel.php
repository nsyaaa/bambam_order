<?php
session_start();

// Security check: ensure an admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die("Access Denied. Please login as an admin.");
}

// Include dependencies
if (!file_exists('vendor/autoload.php')) {
    die("Error: PhpSpreadsheet library not found. Please run 'composer require phpoffice/phpspreadsheet' in your project directory.");
}
require 'vendor/autoload.php';
include 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Get date range from GET parameters, with defaults
$start = $_GET['start'] ?? date('Y-m-01');
$end   = $_GET['end'] ?? date('Y-m-d');
$startSql = $start . ' 00:00:00';
$endSql   = $end . ' 23:59:59';

try {
    // Query the database for sales records within the date range
    $stmt = $pdo->prepare(
        "SELECT id, created_at, customer_name, branch, order_type, total_amount, status, payment_method 
         FROM orders 
         WHERE created_at BETWEEN ? AND ? AND status IN ('Served', 'Completed') 
         ORDER BY created_at DESC"
    );
    $stmt->execute([$startSql, $endSql]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Sales Report');

    // Set Header and make it bold
    $header = ['Order ID', 'Date', 'Customer', 'Branch', 'Type', 'Total (RM)', 'Status', 'Payment Method'];
    $sheet->fromArray($header, NULL, 'A1');
    $sheet->getStyle('A1:H1')->getFont()->setBold(true);

    // Populate data rows
    $rowIndex = 2;
    $totalSales = 0;
    foreach ($rows as $row) {
        $totalSales += (float)$row['total_amount'];
        $sheet->setCellValue('A' . $rowIndex, $row['id']);
        $sheet->setCellValue('B' . $rowIndex, Date::PHPToExcel(new DateTime($row['created_at'])));
        $sheet->setCellValue('C' . $rowIndex, $row['customer_name']);
        $sheet->setCellValue('D' . $rowIndex, $row['branch']);
        $sheet->setCellValue('E' . $rowIndex, $row['order_type']);
        $sheet->setCellValue('F' . $rowIndex, (float)$row['total_amount']);
        $sheet->setCellValue('G' . $rowIndex, $row['status']);
        $sheet->setCellValue('H' . $rowIndex, $row['payment_method']);
        $rowIndex++;
    }

    // Add and format the final TOTAL row
    $totalRowIndex = $rowIndex + 1;
    $sheet->setCellValue('E' . $totalRowIndex, 'TOTAL');
    $sheet->getStyle('E' . $totalRowIndex)->getFont()->setBold(true);
    $sheet->setCellValue('F' . $totalRowIndex, $totalSales);
    $sheet->getStyle('F' . $totalRowIndex)->getFont()->setBold(true);

    // Apply formatting to columns
    $sheet->getStyle('B2:B' . $rowIndex)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);
    $sheet->getStyle('F2:F' . $totalRowIndex)->getNumberFormat()->setFormatCode('RM #,##0.00');

    // Auto-size columns for readability
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Set HTTP headers for a clean download
    ob_end_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="sales_report_' . $start . '_to_' . $end . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    die("Error generating Excel report: " . $e->getMessage());
}
?>