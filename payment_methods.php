<?php
// ===============================
// Bambam Burger - Payment Methods
// ===============================
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'db.php';

// Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$msg = "";

// Auto-create column in users if not exists
try {
    $pdo->query("SELECT preferred_payment_method FROM users LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE users ADD COLUMN preferred_payment_method VARCHAR(50) DEFAULT 'Cash'");
}

// Handle Save
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['preferred_payment_method'])) {
    $method = $_POST['preferred_payment_method'] ?? 'Cash';

    $allowed = ['Cash', 'TnG', 'ToyyibPay'];
    if (!in_array($method, $allowed, true)) {
        $method = 'Cash';
    }

    $stmt = $pdo->prepare("UPDATE users SET preferred_payment_method = ? WHERE id = ?");
    if ($stmt->execute([$method, $userId])) {
        $msg = "Preferred payment method updated!";
    }
}

// Fetch Current Preference
$stmt = $pdo->prepare("SELECT preferred_payment_method FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$preferred = $user['preferred_payment_method'] ?? 'Cash';

include 'header.php';
?>

<style>
    body {
        background: url('images/ch.png') no-repeat center center fixed !important;
        background-size: cover !important;
        color: #ffffff;
        font-family: 'Poppins', sans-serif;
    }
    .payment-container {
        max-width: 500px;
        margin: 100px auto 40px;
        background: rgba(0, 0, 0, 0.85);
        padding: 30px;
        border: 2px solid #ff5100;
        border-radius: 25px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.6);
    }
    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    .back-btn { color: #ffffff; font-size: 20px; text-decoration: none; opacity: 0.7; }
    
    .payment-option {
        display: flex; align-items: center; padding: 20px;
        background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
        border-radius: 16px; margin-bottom: 12px; cursor: pointer; transition: 0.3s;
    }
    .payment-option:hover { background: rgba(255,255,255,0.1); border-color: #ff5100; }
    .payment-option.active { border-color: #ff5100; background: rgba(255, 81, 0, 0.1); }
    
    .option-icon { width: 40px; font-size: 20px; color: #ff5100; text-align: center; margin-right: 15px; }
    .option-info { flex: 1; }
    .option-title { font-weight: 600; font-size: 15px; display: block; }
    .option-desc { font-size: 12px; color: #aaa; }
    
    .radio-check { width: 20px; height: 20px; border: 2px solid #444; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .payment-option.active .radio-check { border-color: #ff5100; }
    .payment-option.active .radio-check::after { content: ''; width: 10px; height: 10px; background: #ff5100; border-radius: 50%; }

    .alert-success { background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 10px; border-radius: 10px; margin-bottom: 20px; text-align: center; border: 1px solid #2ecc71; }
</style>

<div class="payment-container animate-fade-up">
    <div class="header-bar">
        <a href="profile.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
        <h3 style="margin:0; font-size:16px; letter-spacing:1px;"><?php echo $t['payment_method'] ?? 'Payment Method'; ?></h3>
        <div style="width:20px;"></div>
    </div>

    <?php if ($msg): ?>
        <div class="alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form id="paymentForm" method="POST">
        <div class="payment-option <?php echo $preferred == 'Cash' ? 'active' : ''; ?>" onclick="selectMethod('Cash')">
            <div class="option-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="option-info">
                <span class="option-title">Cash on Delivery</span>
                <span class="option-desc">Pay when your burger arrives</span>
            </div>
            <div class="radio-check"></div>
            <input type="radio" name="preferred_payment_method" value="Cash" <?php echo $preferred == 'Cash' ? 'checked' : ''; ?> style="display:none;">
        </div>

        <div class="payment-option <?php echo $preferred == 'TnG' ? 'active' : ''; ?>" onclick="selectMethod('TnG')">
            <div class="option-icon"><i class="fas fa-wallet"></i></div>
            <div class="option-info">
                <span class="option-title">TnG E-Wallet</span>
                <span class="option-desc">Fast payment via Touch 'n Go</span>
            </div>
            <div class="radio-check"></div>
            <input type="radio" name="preferred_payment_method" value="TnG" <?php echo $preferred == 'TnG' ? 'checked' : ''; ?> style="display:none;">
        </div>

        <div class="payment-option <?php echo $preferred == 'ToyyibPay' ? 'active' : ''; ?>" onclick="selectMethod('ToyyibPay')">
            <div class="option-icon"><i class="fas fa-university"></i></div>
            <div class="option-info">
                <span class="option-title">Online Banking</span>
                <span class="option-desc">FPX via ToyyibPay</span>
            </div>
            <div class="radio-check"></div>
            <input type="radio" name="preferred_payment_method" value="ToyyibPay" <?php echo $preferred == 'ToyyibPay' ? 'checked' : ''; ?> style="display:none;">
        </div>
    </form>
</div>

<script>
function selectMethod(val) {
    const form = document.getElementById('paymentForm');
    form.querySelector(`input[value="${val}"]`).checked = true;
    form.submit();
}
</script>

<?php include 'footer.php'; ?>