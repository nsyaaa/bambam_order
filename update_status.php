<?php
include 'db.php';

// 1. Handle Global Store Status Toggle
if (isset($_POST['status']) && !isset($_POST['id'])) {
    $status = $_POST['status']; // Expected: 'open' or 'closed'
    
    $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'global_store_status'");
    if ($stmt->execute([$status])) {
        echo "Success";
    }
} 
// 2. Handle Order Status Update (Existing Functionality)
else if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if($stmt->execute([$status, $id])) {
        echo "Success";
    }
}