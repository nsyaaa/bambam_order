<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID not provided.']);
    exit;
}

$userId = $_SESSION['user_id'];
$productId = $_POST['product_id'];

try {
    // Check if it's already a favorite
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        // It exists, so remove it
        $deleteStmt = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
        $deleteStmt->execute([$existing['id']]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // It doesn't exist, so add it
        $insertStmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
        $insertStmt->execute([$userId, $productId]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>