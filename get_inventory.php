<?php
include 'db.php';
header('Content-Type: application/json');

$stmt = $pdo->query("SELECT id, item_name, quantity, unit, status FROM inventory ORDER BY item_name ASC");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => $items
]);