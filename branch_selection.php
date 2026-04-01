<?php 
include 'header.php'; 
include_once 'db.php';

// Check global status first
$globalStoreStatus = 'open';
$openBranches = [];
try {
    // Enforce branch availability using system_settings
    $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'global_store_status'");
    $globalStoreStatus = trim(strtolower($stmt->fetchColumn() ?: 'open'));

    // Only fetch branches if the store is globally open
    if ($globalStoreStatus === 'open') {
        $stmt = $pdo->query("SELECT name, phone FROM branches WHERE is_open = 1 ORDER BY name ASC");
        $openBranches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // DEBUGGING: If no branches show up, check the HTML source code (Ctrl+U)
        if (empty($openBranches)) {
            $debugStmt = $pdo->query("SELECT * FROM branches");
            $allData = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<!-- DEBUG: Branches in DB: " . print_r($allData, true) . " -->";
        }
    }
} catch (Exception $e) {}
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        margin: 0;
        padding-top: 120px;
    }

    .branch-select-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
        text-align: center;
    }

    /* Bold Header with Premium Spacing */
    .branch-select-container h2 {
        font-family: 'Playfair Display', serif;
        font-size: 52px;
        text-transform: uppercase;
        color: #ffffff;
        margin-bottom: 60px;
        letter-spacing: -2px;
    }

    .branch-select-container h2 span {
        color: #ff5100;
    }

    /* The Fix: Centered Flex Grid */
    .branch-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center; /* This ensures the bottom row is perfectly centered */
        gap: 30px;
        max-width: 1000px;
        margin: 0 auto;
    }

    /* Premium Card Design */
    .branch-option-card {
        background: #181818;
        border-radius: 16px;
        padding: 50px 30px;
        width: 260px;
        cursor: pointer;
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        align-items: center;
        border: 1px solid #333;
    }

    /* Icon Styling */
    .branch-option-card i {
        font-size: 48px;
        color: #ff5100;
        margin-bottom: 25px;
        transition: transform 0.3s ease;
    }

    .branch-option-card h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 600;
        color: #fff;
        transition: color 0.3s ease;
    }

    /* THE ATTRACTIVE PART: Smooth Dark Hover */
    .branch-option-card:hover {
        background: #222;
        transform: translateY(-15px) scale(1.02);
        box-shadow: 0 30px 60px rgba(255, 81, 0, 0.25);
        border-color: #ff5100;
    }

    .branch-option-card:hover h3 {
        color: #ffffff;
    }

    .branch-option-card:hover i {
        transform: scale(1.2) rotate(-5deg);
    }

    /* Animation for initial page load */
    .animate-up {
        animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="branch-select-container animate-up">
    <h2>Choose Your <span>Branch</span></h2>
    
    <div class="branch-grid">
        <?php if ($globalStoreStatus !== 'open'): ?>
            <div style="grid-column: 1 / -1; background: #181818; padding: 40px; border-radius: 15px; border: 1px solid #333;">
                <h3 style="color: #e74c3c;">Store is currently closed. Please try again later.</h3>
                <p style="color: #aaa;">Please check back later. We're sorry for the inconvenience.</p>
            </div>
        <?php elseif (empty($openBranches)): ?>
            <div style="grid-column: 1 / -1; background: #181818; padding: 40px; border-radius: 15px; border: 1px solid #333;">
                <h3 style="color: #ff5100;">No Branches Available</h3>
                <p style="color: #aaa;">Please check back later.</p>
            </div>
        <?php else: ?>
            <?php foreach($openBranches as $branch): ?>
                <div class="branch-option-card" onclick="selectBranch('<?php echo htmlspecialchars($branch['name']); ?>')">
                    <i class="fas fa-store"></i>
                    <h3><?php echo htmlspecialchars($branch['name']); ?></h3>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function selectBranch(branch) { 
    localStorage.setItem('selected_branch', branch); 
    window.location.href = 'menu.php'; 
}
</script>

<?php include 'footer.php'; ?>