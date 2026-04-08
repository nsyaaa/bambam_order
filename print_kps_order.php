<?php
session_start();
include 'db.php';

// Security check: ensure staff is logged in
if (
    !isset($_SESSION['staff_logged_in']) ||
    $_SESSION['staff_logged_in'] !== true ||
    !isset($_SESSION['branch_id'])
) {
    die("Unauthorized access.");
}

$order_id = $_GET['id'] ?? 0;

if (!$order_id) {
    die("Order ID not provided.");
}

$order = null;
$order_items = [];

try {
    // Fetch order details
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Fetch order items
        $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$order_id]);
        $order_items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

if (!$order) {
    die("Order not found.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Order Slip #<?php echo $order_id; ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #fff;
            -webkit-print-color-adjust: exact; /* For printing background colors */
        }
        .order-slip {
            width: 300px; /* Standard slip width */
            margin: 0 auto;
            border: 1px dashed #999;
            padding: 15px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
            color: #ff5100;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 12px;
        }
        .details p {
            margin: 5px 0;
            font-size: 14px;
        }
        .items {
            border-top: 1px dashed #999;
            border-bottom: 1px dashed #999;
            padding: 15px 0;
            margin: 15px 0;
        }
        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 16px;
            font-weight: bold;
        }
        .item span:first-child {
            flex-grow: 1;
        }
        .customization {
            font-size: 12px;
            color: #e67e22;
            margin-left: 15px;
            font-weight: normal;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 20px;
        }
        @media print {
            body { margin: 0; padding: 0; }
            .order-slip { border: none; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="order-slip">
        <div class="header">
            <h2>BamBam Burger</h2>
            <p>Kitchen Order Slip</p>
            <p>Order #<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></p>
        </div>
        <div class="details">
            <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($order['created_at'])); ?></p>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name'] ?? 'Walk-in'); ?></p>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($order['order_type']); ?></p>
            <p><strong>Branch:</strong> <?php echo htmlspecialchars($order['branch']); ?></p>
        </div>
        <div class="items">
            <?php foreach ($order_items as $item): ?>
                <div class="item">
                    <span><?php echo $item['qty']; ?>x <?php echo htmlspecialchars($item['item_name']); ?></span>
                    <span><?php echo htmlspecialchars($item['variant'] ?? ''); ?></span>
                </div>
                <?php if (!empty($item['protein'])): ?>
                    <p class="customization">Protein: <?php echo htmlspecialchars($item['protein']); ?></p>
                <?php endif; ?>
                <?php if (!empty($item['customization'])): ?>
                    <p class="customization">Note: <?php echo htmlspecialchars($item['customization']); ?></p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div class="footer">
            <p>Thank you!</p>
            <p>Printed: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
            // Optionally close the window after printing
            // window.onafterprint = function() { window.close(); };
        };
    </script>
</body>
</html>
```