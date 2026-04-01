<?php
include 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    try {
        if (isset($data['status'])) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$data['status'], $data['id']]);
        }
        if (isset($data['payment_status'])) {
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
            $stmt->execute([$data['payment_status'], $data['id']]);
        }
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>