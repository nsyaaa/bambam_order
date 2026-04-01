<?php
session_start();
include_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Auto-fix: Check if 'customization' column exists, if not add it
    try {
        // Add payment status columns if they don't exist (for robustness)
        try { $pdo->query("SELECT payment_status FROM orders LIMIT 1"); } catch (Exception $e) { $pdo->exec("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(50) NOT NULL DEFAULT 'Pending' AFTER payment_method"); }
        try { $pdo->query("SELECT paid_at FROM orders LIMIT 1"); } catch (Exception $e) { $pdo->exec("ALTER TABLE orders ADD COLUMN paid_at TIMESTAMP NULL DEFAULT NULL AFTER payment_status"); }
        try { $pdo->query("SELECT processed_by_staff_id FROM orders LIMIT 1"); } catch (Exception $e) { $pdo->exec("ALTER TABLE orders ADD COLUMN processed_by_staff_id INT NULL AFTER paid_at"); }
        $pdo->query("SELECT customization FROM order_items LIMIT 1");
    } catch (Exception $e) {
        try {
            $pdo->exec("ALTER TABLE order_items ADD COLUMN customization TEXT DEFAULT NULL");
        } catch (Exception $ex) {
            // Continue and let the transaction fail if schema is still wrong
        }
    }
    
    // Auto-fix: Check if 'address' column exists
    try {
        $pdo->query("SELECT address FROM orders LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN address TEXT DEFAULT NULL");
    }
    
    // Auto-fix: Check if 'customer_phone' column exists
    try {
        $pdo->query("SELECT customer_phone FROM orders LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN customer_phone VARCHAR(20) DEFAULT NULL AFTER customer_name");
    }

    try {
        $pdo->beginTransaction();

        // Server-side validation: Check global status first
        $stmtGlobal = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'global_store_status'");
        $globalStatus = $stmtGlobal->fetchColumn();

        if ($globalStatus === 'closed') {
            throw new Exception("All branches are currently closed and cannot accept orders.");
        }

        // Server-side validation: Check if branch is open
        $branchName = $_POST['branch'] ?? 'Kangar';
        $stmtBranch = $pdo->prepare("SELECT is_open FROM branches WHERE name = ?");
        $stmtBranch->execute([$branchName]);
        $branchStatus = $stmtBranch->fetchColumn();

        if ($branchStatus === 0 || $branchStatus === '0') {
            throw new Exception("The selected branch ($branchName) is currently closed and cannot accept orders.");
        }


        // 1. Handle File Upload (Receipt)
        $fileName = null;
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/receipts/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
            $fileName = 'receipt_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetPath)) {
                throw new Exception("Failed to upload receipt.");
            }
        }

        // 2. Insert into 'orders' table
        // Fixed column names to match DB schema (total -> total_amount, sender_name -> customer_name, payment_proof -> receipt_img)
        // Added user_id for history tracking
        $user_id = $_SESSION['user_id'] ?? null;
        
        // Fix: Ensure customer_name is not null for Cash orders
        $customerName = !empty($_POST['sender_name']) ? $_POST['sender_name'] : ($_SESSION['user_name'] ?? 'Walk-in Customer');
        
        // Fetch registered phone number if user is logged in
        $customerPhone = null;
        if ($user_id) {
            $stmtPhone = $pdo->prepare("SELECT phone FROM users WHERE id = ?");
            $stmtPhone->execute([$user_id]);
            $customerPhone = $stmtPhone->fetchColumn();
        }

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, branch, order_type, payment_method, payment_status, total_amount, customer_name, customer_phone, receipt_img, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([
            $user_id,
            $_POST['branch'],
            $_POST['order_type'],
            $_POST['payment_method'],
            'Pending', // Default payment_status for all new orders
            $_POST['total'],
            $customerName,
            $customerPhone,
            $fileName,
            $_POST['address'] ?? null
        ]);

        $orderId = $pdo->lastInsertId();

        // 3. Insert into 'order_items' table
        $items = json_decode($_POST['items'], true);
        if ($items) {
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, item_name, protein, variant, price, qty, customization) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['name'],
                    $item['protein'],
                    $item['variant'],
                    $item['price'],
                    $item['qty'],
                    $item['note'] ?? ($item['customization'] ?? '')
                ]);
            }
        }

        $pdo->commit();

        // 4. Send Email Notification
        $emailStatus = "Not attempted";
        $emailError = "";

        // Use PHPMailer for reliable delivery (SMTP)
        $autoloadPath = __DIR__ . '/vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            try {
                // Server settings (Variables from db.php)
                $mail->isSMTP();
                $mail->Host       = $smtpHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtpUser;
                $mail->Password   = $smtpPass;
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $smtpPort;

                // A. Email to Admin
                $mail->setFrom($smtpUser, 'BamBam Burger System');
                $mail->addAddress($smtpUser); // Send to Admin
                
                $mail->isHTML(false);
                $mail->Subject = "New Order Received - #BAM-" . $orderId;
                $addrText = !empty($_POST['address']) ? "\nAddress: " . $_POST['address'] : "";
                $mail->Body    = "New order received from " . $customerName . ".\nTotal: RM " . $_POST['total'] . "\nBranch: " . $_POST['branch'] . "\nType: " . $_POST['order_type'] . $addrText;
                $mail->send();

                // B. Email to Customer (if registered)
                if ($user_id) {
                    $stmtUser = $pdo->prepare("SELECT gmail FROM users WHERE id = ?");
                    $stmtUser->execute([$user_id]);
                    $customerEmail = $stmtUser->fetchColumn();

                    if ($customerEmail) {
                        $mail->clearAddresses(); // Clear admin address
                        $mail->addAddress($customerEmail);
                        $mail->Subject = "Order Confirmation - #BAM-" . $orderId;
                        $mail->Body    = "Hi " . $customerName . ",\n\nThank you for your order!\n\nOrder ID: #BAM-" . $orderId . "\nTotal: RM " . $_POST['total'] . "\nBranch: " . $_POST['branch'] . "\n\nWe are preparing your meal now.";
                        $mail->send();
                    }
                }
                $emailStatus = "Sent via PHPMailer";
            } catch (Exception $e) {
                $emailStatus = "PHPMailer Failed";
                $emailError = $mail->ErrorInfo;
            }
        } else {
            // Fallback: PHPMailer not installed
            $emailStatus = "PHPMailer not installed. Expected at: " . $autoloadPath;
            // Try basic mail() as last resort (works for local Laragon mail catcher)
            $headers = "From: no-reply@bambamburger.com";
            @mail("bambamburgerperlis@gmail.com", "New Order #$orderId", "Order from $customerName", $headers);
        }

        echo json_encode([
            'success' => true, 
            'order_id' => $orderId, 
            'email_status' => $emailStatus, 
            'email_error' => $emailError
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $msg = $e->getMessage();
        if (strpos($msg, "Unknown column 'customization'") !== false) {
            $msg = "Database Error: Missing 'customization' column. Please run update_db.php.";
        }
        echo json_encode(['success' => false, 'message' => $msg]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>