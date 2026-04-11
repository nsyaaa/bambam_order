<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (
    !isset($_SESSION['staff_logged_in']) ||
    $_SESSION['staff_logged_in'] !== true ||
    !isset($_SESSION['branch_id'])
) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

$branch_id = $_SESSION['branch_id'];
$staff_id = $_GET['staff_id'] ?? '';
$filter_date = $_GET['date'] ?? '';

if (empty($staff_id) || !is_numeric($staff_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid staff ID'
    ]);
    exit;
}

try {
    $sql = "
        SELECT id, work_date, clock_in, clock_out, total_hours, status
        FROM attendance_logs
        WHERE staff_id = ?
          AND branch_id = ?
    ";

    $params = [(int)$staff_id, (int)$branch_id];

    if (!empty($filter_date)) {
        $sql .= " AND work_date = ? ";
        $params[] = $filter_date;
    }

    $sql .= " ORDER BY work_date DESC, id DESC ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $records = [];

    foreach ($rows as $row) {
        $clockInDisplay = '-';
        $clockOutDisplay = '-';
        $totalDisplay = '-';

        if (!empty($row['clock_in'])) {
            $clockInDisplay = date('h:i:s A', strtotime($row['clock_in']));
        }

        if (!empty($row['clock_out'])) {
            $clockOutDisplay = date('h:i:s A', strtotime($row['clock_out']));
        }

        if (!empty($row['clock_in']) && !empty($row['clock_out'])) {
            $clockIn = new DateTime($row['clock_in']);
            $clockOut = new DateTime($row['clock_out']);

            $totalSeconds = $clockOut->getTimestamp() - $clockIn->getTimestamp();
            if ($totalSeconds < 0) {
                $totalSeconds = 0;
            }

            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;

            $totalDisplay = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        $records[] = [
            'id' => (int)$row['id'],
            'work_date' => $row['work_date'],
            'clock_in' => $clockInDisplay,
            'clock_out' => $clockOutDisplay,
            'total_hours' => $totalDisplay,
            'status' => $row['status']
        ];
    }

    echo json_encode([
        'success' => true,
        'records' => $records
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>