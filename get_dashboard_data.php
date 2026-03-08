<?php
include 'db.php';
header('Content-Type: application/json');

// Fetch all active orders from MySQL
$stmt = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For each order, get the items (burgers/drinks)
foreach ($orders as &$order) {
    $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemStmt->execute([$order['id']]);
    $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($orders);