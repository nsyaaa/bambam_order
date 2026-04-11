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
$staff_id = $_POST['staff_id'] ?? '';
$action = $_POST['action'] ?? '';
$date = date('Y-m-d');

if (empty($staff_id) || !is_numeric($staff_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid staff ID']);
    exit;
}

try {
    if ($action === 'clock_in') {
        $check = $pdo->prepare("
            SELECT id
            FROM attendance_logs
            WHERE staff_id = ? AND work_date = ? AND status = 'Active'
            LIMIT 1
        ");
        $check->execute([$staff_id, $date]);
        $existing = $check->fetch();

        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Staff already clocked in today']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO attendance_logs (staff_id, branch_id, clock_in, work_date, status)
            VALUES (?, ?, NOW(), ?, 'Active')
        ");
        $stmt->execute([$staff_id, $branch_id, $date]);

        echo json_encode(['success' => true, 'message' => 'Clocked in successfully']);
    }

    elseif ($action === 'clock_out') {
        $stmt = $pdo->prepare("
            SELECT id, clock_in
            FROM attendance_logs
            WHERE staff_id = ? AND work_date = ? AND status = 'Active'
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$staff_id, $date]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$log) {
            echo json_encode(['success' => false, 'message' => 'No active shift found']);
            exit;
        }

        $clock_in = new DateTime($log['clock_in']);
        $clock_out = new DateTime();

        $totalSeconds = $clock_out->getTimestamp() - $clock_in->getTimestamp();
        if ($totalSeconds < 0) {
            $totalSeconds = 0;
        }

        $workHours = round($totalSeconds / 3600, 2);

        $update = $pdo->prepare("
            UPDATE attendance_logs
            SET clock_out = NOW(), total_hours = ?, status = 'Completed'
            WHERE id = ?
        ");
        $update->execute([$workHours, $log['id']]);

        echo json_encode([
            'success' => true,
            'message' => 'Clocked out successfully',
            'work_hours' => $workHours
        ]);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>