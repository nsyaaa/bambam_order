<?php
// ===============================
// Bambam Burger - About Section
// ===============================
include 'header.php';
?>

<style>
    body {
        background-color: #fff8f0;
    }
    .about-section {
        display: flex;
        flex-wrap: wrap;
        padding: 40px;
        align-items: center;
        gap: 20px;
        margin-top: 80px;
    }
    .about-image {
        flex: 1 1 300px;
        max-width: 400px;
    }
    .about-image img {
        width: 100%;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .about-text {
        flex: 2 1 400px;
    }
    .about-text h2 {
        font-size: 32px;
        margin-bottom: 20px;
        color: #B11226; /* Brand color */
    }
    .about-text p {
        font-size: 18px;
        line-height: 1.6;
    }
    .banner {
        width: 100%;
        margin: 40px 0;
        text-align: center;
    }
    .banner img {
        width: 90%;
        max-width: 1200px;
        border-radius: 15px;
    }
</style>

<!-- About Section -->
<section class="about-section">
    <div class="about-image reveal">
        <img src="images/bout.png" alt="Delicious Bambam Burger">
    </div>
    <div class="about-text reveal">
        <h2><?php echo $t['about_title']; ?></h2>
        <p>Welcome to <strong>Bambam Burger</strong>, the home of burgers packed with unforgettable flavor! From fresh ingredients to our special recipes, every burger is made with love. Whether you’re craving a classic favorite or one of our creative specials, we promise – every bite will put a smile on your face. Come and taste the deliciousness everyone’s talking about!</p>
    </div>
</section>

<!-- Banner Section -->
<div class="banner">
    <img src="images/bannerabout.png" alt="Bambam Burger Banner">
</div>

<?php include 'footer.php'; ?>
