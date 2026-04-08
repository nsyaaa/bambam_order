<?php
use PHPMailer\PHPMailer\PHPMailer;
session_start();
include 'db.php';
if (file_exists('vendor/autoload.php')) { require 'vendor/autoload.php'; }

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['branch_id'])) {
        echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
        exit;
    }

    $type = $_POST['leave_type'];
    $reason = trim($_POST['reason']);
    $fileName = null;

    // 1. Mandatory MC for Sick Leave
    if ($type === 'Sick' && (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] !== 0)) {
        echo json_encode(['success' => false, 'message' => 'Medical Certificate (MC) is mandatory for Sick Leave.']);
        exit;
    }

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        if (!is_dir("uploads/leaves/")) { mkdir("uploads/leaves/", 0777, true); }
        $fileName = time() . "_" . $_FILES['attachment']['name'];
        move_uploaded_file($_FILES['attachment']['tmp_name'], "uploads/leaves/" . $fileName);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO leave_requests (staff_id, leave_type, start_date, end_date, reason, attachment) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['branch_id'], $type, $_POST['start_date'], $_POST['end_date'], $reason, $fileName]);
        $leave_id = $pdo->lastInsertId();

        // 2. High Priority Notification for Emergency Leave
        if ($type === 'Emergency' && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $smtpPort;

            $mail->setFrom($smtpUser, 'BamBam HR');
            $mail->addAddress($smtpUser); // Notify Admin/Manager
            $mail->Subject = "PRIORITY: Emergency Leave Request - Branch " . $_SESSION['branch_name'];
            $mail->Body = "Emergency leave requested by branch staff.\nReason: $reason\nDates: " . $_POST['start_date'];
            $mail->send();
        }

        echo json_encode(['success' => true, 'message' => 'Leave request submitted for approval.']);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}