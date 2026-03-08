<?php 
include 'header.php'; 
include_once 'db.php';

// Check Store Status
$storeStatus = 'open';
try {
    $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'store_status'");
    $storeStatus = $stmt->fetchColumn() ?: 'open';
} catch (Exception $e) {}

// Fetch best selling item names (e.g., top 3)
$bestSellers = [];
try {
    $bestSellerStmt = $pdo->query("
        SELECT item_name 
        FROM order_items 
        GROUP BY item_name 
        ORDER BY SUM(qty) DESC 
        LIMIT 3
    ");
    $bestSellers = $bestSellerStmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) { /* Fail silently if table doesn't exist */ }

// Fetch menu items and group by category
$stmt = $pdo->query("SELECT category, id, name, description, price, has_protein, variants, created_at FROM menu_items ORDER BY id ASC");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$menuItems = [];
foreach ($results as $row) {
    // Hotfix: Ensure Wagyu and Itik are single items (no protein selection) even if DB says otherwise
    if (in_array($row['name'], ['Wagyu Burger', 'Itik Burger', 'Burger Wagyu', 'Burger Itik'])) {
        $row['has_protein'] = 0;
    }
    $menuItems[$row['category']][] = $row;
}

// Manually add new items that are not in the database yet
$menuItems['burger'][] = [
    'id' => 10002,
    'category' => 'burger',
    'name' => 'Burger Mix XL', 
    'description' => 'Combined proteins standard',
    'price' => 12.00,
    'has_protein' => 0,
    'variants' => json_encode([
        ['name' => 'Single', 'price' => 12.00],
        ['name' => 'Double', 'price' => 18.00],
        ['name' => 'Triple', 'price' => 24.00]
    ]),
    'created_at' => date('Y-m-d H:i:s')
];
$menuItems['burger'][] = [
    'id' => 10001,
    'category' => 'burger',
    'name' => 'Burger Kambing', 
    'description' => 'Juicy lamb patty with special herbs and spices.',
    'price' => 16.00,
    'has_protein' => 0,
    'variants' => null,
    'created_at' => date('Y-m-d H:i:s')
];

// Prepare Data for JavaScript (Variants & Protein info)
$jsMenuData = [];
$categories = ['burger', 'special', 'addon', 'minuman'];
foreach ($categories as $cat) {
    if (isset($menuItems[$cat])) {
        foreach ($menuItems[$cat] as $item) {
            $variants = $item['variants'] ? json_decode($item['variants'], true) : [];
            $jsMenuData[$item['name']] = ['has_protein' => $item['has_protein'], 'variants' => $variants];
        }
    }
}

$user_favorites = [];
if (isset($_SESSION['user_id'])) {
    $favStmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
    $favStmt->execute([$_SESSION['user_id']]);
    $user_favorites = $favStmt->fetchAll(PDO::FETCH_COLUMN, 0);
}
?>

<style>
 body {
        padding-top: 100px;
    }

    #menu-content { max-width: 1200px; margin: 0 auto; padding: 20px; }

    /* Branch Selection Glass & Animation */
    .branch-info-bar {
        background: rgba(26, 26, 27, 0.95);
        backdrop-filter: blur(10px);
        padding: 20px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        border: 1px solid #333;
        position: sticky;
        top: 85px; /* Sticks just below the fixed header */
        z-index: 990;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    
    .branch-left { display: flex; align-items: center; gap: 15px; }
    
    .branch-icon-circle {
        background: #ff5100;
        width: 50px; height: 50px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.2rem;
        border: none;
        animation: pulseGlow 2.5s infinite ease-in-out;
    }

    .branch-label {
        font-size: 0.75rem; color: #ccc;
        text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px;
    }
    
    .branch-name {
        font-size: 1.4rem; font-weight: 700; color: white;
        font-family: 'Poppins', sans-serif; line-height: 1;
    }

    .branch-action-btn {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 1px solid rgba(255,255,255,0.2);
        padding: 8px 15px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
    }
    .branch-action-btn:hover {
        background: #ff5100;
        border-color: #ff5100;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 81, 0, 0.4);
    }

    /* STORE CLOSED OVERLAY */
    .store-closed-banner {
        position: fixed; bottom: 0; left: 0; width: 100%; background: #e74c3c; color: white;
        text-align: center; padding: 15px; font-weight: bold; z-index: 9999;
        box-shadow: 0 -5px 20px rgba(0,0,0,0.3);
    }
    @keyframes slideDownFade {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulseGlow {
        0%, 100% {
            box-shadow: 0 0 15px rgba(255, 81, 0, 0.2);
        }
        50% {
            box-shadow: 0 0 25px rgba(255, 81, 0, 0.5);
        }
    }

    /* 2. OVAL CATEGORY BUTTONS */
    .menu-landing {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        max-width: 800px;
        margin: 0 auto 40px;
    }

    .menu-box {
        background: #ff5100;
        border: none;
        border-radius: 8px; /* Unified Shape: 8px radius */
        padding: 40px 20px;
        text-align: center;
        color: white;
        box-shadow: 0 20px 60px rgba(255, 81, 0, 0.2); /* Softer, wider shadow */
        cursor: pointer;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .menu-box:hover { 
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(255, 81, 0, 0.5); 
        background: #000;
    }
    .menu-box h3 { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 600; margin: 0; display: flex; align-items: center; justify-content: center; gap: 10px; letter-spacing: 0.5px; }
    .menu-box h3 i { margin-right: 12px; } /* Breathability */
    
    /* 3. MENU CARDS */
    .menu-grid {
        display: none;
        grid-template-columns: repeat(4, 1fr); /* 4-Column Grid */
        gap: 30px;
        padding-top: 20px;
    }
    
    @media (max-width: 1200px) { .menu-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 900px) { .menu-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px) { .menu-grid { grid-template-columns: 1fr; } }

    .menu-card {
        background: #181818; /* Charcoal */
        border-radius: 30px;
        padding: 25px;
        position: relative;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3); /* Softer, wider shadow */
        text-align: center;
        font-family: 'Poppins', sans-serif;
        transition: transform 0.3s ease;

        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        height: 100%;
    }

    .menu-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(255, 81, 0, 0.6);
        border-color: #ff5100;
    }

    /* Floating Image */
    .card-img {
        width: 100%;
        height: 200px;
        object-fit: contain;
        margin-bottom: 15px;
        filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1));
        z-index: 1;
        pointer-events: none; /* Prevents image from blocking clicks */
    }

    .menu-card:hover .card-img { transform: scale(1.05); transition: transform 0.3s ease; }

    .menu-card h3 { 
        margin-top: 0;
        font-weight: 900;
        font-size: 1.5rem;
        text-transform: uppercase;
        color: #fff;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 3.6rem;
    }
    
    .menu-card p { 
        color: #ccc;
        font-size: 0.9rem;
        margin: 15px 0 25px 0;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 4.5em;
    }

    .menu-content {
        margin-top: 0;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .price {
        font-size: 1.4rem;
        font-weight: 900;
        color: #e63946;
    }

    .fav-btn {
        position: absolute;
        top: 10px;
        right: 15px;
        background: transparent;
        border: none;
        font-size: 20px;
        color: #ccc; /* Default Grey */
        cursor: pointer;
        z-index: 20; /* Higher than image (10) */
        transition: transform 0.2s;
    }
    .fav-btn.active {
        color: #ff0000;
    }
    .fav-btn:hover { transform: scale(1.1); }

    .new-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #ff0000;
        color: white;
        padding: 4px 10px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.7rem;
        z-index: 20; /* Higher than image */
        text-transform: uppercase;
    }

    .bestseller-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #ffc107; /* Gold */
        color: black;
        padding: 4px 10px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.7rem;
        z-index: 20; /* Higher than image */
        text-transform: uppercase;
    }

    /* 4. CONTROLS & ADD BUTTON */
    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px dashed #eee;
        padding-top: 20px;
    }

    .menu-card select {
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ddd;
        background: #f9f9f9;
        color: #333;
        font-size: 0.9rem;
        width: 100%;
        margin-bottom: 10px;
    }

    .add-btn {
        background: #2ecc71;
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 8px; /* Unified Shape */
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        text-decoration: none;
        font-weight: bold;
        border: none;
        cursor: pointer;
    }
    
    /* Hide Qty Input visually but keep for logic */
    .qty {
        display: none; 
    }

    /* 5. CART BUTTON & MODAL */
    #cart-trigger {
        position: fixed; bottom: 20px; right: 20px; background: #FFA500; color: black;
        padding: 15px 25px; border-radius: 50px; font-weight: bold; cursor: pointer;
        z-index: 2000;
        box-shadow: 0 4px 15px rgba(0,0,0,0.5); border: 2px solid white;
    }

    #cart-modal {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.85); z-index: 3000; justify-content: center; align-items: center;
    }

    .cart-content {
        background: #1a1a1a; color: white; width: 95%; max-width: 500px; border-radius: 15px;
        padding: 25px; max-height: 85vh; overflow-y: auto; border: 2px solid #FFA500;
    }

    .cart-item {
        display: flex; justify-content: space-between; align-items: center;
        border-bottom: 1px solid #333; padding: 12px 0;
    }

    /* TOAST NOTIFICATION */
    #toast {
        visibility: hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center;
        border-radius: 50px; padding: 16px; position: fixed; z-index: 5000; left: 50%; bottom: 30px;
        transform: translateX(-50%); font-size: 17px; box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        border: 1px solid #FFA500;
    }
    #toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
    @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
    @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }

    .checkout-form { display: none; margin-top: 20px; border-top: 1px solid #444; padding-top: 20px; text-align: left; }
    .checkout-form label { display: block; margin-bottom: 5px; color: #ccc; font-size: 0.9rem; }
    .checkout-form select, .checkout-form input { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; background: #333; color: white; border: 1px solid #555; }
    .hidden { display: none; }

    .total-price { font-size: 24px; font-weight: bold; text-align: right; margin: 20px 0; color: #FFA500; }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<?php if($storeStatus === 'closed'): ?>
    <div class="store-closed-banner">⛔ The store is currently CLOSED. Orders cannot be placed at this time.</div>
<?php endif; ?>

<div id="menu-content">
    <div class="branch-info-bar">
        <div class="branch-left">
            <div class="branch-icon-circle"><i class="fas fa-map-marker-alt"></i></div>
            <div>
                <div class="branch-label">Current Location</div>
                <div class="branch-name" id="current-branch-display">Loading...</div>
            </div>
        </div>
        <button class="branch-action-btn" onclick="changeBranch()">Change Branch</button>
    </div>

    <!-- Level 1: Main Choice -->
    <section class="menu-landing" id="main-choice">
        <div class="menu-box" onclick="showCategories()"><h3><i class="fas fa-utensils"></i> Standard Menu</h3></div>
        <div class="menu-box" onclick="window.location.href='custom_burger.php'"><h3><i class="fas fa-layer-group"></i> Custom Burger</h3></div>
    </section>

    <!-- Level 2: Categories (Hidden initially) -->
    <section class="menu-landing" id="category-choice" style="display:none;">
        <div class="menu-box" data-category="burger"><h3>Burger</h3></div>
        <div class="menu-box" data-category="special"><h3>Special</h3></div>
        <div class="menu-box" data-category="minuman"><h3>Minuman</h3></div>
        <div class="menu-box" data-category="addon"><h3>Add-On</h3></div>
        <div class="menu-box" onclick="showMainChoice()" style="background:#333; border-color:#555;"><h3>⬅ Back</h3></div>
    </section>

    <section class="menu-grid" id="menuGrid">
    <?php
    $categories = ['burger', 'special', 'addon', 'minuman'];
    foreach ($categories as $cat) {
        if (isset($menuItems[$cat])) {
            foreach ($menuItems[$cat] as $item) {
                // Filter out items without images
                if (in_array($item['name'], ['Ayam Goreng Krup Krap XL', 'Daging / Ayam Biasa', 'Sosej Jumbo', 'Benjo', 'Burger XL', 'Burger Mix'])) {
                    continue;
                }

                $variants = $item['variants'] ? json_decode($item['variants'], true) : null;
                
                // Check if item is new (created within last 7 days)
                $isNew = false;
                if (!empty($item['created_at'])) {
                    $isNew = (new DateTime($item['created_at']) > new DateTime('-7 days'));
                }
                $isBestSeller = in_array($item['name'], $bestSellers);
                $isFavorite = in_array($item['id'], $user_favorites);

                // Image Mapping Logic
                $lowerName = strtolower($item['name']);
                $imgFilename = str_replace(' ', '', $lowerName) . '.png'; // Default behavior

                // Custom Overrides for specific items (using .jpeg or .jpg)
                if ($lowerName === 'ayam goreng krup krap') $imgFilename = 'ayamkrupkrap.jpg';
                if ($lowerName === 'burger mix xl') $imgFilename = 'xl.jpg';
                if ($lowerName === 'lava cheese burger') $imgFilename = 'lavacheese.jpg';
                if ($lowerName === 'cheese steak') $imgFilename = 'cheesesteak.jpg';
                if ($lowerName === 'chicken grill burger') $imgFilename = 'grill.jpg';
                if ($lowerName === 'hawaiian spicy') $imgFilename = 'hawaii.jpg';
                if ($lowerName === 'burger kambing') $imgFilename = 'kambing.jpg';
                if ($lowerName === 'burger sate ayam') $imgFilename = 'sate.jpg';
                if ($lowerName === 'smash burger') $imgFilename = 'smash.jpg';
                if ($lowerName === 'mozzarella cheese') $imgFilename = 'mozz.jpg';
                if ($lowerName === 'telur') $imgFilename = 'egg.jpg';
                if ($lowerName === 'cheddar cheese') $imgFilename = 'cheddar.jpg';
                if ($lowerName === 'green tea') $imgFilename = 'green.jpg';
                if ($lowerName === 'chocolate') $imgFilename = 'milo.jpg';
                if ($lowerName === 'indocafe') $imgFilename = 'kopi.jpg';
                if ($lowerName === 'teh') $imgFilename = 'tea.jpg';
                if ($lowerName === 'kopi') $imgFilename = 'black.jpg';
                if ($lowerName === 'jus buah') $imgFilename = 'oren.jpg';
                if ($lowerName === 'teh o limau') $imgFilename = 'limau.jpg';
                if ($lowerName === 'minuman bergas') $imgFilename = 'aw.jpg';
                if ($lowerName === 'burger wagyu') $imgFilename = 'wagyu.jpg';
                if ($lowerName === 'burger itik') $imgFilename = 'itik.jpg';
                if ($lowerName === 'nugget tempura') $imgFilename = 'nug.jpg';
                if ($lowerName === 'cheezy wedges') $imgFilename = 'wedgesss.jpg';
                if ($lowerName === 'ayam popcorn') $imgFilename = 'pop.jpg';
                ?>
                <div class="menu-card <?php echo $cat; ?>">
                    <img src="images/<?php echo $imgFilename; ?>" 
                         class="card-img"
                         onerror="this.src='images/hero_burger.png'" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>">

                    <?php if($isBestSeller): ?><div class="bestseller-badge">BEST</div><?php endif; ?>
                    
                    <button type="button" class="fav-btn <?php echo $isFavorite ? 'active' : ''; ?>" onclick="toggleFav(this, <?php echo $item['id']; ?>)">
                        <?php echo $isFavorite ? '❤️' : '🤍'; ?>
                    </button>
                    
                    <div class="menu-content">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        
                        <?php if ($item['has_protein']): ?>
                            <select class="protein">
                                <option value="Ayam">Ayam</option>
                                <option value="Daging">Daging</option>
                            </select>
                        <?php endif; ?>
                        
                        <?php if ($variants): ?>
                            <select class="variant" onchange="updatePrice(this, <?php echo $item['id']; ?>)">
                                <?php foreach ($variants as $v): ?>
                                    <option value="<?php echo $v['name']; ?>" data-price="<?php echo $v['price']; ?>">
                                        <?php echo $v['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer">
                        <span class="price" id="price-<?php echo $item['id']; ?>">
                            RM <?php echo number_format($item['price'], 2); ?>
                        </span>
                        
                        <input type="number" class="qty" value="1">
                        
                        <?php if($storeStatus === 'open'): ?>
                        <a href="#" class="add-btn" onclick="event.preventDefault(); addToCart(this);">
                            +
                        </a>
                        <?php else: ?>
                        <span style="color:#777; font-size:12px;">Closed</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
        }
    }
    ?>
    </section>
</div>

<button id="cart-trigger" onclick="toggleCart()">🛒 My Cart (<span id="cart-count">0</span>)</button>

<div id="toast">Item added to cart!</div>

<div id="cart-modal">
    <div class="cart-content">
        <h2 style="color:#FFA500; margin-top:0;">Your Selection</h2>
        <div id="cart-items-list"></div>
        <div class="total-price">Total: RM <span id="cart-total">0.00</span></div>
        
        <div id="checkout-section" class="checkout-form">
            <h3 style="color:white;">Checkout Details</h3>
            
            <div id="sender-name-container">
                <label>Your Name:</label>
                <input type="text" id="sender-name" placeholder="Enter your name">
            </div>

            <div id="sender-phone-container" style="display: none;">
                <label>Phone Number:</label>
                <input type="text" id="sender-phone" readonly>
            </div>

            <label>Branch:</label>
            <div id="cart-branch-display" style="padding: 10px; background: #333; border-radius: 5px; margin-bottom: 15px;"></div>

            <label>Order Type:</label>
            <select id="order-type" onchange="toggleAddressField()">
                <option value="Delivery">Delivery</option>
                <option value="Pick-Up">Pick-Up</option>
            </select>
            
            <div id="address-container">
                <label>Delivery Address:</label>
                <textarea id="delivery-address" placeholder="Enter full delivery address..." style="width:100%; padding:10px; margin-bottom:15px; border-radius:5px; background:#333; color:white; border:1px solid #555;"></textarea>
            </div>
            
            <label>Payment Method:</label>
            <select id="order-payment" onchange="togglePaymentInfo()">
                <option value="Cash">Cash</option>
                <option value="E-Wallet">E-Wallet</option>
                <option value="Transfer">Transfer</option>
            </select>

            <div id="payment-details" class="hidden">
                <div style="background:#222; padding:10px; border-radius:5px; margin-bottom:10px; text-align:center;">
                    <p style="color:#FFA500; margin:0;"><strong>Bank Transfer / QR</strong></p>
                    <p style="font-size:0.8rem; margin:5px 0;">Maybank: 551234567890<br>Bambam Burger Sdn Bhd</p>
                </div>
                <label>Upload Receipt:</label>
                <input type="file" id="payment-proof" accept="image/*">
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top: 15px;">
            <button onclick="toggleCart()" style="flex:1; padding:12px; background:#444; color:white; border:none; border-radius:5px; cursor:pointer;">Back</button>
            <button id="btn-checkout-action" onclick="showCheckoutForm()" style="flex:2; padding:12px; background:#FFA500; color:black; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">Proceed to Checkout</button>
        </div>
    </div>
</div>

<script>
const menuData = <?php echo json_encode($jsMenuData); ?>;
const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
let cart = JSON.parse(localStorage.getItem('bambam_cart')) || [];

function showCategories() {
    document.getElementById('main-choice').style.display = 'none';
    document.getElementById('category-choice').style.display = 'grid';
}

function showMainChoice() {
    document.getElementById('category-choice').style.display = 'none';
    document.getElementById('menuGrid').style.display = 'none';
    document.getElementById('main-choice').style.display = 'grid';
}

function changeBranch() {
    window.location.href = 'branch_selection.php';
}

function toggleFav(btn, productId) {
    if (!isLoggedIn) {
        if (confirm("You must be logged in to add favorites. Go to login page?")) {
            window.location.href = 'login.php';
        }
        return;
    }

    const formData = new FormData();
    formData.append('product_id', productId);

    fetch('toggle_favorite.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.action === 'added') {
                btn.innerHTML = '❤️';
                btn.classList.add('active');
            } else {
                btn.innerHTML = '🤍';
                btn.classList.remove('active');
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Favorite toggle error:', error));
}

function updatePrice(sel, id) {
    const price = sel.selectedOptions[0].dataset.price;
    document.getElementById('price-' + id).innerText = 'RM ' + parseFloat(price).toFixed(2);
}

function addToCart(btn) {
    if (!isLoggedIn) {
        if (confirm("You need to login to add items to your cart.\n\nClick OK to Login/Sign Up.")) {
            window.location.href = 'login.php';
        }
        return;
    }

    const card = btn.closest('.menu-card');
    const name = card.querySelector('h3').innerText;
    const qty = parseInt(card.querySelector('.qty').value);
    
    const variantSelect = card.querySelector('.variant');
    const variantName = variantSelect ? variantSelect.value : 'Standard';
    const price = variantSelect ? 
        parseFloat(variantSelect.selectedOptions[0].dataset.price) : 
        parseFloat(card.querySelector('.price').innerText.replace('RM ', ''));

    const proteinSelect = card.querySelector('.protein');
    const protein = proteinSelect ? proteinSelect.value : '';

    const itemID = `${name}-${variantName}-${protein}`;
    const existing = cart.find(i => i.id === itemID);

    if(existing) {
        existing.qty += qty;
    } else {
        cart.push({ id: itemID, name, price, qty, variant: variantName, protein });
    }


    // Show Toast Notification
    const toast = document.getElementById("toast");
    if(window.toastTimeout) clearTimeout(window.toastTimeout); // Clear previous timer
    toast.innerText = "✅ " + name + " added to cart!";
    toast.classList.add("show");
    // Hide after 3 seconds
    window.toastTimeout = setTimeout(function(){ toast.classList.remove("show"); }, 3000);

    updateCartUI();
}

function updateCartUI() {
    const list = document.getElementById('cart-items-list');
    const totalSpan = document.getElementById('cart-total');
    const countSpan = document.getElementById('cart-count');
    
    list.innerHTML = '';
    let total = 0, count = 0;
    
    // Helper to generate options
    const getOptions = (selected, opts) => opts.map(o => `<option value="${o.name}" ${selected===o.name?'selected':''}>${o.name}</option>`).join('');

    cart.forEach((item, index) => {
        total += (item.price * item.qty);
        count += item.qty;
        
        const itemInfo = menuData[item.name] || {};
        
        // Protein Select
        let proteinInput = '';
        if(itemInfo.has_protein == 1) {
            proteinInput = `<select onchange="updateCartItem(${index}, 'protein', this.value)" style="padding:5px; margin:2px 0; width:100%; background:#333; color:white; border:1px solid #555; border-radius:4px;">
                <option value="Ayam" ${item.protein === 'Ayam' ? 'selected' : ''}>Ayam</option>
                <option value="Daging" ${item.protein === 'Daging' ? 'selected' : ''}>Daging</option>
            </select>`;
        }

        // Variant Select
        let variantInput = `<small>${item.variant}</small>`;
        if(itemInfo.variants && itemInfo.variants.length > 0) {
            variantInput = `<select onchange="updateCartItem(${index}, 'variant', this.value)" style="padding:5px; margin:2px 0; width:100%; background:#333; color:white; border:1px solid #555; border-radius:4px;">
                ${itemInfo.variants.map(v => `<option value="${v.name}" ${item.variant===v.name?'selected':''}>${v.name} (RM ${v.price})</option>`).join('')}
            </select>`;
        }

        list.innerHTML += `
            <div class="cart-item">
                <div style="flex:1; margin-right:10px;">
                    <strong>${item.name}</strong><br>
                    ${proteinInput}
                    ${variantInput}
                    <input type="text" placeholder="Add note..." value="${item.note || ''}" onchange="updateCartItem(${index}, 'note', this.value)" style="width:100%; padding:5px; margin-top:5px; background:#333; color:white; border:1px solid #555; border-radius:4px; font-size:12px;">
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <button onclick="changeQty(${index}, -1)" style="width:25px; cursor:pointer;">-</button>
                    <span>${item.qty}</span>
                    <button onclick="changeQty(${index}, 1)" style="width:25px; cursor:pointer;">+</button>
                    <span style="min-width:60px; text-align:right;">RM ${(item.price * item.qty).toFixed(2)}</span>
                    <button onclick="removeItem(${index})" style="background:none; border:none; color:#ff4444; cursor:pointer; margin-left:5px;"><i class="fas fa-trash"></i></button>
                </div>
            </div>`;
    });

    totalSpan.innerText = total.toFixed(2);
    countSpan.innerText = count;
    localStorage.setItem('bambam_cart', JSON.stringify(cart));
}

function updateCartItem(index, field, value) {
    if (field === 'protein') cart[index].protein = value;
    if (field === 'note') cart[index].note = value;
    if (field === 'variant') {
        cart[index].variant = value;
        // Update price based on variant
        const itemInfo = menuData[cart[index].name];
        const newVariant = itemInfo.variants.find(v => v.name === value);
        if(newVariant) cart[index].price = newVariant.price;
    }
    localStorage.setItem('bambam_cart', JSON.stringify(cart));
    updateCartUI();
}

function changeQty(index, amt) {
    cart[index].qty += amt;
    if(cart[index].qty < 1) cart.splice(index, 1);
    updateCartUI();
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCartUI();
}

function toggleAddressField() {
    const type = document.getElementById('order-type').value;
    document.getElementById('address-container').style.display = (type === 'Delivery') ? 'block' : 'none';
}

function toggleCart() {
    const modal = document.getElementById('cart-modal');
    modal.style.display = (modal.style.display === 'flex') ? 'none' : 'flex';
}

function togglePaymentInfo() {
    const method = document.getElementById('order-payment').value;
    const details = document.getElementById('payment-details');
    details.style.display = (method === 'Cash') ? 'none' : 'block';
}

function showCheckoutForm() {
    const form = document.getElementById('checkout-section');
    const btn = document.getElementById('btn-checkout-action');

    if (form.style.display === 'block') {
        // Already showing form, so this is the "Place Order" action
        placeOrder();
    } else {
        // Show form
        form.style.display = 'block';
        btn.innerText = "Place Order";
        // Populate branch display when showing checkout form
        const branch = localStorage.getItem('selected_branch') || 'Kangar';
        // Auto-fill name if logged in
        if (isLoggedIn) {
            let storedName = localStorage.getItem('current_user_name') || '';
            if (storedName.startsWith('"') && storedName.endsWith('"')) {
                storedName = storedName.slice(1, -1);
            }
            const nameInput = document.getElementById('sender-name');
            nameInput.value = storedName;
            nameInput.readOnly = true;
            nameInput.style.backgroundColor = '#555';
            nameInput.style.color = '#aaa';
            document.getElementById('sender-name-container').style.display = 'block';

            // Auto-fill phone if logged in
            let storedPhone = localStorage.getItem('current_user_phone') || '';
            if (storedPhone.startsWith('"') && storedPhone.endsWith('"')) {
                storedPhone = storedPhone.slice(1, -1);
            }
            const phoneInput = document.getElementById('sender-phone');
            phoneInput.value = storedPhone;
            phoneInput.style.backgroundColor = '#555';
            phoneInput.style.color = '#aaa';
            document.getElementById('sender-phone-container').style.display = 'block';
        }
        document.getElementById('cart-branch-display').innerText = branch;
    }
}

function placeOrder() {
    if(cart.length === 0) return alert("Cart is empty!");

    const branch = localStorage.getItem('selected_branch') || 'Kangar';

    const formData = new FormData();
    formData.append('branch', branch);
    formData.append('order_type', document.getElementById('order-type').value);
    formData.append('payment_method', document.getElementById('order-payment').value);
    formData.append('total', document.getElementById('cart-total').innerText);
    formData.append('items', JSON.stringify(cart));

    if (document.getElementById('order-type').value === 'Delivery') {
        const addr = document.getElementById('delivery-address').value.trim();
        if (!addr) return alert("Please enter your delivery address.");
        formData.append('address', addr);
    }

    // Always send name if present (auto-filled or typed)
    const name = document.getElementById('sender-name').value.trim();
    if (!isLoggedIn && !name) return alert("Please enter your name.");
    if (name) formData.append('sender_name', name);

    const payment = document.getElementById('order-payment').value;
    if(payment !== 'Cash') {
        const file = document.getElementById('payment-proof').files[0];
        if(!file) return alert("Please upload receipt.");
        formData.append('payment_proof', file);
    }

    fetch('place_order.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            // Show Success Popup with Email Status
            let msg = "Order Placed Successfully! (#" + data.order_id + ")";
            if(data.email_status.includes("Failed") || data.email_status.includes("missing")) {
                msg += "\n\n⚠️ Email Warning: " + data.email_status;
                if(data.email_error) msg += "\nError: " + data.email_error;
            } else {
                msg += "\n\n📧 Confirmation email sent.";
            }
            alert(msg);

            localStorage.removeItem('bambam_cart');
            window.location.href = 'receipt.php?id=' + data.order_id;
        } else {
            alert("Order failed: " + data.message);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    updateCartUI();
    toggleAddressField();

    // Auto-open cart if requested (e.g. from Reorder)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('cart_open') === 'true') {
        toggleCart();
    }

    const branch = localStorage.getItem('selected_branch') || 'Main Branch';
    document.getElementById('current-branch-display').innerText = branch;

    document.querySelectorAll('.menu-box').forEach(box => {
        box.addEventListener('click', () => {
            const cat = box.dataset.category;
            if (!cat) return;
            document.getElementById('menuGrid').style.display = 'grid';
            document.querySelectorAll('.menu-card').forEach(card => {
                card.style.display = card.classList.contains(cat) ? 'flex' : 'none';
            });
            window.scrollTo({top: document.getElementById('menuGrid').offsetTop - 100, behavior: 'smooth'});
        });
    });
});
</script>

<?php include 'footer.php'; ?>