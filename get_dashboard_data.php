<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';
header('Content-Type: application/json');

$branch_name = $_SESSION['branch_name'] ?? '';

if ($branch_name === '') {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT * 
    FROM orders 
    WHERE branch = ?
    ORDER BY created_at DESC
");
$stmt->execute([$branch_name]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($orders as &$order) {
    $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemStmt->execute([$order['id']]);
    $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($orders);
exit;