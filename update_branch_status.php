<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$status = strtolower(trim($data['status'] ?? ''));

if (!isset($_SESSION['branch_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No branch session'
    ]);
    exit;
}

$branch_id = (int) $_SESSION['branch_id'];
$is_open = ($status === 'open') ? 1 : 0;

try {
    $stmt = $pdo->prepare("UPDATE branches SET is_open = ? WHERE id = ?");
    $stmt->execute([$is_open, $branch_id]);

    echo json_encode([
        'success' => true,
        'branch_id' => $branch_id,
        'status' => $status
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}