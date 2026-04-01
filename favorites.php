<?php include 'header.php'; ?>
<?php include_once 'db.php'; ?>

<style>
    /* Refined Aesthetic for Favorites Page */
    body {
        background: #181818 !important; /* Charcoal Background */
        color: #ffffff; /* White Text */
        padding-top: 100px; /* Space for fixed header */
    }

    /* Elegant Page Title */
    .page-title {
        text-align: center;
        margin: 40px auto;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .page-title h2 {
        font-family: 'Poppins', sans-serif; /* Modern Sans-Serif */
        font-weight: 800; /* Thick, bold letters */
        font-size: 48px;
        color: #ffffff;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px; /* Breathing room */
        text-transform: uppercase;
    }
    .page-title svg {
        width: 50px;
        height: 50px;
        filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4)); /* 3D Shadow */
        animation: pulse 2s infinite ease-in-out;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.15); filter: drop-shadow(0 0 15px rgba(255, 81, 0, 0.6)); }
    }

    /* Grid Layout */
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* Better responsive scaling */
        gap: 20px;
        max-width: 1000px;
        margin: 40px auto;
        padding: 20px;
    }

    /* Floating Card Design */
    .menu-card {
        background: rgba(255, 255, 255, 0.05); /* Very subtle white tint */
        backdrop-filter: blur(15px);           /* Blurs background elements behind card */
        color: #ffffff;
        border-radius: 20px; /* Softened corners */
        padding: 35px; /* Increased padding */
        border: 1px solid rgba(255, 255, 255, 0.1); /* Thin "glass" edge */
        position: relative;
        text-align: center;
        box-shadow: 0 15px 40px rgba(0,0,0,0.4);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: visible; /* Allow image to break container */
        margin-top: 40px; /* Space for the pop-up image */
    }
    .menu-card:hover {
        transform: translateY(-5px);           /* Sophisticated "lift" effect */
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5); /* Deepens shadow for realism */
    }

    /* Image Safety Container */
    .card-img-container {
        width: 140px;
        height: 140px;
        margin: -80px auto 20px; /* Negative margin to pop out */
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .card-img-container::after {
        content: '🍔';
        font-size: 40px;
        position: absolute;
        z-index: 0;
        opacity: 0.5;
    }
    .card-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: relative;
        z-index: 1;
        border-radius: 50%; /* Circular crop */
        box-shadow: 0 10px 20px rgba(0,0,0,0.3);
    }

    /* Card Content Typography & Controls */
.card-content {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* Align content */
}
.menu-card h3 { margin: 15px 0 5px 0; color: #ffffff; font-size: 20px; font-weight: 700; font-family: 'Poppins', sans-serif; }
.menu-card p { color: #B0B0B0; font-size: 14px; line-height: 1.5; min-height: 50px; margin-bottom: 15px; font-family: 'Poppins', sans-serif; }
.menu-card .price { color: #ff5100; font-weight: 900; font-size: 26px; margin-top: auto; font-family: 'Poppins', sans-serif; }
    .menu-card select { 
        width: 100%;
        padding: 10px; 
        border-radius: 8px; 
        border: 1px solid rgba(255,255,255,0.2);
        background: #222 url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23FFFFFF%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E') no-repeat right 10px center;
        background-size: 10px;
        appearance: none;
        color: #fff;
        margin-bottom: 15px;
        cursor: pointer;
    }
    .card-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #333; padding-top: 20px; margin-top: 20px; }
    .add-btn { 
        background: #ff5100; 
        color: white;
        border: none; 
        width: 45px; height: 45px;
        border-radius: 12px; 
        cursor: pointer; 
        font-weight: bold; 
        font-size: 18px;
        display: flex; align-items: center; justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .add-btn:hover { background: #e04600; transform: scale(1.1); box-shadow: 0 0 15px rgba(255, 81, 0, 0.6); }
    
    /* Remove from Favorites Button */
    .fav-btn {
        position: absolute; top: 10px; right: 10px;
        background: transparent;
        border: none;
        width: 30px; height: 30px; 
        font-size: 20px; color: #777;
        cursor: pointer; 
        z-index: 10;
        transition: all 0.3s ease;
    }
    .fav-btn:hover { color: #e74c3c; transform: scale(1.2); }
    
    /* Empty State */
    .empty-msg { 
        grid-column: 1 / -1; 
        text-align: center; 
        color: #ccc; 
        background: #181818; 
        padding: 60px 20px; 
        border-radius: 15px; 
        border: 1px solid #333;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .empty-msg h3 { color: #fff; margin-bottom: 10px; }
    .empty-msg p { margin-bottom: 25px; color: #aaa; }

    /* Quick View Button */
    .quick-view-btn {
        position: absolute; top: 10px; left: 10px;
        background: transparent;
        border: none;
        width: 30px; height: 30px; 
        font-size: 18px; color: #777;
        cursor: pointer; 
        z-index: 10;
        transition: all 0.3s ease;
    }
    .quick-view-btn:hover { color: #3498db; transform: scale(1.2); }

    /* Quick View Modal */
    #qv-modal {
        display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.85); backdrop-filter: blur(8px);
        align-items: center; justify-content: center;
    }
    .qv-content {
        background: #181818; border: 1px solid rgba(255,255,255,0.1); border-radius: 20px;
        width: 90%; max-width: 700px; display: flex; overflow: hidden; position: relative;
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    }
    .qv-image-col { flex: 1; background: rgba(255,255,255,0.02); display: flex; align-items: center; justify-content: center; padding: 40px; }
    .qv-image-col img { width: 100%; max-width: 280px; filter: drop-shadow(0 15px 30px rgba(0,0,0,0.4)); border-radius: 10px; }
    .qv-details-col { flex: 1.2; padding: 40px; display: flex; flex-direction: column; justify-content: center; }
    .qv-close { position: absolute; top: 15px; right: 20px; color: #aaa; font-size: 28px; cursor: pointer; background: none; border: none; }
    .qv-close:hover { color: white; }
    .qv-title { font-family: 'Playfair Display', serif; font-size: 32px; margin: 0 0 15px 0; color: white; }
    .qv-desc { color: #ccc; line-height: 1.6; margin-bottom: 25px; font-size: 15px; }
    .qv-price { font-size: 28px; color: #ff5100; font-weight: 800; margin-bottom: 25px; }
    .qv-add-btn {
        background: #ff5100; color: white; border: none; padding: 15px 30px;
        border-radius: 50px; font-weight: bold; font-size: 16px; cursor: pointer;
        transition: 0.3s; width: fit-content;
    }
    .qv-add-btn:hover { background: #e04600; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(255, 81, 0, 0.3); }

    @media (max-width: 768px) {
        .qv-content { flex-direction: column; max-height: 90vh; overflow-y: auto; }
        .qv-image-col { padding: 30px; min-height: 250px; }
        .qv-image-col img { max-width: 180px; }
        .qv-details-col { padding: 30px; }
    }

    /* CART UI (Reused) */
    #cart-trigger { position: fixed; bottom: 20px; right: 20px; background: #FFA500; color: black; padding: 15px 25px; border-radius: 50px; font-weight: bold; cursor: pointer; z-index: 2000; box-shadow: 0 4px 15px rgba(0,0,0,0.5); border: 2px solid white; }
    #cart-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 3000; justify-content: center; align-items: center; }
    .cart-content { background: #1a1a1a; color: white; width: 90%; max-width: 500px; border-radius: 15px; padding: 25px; max-height: 80vh; overflow-y: auto; border: 2px solid #FFA500; }
    .cart-item { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #333; padding: 15px 0; }
    .cart-controls { display: flex; align-items: center; gap: 10px; }
    .cart-controls button { background: #444; color: white; border: none; width: 30px; height: 30px; border-radius: 5px; cursor: pointer; font-size: 18px; }
    .total-price { font-size: 22px; font-weight: bold; text-align: right; margin-top: 20px; color: #FFA500; }
    .btn-group { display: flex; gap: 10px; margin-top: 20px; }
    .btn-close { background: #666; color: white; border: none; flex: 1; padding: 12px; border-radius: 5px; cursor: pointer; }
    .btn-checkout { background: #FFA500; color: black; border: none; flex: 2; padding: 12px; border-radius: 5px; cursor: pointer; font-weight: bold; }
</style>

<div class="page-title">
    <h2>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <defs>
                <linearGradient id="heart3d" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#ff9a44;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#e04600;stop-opacity:1" />
                </linearGradient>
            </defs>
            <path fill="url(#heart3d)" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>
        My Favorites
    </h2>
</div>

<section class="menu-grid" id="favGrid">
    <?php
    // Get the logged-in user's ID
    $user_id = $_SESSION['user_id'] ?? null;

    if ($user_id) {
        try {
            // SQL to get only this user's favorite burgers using PDO
            // Assumes a 'favorites' table exists with columns: id, user_id, product_id
            $stmt = $pdo->prepare("
                SELECT m.*, f.id as fav_record_id 
                FROM menu_items m 
                JOIN favorites f ON m.id = f.product_id 
                WHERE f.user_id = ?
            ");
            $stmt->execute([$user_id]);
            $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($favorites) > 0) {
                foreach($favorites as $row) {
                    // Replicate Image Logic from menu.php
                    $lowerName = strtolower($row['name']);
                    $imgFilename = str_replace(' ', '', $lowerName) . '.png';
                    
                    // Custom overrides (copied from menu.php for consistency)
                    if ($lowerName === 'ayam goreng krup krap') $imgFilename = 'ayamkrupkrap.jpg';
                    if ($lowerName === 'burger mix xl') $imgFilename = 'xl.jpg';
                    if ($lowerName === 'lava cheese burger') $imgFilename = 'lavacheese.jpg';
                    if ($lowerName === 'chicken grill burger') $imgFilename = 'grill.jpg';
                    if ($lowerName === 'beef smash burger') $imgFilename = 'smash.jpg';
                    if ($lowerName === 'wagyu burger') $imgFilename = 'wagyu.jpg';
                    if ($lowerName === 'burger sate ayam') $imgFilename = 'sate.jpg';
                    // Add other overrides here if needed
                    ?>
                    <div class="menu-card">
                        <button class="quick-view-btn" onclick="openQuickView('<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars($row['description']); ?>', '<?php echo $row['price']; ?>', 'images/<?php echo $imgFilename; ?>')" title="Quick View"><i class="fas fa-eye"></i></button>
                        <button class="fav-btn" onclick="removeFavDB(<?php echo $row['fav_record_id']; ?>, this)" title="Remove"><i class="fas fa-times"></i></button>
                        
                        <div class="card-img-container">
                            <img src="images/<?php echo $imgFilename; ?>" 
                                 onerror="this.style.display='none'"
                                 alt="<?php echo htmlspecialchars($row['name']); ?>">
                        </div>
                        
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p><?php echo htmlspecialchars($row['description']); ?></p>
                            
                            <!-- Hidden inputs for addToCart logic -->
                            <input type="hidden" class="qty" value="1">
                            <?php if ($row['has_protein']): ?>
                                <select class="protein">
                                    <option value="Ayam">Ayam</option>
                                    <option value="Daging">Daging</option>
                                </select>
                            <?php endif; ?>
                            
                            <div class="price">RM <?php echo number_format($row['price'], 2); ?></div>
                        </div>
                        
                        <div class="card-footer">
                            <button class="add-btn" onclick="addToCart(this)" title="Add to Cart"><i class="fas fa-cart-plus"></i></button>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="empty-msg"><h3>You haven\'t added any favorites yet! 🍔</h3><p>Go explore our menu and find something delicious.</p><a href="menu.php" style="display:inline-block; padding:12px 25px; background:#ff5100; color:white; text-decoration:none; border-radius:8px; font-weight:bold; transition:0.3s;">Browse Menu</a></div>';
            }
        } catch (PDOException $e) {
            echo '<div class="empty-msg"><h3>Error loading favorites.</h3></div>';
        }
    } else {
        echo '<div class="empty-msg"><h3>Please login to view favorites.</h3></div>';
    }
    ?>
</section>

<!-- QUICK VIEW MODAL -->
<div id="qv-modal">
    <div class="qv-content">
        <button class="qv-close" onclick="closeQuickView()">&times;</button>
        <div class="qv-image-col">
            <img id="qv-img" src="" alt="Burger">
        </div>
        <div class="qv-details-col">
            <h2 id="qv-title" class="qv-title"></h2>
            <p id="qv-desc" class="qv-desc"></p>
            <div id="qv-price" class="qv-price"></div>
            <button class="qv-add-btn" onclick="closeQuickView()">Close</button>
        </div>
    </div>
</div>

<!-- CART BUTTON & MODAL -->
<button id="cart-trigger" onclick="toggleCart()">🛒 My Cart (<span id="cart-count">0</span>)</button>

<div id="cart-modal">
    <div class="cart-content">
        <h2 style="color:#FFA500; margin-top:0;">Your Selection</h2>
        <div id="cart-items-list"></div>
        <div class="total-price">Total: RM <span id="cart-total">0.00</span></div>
        
        <div style="margin-top:20px; text-align:left;">
            <label style="display:block; margin-bottom:5px; color:#ccc;"><?php echo $t['branch']; ?>:</label>
            <div id="cart-branch-display" style="padding: 10px; background: #333; border-radius: 5px; margin-bottom: 10px; border:1px solid #555;"></div>

            <label style="display:block; margin-bottom:5px; color:#ccc;"><?php echo $t['order_type']; ?>:</label>
            <select id="order-type" onchange="toggleAddressField()" style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; background:#333; color:white; border:1px solid #555;">
                <option value="Delivery">Delivery</option>
                <option value="Take-Away"><?php echo $t['take_away']; ?></option>
                <option value="Drive-Thru"><?php echo $t['drive_thru']; ?></option>
            </select>
            
            <div id="address-container">
                <label style="display:block; margin-bottom:5px; color:#ccc;">Delivery Address:</label>
                <textarea id="delivery-address" placeholder="Enter full delivery address..." style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; background:#333; color:white; border:1px solid #555;"></textarea>
            </div>
            
            <label style="display:block; margin-bottom:5px; color:#ccc;"><?php echo $t['payment']; ?>:</label>
            <select id="order-payment" onchange="togglePaymentInfo()" style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; background:#333; color:white; border:1px solid #555;">
                <option value="Cash">Cash</option>
                <option value="E-Wallet">E-Wallet</option>
                <option value="Transfer">Transfer</option>
            </select>

            <!-- Payment Details Container -->
            <div id="payment-details" style="display:none; background:#222; padding:15px; border-radius:5px; margin-bottom:10px; border:1px dashed #555;">
                <!-- E-Wallet Info -->
                <div id="info-ewallet" style="display:none; text-align:center;">
                    <p style="color:#FFA500; font-weight:bold; margin-top:0;">Scan to Pay</p>
                    <div style="width:150px; height:150px; background:white; margin:0 auto 10px; display:flex; align-items:center; justify-content:center; color:black;">QR CODE HERE</div>
                </div>
                
                <!-- Transfer Info -->
                <div id="info-transfer" style="display:none;">
                    <p style="color:#FFA500; font-weight:bold; margin-top:0;"><?php echo $t['bank_details']; ?></p>
                    <p style="margin:5px 0; font-size:14px;">Bank: <strong>Maybank</strong></p>
                    <p style="margin:5px 0; font-size:14px;">Acc No: <strong>551234567890</strong></p>
                    <p style="margin:5px 0; font-size:14px;">Holder: <strong>Bambam Burger Sdn Bhd</strong></p>
                </div>

                <!-- Sender Name -->
                <div id="sender-name-wrapper">
                    <label style="display:block; margin-top:10px; margin-bottom:5px; color:#ccc;"><?php echo $t['sender_name']; ?>:</label>
                    <input type="text" id="sender-name" placeholder="Enter your name" style="width:100%; padding:10px; border-radius:5px; border:1px solid #555; background:#333; color:white;">
                </div>
                
                <!-- Phone Number -->
                <div id="sender-phone-wrapper" style="display:none;">
                    <label style="display:block; margin-top:10px; margin-bottom:5px; color:#ccc;">Phone Number:</label>
                    <input type="text" id="sender-phone" readonly style="width:100%; padding:10px; border-radius:5px; border:1px solid #555; background:#555; color:#aaa;">
                </div>

                <!-- Upload Receipt -->
                <label style="display:block; margin-top:10px; margin-bottom:5px; color:#ccc;"><?php echo $t['upload_receipt']; ?>:</label>
                <input type="file" id="payment-proof" accept="image/*" style="width:100%; color:white;" onchange="previewReceipt(this)">
                
                <!-- Receipt Preview & Confirmation -->
                <div id="receipt-preview-container" style="display:none; margin-top:15px; text-align:center; border:1px solid #555; padding:10px; border-radius:5px;">
                    <p style="color:#ccc; margin:0 0 5px 0;"><?php echo $t['receipt_preview']; ?></p>
                    <img id="receipt-preview-img" style="max-width:100%; max-height:200px; border-radius:5px; margin-bottom:10px;">
                    <label style="display:flex; align-items:center; justify-content:center; gap:10px; color:#FFA500; font-weight:bold; cursor:pointer;">
                        <input type="checkbox" id="confirm-authentic" style="width:20px; height:20px;"> <?php echo $t['confirm_authentic']; ?>
                    </label>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <button class="btn-close" onclick="toggleCart()">Back</button>
            <button class="btn-checkout" onclick="processCheckout()">Order Now</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('favGrid');
    toggleAddressField();

    // If logged in, auto-fill name
    if (isLoggedIn) {
        let storedName = localStorage.getItem('current_user_name') || '';
        if (storedName.startsWith('"') && storedName.endsWith('"')) {
            storedName = storedName.slice(1, -1);
        }
        const nameInput = document.getElementById('sender-name');
        if(nameInput) {
            nameInput.value = storedName;
            nameInput.readOnly = true;
            nameInput.style.backgroundColor = '#555';
            nameInput.style.color = '#aaa';
            const wrapper = document.getElementById('sender-name-wrapper');
            if(wrapper) wrapper.style.display = 'block';
        }

        // Auto-fill phone
        let storedPhone = localStorage.getItem('current_user_phone') || '';
        if (storedPhone.startsWith('"') && storedPhone.endsWith('"')) {
            storedPhone = storedPhone.slice(1, -1);
        }
        const phoneInput = document.getElementById('sender-phone');
        const phoneWrapper = document.getElementById('sender-phone-wrapper');
        if(phoneInput && phoneWrapper) { phoneInput.value = storedPhone; phoneWrapper.style.display = 'block'; }

    }

    // Set branch display in cart
    const branch = localStorage.getItem('selected_branch') || 'Kangar'; // Default
    const branchDisplay = document.getElementById('cart-branch-display');
    if(branchDisplay) branchDisplay.innerText = branch;

});

// Re-implementing Cart Logic for this page
let cart = [];
const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

function addToCart(btn) {
    if (!isLoggedIn) {
        if (confirm("You need to login to add items to your cart.\n\nClick OK to Login/Sign Up.")) {
            window.location.href = 'login.php';
        }
        return;
    }

    const card = btn.closest('.menu-card');
    const name = card.querySelector('h3').innerText;
    const proteinSelect = card.querySelector('.protein');
    const protein = proteinSelect ? proteinSelect.value : '';
    const variantSelect = card.querySelector('.variant');
    const variant = variantSelect ? variantSelect.value : '';
    
    let price = 0;
    if(variantSelect && variantSelect.selectedOptions[0].dataset.price){
        price = parseFloat(variantSelect.selectedOptions[0].dataset.price);
    } else {
        const priceMatch = card.querySelector('p').innerText.match(/[0-9.]+/);
        price = priceMatch ? parseFloat(priceMatch[0]) : 0;
    }

    const qty = parseInt(card.querySelector('.qty').value);
    const itemID = `${name}-${protein}-${variant}`;

    const existing = cart.find(i => i.id === itemID);
    if(existing){ existing.qty += qty; }
    else{ cart.push({ id: itemID, name, protein, variant, price, qty }); }
    updateCartUI();
}

function updateCartUI() {
    const list = document.getElementById('cart-items-list');
    const totalSpan = document.getElementById('cart-total');
    const countSpan = document.getElementById('cart-count');
    list.innerHTML = '';
    let total = 0; let count = 0;
    cart.forEach((item,index)=>{
        total += (item.price*item.qty); count += item.qty;
        list.innerHTML += `<div class="cart-item"><div><strong>${item.name}</strong><br><small>${item.protein} ${item.variant}</small></div><div class="cart-controls"><button onclick="changeQty(${index},-1)">-</button><span>${item.qty}</span><button onclick="changeQty(${index},1)">+</button><button onclick="removeItem(${index})" style="background:none;color:#ff4444;font-size:20px;">×</button></div></div>`;
    });
    totalSpan.innerText = total.toFixed(2);
    countSpan.innerText = count;
}
function changeQty(index,amt){ cart[index].qty += amt; if(cart[index].qty<1) removeItem(index); else updateCartUI(); }
function removeItem(index){ cart.splice(index,1); updateCartUI(); }
function toggleCart(){ const modal = document.getElementById('cart-modal'); modal.style.display = (modal.style.display==='flex') ? 'none':'flex'; }

function previewReceipt(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('receipt-preview-img').src = e.target.result;
            document.getElementById('receipt-preview-container').style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        document.getElementById('receipt-preview-container').style.display = 'none';
    }
}

function togglePaymentInfo() {
    const method = document.getElementById('order-payment').value;
    const detailsDiv = document.getElementById('payment-details');
    const ewalletDiv = document.getElementById('info-ewallet');
    const transferDiv = document.getElementById('info-transfer');

    if (method === 'Cash') {
        detailsDiv.style.display = 'none';
    } else {
        detailsDiv.style.display = 'block';
        ewalletDiv.style.display = (method === 'E-Wallet') ? 'block' : 'none';
        transferDiv.style.display = (method === 'Transfer') ? 'block' : 'none';
    }
}

function toggleAddressField() {
    const type = document.getElementById('order-type').value;
    document.getElementById('address-container').style.display = (type === 'Delivery') ? 'block' : 'none';
}

function processCheckout() {
    if(cart.length === 0) { alert("Your cart is empty!"); return; }
    
    const branch = localStorage.getItem('selected_branch') || 'Kangar';
    const orderType = document.getElementById('order-type').value;
    const payment = document.getElementById('order-payment').value;
    const total = document.getElementById('cart-total').innerText;
    
    // Prepare Form Data for Server
    const formData = new FormData();
    formData.append('branch', branch);
    formData.append('order_type', orderType);
    formData.append('payment_method', payment);
    formData.append('total', total);
    formData.append('items', JSON.stringify(cart));

    if (orderType === 'Delivery') {
        const addr = document.getElementById('delivery-address').value.trim();
        if (!addr) return alert("Please enter your delivery address.");
        formData.append('address', addr);
    }

    // Validation for non-cash payments
    if (payment !== 'Cash') {
        const senderName = document.getElementById('sender-name').value.trim();
        if (!isLoggedIn && !senderName) {
            alert("Please enter the Sender Name.");
            return;
        }
        if (senderName) formData.append('sender_name', senderName);

        const fileInput = document.getElementById('payment-proof');
        if (fileInput.files.length === 0) {
            alert("Please upload your payment receipt.");
            return;
        }
        formData.append('payment_proof', fileInput.files[0]);

        const confirmed = document.getElementById('confirm-authentic').checked;
        if (!confirmed) {
            alert("Please confirm that the receipt is authentic.");
            return;
        }
    }
    
    // Disable button to prevent double submit
    const btn = document.querySelector('.btn-checkout');
    const originalText = btn.innerText;
    btn.innerText = "Processing...";
    btn.disabled = true;

    // Send to Server
    fetch('place_order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = [];
            updateCartUI();
            toggleCart();
            // Redirect to Receipt with ID
            window.location.href = 'receipt.php?id=' + data.order_id;
        } else {
            alert("Order Failed: " + data.message);
            btn.innerText = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("An error occurred. Please try again.");
        btn.innerText = originalText;
        btn.disabled = false;
    });
}

function openQuickView(name, desc, price, imgSrc) {
    document.getElementById('qv-title').innerText = name;
    document.getElementById('qv-desc').innerText = desc;
    document.getElementById('qv-price').innerText = 'RM ' + parseFloat(price).toFixed(2);
    document.getElementById('qv-img').src = imgSrc;
    document.getElementById('qv-modal').style.display = 'flex';
}

function closeQuickView() {
    document.getElementById('qv-modal').style.display = 'none';
}

// Remove Item from Favorites (Database Version)
function removeFavDB(favId, btn) {
    if(!confirm("Are you sure you want to remove this?")) return;

    const formData = new FormData();
    formData.append('fav_id', favId);

    fetch('delete_favorite.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            const card = btn.closest('.menu-card');
            card.style.transform = 'scale(0.9)';
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
                // Check if grid is empty
                const grid = document.getElementById('favGrid');
                if (grid.children.length === 0) {
                    grid.innerHTML = '<div class="empty-msg"><h3>You haven\'t added any favorites yet! 🍔</h3><p>Go explore our menu and find something delicious.</p><a href="menu.php" style="display:inline-block; padding:12px 25px; background:#ff5100; color:white; text-decoration:none; border-radius:8px; font-weight:bold; transition:0.3s;">Browse Menu</a></div>';
                }
            }, 300);
        } else {
            alert("Error removing item: " + (data.message || "Unknown error"));
        }
    })
    .catch(err => console.error(err));
}
</script>

<?php include 'footer.php'; ?>