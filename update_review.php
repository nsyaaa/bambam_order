<?php
include_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $review = $_POST['review'] ?? '';

    // Basic Validation
    if (!$order_id || !$rating) {
        echo json_encode(['success' => false, 'message' => 'Missing rating or order reference.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE orders SET rating = ?, review = ? WHERE id = ?");
        $result = $stmt->execute([$rating, $review, $order_id]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>