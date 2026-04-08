<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['staff_logged_in'])) {
    echo json_encode([]); exit;
}

$stmt = $pdo->prepare("SELECT * FROM leave_requests WHERE staff_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['branch_id']]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));