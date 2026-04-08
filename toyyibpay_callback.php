<?php
include 'db.php';

// ToyyibPay sends payment data via POST to this URL
$refno = $_POST['order_id']; // This corresponds to your billExternalReferenceNo
$status = $_POST['status']; // 1=Success, 2=Pending, 3=Fail

if ($status == 1) {
    // The reference number (Order ID) is returned in the 'order_id' POST field
    $orderId = $_POST['order_id']; 
    
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Paid', paid_at = NOW() WHERE id = ?");
    $stmt->execute([$orderId]);
}

echo "OK"; // ToyyibPay expects a response