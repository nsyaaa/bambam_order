<?php
include_once 'db.php';

// Handle Order Completion (Customer/Staff Tap)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_received'], $_POST['order_id'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = 'Completed' WHERE id = ?");
    $stmt->execute([$_POST['order_id']]);

    // Return JSON for AJAX calls
    if (isset($_POST['ajax'])) {
        echo json_encode(['success' => true]);
        exit;
    }

    header("Location: receipt.php?id=" . $_POST['order_id']);
    exit;
}

// Handle Rating Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rating'], $_POST['order_id'])) {
    // Auto-create columns if they don't exist
    try {
        $pdo->query("SELECT review_image FROM orders LIMIT 1");
    } catch (Exception $e) {
        // Add columns for advanced reviews if missing
        $pdo->exec("ALTER TABLE orders 
            ADD COLUMN rating INT DEFAULT NULL, 
            ADD COLUMN review TEXT DEFAULT NULL,
            ADD COLUMN review_image VARCHAR(255) DEFAULT NULL,
            ADD COLUMN reaction VARCHAR(50) DEFAULT NULL,
            ADD COLUMN admin_reply TEXT DEFAULT NULL
        ");
    }

    // Handle Image Upload
    $imagePath = null;
    if (!empty($_FILES['review_image']['name'])) {
        $targetDir = "uploads/reviews/";
        if (!is_dir($targetDir))
            mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['review_image']['name']);
        if (move_uploaded_file($_FILES['review_image']['tmp_name'], $targetDir . $fileName)) {
            $imagePath = $fileName;
        }
    }

    $stmt = $pdo->prepare("UPDATE orders SET rating = ?, review = ?, reaction = ?, review_image = ? WHERE id = ?");
    $stmt->execute([$_POST['rating'], $_POST['review'], $_POST['reaction'] ?? null, $imagePath, $_POST['order_id']]);
    header("Location: receipt.php?id=" . $_POST['order_id'] . "&rated=1");
    exit;
}

include 'header.php'; // Ensures your navbar stays at the top

// 1. DATABASE LOGIC: Get Order Status
$order_id = $_GET['id'] ?? 0;
$raw_status = 'pending'; // Default starting point

if ($order_id) {
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as staff_name 
        FROM orders o
        LEFT JOIN users u ON o.processed_by_staff_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if ($order) {
        $raw_status = strtolower($order['status']);
        $isDelivery = isset($order['order_type']) && strtolower($order['order_type']) === 'delivery';
        $isPaid = isset($order['payment_status']) && $order['payment_status'] === 'Paid';

        // Fetch Order Items
        $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$order_id]);
        $order_items = $stmtItems->fetchAll();
    }
} else {
    // Default to false if no order is found to prevent errors
    $isDelivery = false;
    $isPaid = false;
}

// 2. CONFIGURATION: Map DB Status to Design
// Fix: Map various DB statuses to the 4 visual stages
$status_map = [
    'pending' => 'placed',
    'placed' => 'placed',
    'confirmed' => 'placed',
    'preparing' => 'preparing',
    'cooking' => 'preparing',
    'ready' => 'delivery',
    'pickup' => 'delivery',
    'delivery' => 'delivery',
    'shipped' => 'delivery',
    'completed' => 'delivered',
    'served' => 'delivered',
    'delivered' => 'delivered'
];

$status = $status_map[$raw_status] ?? 'placed';

$stages = [
    'placed' => [
        'percent' => '25%',
        'title' => 'ORDER PLACED',
        'desc' => 'We have received your order. Hang tight!',
        'image' => 'images/order_placed.png',
        'fallback' => '📋'
    ],
    'preparing' => [
        'percent' => '50%',
        'title' => 'IN PROGRESS...',
        'desc' => 'Your Bambam Burger is being expertly prepared.',
        'image' => 'images/t1.png',
        'fallback' => '🔥'
    ],
    'delivery' => [
        'percent' => '75%',
        'title' => $isDelivery ? 'ON THE WAY!' : 'READY FOR PICKUP',
        'desc' => $isDelivery ? 'Our rider is speeding to your location.' : 'Your order is ready at the counter.',
        'image' => $isDelivery ? 'images/delivery_scooter.png' : 'images/t1.png',
        'fallback' => $isDelivery ? '🛵' : '🛍️'
    ],
    'delivered' => [
        'percent' => '100%',
        'title' => $isDelivery ? 'ENJOY!' : 'ORDER COLLECTED',
        'desc' => $isDelivery ? 'Your delicious order has arrived. Makan time!' : 'Thank you for your order!',
        'image' => 'images/t1.png',
        'fallback' => '✅'
    ]
];

$current = $stages[$status] ?? $stages['placed'];
?>

<style>
    /* THEME SETUP */
    body {
        background: url('images/ch.png') no-repeat center center fixed;
        background-size: cover;
        font-family: 'DM Sans', sans-serif;
        color: white;
    }

    .tracker-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        padding-top: 50px;
    }

    .bambam-tracker {
        background: #1a1a1a;
        width: 100%;
        max-width: 420px;
        padding: 40px 30px;
        border-radius: 40px;
        text-align: center;
        box-shadow: 0 40px 100px rgba(0, 0, 0, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.05);
        position: relative;
    }

    /* PROGRESS DOTS & LINE */
    .status-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        padding: 0 10px;
    }

    .dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #333;
        position: relative;
        transition: 0.5s;
    }

    .dot.active {
        background: #ff5100;
        box-shadow: 0 0 15px #ff5100;
    }

    .progress-bar-container {
        width: 100%;
        height: 6px;
        background: #333;
        border-radius: 10px;
        margin-bottom: 40px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #ff5100, #ff8c00);
        width:
            <?php echo $current['percent']; ?>
        ;
        transition: width 1.5s ease-in-out;
    }

    /* STAGE ILLUSTRATION */
    .illustration-stage {
        height: 220px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 30px;
        position: relative;
    }

    .main-status-img {
        width: 100%;
        max-width: 220px;
        filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.6));
        z-index: 5;
    }

    /* ANIMATIONS BASED ON PHP STATUS */
    .main-status-img.anim-preparing,
    .status-fallback.anim-preparing {
        animation: sizzle 0.3s infinite alternate;
    }

    .illustration-stage.glow-effect::before {
        content: '';
        position: absolute;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(255, 81, 0, 0.3) 0%, transparent 70%);
        animation: glow 2s infinite;
    }

    .main-status-img.anim-delivery,
    .status-fallback.anim-delivery {
        animation: bounce 0.4s infinite;
    }

    /* TEXT ELEMENTS */
    .status-title {
        color: #ff5100;
        font-weight: 900;
        letter-spacing: 2px;
        font-size: 1.6rem;
        margin: 0 0 10px 0;
        text-transform: uppercase;
    }

    .status-desc {
        color: #aaa;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 30px;
    }

    /* REUSABLE KEYFRAMES */
    @keyframes sizzle {
        from {
            transform: translateY(0);
        }

        to {
            transform: translateY(-4px);
        }
    }

    @keyframes glow {

        0%,
        100% {
            opacity: 0.5;
            transform: scale(1);
        }

        50% {
            opacity: 1;
            transform: scale(1.2);
        }
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0) rotate(0);
        }

        50% {
            transform: translateY(-5px) rotate(1deg);
        }
    }

    .order-meta {
        border-top: 1px solid #333;
        padding-top: 20px;
        font-size: 0.8rem;
        color: #666;
    }

    /* RECEIPT DETAILS */
    .receipt-card {
        background: white;
        color: #333;
        padding: 30px;
        border-radius: 15px;
        margin-top: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        font-family: 'Courier New', Courier, monospace;
        text-align: left;
        position: relative;
        overflow: hidden;
    }

    .receipt-header-section {
        text-align: center;
        border-bottom: 2px dashed #ccc;
        padding-bottom: 15px;
        margin-bottom: 15px;
    }

    .receipt-logo {
        font-family: 'Fraunces', serif;
        font-weight: 800;
        font-size: 24px;
        color: #ff5100;
        margin-bottom: 5px;
    }

    .receipt-info {
        font-size: 12px;
        color: #666;
        line-height: 1.4;
    }

    .receipt-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        font-size: 12px;
        margin-bottom: 15px;
        border-bottom: 2px dashed #ccc;
        padding-bottom: 15px;
    }

    .meta-label {
        font-weight: bold;
        color: #888;
    }

    .meta-val {
        font-weight: bold;
        color: #000;
        text-align: right;
    }

    .receipt-items-table {
        width: 100%;
        font-size: 13px;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .receipt-items-table th {
        text-align: left;
        color: #888;
        font-size: 10px;
        text-transform: uppercase;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }

    .receipt-items-table td {
        padding: 8px 0;
        vertical-align: top;
    }

    .receipt-items-table .item-price {
        text-align: right;
    }

    .receipt-total-section {
        border-top: 2px dashed #ccc;
        padding-top: 15px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        font-size: 14px;
    }

    .grand-total {
        font-size: 18px;
        font-weight: bold;
        color: #ff5100;
        margin-top: 10px;
    }

    .print-btn {
        display: block;
        width: 100%;
        background: #333;
        color: white;
        border: none;
        padding: 12px;
        border-radius: 8px;
        margin-top: 20px;
        cursor: pointer;
        font-weight: bold;
        text-transform: uppercase;
        transition: 0.3s;
    }

    .print-btn:hover {
        background: #ff5100;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .receipt-card,
        .receipt-card * {
            visibility: visible;
        }

        .receipt-card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            box-shadow: none;
        }

        .print-btn {
            display: none;
        }
    }

    /* CONFIRM BUTTON */
    .btn-confirm {
        background: #2ecc71;
        color: white;
        border: none;
        padding: 15px 30px;
        font-size: 1rem;
        font-weight: bold;
        border-radius: 50px;
        cursor: pointer;
        box-shadow: 0 10px 20px rgba(46, 204, 113, 0.4);
        transition: transform 0.2s, box-shadow 0.2s;
        margin-top: 20px;
        margin-bottom: 20px;
        width: 100%;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .btn-confirm:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 25px rgba(46, 204, 113, 0.6);
        background: #27ae60;
    }

    .btn-confirm:active {
        transform: translateY(1px);
    }

    /* RATING SECTION */
    .rating-section {
        background: #fff;
        color: #333;
        padding: 25px;
        border-radius: 15px;
        margin-top: 20px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        display: none;
        /* Hidden by default, toggled by JS/PHP */
    }

    .rating-stars {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 15px 0;
    }

    .star {
        font-size: 30px;
        color: #ddd;
        cursor: pointer;
        transition: color 0.2s;
    }

    .star.selected,
    .star:hover {
        color: #ffcc00;
    }

    .rating-textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        margin-bottom: 15px;
        font-family: inherit;
    }

    .btn-submit-rating {
        background: #ff5100;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        width: 100%;
    }

    /* REACTIONS & UPLOAD */
    .reaction-group {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .reaction-opt {
        cursor: pointer;
        opacity: 0.5;
        transition: 0.3s;
        text-align: center;
    }

    .reaction-opt:hover,
    .reaction-opt.active {
        opacity: 1;
        transform: scale(1.1);
    }

    .reaction-opt span {
        display: block;
        font-size: 24px;
    }

    .reaction-opt small {
        font-size: 10px;
        color: #666;
    }

    .file-upload-wrapper {
        margin-bottom: 15px;
        text-align: left;
    }

    .file-upload-wrapper input {
        display: none;
    }

    .file-upload-label {
        display: block;
        padding: 10px;
        border: 1px dashed #ccc;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        color: #666;
        font-size: 0.9rem;
    }

    .file-upload-label:hover {
        border-color: #ff5100;
        color: #ff5100;
    }

    .preview-thumb {
        width: 100%;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        margin-top: 10px;
        display: none;
    }
</style>

<div class="tracker-wrapper">
    <div class="bambam-tracker">
        <p style="font-size: 0.7rem; letter-spacing: 2px; opacity: 0.5; margin-bottom: 5px;">TRACKING ORDER</p>
        <p style="font-weight: bold; margin-bottom: 25px;">#BAM-<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?>
        </p>

        <div class="status-header">
            <div
                class="dot <?php echo ($status == 'placed' || $status == 'preparing' || $status == 'delivery' || $status == 'delivered') ? 'active' : ''; ?>">
            </div>
            <div
                class="dot <?php echo ($status == 'preparing' || $status == 'delivery' || $status == 'delivered') ? 'active' : ''; ?>">
            </div>
            <div class="dot <?php echo ($status == 'delivery' || $status == 'delivered') ? 'active' : ''; ?>"></div>
            <div class="dot <?php echo ($status == 'delivered') ? 'active' : ''; ?>"></div>
        </div>

        <div class="progress-bar-container">
            <div class="progress-fill"></div>
        </div>

        <div class="illustration-stage <?php echo ($status == 'preparing') ? 'glow-effect' : ''; ?>">
            <img src="<?php echo $current['image']; ?>"
                class="main-status-img <?php echo ($status == 'preparing') ? 'anim-preparing' : (($status == 'delivery') ? 'anim-delivery' : ''); ?>"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />

            <div class="status-fallback <?php echo ($status == 'preparing') ? 'anim-preparing' : (($status == 'delivery') ? 'anim-delivery' : ''); ?>"
                style="display:none; font-size: 100px;"><?php echo $current['fallback']; ?></div>
        </div>

        <h2 class="status-title"><?php echo $current['title']; ?></h2>
        <p class="status-desc"><?php echo $current['desc']; ?></p>

        <!-- RECEIPT SECTION -->
        <?php if (!empty($order_items)): ?>
            <div class="receipt-card">
                <!-- 1. SHOP INFO -->
                <div class="receipt-header-section">
                    <div class="receipt-logo"><?php echo $isPaid ? 'Official Receipt' : 'Order Confirmation'; ?> 🍔</div>
                    <div class="receipt-info">
                        <?php echo htmlspecialchars($order['branch'] ?? 'Main Branch'); ?>, Perlis<br>
                        Tel: +60 17-590 0799<br>
                        www.bambamburger.com
                    </div>
                </div>

                <!-- 2. ORDER INFO -->
                <div class="receipt-meta-grid">
                    <div>
                        <div class="meta-label">ORDER ID</div>
                        <div>#BAM-<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div style="text-align:right;">
                        <div class="meta-label">DATE</div>
                        <div><?php echo date('d/m/Y h:i A', strtotime($order['created_at'])); ?></div>
                    </div>

                    <div style="margin-top:10px;">
                        <div class="meta-label">CUSTOMER</div>
                        <div><?php echo htmlspecialchars($order['customer_name']); ?></div>
                    </div>
                    <div style="text-align:right; margin-top:10px;">
                        <div class="meta-label">PHONE</div>
                        <div><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></div>
                    </div>

                    <div style="margin-top:10px;">
                        <div class="meta-label">BRANCH</div>
                        <div><?php echo htmlspecialchars($order['branch']); ?></div>
                    </div>
                    <div style="text-align:right; margin-top:10px;">
                        <div class="meta-label">ORDER TYPE</div>
                        <div><?php echo htmlspecialchars($order['order_type']); ?></div>
                    </div>

                    <div style="margin-top:10px;">
                        <div class="meta-label">PAYMENT METHOD</div>
                        <div><?php
                        $pm = $order['payment_method'];
                        echo ($pm === 'TnG') ? 'TnG E-Wallet' : (($pm === 'ToyyibPay') ? 'Online Banking (ToyyibPay)' : htmlspecialchars($pm));
                        ?></div>
                    </div>
                    <div style="text-align:right; margin-top:10px;">
                        <div class="meta-label">PAYMENT STATUS</div>
                        <div style="font-weight:bold; color: <?php echo $isPaid ? '#2ecc71' : '#f1c40f'; ?>;">
                            <?php echo htmlspecialchars($order['payment_status'] ?? 'Pending'); ?></div>
                    </div>

                    <?php if ($isPaid && !empty($order['paid_at'])): ?>
                        <div style="margin-top:10px;">
                            <div class="meta-label">PAID AT</div>
                            <div><?php echo date('d/m/Y h:i A', strtotime($order['paid_at'])); ?></div>
                        </div>
                        <div style="text-align:right; margin-top:10px;">
                            <div class="meta-label">STAFF</div>
                            <div><?php echo htmlspecialchars($order['staff_name'] ?? 'N/A'); ?></div>
                        </div>
                    <?php endif; ?>
                    <div
                        style="text-align:right; margin-top:10px; grid-column: span 2; border-top:1px dashed #eee; padding-top:10px;">
                        <div class="meta-label">ORDER STATUS</div>
                        <div id="receipt-status-text" style="text-transform:uppercase; font-weight:bold;">
                            <?php echo htmlspecialchars($order['status']); ?></div>
                    </div>

                    <?php if (!empty($order['address'])): ?>
                        <div style="margin-top:10px; grid-column: span 2; border-top:1px dashed #eee; padding-top:10px;">
                            <div class="meta-label">DELIVERY ADDRESS</div>
                            <div style="font-size:11px; margin-top:2px;">
                                <?php echo nl2br(htmlspecialchars($order['address'])); ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 3. ITEM LIST -->
                <table class="receipt-items-table">
                    <thead>
                        <tr>
                            <th style="width:50%">ITEM</th>
                            <th style="width:15%; text-align:center;">QTY</th>
                            <th style="width:35%; text-align:right;">PRICE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                    <?php if ($item['variant']): ?><br><small
                                            style="color:#666;"><?php echo htmlspecialchars($item['variant']); ?></small><?php endif; ?>
                                    <?php if (!empty($item['customization'])): ?><br><small
                                            style="color:#888; font-style:italic;">Note:
                                            <?php echo htmlspecialchars($item['customization']); ?></small><?php endif; ?>
                                </td>
                                <td style="text-align:center;">x<?php echo $item['qty']; ?></td>
                                <td class="item-price">RM <?php echo number_format($item['price'] * $item['qty'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- 4. TOTAL -->
                <div class="receipt-total-section">
                    <div class="total-row grand-total"><span>TOTAL</span><span>RM
                            <?php echo number_format($order['total_amount'], 2); ?></span></div>
                </div>

                <!-- CUSTOMER CONFIRMATION BUTTON -->
                <button type="button" onclick="confirmOrder()" class="btn-confirm" id="btn-confirm-order"
                    style="display: <?php echo ($status == 'delivery') ? 'block' : 'none'; ?>;">
                    ✅ Order Received / Picked Up
                </button>

                <button onclick="window.print()" class="print-btn">🖨️ Print Receipt</button>
            </div>
        <?php endif; ?>

        <!-- RATE YOUR ORDER SECTION -->
        <?php
        $showRating = ($status == 'delivered' && empty($order['rating']));
        $alreadyRated = !empty($order['rating']);
        ?>

        <div class="rating-section" id="rating-card" style="display: <?php echo $showRating ? 'block' : 'none'; ?>;">
            <h3 style="margin:0 0 10px 0; color:#ff5100;">Rate Your Meal! ⭐</h3>
            <p style="font-size:0.9rem; color:#666;">How was your Bambam Burger?</p>

            <form nput type="hidden" name="order_id" value="<?php echo $order_id; ?>">
            <form id="reviewForm">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <input type="hidden" name="submit_rating" value="1">
                <input type="hidden" name="rating" id="rating-value" value="5">

                <div class="rating-stars">
                    <i class="star selected" onclick="setRating(1)">★</i><i class="star selected"
                        onclick="setRating(2)">★</i><i class="star selected" onclick="setRating(3)">★</i><i
                        class="star selected" onclick="setRating(4)">★</i><i class="star selected"
                        onclick="setRating(5)">★</i>
                </div>

                <div class="reaction-group">
                    <label class="reaction-opt" onclick="selectReaction(this)">
                        <input type="radio" name="reaction" value="Sedap" style="display:none;">
                        <span>🔥</span><small>Sedap</small>
                    </label>
                    <label class="reaction-opt" onclick="selectReaction(this)">
                        <input type="radio" name="reaction" value="Favourite" style="display:none;">
                        <span>😍</span><small>Fav</small>
                    </label>
                    <label class="reaction-opt" onclick="selectReaction(this)">
                        <input type="radio" name="reaction" value="Spicy" style="display:none;">
                        <span>🥵</span><small>Spicy</small>
                    </label>
                </div>

                <div class="file-upload-wrapper">
                    <label for="review_img" class="file-upload-label">📷 Upload Photo (Optional)</label>
                    <input type="file" id="review_img" name="review_image" accept="image/*"
                        onchange="previewReviewImg(this)">
                    <img id="review-preview" class="preview-thumb">
                </div>

                <textarea name="review" class="rating-textarea" rows="3"
                    placeholder="Write a review (optional)..."></textarea>
                <button type="button" onclick="submitReviewAjax()" class="btn-submit-rating">Submit Review</button>
            </form>
        </div>
        <?php if ($alreadyRated): ?>
            <div
                style="background:rgba(255,255,255,0.1); padding:15px; border-radius:10px; margin-top:20px; text-align:center;">
                Thanks for your rating! ⭐</div><?php endif; ?>

        <div class="order-meta">
            <span>🛵 Est. Delivery: 15-20 Mins</span><br>
            <span style="margin-top: 5px; display: inline-block;">Status updated: <?php echo date('H:i A'); ?></span>
        </div>
    </div>
</div>

<!-- Confetti Library -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
    // REAL-TIME UPDATE: AJAX Polling
    const stages = <?php echo json_encode($stages); ?>;
    const statusMap = <?php echo json_encode($status_map); ?>;
    const orderId = <?php echo json_encode($order_id); ?>;

    function updateTracker() {
        if (!orderId) return;

        fetch('get_status.php?id=' + orderId)
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    let rawStatus = data.status.toLowerCase();
                    let mappedStatus = statusMap[rawStatus] || 'placed';

                    // Update receipt status text
                    const statusTextEl = document.getElementById('receipt-status-text');
                    if (statusTextEl && statusTextEl.innerText.toLowerCase() !== rawStatus) {
                        statusTextEl.innerText = data.status;
                    }

                    let stage = stages[mappedStatus];

                    // Update Text & Progress
                    document.querySelector('.status-title').innerText = stage.title;
                    document.querySelector('.status-desc').innerText = stage.desc;
                    document.querySelector('.progress-fill').style.width = stage.percent;

                    // Update Image & Animations
                    let img = document.querySelector('.main-status-img');
                    let stageContainer = document.querySelector('.illustration-stage');

                    // Update Fallback Emoji
                    if (img.nextElementSibling) {
                        img.nextElementSibling.innerText = stage.fallback;
                    }

                    if (img.getAttribute('src') !== stage.image) {
                        img.src = stage.image;
                        img.style.display = 'block'; // Reset if previously hidden by error
                        if (img.nextElementSibling) img.nextElementSibling.style.display = 'none';
                    }

                    // Reset classes
                    img.classList.remove('anim-preparing', 'anim-delivery');
                    if (img.nextElementSibling) img.nextElementSibling.classList.remove('anim-preparing', 'anim-delivery');
                    stageContainer.classList.remove('glow-effect');

                    if (mappedStatus === 'preparing') {
                        img.classList.add('anim-preparing');
                        if (img.nextElementSibling) img.nextElementSibling.classList.add('anim-preparing');
                        stageContainer.classList.add('glow-effect');
                    } else if (mappedStatus === 'delivery') {
                        img.classList.add('anim-delivery');
                        if (img.nextElementSibling) img.nextElementSibling.classList.add('anim-delivery');
                    }

                    // Toggle Confirm Button
                    const btnConfirm = document.getElementById('btn-confirm-order');
                    if (btnConfirm) {
                        btnConfirm.style.display = (mappedStatus === 'delivery') ? 'block' : 'none';
                    }

                    // Toggle Rating Section (Show if delivered and not rated)
                    const ratingCard = document.getElementById('rating-card');
                    if (ratingCard && mappedStatus === 'delivered' && !data.is_rated) {
                        ratingCard.style.display = 'block';
                    }

                    // Update Dots
                    const dots = document.querySelectorAll('.dot');
                    const stageKeys = ['placed', 'preparing', 'delivery', 'delivered'];
                    const currentIndex = stageKeys.indexOf(mappedStatus);

                    dots.forEach((dot, index) => {
                        if (index <= currentIndex) dot.classList.add('active');
                        else dot.classList.remove('active');
                    });
                }
            })
            .catch(err => console.error('Tracker Error:', err));
    }

    // Poll every 3 seconds for real-time feel
    setInterval(updateTracker, 3000);

    // CONFETTI & CONFIRMATION LOGIC
    function confirmOrder() {
        // 1. Trigger Confetti
        confetti({
            particleCount: 150,
            spread: 70,
            origin: { y: 0.6 },
            colors: ['#ff5100', '#ffffff', '#FFA500']
        });

        // 2. AJAX Update
        const btn = document.getElementById('btn-confirm-order');
        btn.innerText = "Updating...";
        btn.disabled = true;

        const formData = new FormData();
        formData.append('confirm_received', '1');
        formData.append('order_id', orderId);
        formData.append('ajax', '1');

        fetch('receipt.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.style.display = 'none'; // Hide button
                    document.getElementById('rating-card').style.display = 'block'; // Show Rating
                    updateTracker(); // Force UI update
                }
            });
    }

    // STAR RATING LOGIC
    function setRating(n) {
        document.getElementById('rating-value').value = n;
        const stars = document.querySelectorAll('.star');
        stars.forEach((s, i) => {
            if (i < n) s.classList.add('selected');
            else s.classList.remove('selected');
        });
    }

    function selectReaction(el) {
        document.querySelectorAll('.reaction-opt').forEach(r => r.classList.remove('active'));
        el.classList.add('active');
        el.querySelector('input').checked = true;
    }

    function previewReviewImg(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.getElementById('review-preview');
                img.src = e.target.result;
                img.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function submitReviewAjax() {
        // 1. Ambil data dari input
        const rating = $('#rating-value').val();
        const review = $('.rating-textarea').val();

        // 2. Cara paling selamat ambil ID dari URL (?id=88)
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('id');

        // Validation
        if (!rating || rating == 0) {
            alert("Please select a star rating!");
            return;
        }

        if (!orderId) {
            alert("Order ID not found in URL!");
            return;
        }

        const btn = $('.btn-submit-rating');
        btn.prop('disabled', true).text('Submitting...');

        $.ajax({
            url: 'update_review.php',
            type: 'POST',
            dataType: 'json', // Bagitahu jQuery kita expect JSON dari PHP
            data: {
                order_id: orderId, // Nama variable ni kena sama dengan $_POST['order_id'] kat PHP
                rating: rating,
                review: review
            },
            success: function (response) {
                console.log("Server Response:", response);
                if (response.success) {
                    // Efek lepas berjaya
                    $('#rating-card').fadeOut(300, function () {
                        $(this).after('<div style="background:rgba(46, 204, 113, 0.1); color:#2ecc71; padding:20px; border-radius:10px; margin-top:20px; text-align:center; border: 1px solid #2ecc71; font-weight:bold;">Review submitted! Thank you. ⭐</div>');
                    });
                } else {
                    alert("Error: " + response.message);
                    btn.prop('disabled', false).text('Submit Review');
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error Details:", xhr.responseText);
                alert("Connection error. Check console (F12) for details.");
                btn.prop('disabled', false).text('Submit Review');
            }
        });
    }
</script>

<?php include 'footer.php'; ?>