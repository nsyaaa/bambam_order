<?php 
include 'header.php'; 
include_once 'db.php';

$globalStoreStatus = 'open';
$branches = [];
$availableBranches = [];

try {
    $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'global_store_status' LIMIT 1");
    $globalStoreStatus = trim(strtolower($stmt->fetchColumn() ?: 'open'));

    $stmt = $pdo->query("SELECT id, name, phone, is_open FROM branches ORDER BY name ASC");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($globalStoreStatus === 'open') {
        foreach ($branches as $branch) {
            if ((int)$branch['is_open'] === 1) {
                $availableBranches[] = $branch;
            }
        }
    }
} catch (Exception $e) {}
?>

<style>
    body {
        overflow-x: hidden;
    }

    /* --- Floating Food Assets --- */
    .bg-asset {
        position: fixed;
        z-index: -1;
        pointer-events: none;
        filter: drop-shadow(0 10px 15px rgba(0,0,0,0.05));
    }
    .fries  { top: 10%; left: -20px; width: 180px; transform: rotate(-10deg); }
    .tomato { top: 45%; left: -30px; width: 150px; }
    .nugget { bottom: 15%; right: 5%; width: 120px; }
    .bun    { bottom: -30px; right: 10%; width: 250px; }
    .veggie { top: 15%; right: -20px; width: 140px; }

    /* --- Navbar Styling --- */
    .navbar {
        background: #ff5722; /* Branding Orange */
        padding: 15px 5%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
    }
    .logo { font-weight: 800; font-size: 1.6rem; }
    .nav-right { display: flex; align-items: center; gap: 15px; font-size: 0.9rem; }
    .menu-icon { font-size: 1.5rem; cursor: pointer; }

    /* --- Main Container --- */
    .selection-container {
        text-align: center;
        padding: 80px 5%;
    }

    .title {
        font-size: 2.8rem;
        font-weight: 900;
        margin-bottom: 50px;
        text-transform: uppercase;
        color: #ffffff;
    }
    .title span { color: #ff5722; }

    /* --- Grid & Cards --- */
    .branch-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 30px;
        max-width: 700px; /* Forces wrap after 3 items (190px * 3 + gaps) */
        margin: 0 auto;
    }

    @media (max-width: 768px) {
        .branch-grid {
            max-width: 450px; /* Forces wrap after 2 items on smaller screens */
            gap: 20px;
        }
    }

    .card {
        background: #ffffff;
        width: 190px;
        height: 190px;
        border-radius: 25px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05); /* Soft premium shadow */
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
    }

    /* Icon Colors based on your image */
    .card i { font-size: 2.5rem; color: #ff5722; margin-bottom: 12px; }
    .card p { font-weight: 700; color: #333; margin: 0; }

    /* --- The EXACT Active State (Jejawi Look) --- */
    .card.active {
        background: #222; /* Dark card */
        transform: scale(1.05);
        box-shadow: 0 15px 35px rgba(255, 87, 34, 0.2);
    }
    .card.active p { color: #fff; }
    .card.active i { color: #ff5722; }

    .card:hover:not(.active) {
        background: #222; /* Card turns black on hover */
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }

    .card:hover:not(.active) p {
        color: #fff; /* Text turns white on hover */
    }

    .card:hover:not(.active) i {
        animation: icon-interact 0.5s ease infinite alternate; /* Icon animation */
    }

    @keyframes icon-interact {
        from { transform: scale(1) translateY(0); }
        to { transform: scale(1.2) translateY(-5px); }
    }

    .closed {
    background: #555 !important;
    cursor: not-allowed;
    opacity: 0.6;
}

.closed:hover {
    transform: none !important;
    box-shadow: none !important;
}

.closed-label {
    font-size: 12px;
    color: red;
    font-weight: bold;
}

.store-closed-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 65vh;
}

.store-closed-card {
    max-width: 620px;
    width: 100%;
    background: linear-gradient(145deg, #1f1f1f, #2a2a2a);
    border: 1px solid rgba(255, 87, 34, 0.2);
    border-radius: 28px;
    padding: 45px 35px;
    text-align: center;
    box-shadow: 0 20px 50px rgba(0,0,0,0.18);
}

.store-closed-icon {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: rgba(255, 87, 34, 0.12);
    color: #ff5722;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
    margin: 0 auto 20px;
}

.store-closed-card h1 {
    color: #fff;
    font-size: 2rem;
    margin-bottom: 12px;
    font-weight: 900;
}

.store-closed-card p {
    color: #d0d0d0;
    font-size: 1rem;
    line-height: 1.7;
    margin-bottom: 20px;
}

.closed-note {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.06);
    color: #ffb089;
    padding: 10px 16px;
    border-radius: 999px;
    font-size: 0.9rem;
    font-weight: 700;
}
</style>

<link rel="icon" href="logo.png">

<img src="images/fries.png" class="bg-asset fries" alt="">
<img src="images/tomato.png" class="bg-asset tomato" alt="">
<img src="images/nugget.png" class="bg-asset nugget" alt="">
<img src="images/bun.png" class="bg-asset bun" alt="">
<img src="images/veggie.png" class="bg-asset veggie" alt="">

<main class="selection-container">
    <?php if ($globalStoreStatus !== 'open' || empty($availableBranches)): ?>
        <div class="store-closed-wrapper">
            <div class="store-closed-card">
                <div class="store-closed-icon">
                    <i class="fas fa-store-slash"></i>
                </div>
                <h1>Semua Branch Ditutup</h1>
                <p>Kami sedang berehat sebentar. Sila cuba lagi nanti apabila semua branch kembali beroperasi.</p>
                <div class="closed-note">
                    <i class="fas fa-circle-info"></i>
                    Status semasa dikawal oleh pihak admin.
                </div>
            </div>
        </div>
    <?php else: ?>
        <h1 class="title">CHOOSE YOUR <span>BRANCH</span></h1>

        <div class="branch-grid">
            <?php foreach($branches as $branch): ?>
                <div class="card <?php echo !(int)$branch['is_open'] ? 'closed' : ''; ?>"
                    <?php if((int)$branch['is_open'] === 1): ?>
                        onclick="selectBranch('<?php echo htmlspecialchars($branch['name']); ?>')"
                    <?php endif; ?>>
                    
                    <?php 
                        $icon = "fa-store";
                        if($branch['name'] == 'Kangar') $icon = "fa-utensils";
                        if($branch['name'] == 'Jejawi') $icon = "fa-hamburger";
                        if($branch['name'] == 'Kuala Perlis') $icon = "fa-map-marker-alt";
                        if($branch['name'] == 'Beseri') $icon = "fa-fire";
                    ?>

                    <i class="fas <?php echo $icon; ?>"></i>
                    <p>
                        <?php echo htmlspecialchars($branch['name']); ?>
                        <?php if(!(int)$branch['is_open']): ?>
                            <br><span class="closed-label">CLOSED</span>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>


<script>
    //trycommit
function selectBranch(branch) { 
    localStorage.setItem('selected_branch', branch); 
    window.location.href = 'menu.php'; 
}



</script>


<?php include 'footer.php'; ?>