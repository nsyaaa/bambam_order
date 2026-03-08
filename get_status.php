<?php
include 'db.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;

// Fetch status AND rating to help frontend decide what to show
$stmt = $pdo->prepare("SELECT status, rating FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if ($order) {
    echo json_encode(['status' => $order['status'], 'is_rated' => !empty($order['rating'])]);
} else {
    echo json_encode(['status' => 'Error']);
}