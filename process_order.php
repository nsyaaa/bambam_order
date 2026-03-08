<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Capture POST data
    $name = $_POST['customer_name'] ?? 'Unknown';
    $method = $_POST['payment_method'] ?? 'Unknown';
    $total = $_POST['total_amount'] ?? 0.00; // This captures the RM value
    $branch = $_POST['branch'] ?? 'Main';
    $order_type = $_POST['order_type'] ?? 'Take-Away';
    $cart_json = $_POST['cart_json'] ?? '[]';
    
    $items = json_decode($cart_json, true);
    $receipt_name = null;
    $user_id = $_SESSION['user_id'] ?? null;

    // 2. Handle Receipt Upload
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === 0) {
        $receipt_name = time() . "_" . $_FILES['receipt']['name'];
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        move_uploaded_file($_FILES['receipt']['tmp_name'], "uploads/" . $receipt_name);
    }

    // Fetch registered phone number if user is logged in
    $customerPhone = null;
    if ($user_id) {
        $stmtPhone = $pdo->prepare("SELECT phone FROM users WHERE id = ?");
        $stmtPhone->execute([$user_id]);
        $customerPhone = $stmtPhone->fetchColumn();
    }

    try {
        $pdo->beginTransaction();

        // 3. Insert into 'orders' table
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, branch, order_type, customer_name, customer_phone, total_amount, payment_method, receipt_img, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$user_id, $branch, $order_type, $name, $customerPhone, $total, $method, $receipt_name]);
        $order_id = $pdo->lastInsertId();

        // 4. Insert each burger into 'order_items'
        if (!empty($items)) {
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, item_name, protein, variant, price, qty) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $itemStmt->execute([
                    $order_id, 
                    $item['name'], 
                    $item['protein'] ?? null,
                    $item['variant'], 
                    $item['price'], 
                    $item['qty']
                ]);
            }
        }

        $pdo->commit();
        
        // 5. Success! Clear cart and go to Receipt Tracker
        echo "<script>
                localStorage.removeItem('bambam_cart'); 
                window.location.href='receipt.php?id=$order_id';
              </script>";

    } catch (Exception $e) {
        $pdo->rollBack();
        if (strpos($e->getMessage(), "Unknown column") !== false) {
            die("<div style='font-family:sans-serif; padding:20px; text-align:center; background:#ffebee; color:#c62828; border:1px solid #ef9a9a; border-radius:10px;'><h2>Database Error</h2><p>Your database is missing required columns.</p><p>👉 Please run <a href='update_db.php' style='font-weight:bold; color:#b71c1c;'>update_db.php</a> to fix this automatically.</p></div>");
        }
        die("Order Failed: " . $e->getMessage());
    }
}
?>