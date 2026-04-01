<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (
    !isset($_SESSION['staff_logged_in']) ||
    $_SESSION['staff_logged_in'] !== true ||
    !isset($_SESSION['branch_name'])
) {
    echo json_encode([
        'debug' => 'session missing',
        'session' => $_SESSION
    ]);
    exit;
}

$branch_name = trim($_SESSION['branch_name']);

try {
    $stmt = $pdo->prepare("SELECT id, name, email, phone, role, branch, created_at FROM staff WHERE TRIM(branch) = ? ORDER BY name ASC");
    $stmt->execute([$branch_name]);
    $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'debug' => 'ok',
        'branch_session' => $branch_name,
        'count' => count($staffList),
        'data' => $staffList
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'debug' => 'db error',
        'error' => $e->getMessage()
    ]);
}
?>