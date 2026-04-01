<?php include 'header.php'; ?>

<style>
    body {
        background: url('images/wall.png') no-repeat center center fixed;
        background-size: cover;
        padding-top: 100px;
        color: white;
    }
    .track-container {
        max-width: 450px;
        margin: 50px auto;
        background: rgba(0, 0, 0, 0.85);
        padding: 40px;
        border-radius: 20px;
        border: 1px solid #ff5100;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        text-align: center;
        backdrop-filter: blur(5px);
    }
    .track-icon {
        font-size: 50px;
        margin-bottom: 20px;
        display: inline-block;
        animation: float 3s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .input-group { margin-bottom: 25px; position: relative; }
    .input-group input {
        width: 100%;
        padding: 15px;
        border-radius: 50px;
        border: 2px solid #444;
        background: #222;
        color: white;
        outline: none;
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        transition: 0.3s;
    }
    .input-group input:focus {
        border-color: #ff5100;
        box-shadow: 0 0 15px rgba(255, 81, 0, 0.3);
    }
    .btn-track {
        width: 100%;
        padding: 15px;
        background: #ff5100;
        color: white;
        border: none;
        border-radius: 50px;
        font-size: 18px;
        font-weight: 800;
        cursor: pointer;
        transition: 0.3s;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .btn-track:hover {
        background: #e04600;
        transform: scale(1.02);
        box-shadow: 0 5px 15px rgba(224, 70, 0, 0.4);
    }
</style>

<div class="track-container animate-fade-up">
    <div class="track-icon">🛵</div>
    <h2 style="color: #ff5100; margin-top: 0; margin-bottom: 10px;">Track Your Order</h2>
    <p style="color: #ccc; margin-bottom: 30px; line-height: 1.5;">
        Enter your Order ID below to see the live status of your meal.
    </p>
    
    <form action="receipt.php" method="GET">
        <div class="input-group">
            <input type="number" name="id" placeholder="Order ID (e.g. 101)" required autofocus>
        </div>
        <button type="submit" class="btn-track">Check Status</button>
    </form>
</div>

<?php include 'footer.php'; ?>