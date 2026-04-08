<?php
include_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Get data from toyyibPay redirect
$status_id = $_GET['status_id'] ?? 3; 
$order_id = $_GET['order_id'] ?? 0;
$transaction_id = $_GET['transaction_id'] ?? 'N/A';

$isSuccess = ($status_id == 1);

if ($isSuccess && $order_id) {
    // Update your database to 'Paid'
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Paid', paid_at = NOW() WHERE id = ?");
    $stmt->execute([$order_id]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status | BamBam Burger</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ff9f1c;
            --success: #2ec4b6;
            --danger: #e71d36;
            --glass: rgba(255, 255, 255, 0.1);
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background: #0f0f0f url('images/ch.png') center/cover no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .receipt-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }

        .icon-box {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .success-icon { background: var(--success); box-shadow: 0 0 20px rgba(46, 196, 182, 0.4); }
        .fail-icon { background: var(--danger); box-shadow: 0 0 20px rgba(231, 29, 54, 0.4); }

        h1 {
            font-family: 'Fraunces', serif;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .details {
            margin: 30px 0;
            text-align: left;
            border-top: 1px dashed rgba(255,255,255,0.2);
            border-bottom: 1px dashed rgba(255,255,255,0.2);
            padding: 20px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
            color: rgba(255,255,255,0.7);
        }

        .row span:last-child {
            color: white;
            font-weight: 600;
        }

        .btn {
            display: inline-block;
            background: var(--primary);
            color: #000;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
            margin-top: 20px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 159, 28, 0.3);
        }

        .secondary-link {
            display: block;
            margin-top: 20px;
            color: rgba(255,255,255,0.5);
            font-size: 13px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="receipt-card">
    <?php if ($isSuccess): ?>
        <script>localStorage.removeItem('bambam_cart');</script>
        <div class="icon-box success-icon">✓</div>
        <h1>Payment Received!</h1>
        <p>We are starting on your burger right now.</p>
        
        <div class="details">
            <div class="row"><span>Order ID</span><span>#BAM-<?php echo $order_id; ?></span></div>
            <div class="row"><span>Transaction ID</span><span><?php echo htmlspecialchars($transaction_id); ?></span></div>
            <div class="row"><span>Status</span><span style="color: var(--success);">Paid</span></div>
            <div class="row"><span>Date</span><span><?php echo date('d M Y, h:i A'); ?></span></div>
        </div>

        <a href="receipt.php?id=<?php echo $order_id; ?>" class="btn">Track My Order</a>
    <?php else: ?>
        <div class="icon-box fail-icon">✕</div>
        <h1>Payment Failed</h1>
        <p>Something went wrong with the transaction.</p>
        <a href="menu.php" class="btn">Return to Menu</a>
    <?php endif; ?>

    <a href="index.php" class="secondary-link">Back to Homepage</a>
</div>

</body>
</html>