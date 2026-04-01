<?php 
include 'header.php'; 
include_once 'db.php';
?>

<style>
    body {
        background: url('images/ch.png') no-repeat center center fixed;
        background-size: cover;
        padding-top: 100px;
    }
    .history-container {
        max-width: 800px;
        margin: 20px auto 40px;
        padding: 20px;
    }
    .page-title {
        text-align: center;
        background: white;
        padding: 15px;
        border-radius: 50px;
        border: 2px solid #ff5100;
        color: #ff5100;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .order-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-left: 10px solid #ff5100;
        transition: transform 0.2s;
    }
    .order-card:hover {
        transform: translateY(-5px);
    }
    .order-header {
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 10px;
        font-weight: bold;
        color: #333;
    }
    .order-date { color: #888; font-size: 14px; font-weight: normal; }
    .order-items { margin-bottom: 15px; color: #555; line-height: 1.6; }
    .order-details-extra {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-top: 10px;
        border-top: 1px dashed #eee;
    }
    .order-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: bold;
    }
    .order-total { color: #ff5100; font-size: 18px; }
    .order-status {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 14px;
        text-transform: uppercase;
    }
    .status-completed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
    
    .empty-history {
        text-align: center;
        padding: 50px 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .empty-history p {
        font-size: 18px;
        color: #666;
        margin-bottom: 20px;
    }
    .shop-btn {
        display: inline-block;
        background: #ff5100;
        color: white;
        padding: 12px 30px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s;
    }
    .shop-btn:hover {
        background: #e04600;
    }
</style>

<div class="history-container animate-fade-up">
    <div class="page-title">
        <h2 style="margin:0;">🛍️ Purchase History</h2>
    </div>

    <div id="history-content">
        <?php
        if (!isset($_SESSION['user_id'])) {
            echo '<div class="empty-history"><p>Please login to view your history.</p><a href="login.php" class="shop-btn">Login</a></div>';
        } else {
            // Fetch orders from Database
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$_SESSION['user_id']]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($orders) === 0) {
                echo '<div class="empty-history">
                        <p>You haven\'t made any purchases yet.</p>
                        <a href="menu.php" class="shop-btn">Start Ordering</a>
                      </div>';
            } else {
                foreach ($orders as $order) {
                    // Fetch items for this order
                    $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                    $stmtItems->execute([$order['id']]);
                    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                    
                    $itemsDisplay = [];
                    $reorderItems = [];

                    foreach ($items as $item) {
                        $variantText = $item['variant'] ? "({$item['variant']})" : "";
                        $itemsDisplay[] = "{$item['qty']}x {$item['item_name']} <small>$variantText</small>";
                        
                        // Prepare data for Reorder button
                        $reorderItems[] = [
                            'id' => $item['item_name'] . '-' . ($item['variant']??'') . '-' . ($item['protein']??''),
                            'name' => $item['item_name'],
                            'price' => (float)$item['price'],
                            'qty' => (int)$item['qty'],
                            'variant' => $item['variant'],
                            'protein' => $item['protein'] ?? '',
                            'note' => $item['customization'] ?? ''
                        ];
                    }
                    
                    $itemsHtml = implode('<br>', $itemsDisplay);
                    $reorderJson = htmlspecialchars(json_encode($reorderItems), ENT_QUOTES, 'UTF-8');
                    $date = date('d M Y, h:i A', strtotime($order['created_at']));
                    $statusClass = ($order['status'] == 'Completed' || $order['status'] == 'Served') ? 'status-completed' : 'status-cancelled';
                    
                    // Check if order can be rated (Completed/Served/Delivered AND not rated yet)
                    $statusLower = strtolower($order['status']);
                    $canRate = in_array($statusLower, ['completed', 'served', 'delivered']) && empty($order['rating']);
                    $rateBtn = $canRate ? "<a href='receipt.php?id={$order['id']}' class='shop-btn' style='padding:5px 15px; font-size:12px; background:#2ecc71; margin-right:5px; text-decoration:none;'>Rate</a>" : "";

                    echo "
                    <div class='order-card'>
                        <div class='order-header'>
                            <span>Order #{$order['id']}</span>
                            <span class='order-date'>$date</span>
                        </div>
                        <div class='order-items'>$itemsHtml</div>
                        <div class='order-details-extra'>
                            <span>📍 {$order['branch']}</span>
                            <span>💳 {$order['payment_method']}</span>
                        </div>
                        <div class='order-footer'>
                            <span class='order-total'>RM " . number_format($order['total_amount'], 2) . "</span>
                            <div style='display:flex; align-items:center; gap:10px;'>
                                <button class='shop-btn' style='padding:5px 15px; font-size:12px; cursor:pointer;' onclick='reorder($reorderJson)'>Reorder</button>
                                $rateBtn
                                <span class='order-status $statusClass'>{$order['status']}</span>
                            </div>
                        </div>
                    </div>";
                }
            }
        }
        ?>
    </div>
</div>

<script>
function reorder(items) {
    if (items && items.length > 0) {
        let currentCart = JSON.parse(localStorage.getItem('bambam_cart')) || [];
        
        items.forEach(newItem => {
            const existing = currentCart.find(i => i.id === newItem.id);
            if (existing) existing.qty += newItem.qty;
            else currentCart.push(newItem);
        });
        
        localStorage.setItem('bambam_cart', JSON.stringify(currentCart));
        window.location.href = 'menu.php?cart_open=true';
    }
}
</script>

<?php include 'footer.php'; ?>