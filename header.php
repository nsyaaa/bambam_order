<?php 
if (ob_get_level() == 0) ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'languages.php'; 

$ticker_items = ['LAVA CHEESE BURGER', 'SPECIAL PROMO', 'CHICKEN GRILL BURGER', 'BEEF SMASH BURGER', 'FREE DELIVERY KANGAR'];

// Check if the user is an admin to show the admin link
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    // This includes the database connection. Use include_once to prevent errors if already included.
    include_once 'db.php'; 
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            if ($stmt->fetchColumn() === 'admin') {
                $isAdmin = true;
            }
        } catch (PDOException $e) { /* Fail silently on header */ }
    }
}

// Restrict admin from customer pages
$customerPages = ['index.php', 'about.php', 'contact.php', 'profile.php', 'history.php', 'branch_selection.php'];
if ($isAdmin && in_array(basename($_SERVER['PHP_SELF']), $customerPages)) {
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bambam Burger</title>

<!-- Google Fonts: Fraunces (Serif) & DM Sans (Sans-Serif) -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- Font Awesome for Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
/* Global Fix for Layout Calculation */
*, *::before, *::after {
    box-sizing: border-box;
}

body{
    margin:0;
    font-family: 'Poppins', sans-serif; /* Body Text: Sans-Serif */
    /* Add padding to bottom so content isn't hidden by fixed elements */
    padding-bottom: 75px; /* Space for fixed footer */
    background-color: #181818; /* Charcoal Background */
    color: #ffffff; /* White text */
    position: relative;
}

/* Background Texture Overlay (Floating Ingredients) */
body::before {
    content: "";
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: url('images/ch.png') no-repeat center center fixed;
    background-size: cover;
    opacity: 0.08; /* Darker overlay (lower opacity of texture) for better text pop */
    z-index: -1;
    pointer-events: none;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Playfair Display', serif; /* Headings: Serif */
}

/* ===== HEADER ===== */
header{
    background-color: #FF6B00 !important; /* Consistent Orange */
    opacity: 1 !important;
    border-bottom: none;
    color: #ffffff; /* White text */
    padding: 10px 50px; /* Reduced vertical padding */
    display:flex;
    justify-content:space-between;
    align-items:center;
    position:fixed;
    width:100%;
    top:0;
    z-index:1000;
    transition: all 0.3s ease;
    box-shadow: none;
}
header h1 a {
    color: #ffffff; /* White logo text */
}
header h1{
    margin:0;
    font-size:24px;
    font-weight: 700;
    letter-spacing: -1px;
}

/* ===== TICKER BANNER ===== */
.ticker-wrap {
    position: fixed;
    top: 50px; /* Adjusted for smaller header */
    left: 0;
    width: 100%;
    background-color: rgba(0, 0, 0, 0.7); /* Slightly transparent */
    backdrop-filter: blur(5px); /* Premium frosted look */
    color: rgba(255, 255, 255, 0.8); /* Soft white text */
    padding: 8px 0;
    z-index: 999;
    font-size: 18px;
    letter-spacing: 2px;
    overflow: hidden;
}
.ticker-move {
    display: inline-block;
    white-space: nowrap;
    animation: ticker-scroll 200s linear infinite; 
}
.ticker-item {
    display: inline-block;
    font-weight: 300;
    text-transform: uppercase;
    padding: 0 2rem;
}
.ticker-wrap:hover .ticker-move { animation-play-state: paused; }

@keyframes ticker-scroll {
    0% { transform: translateX(0%); }
    100% { transform: translateX(-100%); }
}

/* ===== FOOTER ===== */
footer{
    background-color: #FF5E0E; /* Match header color */
    background-color: #ff5e0e !important;
    opacity: 1 !important;
    color:white;
    text-align:center;
    padding:15px 0;
    position:fixed;
    bottom:0;
    width:100%;
    z-index:1000;
    box-shadow: 0 -10px 30px rgba(0,0,0,0.1); /* Soft Shadow */
}

/* ===== ANIMATIONS ===== */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes scaleIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

/* Utility Classes for Page Load */
.animate-fade-up { animation: fadeInUp 0.8s ease-out forwards; }
.animate-scale-in { animation: scaleIn 0.8s ease-out forwards; }
.animate-fade-in { animation: fadeIn 1.0s ease-out forwards; }
.shake-error { animation: shake 0.5s ease-in-out; }

/* Scroll Reveal Class (handled by JS in footer) */
.reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease-out;
}
.reveal.active {
    opacity: 1;
    transform: translateY(0);
}

/* ===== LOADER ===== */
#loader-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #fff8f0;
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: opacity 0.5s ease-out, visibility 0.5s;
}
.burger-loader {
    display: flex;
    flex-direction: column;
    align-items: center;
    animation: bounce 0.6s infinite alternate ease-in-out;
}
.b-bun-top {
    width: 60px;
    height: 30px;
    background: #ff9f43;
    border-radius: 30px 30px 5px 5px;
    position: relative;
}
.b-bun-top::after {
    content: '';
    position: absolute;
    top: 8px;
    left: 15px;
    width: 4px;
    height: 4px;
    background: #fff;
    border-radius: 50%;
    box-shadow: 10px 0 0 #fff, 20px 5px 0 #fff, 5px 10px 0 #fff, 25px 0 0 #fff;
    opacity: 0.7;
}
.b-lettuce { width: 66px; height: 10px; background: #2ecc71; border-radius: 10px; margin: -3px 0; }
.b-cheese { width: 64px; height: 8px; background: #f1c40f; border-radius: 5px; margin-top: -3px; }
.b-patty { width: 62px; height: 16px; background: #6d4c41; border-radius: 10px; margin-top: -3px; }
.b-bun-bottom { width: 60px; height: 20px; background: #ff9f43; border-radius: 5px 5px 20px 20px; margin-top: -3px; }
@keyframes bounce { from { transform: translateY(0); } to { transform: translateY(-20px); } }

/* ===== HAMBURGER MENU ===== */
.hamburger {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 30px;
    height: 24px;
    background: transparent;
    border: none;
    cursor: pointer;
    z-index: 2000;
    padding: 0;
}
.hamburger span {
    width: 100%;
    height: 3px;
    background: #ffffff; /* White hamburger lines */
    border-radius: 2px;
    transition: all 0.3s ease-in-out;
}
.hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 6px); }
.hamburger.active span:nth-child(2) { opacity: 0; }
.hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -7px); }

/* Nav Drawer */
nav {
    position: fixed;
    top: 0;
    right: -100%;
    width: 280px;
    height: 100vh;
    background: #1a1a1a;
    padding: 100px 30px;
    display: flex;
    flex-direction: column;
    gap: 25px;
    transition: right 0.4s cubic-bezier(0.77, 0, 0.175, 1);
    z-index: 1500;
    box-shadow: -10px 0 30px rgba(0,0,0,0.5);
}
nav.active { right: 0; }

nav a {
    color: white;
    text-decoration: none;
    font-size: 1.2rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 2px;
    opacity: 0;
    transform: translateX(30px);
    transition: all 0.3s ease;
    padding: 10px;
    border-radius: 8px;
}
nav a:hover { 
    color: #ff5100; 
    background: rgba(255, 255, 255, 0.1);
    padding-left: 20px;
}
nav.active a { opacity: 1; transform: translateX(0); }

/* Staggered Animation */
nav.active a:nth-child(1) { transition-delay: 0.1s; }
nav.active a:nth-child(2) { transition-delay: 0.2s; }
nav.active a:nth-child(3) { transition-delay: 0.3s; }
nav.active a:nth-child(4) { transition-delay: 0.4s; }
nav.active a:nth-child(5) { transition-delay: 0.5s; }
nav.active a:nth-child(6) { transition-delay: 0.6s; }

/* Overlay */
.menu-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(3px);
    z-index: 1400; opacity: 0; visibility: hidden; transition: all 0.3s;
}
.menu-overlay.active { opacity: 1; visibility: visible; }
</style>
</head>

<body>

<div id="loader-wrapper">
    <div class="burger-loader">
        <div class="b-bun-top"></div><div class="b-lettuce"></div><div class="b-cheese"></div><div class="b-patty"></div><div class="b-bun-bottom"></div>
    </div>
</div>
<script>
// Failsafe: Force hide loader after 5 seconds if page logic fails
setTimeout(function(){
    var l = document.getElementById('loader-wrapper');
    if(l){ l.style.opacity='0'; l.style.visibility='hidden'; }
}, 5000);
</script>

<header>
    <h1><a href="index.php" style="text-decoration:none;"><i class="fas fa-hamburger"></i> Bambam Burger</a></h1>
    
    <div id="user-welcome-display" style="margin-left: auto; margin-right: 20px; font-size: 18px; font-weight: bold; display: none; align-items: center;">
        <a href="index.php" style="color: #ffffff; text-decoration: none; margin-right: 20px; font-weight: 700; letter-spacing: 0.5px; line-height: 1;">Home</a>
        <a href="profile.php" style="color: #ffffff; text-decoration: none; line-height: 1;">
            Welcome, <span id="display-username" style="font-weight: 800;">User</span>
        </a>
    </div>

    <button class="hamburger" onclick="toggleMenu()">
        <span></span><span></span><span></span>
    </button>
    <div class="menu-overlay" onclick="toggleMenu()"></div>

    <nav id="main-nav">
        <?php if (!$isAdmin): ?>
            <a href="index.php"><?php echo $t['home']; ?></a>
            <a href="menu.php"><?php echo $t['menu']; ?></a>
            <a href="reviews.php">Reviews</a>
            <a href="about.php"><?php echo $t['about']; ?></a>
            <a href="contact.php"><?php echo $t['contact']; ?></a>
            <a href="login.php" id="nav-login-link"><?php echo $t['login']; ?></a>
        <?php else: ?>
            <a href="admin.php">Dashboard</a>
        <?php endif; ?>
    </nav>
</header>

<div class="ticker-wrap">
    <div class="ticker-move">
        <?php for ($i = 0; $i < 5; $i++): // Repeat loop for seamless effect ?>
            <?php foreach ($ticker_items as $item): ?>
                <div class="ticker-item"><?php echo htmlspecialchars($item); ?> &#x2605;</div>
            <?php endforeach; ?>
        <?php endfor; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Sync Server Session with Local Storage
    // If PHP session is not active, clear client-side login data to prevent false "Welcome" message
    const isSessionActive = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    if (!isSessionActive) {
        localStorage.removeItem('user_logged_in');
        localStorage.removeItem('current_user_name');
        localStorage.removeItem('current_user_phone');
        sessionStorage.removeItem('user_logged_in');
        sessionStorage.removeItem('current_user_name');
    }

    // Check if user is logged in
    if (localStorage.getItem('user_logged_in') === 'true' || sessionStorage.getItem('user_logged_in') === 'true') {
        let userName = localStorage.getItem('current_user_name') || sessionStorage.getItem('current_user_name') || 'User';
        
        // Clean quotes from JSON string if present
        if (userName.startsWith('"') && userName.endsWith('"')) {
            userName = userName.slice(1, -1);
        }

        const welcomeDisplay = document.getElementById('user-welcome-display');
        const displayUsername = document.getElementById('display-username');
        if (welcomeDisplay && displayUsername) {
            displayUsername.innerText = userName;
            welcomeDisplay.style.display = 'flex';
        }

        const loginLink = document.getElementById('nav-login-link');
        if (loginLink) {
            loginLink.innerHTML = `My Profile`;
            loginLink.href = 'profile.php';
        }
    }
});

function toggleMenu() {
    document.getElementById('main-nav').classList.toggle('active');
    document.querySelector('.hamburger').classList.toggle('active');
    document.querySelector('.menu-overlay').classList.toggle('active');
}
</script>