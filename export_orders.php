<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Simple Security Check
$isAdmin = isset($_SESSION['admin_id']);
if (!$isAdmin && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if (in_array($stmt->fetchColumn(), ['admin', 'staff'])) { $isAdmin = true; }
}
if (!$isAdmin) { die("Unauthorized access."); }

// 2. Parameters
$start = $_GET['report_start'] ?? date('Y-m-01');
$end = $_GET['report_end'] ?? date('Y-m-d');
$startSql = $start . ' 00:00:00';
$endSql = $end . ' 23:59:59';

// 3. Handle Autoloader (The fix for your error)
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    // If you have specific PhpSpreadsheet logic, it would go here.
    // For now, we provide a universal CSV fallback that works everywhere.
}

// 4. Universal CSV Export (Opens in Excel, requires no libraries)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Bambam_Orders_' . $start . '_to_' . $end . '.csv');

$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV Headers
fputcsv($output, ['Order ID', 'Date', 'Time', 'Customer', 'Phone', 'Branch', 'Type', 'Payment', 'Status', 'Total (RM)']);

// Fetch Data
$stmt = $pdo->prepare("
    SELECT id, created_at, customer_name, customer_phone, branch, order_type, payment_method, status, total_amount 
    FROM orders 
    WHERE created_at BETWEEN ? AND ? 
    ORDER BY created_at DESC
");
$stmt->execute([$startSql, $endSql]);

$grandTotal = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $grandTotal += (float)$row['total_amount'];
    $timestamp = strtotime($row['created_at']);
    $exportRow = [
        $row['id'],
        date('Y-m-d', $timestamp),    // Separate Date
        date('h:i A', $timestamp),    // Separate Time (12-hour format)
        $row['customer_name'],
        $row['customer_phone'],
        $row['branch'],
        $row['order_type'],
        $row['payment_method'],
        $row['status'],
        'RM ' . number_format($row['total_amount'], 2)
    ];
    fputcsv($output, $exportRow);
}

// Add a summary row at the end
fputcsv($output, ['', '', '', '', '', '', '', '', '', '']); // Blank spacing row
fputcsv($output, ['', '', '', '', '', '', '', '', 'TOTAL', 'RM ' . number_format($grandTotal, 2)]);

fclose($output);
exit;