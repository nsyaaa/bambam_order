<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/vendor/autoload.php')) { require_once __DIR__ . '/vendor/autoload.php'; }

session_start();
include_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Auto-fix: Check if 'customization' column exists, if not add it
    try {
        // Add payment status columns if they don't exist (for robustness)
        try { $pdo->query("SELECT payment_status FROM orders LIMIT 1"); } catch (Throwable $e) { $pdo->exec("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(50) NOT NULL DEFAULT 'Pending' AFTER payment_method"); }
        try { $pdo->query("SELECT paid_at FROM orders LIMIT 1"); } catch (Throwable $e) { $pdo->exec("ALTER TABLE orders ADD COLUMN paid_at TIMESTAMP NULL DEFAULT NULL AFTER payment_status"); }
        try { $pdo->query("SELECT processed_by_staff_id FROM orders LIMIT 1"); } catch (Throwable $e) { $pdo->exec("ALTER TABLE orders ADD COLUMN processed_by_staff_id INT NULL AFTER paid_at"); }
        $pdo->query("SELECT customization FROM order_items LIMIT 1");
    } catch (Throwable $e) {
        try {
            $pdo->exec("ALTER TABLE order_items ADD COLUMN customization TEXT DEFAULT NULL");
        } catch (Throwable $ex) {
            // Continue and let the transaction fail if schema is still wrong
        }
    }
    
    // Auto-fix: Check if 'address' column exists
    try {
        $pdo->query("SELECT address FROM orders LIMIT 1");
    } catch (Throwable $e) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN address TEXT DEFAULT NULL");
    }
    
    // Auto-fix: Check if 'customer_phone' column exists
    try {
        $pdo->query("SELECT customer_phone FROM orders LIMIT 1");
    } catch (Throwable $e) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN customer_phone VARCHAR(20) DEFAULT NULL AFTER customer_name");
    }

    try {
        $pdo->beginTransaction();

        // Server-side validation: Check global status first
        $stmtGlobal = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'global_store_status'");
        $globalStatus = $stmtGlobal->fetchColumn();

        if ($globalStatus === 'closed') {
            throw new Exception("Store is currently closed. Please try again later.");
        }

        // Server-side validation: Check if branch is open
        $branchName = $_POST['branch'] ?? null;
        
        if (!$branchName) {
            throw new Exception("No branch selected. Please select a branch first.");
        }

        $stmtBranch = $pdo->prepare("SELECT is_open FROM branches WHERE name = ?");
        $stmtBranch->execute([$branchName]);
        $branchStatus = $stmtBranch->fetchColumn();

        if ($branchStatus === 0 || $branchStatus === '0') {
            throw new Exception("Selected branch is currently unavailable.");
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

        // --- TOYYIBPAY INTEGRATION ---
        if ($_POST['payment_method'] === 'ToyyibPay') {
            $billAmount = (int)round((float)$_POST['total'] * 100); // Convert RM to cents
            
            $bill_data = array(
                'userSecretKey' => $toyyibpay_secret_key,
                'categoryCode' => $toyyibpay_category_code,
                'billName' => 'BamBam Burger Order #' . $orderId,
                'billDescription' => 'Payment for Order #' . $orderId,
                'billPriceSetting' => 1,
                'billPayorInfo' => 1,
                'billAmount' => $billAmount,
                'billReturnUrl' => 'http://' . $_SERVER['HTTP_HOST'] . rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/') . '/toyyibpay_return.php?order_id=' . $orderId,
                'billCallbackUrl' => 'http://' . $_SERVER['HTTP_HOST'] . rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/') . '/toyyibpay_callback.php',
                'billExternalReferenceNo' => $orderId,
                'billTo' => $customerName,
                'billEmail' => $_SESSION['user_email'] ?? 'customer@bambam.com',
                'billPhone' => $customerPhone ?? '0123456789',
            );

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_URL, $toyyibpay_url . 'index.php/api/createBill');  
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $bill_data);
            $result = curl_exec($curl);
            curl_close($curl);
            
            $obj = json_decode($result);

            if (isset($obj[0]->BillCode)) {
                $pdo->commit(); // Save order to DB before leaving the site
                echo json_encode(['success' => true, 'redirect_url' => $toyyibpay_url . $obj[0]->BillCode]);
                exit;
            } else {
                throw new \Exception("ToyyibPay Error: Unable to generate payment link.");
            }
        }
        // --- END TOYYIBPAY ---

        // 3. Insert into 'order_items' table
        $items = json_decode($_POST['items'], true);
        if ($items) {
            // Server-side Validation: Custom burgers must contain at least a Bun or a Patty
            foreach ($items as $item) {
                if (isset($item['name']) && $item['name'] === 'Custom Burger') {
                    $customization = strtolower($item['note'] ?? ($item['customization'] ?? ''));
                    $hasBase = str_contains($customization, 'bun') || str_contains($customization, 'patty');
                    
                    if (!$hasBase) {
                        throw new Exception("Custom Burger Error: You must include at least one Bun or a Patty. Orders consisting only of toppings are not allowed.");
                    }
                }
            }

            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, item_name, protein, variant, price, qty, customization) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                // Combine burger layers (customization) and any additional user notes
                $layers = $item['customization'] ?? '';
                $userNote = $item['note'] ?? '';
                $combinedDetails = $layers;
                if (!empty($userNote)) {
                    $combinedDetails .= (!empty($combinedDetails) ? " | Extra Note: " : "") . $userNote;
                }

                $itemStmt->execute([
                    $orderId,
                    $item['name'],
                    $item['protein'],
                    $item['variant'],
                    $item['price'],
                    $item['qty'],
                    $combinedDetails
                ]);
            }
        }

        $pdo->commit();

        // 4. Send Email Notification
        $emailStatus = "Not attempted";
        $emailError = "";

        // Use PHPMailer for reliable delivery (SMTP)
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            try {
                $mail = new PHPMailer(true);
                
                // Server settings (Variables from db.php)
                $mail->isSMTP();
                $mail->Host       = $smtpHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtpUser;
                $mail->Password   = $smtpPass;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
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
            } catch (Throwable $e) {
                $emailStatus = "PHPMailer Failed";
                $emailError = $e->getMessage();
            }
        } else {
            // Fallback: PHPMailer not installed
            $emailStatus = "PHPMailer not installed. Please run 'composer require phpmailer/phpmailer'.";
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
    } catch (Throwable $e) {
        // Throwable catches both Exceptions and Fatal Errors (PHP 7+)
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