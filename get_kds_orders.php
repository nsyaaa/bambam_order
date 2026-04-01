<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'db.php';
header('Content-Type: application/json');

// Check if user is admin or staff
$isAllowed = false;
if (isset($_SESSION['admin_id'])) {
    $isAllowed = true;
} elseif (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userRole = $stmt->fetchColumn();
        if (in_array($userRole, ['admin', 'staff'])) {
            $isAllowed = true;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error']);
        exit;
    }
}

if (!$isAllowed) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Fetch active orders for KDS
$kdsStmt = $pdo->query("SELECT * FROM orders WHERE status IN ('Pending', 'Preparing') ORDER BY created_at ASC");
$kdsOrders = $kdsStmt->fetchAll(PDO::FETCH_ASSOC);

$outputOrders = [];

foreach($kdsOrders as $order) {
    // Get items for each order
    $kItemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $kItemsStmt->execute([$order['id']]);
    $order['items'] = $kItemsStmt->fetchAll(PDO::FETCH_ASSOC);
    $outputOrders[] = $order;
}

echo json_encode($outputOrders);

?>