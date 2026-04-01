<footer>
    <div class="footer-container">
        <span><?php echo $t['footer_text']; ?></span>
        <div class="social-icons">
            <!-- Replace these links with your actual profile URLs -->
            <a href="https://www.facebook.com/abiangbam/about?locale=ms_MY" target="_blank" class="social-btn"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/bambamburgers_hq/" target="_blank" class="social-btn"><i class="fab fa-instagram"></i></a>
            <a href="https://www.tiktok.com/@bambamburger" target="_blank" class="social-btn"><i class="fab fa-tiktok"></i></a>
            <a href="https://wa.me/60175900799" target="_blank" class="social-btn"><i class="fab fa-whatsapp"></i></a>
        </div>
    </div>
    <style>
        footer {
            background: #FF6B00 !important; /* Consistent Orange */
            color: white;
            padding: 20px;
            text-align: center;
        }
        .footer-container { display: flex; justify-content: center; align-items: center; gap: 30px; flex-wrap: wrap; }
        .social-icons { display: flex; gap: 15px; }
        .social-btn { 
            color: white; 
            font-size: 1rem; 
            padding: 8px;
            width: 40px; height: 40px;
            background: transparent;
            border: 1px solid #ffffff; /* White Border */
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0 5px;
        }
        .social-btn:hover { background: white; border-color: white; color: #FF6B00; transform: translateY(-3px); }
    </style>
</footer>

<script>
// Global Scroll Animation Observer
document.addEventListener("DOMContentLoaded", function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("active");
                observer.unobserve(entry.target); // Run animation only once
            }
        });
    }, { threshold: 0.1 });

    const reveals = document.querySelectorAll(".reveal");
    reveals.forEach((el) => observer.observe(el));
});

// Hide Loader Logic (with Fallback)
function hideLoader() {
    const loader = document.getElementById('loader-wrapper');
    if (loader) {
        loader.style.opacity = '0';
        loader.style.visibility = 'hidden';
    }
}

window.addEventListener('load', hideLoader);
// Force hide after 3 seconds (Fallback for slow connections/errors)
setTimeout(hideLoader, 3000);
</script>

</body>
</html>