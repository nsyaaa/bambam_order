<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['staff_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$staff_id = $_SESSION['branch_id']; // Using branch_id as proxy for staff_id in this setup
$action = $_POST['action'] ?? '';
$date = date('Y-m-d');

try {
    if ($action === 'clock_in') {
        $stmt = $pdo->prepare("INSERT INTO attendance_logs (staff_id, branch_id, clock_in, work_date) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$staff_id, $_SESSION['branch_id'], $date]);
        echo json_encode(['success' => true, 'message' => 'Clocked in successfully']);
    } 
    elseif ($action === 'clock_out') {
        // Find the active log for today
        $stmt = $pdo->prepare("SELECT id, clock_in FROM attendance_logs WHERE staff_id = ? AND status = 'Active' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$staff_id]);
        $log = $stmt->fetch();

        if ($log) {
            $clock_in = new DateTime($log['clock_in']);
            $clock_out = new DateTime();
            $interval = $clock_in->diff($clock_out);
            
            // Calculation: Hours = (Total Minutes / 60) - 1 hour break
            $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
            $workHours = ($totalMinutes / 60) - 1; 
            if ($workHours < 0) $workHours = 0; // Prevent negative time for short shifts

            $update = $pdo->prepare("UPDATE attendance_logs SET clock_out = NOW(), total_hours = ?, status = 'Completed' WHERE id = ?");
            $update->execute([$workHours, $log['id']]);
            echo json_encode(['success' => true, 'work_hours' => round($workHours, 2)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No active shift found']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}