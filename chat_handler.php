<?php
// chat_handler.php
session_start();
include 'db.php'; // Use your existing connection

$userMsg = trim($_POST['message'] ?? '');

if (empty($userMsg)) exit;

// --- SYNC 1: Get Store Status ---
// Checks the 'system_settings' table for the 'global_store_status'
$stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'global_store_status'");
$stmt->execute();
$storeStatus = $stmt->fetchColumn() ?: 'open';

// --- SYNC 2: Order Tracking ---
// If the user mentions a number (Order ID), check the 'orders' table
$liveOrderInfo = "";
if (preg_match('/(\d+)/', $userMsg, $matches)) {
    $orderId = $matches[1];
    $stmt = $pdo->prepare("SELECT status, total_amount FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if ($order) {
        $liveOrderInfo = "Found Order #$orderId: Status is " . $order['status'] . ". Total: RM" . $order['total_amount'];
    }
}

// --- SYNC 3: Menu Availability ---
// Check which burgers are currently 'is_available'
$stmt = $pdo->query("SELECT name, price FROM menu_items WHERE is_available = 1 LIMIT 5");
$availableBurgers = $stmt->fetchAll(PDO::FETCH_ASSOC);
$menuList = "";
foreach($availableBurgers as $item) {
    $menuList .= "- " . $item['name'] . " (RM" . $item['price'] . ")\n";
}

// --- SYNC 4: Best Sellers ---
$bestSellerList = "None recorded yet";
try {
    $stmt = $pdo->query("SELECT item_name FROM order_items GROUP BY item_name ORDER BY SUM(qty) DESC LIMIT 3");
    $bestSellers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if ($bestSellers) $bestSellerList = implode(", ", $bestSellers);
} catch (Exception $e) {}

// --- RULE-BASED LOGIC (NORMAL BOT) ---
$response = "";
$msgLower = strtolower($userMsg);

if ($storeStatus === 'closed') {
    $response = "Hello! We are currently closed. Please check back during our business hours. 🍔";
} elseif ($liveOrderInfo && (str_contains($msgLower, 'order') || str_contains($msgLower, 'status') || str_contains($msgLower, '#') || preg_match('/(\d+)/', $msgLower))) {
    // If they provided a number and we found an order
    $response = "I found some info for you! " . $liveOrderInfo . ". You can also view your full history in the 'Purchase History' page.";
} elseif (str_contains($msgLower, 'menu') || str_contains($msgLower, 'available') || str_contains($msgLower, 'burger') || str_contains($msgLower, 'eat')) {
    $response = "Here are some of our available items:\n" . $menuList . "\nYou can see the full menu on our View Menu page!";
} elseif (str_contains($msgLower, 'popular') || str_contains($msgLower, 'best seller') || str_contains($msgLower, 'recommend')) {
    $response = "Our customers are currently loving: " . $bestSellerList . ". You should give them a try!";
} elseif (str_contains($msgLower, 'location') || str_contains($msgLower, 'branch') || str_contains($msgLower, 'where')) {
    $response = "We have 5 branches in Perlis: Kangar, Jejawi, Arau, Kuala Perlis, and Beseri. You can choose your preferred branch on our home page or the branch selection screen!";
} elseif (str_contains($msgLower, 'hours') || str_contains($msgLower, 'time') || str_contains($msgLower, 'open')) {
    $response = "Our branches are open from 4:00 PM till 10:30 PM. The best way to check if a specific branch is currently taking orders is to look at the 'Choose Your Branch' page—it shows live status! ⏰";
} elseif (str_contains($msgLower, 'contact') || str_contains($msgLower, 'phone') || str_contains($msgLower, 'whatsapp') || str_contains($msgLower, 'call')) {
    $response = "You can reach our main HQ at 017-590 0799. For branch-specific numbers, please visit our 'Contact' page. We're happy to help! 📞";
} elseif (str_contains($msgLower, 'pay') || str_contains($msgLower, 'cash') || str_contains($msgLower, 'tng') || str_contains($msgLower, 'bank')) {
    $response = "We make it easy to pay! We accept Cash on Pickup/Delivery, Touch 'n Go eWallet (scan the QR at checkout), and Online Banking via ToyyibPay. 💳";
} elseif (str_contains($msgLower, 'how') && (str_contains($msgLower, 'order') || str_contains($msgLower, 'buy') || str_contains($msgLower, 'work'))) {
    $response = "Ordering is simple:\n1. Choose your Branch from the home page.\n2. Browse the Menu and add your favorites to the cart.\n3. Click 'My Cart' and then 'Order Now'.\n4. Fill in your details and choose your payment method. Done! 🍔";
} elseif (str_contains($msgLower, 'custom') || str_contains($msgLower, 'build')) {
    $response = "Yes! You can build your own masterpiece. Just click the 'CUSTOMIZE' button on the home page to start stacking your own ingredients. 🍞🥩🧀";
} elseif (str_contains($msgLower, 'review') || str_contains($msgLower, 'rate') || str_contains($msgLower, 'feedback')) {
    $response = "We love feedback! You can rate your order from your Purchase History once it's completed, or check out what others are saying on our 'Reviews' page. ⭐";
} elseif (str_contains($msgLower, 'about') || str_contains($msgLower, 'who are you')) {
    $response = "BamBam Burger is home to some of the juiciest burgers in Perlis! We pride ourselves on fresh ingredients and gourmet recipes. Check out our 'About Us' page to learn more about our journey. 🧡";
} elseif (str_contains($msgLower, 'account') || str_contains($msgLower, 'login') || str_contains($msgLower, 'profile')) {
    $response = "You can manage your details and view your order history in the 'My Profile' section. If you haven't joined us yet, head over to the Login page to sign up!";
} elseif (str_contains($msgLower, 'hi') || str_contains($msgLower, 'hello') || str_contains($msgLower, 'hey') || str_contains($msgLower, 'yo')) {
    $response = "Hi there! 🍔 I'm the BamBam Assistant. I can help you with the menu, show you our best sellers, or track your order status (just type your Order ID). How can I help you?";
} elseif (str_contains($msgLower, 'thank')) {
    $response = "You're very welcome! Enjoy your meal! 😊";
} elseif (str_contains($msgLower, 'bye') || str_contains($msgLower, 'goodbye')) {
    $response = "Goodbye! Hope to see you ordering soon! 🍔";
} else {
    // Default help message
    if (empty($liveOrderInfo) && preg_match('/(\d+)/', $msgLower)) {
        $response = "I couldn't find an order with that ID. Please double-check the number.";
    } else {
        $response = "I'm not sure I understand. You can ask me about our 'menu', 'best sellers', or track an order by typing the Order ID!";
    }
}

echo $response;
?>