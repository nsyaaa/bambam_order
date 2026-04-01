<?php 
include 'header.php'; 
include_once 'db.php';

// Fetch Data
$avg = 0;
$count = 0;
$reviews = [];

try {
    // Ensure columns exist (just in case receipt.php hasn't run yet)
    // This prevents errors if no one has rated yet
    try { $pdo->query("SELECT rating FROM orders LIMIT 1"); } 
    catch (Exception $e) { /* Columns don't exist yet, so no reviews */ }

    // Calculate Average
    $stmt = $pdo->query("SELECT AVG(rating) as avg, COUNT(*) as count FROM orders WHERE rating IS NOT NULL");
    $res = $stmt->fetch();
    $avg = $res['avg'] ? number_format($res['avg'], 1) : '0.0';
    $count = $res['count'];

    // Fetch Reviews
    $stmt = $pdo->query("SELECT customer_name, rating, review, review_image, reaction, admin_reply, created_at FROM orders WHERE rating IS NOT NULL ORDER BY created_at DESC");
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle DB errors gracefully (e.g. table doesn't exist)
}
?>

<style>
    body {
        background: url('images/ch.png') no-repeat center center fixed;
        background-size: cover;
    }
    .reviews-container {
        max-width: 800px;
        margin: 100px auto 50px;
        padding: 20px;
    }
    .summary-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        margin-bottom: 30px;
    }
    .big-rating {
        font-size: 60px;
        font-weight: 800;
        color: #ff5100;
        margin: 0;
        line-height: 1;
    }
    .stars-display {
        color: #ffcc00;
        font-size: 24px;
        margin: 10px 0;
    }
    .review-card {
        background: rgba(255,255,255,0.95);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .review-card:hover { transform: translateY(-3px); }
    .r-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
    .r-name { font-weight: bold; color: #333; }
    .r-date { font-size: 0.8rem; color: #888; }
    .r-stars { color: #ffcc00; }
    .r-text { color: #555; line-height: 1.5; font-style: italic; }
    
    .r-image { width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; margin-top: 10px; cursor: pointer; }
    .reaction-badge { display: inline-block; background: #fff3e0; color: #ff5100; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: bold; margin-left: 10px; }
    
    .admin-reply-box {
        background: #f1f8e9; border-left: 4px solid #7cb342;
        padding: 10px 15px; margin-top: 15px; border-radius: 0 5px 5px 0;
    }
    .admin-reply-title { font-weight: bold; color: #558b2f; font-size: 12px; margin-bottom: 5px; }
</style>

<div class="reviews-container">
    <!-- SUMMARY -->
    <div class="summary-card">
        <h2 style="margin-top:0;">Customer Reviews</h2>
        <div style="margin-bottom: 20px;">
            <a href="history.php" style="display:inline-block; background:#ff5100; color:white; padding:10px 20px; border-radius:50px; text-decoration:none; font-weight:bold; box-shadow: 0 4px 10px rgba(255, 81, 0, 0.3);">Write a Review</a>
        </div>
        <div class="big-rating"><?php echo $avg; ?></div>
        <div class="stars-display">
            <?php 
            $stars = round($avg);
            for($i=1; $i<=5; $i++) echo ($i <= $stars) ? '★' : '☆';
            ?>
        </div>
        <p style="color:#666;">Based on <?php echo $count; ?> reviews</p>
    </div>

    <!-- LIST -->
    <?php if(empty($reviews)): ?>
        <div class="review-card" style="text-align:center; padding:40px;">
            <h3>No reviews yet! 🍔</h3>
            <p>Be the first to order and leave a review.</p>
            <a href="menu.php" style="display:inline-block; margin-top:10px; background:#ff5100; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;">Order Now</a>
        </div>
    <?php else: ?>
        <?php foreach($reviews as $r): ?>
        <div class="review-card">
            <div class="r-header">
                <div class="r-name"><?php echo htmlspecialchars($r['customer_name'] ?: 'Anonymous'); ?></div>
                <div class="r-date"><?php echo date('d M Y', strtotime($r['created_at'])); ?></div>
            </div>
            <div class="r-stars">
                <?php for($i=1; $i<=5; $i++) echo ($i <= $r['rating']) ? '★' : '☆'; ?>
                <?php if(!empty($r['reaction'])): ?>
                    <span class="reaction-badge"><?php 
                        $emojis = ['Sedap'=>'🔥', 'Favourite'=>'😍', 'Spicy'=>'🥵'];
                        echo ($emojis[$r['reaction']] ?? '') . ' ' . htmlspecialchars($r['reaction']); 
                    ?></span>
                <?php endif; ?>
            </div>
            <?php if($r['review']): ?>
                <p class="r-text">"<?php echo htmlspecialchars($r['review']); ?>"</p>
            <?php endif; ?>
            <?php if(!empty($r['review_image'])): ?>
                <img src="uploads/reviews/<?php echo htmlspecialchars($r['review_image']); ?>" class="r-image" onclick="window.open(this.src)">
            <?php endif; ?>
            
            <?php if(!empty($r['admin_reply'])): ?>
                <div class="admin-reply-box">
                    <div class="admin-reply-title">Admin Response:</div>
                    <div style="font-size: 14px; color: #333;"><?php echo htmlspecialchars($r['admin_reply']); ?></div>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>