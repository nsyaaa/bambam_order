<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    echo json_encode([]); exit;
}

$stmt = $pdo->query("SELECT lr.*, u.name as staff_name 
                     FROM leave_requests lr 
                     LEFT JOIN users u ON lr.staff_id = u.id 
                     ORDER BY lr.created_at DESC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));