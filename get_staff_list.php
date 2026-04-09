<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (
    !isset($_SESSION['staff_logged_in']) ||
    $_SESSION['staff_logged_in'] !== true ||
    !isset($_SESSION['branch_id']) ||
    !isset($_SESSION['branch_name'])
) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

$branch_name = trim($_SESSION['branch_name']);
$today = date('Y-m-d');

try {
    $stmt = $pdo->prepare("
        SELECT 
            s.id,
            s.name,
            s.email,
            s.phone,
            s.role,
            s.branch,
            s.created_at,
            CASE
                WHEN EXISTS (
                    SELECT 1
                    FROM attendance_logs al
                    WHERE al.staff_id = s.id
                    AND al.work_date = ?
                    AND al.status = 'Active'
                )
                THEN 'Clock In'
                ELSE 'Clock Out'
            END AS attendance_status
        FROM staff s
        WHERE TRIM(s.branch) = ?
        ORDER BY s.name ASC
    ");
    $stmt->execute([$today, $branch_name]);
    $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'branch_name' => $branch_name,
        'count' => count($staffList),
        'data' => $staffList
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>