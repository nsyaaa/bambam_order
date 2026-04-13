<?php
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_FILES['payment_proof'])) {
    $orderId = (int)$_POST['order_id'];
    
    $uploadDir = 'uploads/receipts/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileExtension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
    $fileName = 'receipt_resubmit_' . time() . '_' . uniqid() . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetPath)) {
        try {
            // Reset status back to Pending and clear rejection reason
            $stmt = $pdo->prepare("UPDATE orders SET receipt_img = ?, status = 'Pending', payment_status = 'Pending', payment_reject_reason = NULL WHERE id = ?");
            $stmt->execute([$fileName, $orderId]);
            
            header("Location: receipt.php?id=" . $orderId . "&reuploaded=1");
            exit;
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    } else {
        die("Failed to upload file.");
    }
} else {
    header("Location: index.php");
    exit;
}