<?php include 'header.php'; ?>

<style>
    body {
    }
    .contact {
        max-width: 1200px;
        margin: 100px auto 40px; /* Top margin to clear fixed header */
        padding: 20px;
        text-align: center;
    }
    .contact h2 {
        color: #ff5100;
        font-size: 36px;
        margin-bottom: 40px;
    }
    .branch-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 30px;
    }
    .branch-card {
        background: #181818; /* Charcoal */
        border: 1px solid #333;
        border-radius: 15px;
        padding: 30px;
        width: 300px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .branch-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(255, 81, 0, 0.1);
        border-color: #ff5100;
    }
    .branch-card h3 {
        color: #fff;
        margin-top: 0;
        font-size: 20px;
        margin-bottom: 15px;
    }
    .branch-card p {
        color: #ccc;
        margin: 5px 0;
        font-size: 16px;
    }
    .branch-card a {
        display: inline-block;
        margin-top: 10px;
        padding: 10px 25px;
        background: #ff5100;
        color: white;
        text-decoration: none;
        border-radius: 8px; /* Unified Shape */
        font-weight: bold;
        width: 100%;
        transition: background 0.3s;
    }
    .branch-card a:hover {
        background: #cc4100;
        transform: scale(1.05);
    }
    .branch-card a[href*="wa.me"] {
        background: #25D366;
    }
    .branch-card a[href*="wa.me"]:hover {
        background: #1da851;
    }
</style>

<section id="contact" class="contact">
  <h2><?php echo $t['contact_title']; ?></h2>

  <div class="branch-container">

    <div class="branch-card reveal">
      <h3>BamBam Burger Kangar</h3>
      <p>📍 Kangar, Perlis</p>
      <p>📞 017-590 0799</p>
      <a href="tel:0175900799">Call</a>
      <a href="https://wa.me/60175900799" target="_blank">WhatsApp</a>
    </div>

    <div class="branch-card reveal">
      <h3>BamBam Burger Jejawi</h3>
      <p>📍 Jejawi, Perlis</p>
      <p>📞 013-777 1763</p>
      <a href="tel:0137771763">Call</a>
      <a href="https://wa.me/60137771763" target="_blank">WhatsApp</a>
    </div>

    <div class="branch-card reveal">
      <h3>BamBam Burger Arau</h3>
      <p>📍 Arau, Perlis</p>
      <p>📞 019-551 1765</p>
      <a href="tel:0195511765">Call</a>
      <a href="https://wa.me/60195511765" target="_blank">WhatsApp</a>
    </div>

    <div class="branch-card reveal">
      <h3>BamBam Burger K.Perlis</h3>
      <p>📍 Kuala Perlis</p>
      <p>📞 011-1989 8669</p>
      <a href="tel:01119898669">Call</a>
      <a href="https://wa.me/601119898669" target="_blank">WhatsApp</a>
    </div>

    <div class="branch-card reveal">
      <h3>BamBam Burger Beseri</h3>
      <p>📍 Beseri, Perlis</p>
      <p>📞 011-1006 4068</p>
      <a href="tel:01110064068">Call</a>
      <a href="https://wa.me/601110064068" target="_blank">WhatsApp</a>
    </div>

  </div>
</section>

<?php include 'footer.php'; ?>
