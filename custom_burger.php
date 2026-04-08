<?php include 'header.php'; ?>

<style>
    body {
        background: url('images/ch.png') no-repeat center center fixed;
        background-size: cover;
        padding-top: 100px;
        color: white;
        font-family: 'Poppins', sans-serif;
    }

    .builder-container {
        display: flex;
        flex-wrap: wrap;
        max-width: 1200px;
        margin: 0 auto;
        gap: 40px;
        padding: 20px;
    }

    /* Left Side: Controls */
    .controls-area {
        flex: 1;
        background: #121212; /* Deep Charcoal */
        padding: 30px;
        border-radius: 20px;
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        height: fit-content;
    }

    .ingredient-btn {
        display: flex;
        justify-content: space-between;
        align-items: center; 
        background: rgba(255,255,255,0.1); /* Glassmorphism */
        color: white;
        padding: 15px;
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .ingredient-btn:hover {
        background: rgba(255,255,255,0.2);
        transform: translateX(5px);
    }
    .ingredient-btn span {
        font-size: 16px;
        font-weight: 500;
    }

    .ing-price {
        background: #ff5100;
        color: white;
        padding: 2px 8px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: bold;
    }

    .ingredient-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    /* Right Side: Visual Burger */
    .visual-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end; /* Anchor content to the bottom */
        min-height: 500px;
        position: relative;
    }

    #visual-placeholder {
        position: absolute;
        /* Positioned relative to the bottom now */
        bottom: 220px; 
        left: 50%;
        transform: translate(-50%, -50%);
        color: rgba(255,255,255,0.3);
        font-style: italic;
        text-align: center;
        z-index: 1;
        display: none; /* Controlled by JS */
    }

    #burger-stack {
        display: flex;
        flex-direction: column-reverse; /* Stack from bottom up visually */
        align-items: center;
        margin-bottom: 150px; /* Match the plate's bottom position to ground the burger */
        width: 100%;
        transition: all 0.3s ease;
    }

    /* CSS Ingredients */
    .layer {
        width: 220px;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        animation: dropIn 0.4s ease-out;
        position: relative;
        cursor: pointer;
    }
    .layer:hover { transform: scale(1.05); }

    @keyframes dropIn {
        from { transform: translateY(-200px) rotate(10deg); opacity: 0; }
        to { transform: translateY(0) rotate(0deg); opacity: 1; }
    }

    .bun-top { height: 70px; background: linear-gradient(#f39c12, #d35400); border-radius: 100px 100px 10px 10px; margin-bottom: -5px; z-index: 100; box-shadow: inset 0 -5px 10px rgba(0,0,0,0.1); }
    .bun-top::after { content: ' .  . .  . '; color: rgba(255,255,255,0.6); position: absolute; top: 10px; left: 20%; font-size: 20px; font-weight: bold; word-spacing: 15px; }
    .bun-bottom { height: 40px; background: linear-gradient(#f39c12, #d35400); border-radius: 10px 10px 30px 30px; margin-top: -5px; box-shadow: inset 0 -5px 10px rgba(0,0,0,0.2); }
    .patty-beef { height: 45px; background: #5d4037; border-radius: 15px; margin: -5px 0; width: 230px; background-image: radial-gradient(circle at 30% 20%, #6d4c41 5%, transparent 10%); background-size: 20px 20px; box-shadow: inset 0 -3px 5px rgba(0,0,0,0.5); }
    .patty-chicken { height: 45px; background: #e67e22; border-radius: 15px; margin: -5px 0; width: 230px; border: 2px dashed #d35400; }
    .cheese { height: 10px; background: #f1c40f; width: 240px; border-radius: 5px; margin: -2px 0; position: relative; }
    .cheese::before { content: ''; position: absolute; width: 20px; height: 15px; background: #f1c40f; border-radius: 0 0 10px 10px; right: 20px; top: 10px; }
    .lettuce { height: 15px; background: #2ecc71; width: 250px; border-radius: 20px; margin: -3px 0; border: 2px solid #27ae60; }
    .tomato { height: 12px; background: #e74c3c; width: 210px; border-radius: 10px; margin: -2px 0; border: 1px solid #c0392b; }
    .onion { height: 8px; background: transparent; border: 3px solid #9b59b6; width: 200px; border-radius: 50px; margin: -2px 0; }

    /* Summary Box */
    .summary-box { margin-top: 30px; border-top: 1px solid #555; padding-top: 20px; }
    .total-price { 
        font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 28px; color: #ff5100; text-align: right; 
    }
    .action-buttons { display: flex; gap: 10px; margin-top: 20px; }
    .btn-action { flex: 1; padding: 15px; border: none; border-radius: 50px; font-weight: bold; cursor: pointer; text-transform: uppercase; font-size: 14px; }
    .btn-reset { 
        background: transparent; 
        color: #aaa;
        border: 2px solid #444;
        transition: all 0.3s;
    }
    .btn-reset:hover { background: #444; color: white; }
    .btn-add { background: #ff5100; color: white; }
    .btn-add:hover { background: #e04600; }
</style>

<div class="builder-container">
    
    <!-- Controls -->
    <div class="controls-area animate-fade-up">
        <h2 style="font-family:'Fraunces', serif; margin-top:0;">Build Your Burger</h2>
        <p style="color:#aaa; margin-bottom:20px;">Click ingredients to stack them up!</p>

        <div class="ingredient-list">
            <div class="ingredient-btn" onclick="addLayer('bun-top', 2.00, 'Top Bun')">
                <span>🍞 Top Bun</span> <span class="ing-price">+RM 2.00</span>
            </div>
            <div class="ingredient-btn" onclick="addLayer('patty-beef', 6.00, 'Beef Patty')">
                <span>🥩 Beef Patty</span> <span class="ing-price">+RM 6.00</span>
            </div>
            <div class="ingredient-btn" onclick="addLayer('patty-chicken', 5.00, 'Chicken Patty')">
                <span>🍗 Chicken Patty</span> <span class="ing-price">+RM 5.00</span>
            </div>
            <div class="ingredient-btn" onclick="addLayer('cheese', 2.00, 'Cheese Slice')">
                <span>🧀 Cheese</span> <span class="ing-price">+RM 2.00</span>
            </div>
            <div class="ingredient-btn" onclick="addLayer('lettuce', 1.00, 'Lettuce')">
                <span>🥬 Lettuce</span> <span class="ing-price">+RM 1.00</span>
            </div>
            <div class="ingredient-btn" onclick="addLayer('tomato', 1.00, 'Tomato')">
                <span>🍅 Tomato</span> <span class="ing-price">+RM 1.00</span>
            </div>
            <div class="ingredient-btn" onclick="addLayer('onion', 0.50, 'Onion Ring')">
                <span>🧅 Onion</span> <span class="ing-price">+RM 0.50</span>
            </div>
            <div class="ingredient-btn" onclick="addLayer('bun-bottom', 1.00, 'Bottom Bun')">
                <span>🍞 Bottom Bun</span> <span class="ing-price">+RM 1.00</span>
            </div>
        </div>

        <div class="summary-box">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:18px;">Total Price:</span>
                <span class="total-price" id="display-price">RM 0.00</span>
            </div>
            <div class="action-buttons">
                <button class="btn-action btn-reset" onclick="resetBuilder()">Reset</button>
                <button class="btn-action btn-add" onclick="addToCartCustom()">Add to Cart</button>
            </div>
        </div>
    </div>

    <!-- Visuals -->
    <div class="visual-area">
        <div id="burger-stack">
            <!-- Layers go here -->
        </div>
        <div id="visual-placeholder">
            <p>Your masterpiece builds here.</p>
        </div>
        <div class="plate"></div>
    </div>

</div>

<script>
let layers = [];
let totalPrice = 0;

document.addEventListener('DOMContentLoaded', () => {
    resetBuilder();
});

function addLayer(type, price, name) {
    // Check if a top bun has already been added
    const hasTopBun = layers.some(layer => layer.type === 'bun-top');
    if (hasTopBun) {
        alert("You cannot add more ingredients after placing the top bun. Please remove it to add more.");
        return;
    }

    // Check if adding a bottom bun when one already exists
    if (type === 'bun-bottom') {
        const hasBottomBun = layers.some(layer => layer.type === 'bun-bottom');
        if (hasBottomBun) {
            alert("A bottom bun has already been added. You can only have one.");
            return;
        }
    }

    layers.push({ type, price, name });
    totalPrice += price;
    renderLayers();
    updatePrice();
}

function removeLayer(index) {
    totalPrice -= layers[index].price;
    layers.splice(index, 1);
    renderLayers();
    updatePrice();
}

function resetBuilder() {
    layers = [];
    totalPrice = 0;
    renderLayers();
    updatePrice();
}

function renderLayers() {
    const stack = document.getElementById('burger-stack');
    stack.innerHTML = '';
    const placeholder = document.getElementById('visual-placeholder');

    layers.forEach((layer, index) => {
        const div = document.createElement('div');
        div.className = `layer ${layer.type}`;
        div.title = "Click to remove";
        div.onclick = () => removeLayer(index);
        stack.appendChild(div);
    });

    // Show placeholder if only the bottom bun is present
    if (layers.length <= 1) {
        placeholder.style.display = 'block';
    } else {
        placeholder.style.display = 'none';
    }
}

function updatePrice() {
    document.getElementById('display-price').innerText = 'RM ' + totalPrice.toFixed(2);
}

function addToCartCustom() {
    // Check if the burger has at least one Bun or one Patty
    const hasBase = layers.some(l => 
        l.type.includes('bun') || 
        l.type.includes('patty')
    );

    if (!hasBase) {
        alert("Invalid Selection: A custom burger must include at least one Bun or a Patty. Ordering toppings alone (like cheese or vegetables) is not permitted.");
        return;
    }

    if (layers.length < 1) { alert("Please add ingredients to your burger!"); return; }

    const description = layers.map(l => l.name).join(', '); // e.g. "Bottom Bun, Beef Patty, Cheese"
    const customItem = { id: 'custom-' + Date.now(), name: 'Custom Burger', price: totalPrice, qty: 1, variant: 'Custom Build', protein: '', customization: description };
    
    let cart = JSON.parse(localStorage.getItem('bambam_cart')) || [];
    cart.push(customItem);
    localStorage.setItem('bambam_cart', JSON.stringify(cart));
    alert("Custom Burger added to cart!");
    window.location.href = 'menu.php?cart_open=true';
    showToast("✅ Custom Burger added to cart!");
    updateCartUI();
    toggleCart();
}
</script>

<?php include 'footer.php'; ?>