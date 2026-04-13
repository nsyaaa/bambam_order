<?php
include 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : null;
$status = $data['status'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing inventory id']);
    exit;
}

if ($quantity !== null) {
    if ($quantity <= 0) {
        $status = 'Out of Stock';
        $quantity = 0;
    } elseif ($quantity < 10) {
        $status = 'Low Stock';
    } else {
        $status = 'In Stock';
    }

    $stmt = $pdo->prepare("UPDATE inventory SET quantity = ?, status = ? WHERE id = ?");
    $stmt->execute([$quantity, $status, $id]);
} elseif ($status !== null) {
    if ($status === 'Out of Stock') {
        $stmt = $pdo->prepare("UPDATE inventory SET quantity = 0, status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE inventory SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nothing to update']);
    exit;
}

echo json_encode(['success' => true]);