<?php include 'header.php'; ?>

<style>
    body { background: url('images/wall.png') no-repeat center center fixed; background-size: cover; color: white; padding-top: 100px; }
    .checkout-wrap { max-width: 600px; margin: 0 auto 50px; background: rgba(0,0,0,0.9); backdrop-filter: blur(10px); padding: 30px; border-radius: 15px; border: 1px solid #FFA500; }
    .summary-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #333; }
    input, select { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid #444; background: #222; color: white; }
    .btn-submit { width: 100%; background: #FFA500; color: black; font-weight: 800; border: none; padding: 15px; border-radius: 8px; cursor: pointer; text-transform: uppercase; margin-top: 20px; }
</style>

<div class="checkout-wrap">
    <h2 style="color: #FFA500; text-align: center;">Confirm Your Order</h2>
    
    <div id="summary-list"></div>
    <h3 style="text-align: right; color: #FFA500;">Total: RM <span id="display-total">0.00</span></h3>

    <form id="checkoutForm" action="process_order.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="cart_json" id="hidden_cart">
        <input type="hidden" name="total_amount" id="hidden_total">
        <input type="hidden" name="branch" id="hidden_branch">
        <input type="hidden" name="order_type" id="hidden_order_type" value="Take-Away"> <!-- Default value -->

        <label>Customer Name</label>
        <input type="text" name="customer_name" required placeholder="Your Name">

        <label>Payment Method</label>
        <select name="payment_method" required>
            <option value="Online Transfer">Online Transfer</option>
            <option value="E-Wallet">E-Wallet (TNG/Grab)</option>
            <option value="Cash">Cash on Pickup</option>
        </select>

        <label>Upload Receipt</label>
        <input type="file" name="receipt" accept="image/*" required>

        <button type="submit" class="btn-submit">Order Now 🍔</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cart = JSON.parse(localStorage.getItem('bambam_cart')) || [];
    const list = document.getElementById('summary-list');
    let total = 0;

    if(cart.length === 0) {
        alert("Your cart is empty!");
        window.location.href = 'menu.php';
        return;
    }

    cart.forEach(item => {
        const itemTotal = item.price * item.qty;
        total += itemTotal;
        list.innerHTML += `
            <div class="summary-item">
                <span>${item.qty}x ${item.name} (${item.variant})</span>
                <span>RM ${itemTotal.toFixed(2)}</span>
            </div>`;
    });
    
    // Update the visual display
    document.getElementById('display-total').innerText = total.toFixed(2);
});

// CRITICAL: This transfers the data from the screen/storage into the PHP form
document.getElementById('checkoutForm').onsubmit = function() {
    const cartData = localStorage.getItem('bambam_cart');
    const totalValue = document.getElementById('display-total').innerText;

    // Populate branch from localStorage
    const branch = localStorage.getItem('selected_branch') || 'Main';
    document.getElementById('hidden_branch').value = branch;

    // Fill the hidden inputs
    document.getElementById('hidden_cart').value = cartData;
    document.getElementById('hidden_total').value = totalValue;

    console.log("Sending Total:", totalValue); // Debugging
    return true; 
};
</script>

<?php include 'footer.php'; ?>