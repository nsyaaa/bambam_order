<?php
// ===============================
// Bambam Burger - Delivery Address
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

// Auto-create columns in users table if they don't exist
try {
    $pdo->query("SELECT address_line1 FROM users LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE users 
        ADD COLUMN address_line1 TEXT DEFAULT NULL,
        ADD COLUMN address_line2 TEXT DEFAULT NULL,
        ADD COLUMN city VARCHAR(100) DEFAULT NULL,
        ADD COLUMN state VARCHAR(100) DEFAULT NULL,
        ADD COLUMN postcode VARCHAR(10) DEFAULT NULL,
        ADD COLUMN delivery_note TEXT DEFAULT NULL");
}

// Load current values
$stmt = $pdo->prepare("
    SELECT name, phone, address_line1, address_line2, city, postcode, state, delivery_note
    FROM users
    WHERE id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['recipient_name'] ?? '');
    $phone = trim($_POST['contact_number'] ?? '');
    $line1 = trim($_POST['address_line1']);
    $line2 = trim($_POST['address_line2']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postcode = trim($_POST['postcode']);
    $note = trim($_POST['delivery_note']);

    if ($name === '' || $phone === '' || $line1 === '' || $city === '' || $postcode === '' || $state === '') {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE users
            SET
                name = ?,
                phone = ?,
                address_line1 = ?,
                address_line2 = ?,
                city = ?,
                postcode = ?,
                state = ?,
                delivery_note = ?
            WHERE id = ?
        ");
        
        if ($stmt->execute([$name, $phone, $line1, $line2, $city, $postcode, $state, $note, $userId])) {
            $_SESSION['user_name'] = $name;
            $msg = "Address updated successfully!";
            
            // Refresh local user data
            $stmt->execute([$name, $phone, $line1, $line2, $city, $postcode, $state, $note, $userId]);
            $stmt = $pdo->prepare("SELECT name, phone, address_line1, address_line2, city, postcode, state, delivery_note FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

include 'header.php';
?>

<style>
    body {
        background: url('images/ch.png') no-repeat center center fixed !important;
        background-size: cover !important;
        color: #ffffff;
        font-family: 'Poppins', sans-serif;
    }
    .address-container {
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
    .back-btn { color: #ffffff; font-size: 20px; text-decoration: none; opacity: 0.7; transition: 0.3s; }
    .back-btn:hover { opacity: 1; color: #ff5100; }
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; color: #aaa; text-transform: uppercase; letter-spacing: 1px; }
    .form-group input, .form-group textarea {
        width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px; color: white; outline: none; transition: 0.3s;
    }
    .form-group input:focus, .form-group textarea:focus { border-color: #ff5100; background: rgba(255,255,255,0.1); }
    
    .save-btn {
        width: 100%; padding: 15px; background: #ff5100; color: white; border: none; border-radius: 50px;
        font-weight: bold; cursor: pointer; margin-top: 10px; text-transform: uppercase; letter-spacing: 1px;
        transition: 0.3s;
    }
    .save-btn:hover { background: #e04600; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 81, 0, 0.3); }
    
    .alert-success { background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 10px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 14px; border: 1px solid #2ecc71; }
</style>

<div class="address-container animate-fade-up">
    <div class="header-bar">
        <a href="profile.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
        <h3 style="margin:0; font-size:16px; letter-spacing:1px;"><?php echo $t['delivery_address'] ?? 'Delivery Address'; ?></h3>
        <div style="width:20px;"></div>
    </div>

    <?php if ($msg): ?>
        <div class="alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Recipient Name</label>
            <input type="text" name="recipient_name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required placeholder="e.g. John Doe">
        </div>

        <div class="form-group">
            <label>Contact Number</label>
            <input type="tel" name="contact_number" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required placeholder="e.g. 0123456789">
        </div>

        <div class="form-group">
            <label>Address Line 1</label>
            <input type="text" name="address_line1" value="<?php echo htmlspecialchars($user['address_line1'] ?? ''); ?>" required placeholder="House No, Street Name">
        </div>

        <div class="form-group">
            <label>Address Line 2 (Optional)</label>
            <input type="text" name="address_line2" value="<?php echo htmlspecialchars($user['address_line2'] ?? ''); ?>" placeholder="Apartment, Suite, Unit">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>City</label>
                <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Postcode</label>
                <input type="text" name="postcode" value="<?php echo htmlspecialchars($user['postcode'] ?? ''); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>State</label>
            <input type="text" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label>Delivery Note (Optional)</label>
            <textarea name="delivery_note" rows="2" placeholder="e.g. Drop at lobby, ring bell..."><?php echo htmlspecialchars($user['delivery_note'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="save-btn">Save Address</button>
    </form>
</div>

<?php include 'footer.php'; ?>