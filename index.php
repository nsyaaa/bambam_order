<?php
// ===============================
// Bambam Burger - Homepage
// Hero Banner + 2 Big Oval CTA Buttons + Fixed Background
// ===============================
if (session_status() === PHP_SESSION_NONE) { session_start(); }

include 'header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
/* 1. Global Background (Keep as is) */
body {
    margin: 0;
}

/* 2. Hero Section Layout */
.hero-section {
    /* Gradient makes text pop while keeping the burger clear */
    background: linear-gradient(to right, rgba(0,0,0,0.75) 10%, transparent 100%), 
                url('images/banner.png');
    background-size: cover;
    background-position: center;
    height: 90vh;
    display: flex;
    align-items: center;
    padding: 0 8%;
}

.stay-hungry {
    font-family: 'Playfair Display', serif; /* Premium serif */
    font-size: 4rem;
    color: #ffffff;
    margin-bottom: 20px;
    line-height: 1.1;
}

.stay-hungry span {
    color: #FF6B00; /* Your brand orange */
}

.hero-content p {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.1rem; /* Body Text: Sans-Serif */
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 30px;
    max-width: 600px;
    line-height: 1.6; 
}

/* Primary Button */
.btn-primary {
    background: #FF6B00;
    color: white;
    padding: 12px 30px;
    border-radius: 8px; /* Unified Shape: 8px radius */
    font-weight: bold;
    text-decoration: none;
    margin-right: 15px;
    display: inline-block;
    transition: 0.3s;
    border: 1px solid #FF6B00;
}

.btn-primary:hover {
    background: #e04600;
    border-color: #e04600;
}

/* The Ghost Button for "Customize" */
.btn-ghost {
    background: transparent;
    border: 1px solid white;
    color: white;
    padding: 12px 30px;
    border-radius: 8px; /* Unified Shape: 8px radius */
    text-decoration: none;
    transition: 0.3s;
    display: inline-block;
    font-weight: bold;
}
.btn-ghost:hover {
    background: white;
    color: black;
}

/* === NEW CAROUSEL SECTION === */
.nested-carousel-container {
    padding: 80px 0;
    background: #181818; /* Charcoal background to match theme */
    position: relative;
    z-index: 2;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.inner-slider {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 0 50px 0; /* Bottom padding for pagination */
}

.product-slide {
    background: #1a1a1a;
    border-radius: 20px;
    overflow: hidden;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    height: auto;
}

.product-slide img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-slide p {
    padding: 20px;
    margin: 0;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    color: #fff;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.product-slide:hover {
    border-color: #ff5100;
    transform: translateY(-10px);
}
.product-slide:hover img {
    transform: scale(1.1);
}

/* Swiper Customization */
.swiper-button-next, .swiper-button-prev {
    color: #ff5100 !important;
    background: rgba(0,0,0,0.5);
    width: 50px; height: 50px;
    border-radius: 50%;
    backdrop-filter: blur(5px);
}
.swiper-button-next:after, .swiper-button-prev:after {
    font-size: 20px;
    font-weight: bold;
}
.swiper-pagination-bullet-active {
    background: #ff5100 !important;
}
.swiper-pagination-bullet {
    background: #fff;
    opacity: 0.5;
    width: 10px; height: 10px;
}
</style>

<section class="hero-section">
    <div class="hero-content">
        <h1 class="stay-hungry">Stay <span>Hungry.</span></h1>
        <p>Experience the art of the burger. Gourmet ingredients, crafted with passion, served with love.</p>
        
        <div class="hero-btns">
            <a href="menu.php" class="btn-primary">VIEW MENU</a>
            <a href="custom_burger.php" class="btn-ghost">CUSTOMIZE &rarr;</a>
        </div>
    </div>
</section>

<!-- NEW CAROUSEL SECTION -->
<section class="nested-carousel-container">
    <h2 style="text-align:center; color:white; font-family:'Fraunces', serif; margin-bottom:40px; font-size:2.5rem;">Our <span style="color:#ff5100;">Favorites</span></h2>
    
    <div class="swiper inner-slider">
        <div class="swiper-wrapper">
            <!-- Slide 1 -->
            <div class="swiper-slide product-slide">
                <img src="images/lavacheese.jpg" alt="Lava Cheese Burger" onerror="this.src='images/hero_burger.png'">
                <p>Lava Cheese Burger</p>
            </div>
            <!-- Slide 2 -->
            <div class="swiper-slide product-slide">
                <img src="images/smash.jpg" alt="Beef Smash Burger" onerror="this.src='images/hero_burger.png'">
                <p>Beef Smash Burger</p>
            </div>
            <!-- Slide 3 -->
            <div class="swiper-slide product-slide">
                <img src="images/grill.jpg" alt="Chicken Grill" onerror="this.src='images/hero_burger.png'">
                <p>Chicken Grill</p>
            </div>
            <!-- Slide 4 -->
            <div class="swiper-slide product-slide">
                <img src="images/ayamkrupkrap.jpg" alt="Ayam Goreng" onerror="this.src='images/hero_burger.png'">
                <p>Ayam Goreng Krup Krap</p>
            </div>
            <!-- Slide 5 -->
            <div class="swiper-slide product-slide">
                <img src="images/wagyu.jpg" alt="Wagyu Burger" onerror="this.src='images/hero_burger.png'">
                <p>Wagyu Burger</p>
            </div>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    // Initialize Swiper for the inner slider
    const innerSwiper = new Swiper('.inner-slider', {
        // Basic settings
        slidesPerView: 1,        // Mobile default
        spaceBetween: 20,       
        loop: true,              
        centeredSlides: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        
        // Navigation and pagination options
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            768: { slidesPerView: 2, spaceBetween: 30 },
            1024: { slidesPerView: 3, spaceBetween: 40 }
        }
    });
</script>

<?php include 'footer.php'; ?>