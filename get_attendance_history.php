<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (
    !isset($_SESSION['staff_logged_in']) ||
    $_SESSION['staff_logged_in'] !== true ||
    !isset($_SESSION['branch_id'])
) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$branch_id = $_SESSION['branch_id'];
$staff_id = $_GET['staff_id'] ?? '';
$date = $_GET['date'] ?? '';

if (empty($staff_id) || !is_numeric($staff_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid staff ID']);
    exit;
}

try {
    if (!empty($date)) {
        $stmt = $pdo->prepare("
            SELECT work_date, clock_in, clock_out, total_hours, status
            FROM attendance_logs
            WHERE staff_id = ? AND branch_id = ? AND work_date = ?
            ORDER BY id DESC
        ");
        $stmt->execute([$staff_id, $branch_id, $date]);
    } else {
        $stmt = $pdo->prepare("
            SELECT work_date, clock_in, clock_out, total_hours, status
            FROM attendance_logs
            WHERE staff_id = ? AND branch_id = ?
            ORDER BY id DESC
            LIMIT 20
        ");
        $stmt->execute([$staff_id, $branch_id]);
    }

    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as &$row) {
        $row['clock_in'] = !empty($row['clock_in']) ? date('h:i A', strtotime($row['clock_in'])) : '-';
        $row['clock_out'] = !empty($row['clock_out']) ? date('h:i A', strtotime($row['clock_out'])) : '-';
        $row['total_hours'] = isset($row['total_hours']) ? round((float)$row['total_hours'], 2) : 0;
    }

    echo json_encode(['success' => true, 'records' => $records]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>