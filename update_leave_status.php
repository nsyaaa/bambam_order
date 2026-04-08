<?php
use PHPMailer\PHPMailer\PHPMailer;
session_start();
include 'db.php';
if (file_exists('vendor/autoload.php')) { require 'vendor/autoload.php'; }
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if ($id && $status) {
    try {
        // 1. Update Database
        $stmt = $pdo->prepare("UPDATE leave_requests SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        // 2. Get Staff Details for Notification
        $stmt = $pdo->prepare("SELECT lr.*, u.name, u.gmail FROM leave_requests lr JOIN users u ON lr.staff_id = u.id WHERE lr.id = ?");
        $stmt->execute([$id]);
        $leave = $stmt->fetch();

        // 3. Send Email Notification
        if ($leave && !empty($leave['gmail']) && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $smtpPort;

            $mail->setFrom($smtpUser, 'BamBam HR');
            $mail->addAddress($leave['gmail']);
            $mail->Subject = "Leave Request Update: " . $status;
            $mail->Body = "Hi " . $leave['name'] . ",\n\nYour leave request for " . $leave['start_date'] . " has been " . strtoupper($status) . ".\n\nRegards,\nBamBam Admin";
            $mail->send();
        }

        echo json_encode(['success' => true, 'message' => "Request marked as $status and staff notified via email."]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
}
