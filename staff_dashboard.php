<?php
session_start();
include 'db.php';

if (
    !isset($_SESSION['staff_logged_in']) ||
    $_SESSION['staff_logged_in'] !== true ||
    !isset($_SESSION['branch_id']) ||
    !isset($_SESSION['branch_name'])
) {
    header("Location: staff_login.php");
    exit;
}

$branch_id = $_SESSION['branch_id'];
$branch_name = $_SESSION['branch_name'];
$branch_email = '-';
$branch_phone = '-';
$branch_is_open = 0;

try {
   $stmt = $pdo->prepare("SELECT name, email, phone, is_open FROM branches WHERE id = ? LIMIT 1");
    $stmt->execute([$branch_id]);
    $branch = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($branch) {
    $branch_name = $branch['name'] ?? $branch_name;
    $branch_email = $branch['email'] ?? '-';
    $branch_phone = $branch['phone'] ?? '-';
    $branch_is_open = $branch['is_open'] ?? 0;
}
} catch (Exception $e) {
    $branch_email = '-';
    $branch_phone = '-';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kitchen Dashboard</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
    * { box-sizing: border-box; }
    :root {
        --primary: #ff5100;
        --primary-hover: #e04600;
        --dark: #333;
        --light: #f4f6f8;
        --white: #2d2d2d;
        --gray: #444444;
        --success: #2ecc71;
        --warning: #f1c40f;
        --danger: #e74c3c;
        --text-main: #ffffff;
        --text-muted: #aaaaaa;
    }
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: black;
        color: var(--text-main);
        display: flex;
        height: 100vh;
        overflow: hidden;
    }
    .sidebar {
        width: 260px;
        background: #2d2d2d;
        border-right: 1px solid var(--gray);
        display: flex;
        flex-direction: column;
        padding: 20px;
        flex-shrink: 0;
    }
    .logo {
        font-size: 24px;
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 40px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .nav-item {
        padding: 15px;
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 15px;
        border-radius: 10px;
        margin-bottom: 5px;
        transition: 0.3s;
        font-weight: 600;
    }
    .nav-item:hover, .nav-item.active {
        background: #fff0e6;
        color: var(--primary);
    }
    .nav-item i { width: 20px; text-align: center; }

    .main-content {
        flex: 1;
        padding: 20px 30px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .view-section h2, .view-section h3 { color: white; }
    .panel-card h2, .panel-card h3 { color: white; }

    .top-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        background: #ff5100 !important;
        color: var(--white);
        padding: 15px 25px;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }
    .user-info h2 { margin: 0; font-size: 20px; color: white; }
    .user-info p { margin: 0; font-size: 14px; color: rgba(255,255,255,0.8); display: flex; align-items: center; gap: 5px; }
   .header-actions { display: flex; align-items: center; gap: 12px; }
    .logout-btn { color: white; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 8px; font-size: 14px; background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 20px; }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: var(--white);
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .stat-icon {
        width: 50px; height: 50px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px; color: white;
    }
    .stat-info h3 { margin: 0; font-size: 28px; color: var(--text-main); }
    .stat-info p { margin: 0; color: var(--text-muted); font-size: 14px; font-weight: 600; }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 2.5fr 1fr;
        gap: 25px;
        flex: 1;
    }

    .orders-section { display: flex; flex-direction: column; gap: 20px; }
    .filters { display: flex; gap: 10px; margin-bottom: 5px; }
    .filter-btn {
        background: var(--white);
        border: 1px solid var(--gray);
        padding: 10px 25px;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 700;
        color: var(--text-muted);
        transition: 0.2s;
    }
    .filter-btn:hover { background: #f0f0f0; }
    .filter-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .order-card {
        background: var(--white);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        border-left: 6px solid var(--gray);
        position: relative;
        transition: transform 0.2s;
    }
    .order-card:hover { transform: translateY(-3px); }
    .order-card.status-Pending { border-left-color: var(--warning); }
    .order-card.status-Preparing { border-left-color: var(--primary); }
    .order-card.status-Ready { border-left-color: var(--success); }
    .order-card.status-Served { border-left-color: var(--dark); opacity: 0.7; }

    .order-header { display: flex; justify-content: space-between; margin-bottom: 15px; align-items: center; }
    .order-id { font-weight: 800; font-size: 20px; color: var(--primary); }
    .order-time { color: var(--text-muted); font-size: 14px; display: flex; align-items: center; gap: 5px; }

    .order-items { margin-bottom: 20px; background: #3d3d3d; padding: 15px; border-radius: 10px; }
    .order-item { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 15px; font-weight: 500; }
    .order-item:last-child { margin-bottom: 0; }
    .item-variant { color: var(--text-muted); font-size: 13px; margin-left: 5px; }

    .order-actions { display: flex; gap: 15px; margin-top: 15px; }
    .action-btn {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        color: white;
        transition: 0.2s;
        font-size: 14px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-prepare { background: var(--primary); }
    .btn-prepare:hover { background: var(--primary-hover); }
    .btn-ready { background: var(--success); }
    .btn-ready:hover { background: #27ae60; }
    .btn-serve { background: var(--dark); }
    .btn-serve:hover { background: #000; }
    .btn-delete { background: var(--danger); margin-top: 10px; }
    .btn-delete:hover { background: #c0392b; }

    .branch-overview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }
    .branch-card {
        background: var(--white);
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        border-top: 4px solid var(--gray);
    }
    .branch-card.open { border-top-color: var(--success); }
    .branch-name { font-weight: 800; color: font-size: 16px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
    .branch-stat { font-size: 13px; color: var(--text-muted); margin-bottom: 5px; display: flex; justify-content: space-between; }
    .branch-stat strong { color: var(--text-main); }

    .urgent-order {
        border: 2px solid var(--danger) !important;
        animation: pulse-red 2s infinite;
    }
    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(231, 76, 60, 0); }
        100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); }
    }

.connection-status {
    font-size: 15px;
    font-weight: bold;
    padding: 10px 18px;
    border-radius: 30px;
    cursor: pointer;
    transition: 0.2s;
}
.connection-status:hover {
    transform: scale(1.05);
}

.connection-status i {
    font-size: 16px;
}
    .status-online { background: #d4edda; color: #155724; }
    .status-offline { background: #f8d7da; color: #721c24; }

    .receipt-modal-img { max-width: 100%; max-height: 60vh; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 15px; }
    .btn-verify { background: #3498db; color: white; }
    .btn-verify:hover { background: #2980b9; }
    .payment-badge { font-size: 12px; padding: 3px 8px; border-radius: 10px; background: #eee; color: #555; display: inline-block; margin-top: 5px; }

    .right-panel { display: flex; flex-direction: column; gap: 25px; }
    .panel-card {
        background: var(--white);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }
    .panel-title {
        font-weight: 800;
        margin-bottom: 20px;
        font-size: 16px;
        color: var(--text-main);
        display: flex; justify-content: space-between; align-items: center;
    }

    .quick-btn {
        width: 100%;
        padding: 15px;
        margin-bottom: 10px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        text-align: left;
        display: flex; align-items: center; gap: 15px;
        background: #3d3d3d;
        color: var(--text-main);
        transition: 0.2s;
        font-size: 14px;
    }
    .quick-btn:hover { background: #e9ecef; color: var(--primary); }
    .quick-btn i { color: var(--primary); font-size: 18px; width: 25px; text-align: center; }

    .stock-item { display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #444; align-items: center; }
    .stock-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .stock-name { font-weight: 500; font-size: 14px; }
    .stock-status { font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 700; text-transform: uppercase; }
    .stock-ok { background: #d4edda; color: #155724; }
    .stock-low { background: #fff3cd; color: #856404; }
    .stock-out { background: #f8d7da; color: #721c24; }

    .announcement-box { background: #fff8e1; padding: 15px; border-radius: 10px; border-left: 4px solid var(--warning); font-size: 14px; line-height: 1.5; color: #5d4037; }

    .clock-btn { padding: 10px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; color: white; font-size: 13px; }
    .btn-in { background: var(--success); }
    .btn-out { background: var(--danger); }

    .prep-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }
    .prep-item:last-child { border-bottom: none; }
    .prep-count { font-weight: bold; color: var(--primary); }

    .staff-table { width: 100%; border-collapse: collapse; }
    .staff-table th, .staff-table td { padding: 12px; text-align: left; border-bottom: 1px solid #444; }
    .staff-status-badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
    .status-in { background: #d4edda; color: #155724; }
    .status-out { background: #f8d7da; color: #721c24; }

    @media (max-width: 1200px) {
        .dashboard-grid { grid-template-columns: 1fr; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
    .modal-content { background-color: #fefefe; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 10px; position: relative; text-align: center; margin: 10% auto; }
    .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; position: absolute; right: 15px; top: 5px; }
    .close:hover, .close:focus { color: black; text-decoration: none; cursor: pointer; }
</style>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <i class="fas fa-hamburger"></i> BamBam
    </div>
    <a href="#" class="nav-item active" onclick="switchView('dashboard', this)"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="#" class="nav-item" onclick="switchView('orders', this); loadOrderHistory();"><i class="fas fa-receipt"></i> Orders</a>
    <a href="#" class="nav-item" onclick="switchView('stock', this); loadStock();"><i class="fas fa-box-open"></i> Stock</a>
</div>

<div class="main-content">
    <header class="top-header">
    <div class="user-info">
        <h2>Welcome, <?php echo htmlspecialchars($branch_name); ?> Branch</h2>
        <p><i class="fas fa-store"></i> Branch: <?php echo htmlspecialchars($branch_name); ?></p>
    </div>

    <div class="header-actions">
        <div 
            id="branch-status-btn"
            class="connection-status <?php echo $branch_is_open ? 'status-online' : 'status-offline'; ?>"
            data-status="<?php echo $branch_is_open ? 'open' : 'close'; ?>"
            onclick="toggleBranchStatus()"
        >
            <i class="fas <?php echo $branch_is_open ? 'fa-store' : 'fa-store-slash'; ?>"></i>
            <?php echo $branch_is_open ? 'Open' : 'Closed'; ?>
        </div>

        <a href="staff_login.php?action=logout" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>

    <div id="view-dashboard" class="view-section">
        <h3 style="margin-top:0; margin-bottom:15px;">Branch Overview</h3>
        <div id="branch-overview-container" class="branch-overview-grid"></div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #3498db;"><i class="fas fa-clipboard-list"></i></div>
                <div class="stat-info"><h3 id="stat-total">0</h3><p>Total Orders</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--primary);"><i class="fas fa-fire"></i></div>
                <div class="stat-info"><h3 id="stat-prep">0</h3><p>In Preparation</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--success);"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info"><h3 id="stat-completed">0</h3><p>Completed</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--warning);"><i class="fas fa-clock"></i></div>
                <div class="stat-info"><h3 id="stat-pending">0</h3><p>Pending</p></div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="orders-section">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <div class="filters">
                        <button class="filter-btn active" onclick="filterOrders('All')">All</button>
                        <button class="filter-btn" onclick="filterOrders('Pending')">Pending</button>
                        <button class="filter-btn" onclick="filterOrders('Preparing')">Preparing</button>
                        <button class="filter-btn" onclick="filterOrders('Ready')">Ready</button>
                        <button class="filter-btn" onclick="filterOrders('Served')">Served</button>
                    </div>
                    <div style="padding:10px 15px; background:#3d3d3d; border-radius:10px; font-weight:bold; color:white;">
                        Branch: <?php echo htmlspecialchars($branch_name); ?>
                    </div>
                </div>
                <div id="orders-list"></div>
            </div>

            <div class="right-panel">
                <div class="panel-card">
                    <div class="panel-title">⚡ Quick Actions</div>
                    <button class="quick-btn" onclick="window.open('menu.php', '_blank')"><i class="fas fa-plus-circle"></i> Add New Order</button>
                    <button class="quick-btn"><i class="fas fa-exclamation-triangle"></i> Report Out-of-Stock</button>
                    <button class="quick-btn"><i class="fas fa-broom"></i> Mark Cleaning Done</button>
                </div>

                <div class="panel-card">
                    <div class="panel-title">👨‍🍳 Prep Suggestions</div>
                    <div id="prep-list"><p style="color:#777; font-size:13px;">Analyzing orders...</p></div>
                </div>

                <div class="panel-card">
                    <div class="panel-title">📦 Stock Overview</div>
                    <div id="dashboard-stock-list"></div>
                </div>

                <div class="panel-card">
                    <div class="panel-title">📢 Announcements</div>
                    <div class="announcement-box">
                        <strong>Today's Promo:</strong> Lava Cheese Burger promotion today – expect high order volume around 5 PM.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="view-orders" class="view-section" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0;">Order History</h2>
            <input type="date" id="history-date-filter" onchange="loadOrderHistory()" style="padding:8px; border:1px solid #ccc; border-radius:5px;">
        </div>
        <div style="background:var(--white); padding:20px; border-radius:15px; box-shadow:0 2px 10px rgba(0,0,0,0.03);">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#3d3d3d; text-align:left;">
                        <th style="padding:15px; border-radius:10px 0 0 10px;">ID</th>
                        <th style="padding:15px;">Date</th>
                        <th style="padding:15px;">Items</th>
                        <th style="padding:15px;">Total</th>
                        <th style="padding:15px;">Status</th>
                        <th style="padding:15px; border-radius:0 10px 10px 0;">Action</th>
                    </tr>
                </thead>
                <tbody id="full-order-history"></tbody>
            </table>
        </div>
    </div>

    <div id="view-stock" class="view-section" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0;">Stock Management</h2>
            <button onclick="addNewStockItem()" style="padding:10px 20px; background:#3498db; color:white; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">+ Add New Item</button>
        </div>

        <div style="display:flex; gap:15px; margin-bottom:20px; background:var(--white); padding:15px; border-radius:10px; box-shadow:0 2px 5px rgba(0,0,0,0.05);">
            <input type="text" id="stock-search" class="search-bar" placeholder="🔍 Search item..." style="margin:0; flex:1;" onkeyup="loadStock()">
            <select id="stock-filter" class="report-filter" style="width:auto; margin:0; min-width:150px;" onchange="loadStock()">
                <option value="All">All Status</option>
                <option value="In Stock">Available</option>
                <option value="Low Stock">Low Stock</option>
                <option value="Out of Stock">Out of Stock</option>
            </select>
        </div>

        <div class="panel-card" style="padding:0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#3d3d3d; text-align:left; border-bottom:2px solid #444;">
                        <th style="padding:15px;">Item</th>
                        <th style="padding:15px;">Stock Level</th>
                        <th style="padding:15px;">Status</th>
                        <th style="padding:15px;">Action</th>
                    </tr>
                </thead>
                <tbody id="stock-list-container"></tbody>
            </table>
        </div>
    </div>

</div>

<div id="receiptModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeReceiptModal()">&times;</span>
        <h3>Payment Receipt</h3>
        <div id="receipt-content-area"></div>
    </div>
</div>

<script>
let currentFilter = 'All';
let currentBranchFilter = <?php echo json_encode($branch_name); ?>;
const currentBranchId = <?php echo json_encode($branch_id); ?>;

let knownOrderIds = new Set();
let isFirstLoad = true;
let allOrders = [];
let selectedStaffId = null;
let selectedStaffName = '';
const notificationSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');

document.addEventListener('DOMContentLoaded', () => {
    loadOrders();
    updateDashboardStock();
    loadBranchOverview();
    updateBranchStatusUI();
});

function switchView(viewId, navItem) {
    document.querySelectorAll('.view-section').forEach(el => el.style.display = 'none');
    document.getElementById('view-' + viewId).style.display = 'block';
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    navItem.classList.add('active');
}

function filterOrders(status) {
    currentFilter = status;
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.innerText === status) btn.classList.add('active');
    });
    loadOrders();
}

function loadOrders() {
    const container = document.getElementById('orders-list');

    fetch('get_dashboard_data.php?branch_id=' + encodeURIComponent(currentBranchId))
        .then(response => response.json())
        .then(orders => {
            let hasNewOrder = false;
            orders.forEach(order => {
                if (!knownOrderIds.has(order.id)) {
                    if (!isFirstLoad) hasNewOrder = true;
                    knownOrderIds.add(order.id);
                }
            });

            if (hasNewOrder) {
                notificationSound.play().catch(() => {});
            }
            isFirstLoad = false;

            allOrders = orders;
            container.innerHTML = '';

            let countTotal = 0, countPrep = 0, countCompleted = 0, countPending = 0;

            if (orders.length === 0) {
                container.innerHTML = '<p style="text-align:center; color:#777; padding:20px;">No active orders.</p>';
                updateStats(0, 0, 0, 0);
                return;
            }

            orders.forEach((order) => {
                order.branch = order.branch || 'Main';
                order.orderType = order.order_type;
                order.payment = order.payment_method;
                order.paymentProof = order.receipt_img ? 'uploads/' + order.receipt_img : null;
                order.senderName = order.customer_name;
                order.customerPhone = order.customer_phone;
                order.timestamp = new Date(order.created_at).getTime();
                order.paymentStatus = order.payment_status || 'Pending';

                order.items = order.items.map(i => ({
                    name: i.item_name,
                    variant: i.variant,
                    qty: i.qty,
                    customization: i.customization || ''
                }));

                if (!order.status) order.status = 'Pending';

                countTotal++;
                if (order.status === 'Pending') countPending++;
                if (order.status === 'Preparing') countPrep++;
                if (order.status === 'Ready' || order.status === 'Served') countCompleted++;

                if (currentFilter !== 'All' && order.status !== currentFilter) return;
                if (order.branch !== currentBranchFilter) return;
                if (currentFilter === 'All' && order.status === 'Served') return;

                let paymentStatusHtml = '';
                if (order.payment === 'Cash') {
                    paymentStatusHtml = `<span class="payment-badge" style="background:#ffe0b2; color:#e67e22;">Pay at Counter</span>`;
                } else if (order.paymentProof) {
                    if (order.paymentStatus === 'Confirmed') {
                        paymentStatusHtml = `<span class="payment-badge" style="background:#d4edda; color:#155724;">Payment Verified ✅</span>`;
                    } else {
                        paymentStatusHtml = `<button class="action-btn btn-verify" style="padding:5px 10px; font-size:12px; margin-top:5px;" onclick="viewReceipt(${order.id})"><i class="fas fa-file-invoice"></i> Verify Receipt</button>`;
                    }
                }

                let actionButton = '';
                if (order.status === 'Pending') {
                    actionButton = `<button class="action-btn btn-prepare" onclick="updateStatus(${order.id}, 'Preparing')"><i class="fas fa-fire"></i> Start Cooking</button>`;
                } else if (order.status === 'Preparing') {
                    actionButton = `<button class="action-btn btn-ready" onclick="updateStatus(${order.id}, 'Ready')"><i class="fas fa-check"></i> Mark Ready</button>`;
                } else if (order.status === 'Ready') {
                    actionButton = `<button class="action-btn btn-serve" onclick="updateStatus(${order.id}, 'Served')"><i class="fas fa-concierge-bell"></i> Serve Order</button>`;
                } else if (order.status === 'Served') {
                    actionButton = `<div style="text-align:center; color:green; font-weight:bold;"><i class="fas fa-check-double"></i> Served</div>`;
                }

                const deleteButton = `<button class="action-btn btn-delete" onclick="deleteOrder(${order.id})"><i class="fas fa-trash"></i> Delete</button>`;

                let itemsHtml = order.items.map(i => `
                    <div class="order-item">
                        <span>${i.qty}x ${i.name} <span class="item-variant">(${i.variant})</span></span>
                        ${i.customization ? `<div style="font-size:12px; color:#e67e22; margin-left:10px; font-weight:bold;">👉 ${i.customization}</div>` : ''}
                    </div>`).join('');

                const timeElapsed = Math.floor((Date.now() - order.timestamp) / 60000);
                let urgentClass = '';
                if ((order.status === 'Pending' || order.status === 'Preparing') && timeElapsed > 15) {
                    urgentClass = 'urgent-order';
                }

                const customerDetailsHtml = `
                    <div style="font-size:14px;color:black;; margin-bottom:10px; background:#f9f9f9; padding:10px; border-radius:8px;">
                        <div style="font-weight:bold; display:flex; align-items:center; gap:8px;"><i class="fas fa-user" style="color: black;"></i> ${order.senderName || 'Walk-in'}</div>
                        ${order.customerPhone ? `<div style="font-size:13px; color: black; margin-top:5px; display:flex; align-items:center; gap:8px;"><i class="fas fa-phone"></i> ${order.customerPhone}</div>` : ''}
                    </div>
                `;

                const cardHtml = `
                    <div class="order-card status-${order.status} ${urgentClass}">
                        <div class="order-header">
                            <span class="order-id">#${order.id}</span>
                            <span class="order-time" style="${urgentClass ? 'color:var(--danger); font-weight:bold;' : ''}"><i class="far fa-clock"></i> ${timeElapsed}m ago</span>
                        </div>
                        <div style="margin-bottom:10px; font-size:14px;">
                            <strong>${order.orderType || 'Dine-in'}</strong> • ${order.branch}
                        </div>
                        ${customerDetailsHtml}
                        <div style="margin-bottom:10px;">${paymentStatusHtml}</div>
                        <div class="order-items">${itemsHtml}</div>

                        <div class="order-actions">
                            ${actionButton}
                            ${deleteButton}
                            <button class="action-btn" style="background:#3498db;" onclick="printOrderToKPS(${order.id})"><i class="fas fa-print"></i> Print to KPS</button>
                        </div>
                    </div>
                `;
                container.innerHTML += cardHtml;
            });

            updateStats(countTotal, countPrep, countCompleted, countPending);
            generatePrepSuggestions(orders);
            loadBranchOverview();
        })
        .catch(err => console.error('Error loading orders:', err));
}

function loadOrderHistory() {
    const tbody = document.getElementById('full-order-history');
    const orders = allOrders;
    const filterDate = document.getElementById('history-date-filter').value;
    tbody.innerHTML = '';
    let hasData = false;

    orders.forEach((order) => {
        if (filterDate) {
            const orderTime = new Date(order.created_at || order.timestamp).getTime();
            if (!isNaN(orderTime)) {
                const d = new Date(orderTime);
                const localDateStr = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                if (localDateStr !== filterDate) return;
            }
        }

        hasData = true;
        const itemsSummary = order.items.map(i => `${i.qty}x ${i.name}`).join(', ');
        tbody.innerHTML += `
            <tr style="border-bottom:1px solid #444;">
                <td style="padding:15px;">#${order.id}</td>
                <td style="padding:15px;">${new Date(order.created_at || order.timestamp).toLocaleDateString()}</td>
                <td style="padding:15px;">${itemsSummary}</td>
                <td style="padding:15px;">RM ${order.total || order.total_amount}</td>
                <td style="padding:15px;"><span class="stock-status ${order.status === 'Ready' || order.status === 'Served' ? 'stock-ok' : 'stock-low'}">${order.status}</span></td>
                <td style="padding:15px;"><button class="action-btn btn-delete" style="margin:0; padding:5px 10px; width:auto;" onclick="deleteOrder(${order.id})">Delete</button></td>
            </tr>
        `;
    });

    if (!hasData) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px;">No history found.</td></tr>';
    }
}

function updateStats(total, prep, comp, pending) {
    document.getElementById('stat-total').innerText = total;
    document.getElementById('stat-prep').innerText = prep;
    document.getElementById('stat-completed').innerText = comp;
    document.getElementById('stat-pending').innerText = pending;
}

function updateStatus(id, newStatus) {
    fetch('update_order_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: id, status: newStatus })
    }).then(() => loadOrders());
}

function deleteOrder(id) {
    if (!confirm('Are you sure you want to delete this order?')) return;
    fetch('delete_order.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: id })
    }).then(() => loadOrders());
}

function viewReceipt(id) {
    const order = allOrders.find(o => o.id == id);
    if (!order) return;

    const modal = document.getElementById('receiptModal');
    const content = document.getElementById('receipt-content-area');

    content.innerHTML = `
        <p><strong>Sender:</strong> ${order.senderName}</p>
        <img src="${order.paymentProof}" class="receipt-modal-img">
        <button class="action-btn btn-ready" onclick="confirmPayment(${id})">Confirm Payment</button>
    `;
    modal.style.display = 'flex';
}

function closeReceiptModal() {
    document.getElementById('receiptModal').style.display = 'none';
}

function confirmPayment(id) {
    fetch('update_order_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: id, payment_status: 'Confirmed' })
    }).then(() => {
        closeReceiptModal();
        loadOrders();
    });
}

function loadBranchOverview() {
    const orders = allOrders;
    const container = document.getElementById('branch-overview-container');
    container.innerHTML = '';

    const today = new Date().toLocaleDateString();
    let count = 0;
    let sales = 0;

    orders.forEach(o => {
        const oDate = new Date(o.created_at || o.timestamp).toLocaleDateString();
        const oBranch = o.branch || 'Main';

        if (oBranch === currentBranchFilter && oDate === today) {
            count++;
            sales += parseFloat(o.total || o.total_amount || 0);
        }
    });

    container.innerHTML = `
        <div class="branch-card open">
            <div class="branch-name">
                ${currentBranchFilter}
                <span style="font-size:11px; padding:2px 6px; background:#d4edda; color:#155724; border-radius:4px;">Open</span>
            </div>
            <div class="branch-stat">Orders Today: <strong>${count}</strong></div>
            <div class="branch-stat">Daily Sales: <strong>RM ${sales.toFixed(2)}</strong></div>
        </div>
    `;
}

const defaultStock = [
    { name: 'Burger Buns 🍞', status: 'In Stock', level: 50 },
    { name: 'Beef Patties 🥩', status: 'Low Stock', level: 5 },
    { name: 'Cheese 🧀', status: 'Out of Stock', level: 0 },
    { name: 'Lettuce 🥬', status: 'In Stock', level: 20 },
    { name: 'Tomatoes 🍅', status: 'In Stock', level: 25 },
    { name: 'Special Sauce 🥣', status: 'Low Stock', level: 2 }
];

function loadStock() {
    let stock = JSON.parse(localStorage.getItem('bambam_stock')) || defaultStock;
    stock = stock.map(item => {
        if (item.level === undefined) item.level = item.status === 'In Stock' ? 50 : (item.status === 'Low Stock' ? 5 : 0);
        return item;
    });

    const container = document.getElementById('stock-list-container');
    const search = document.getElementById('stock-search').value.toLowerCase();
    const filter = document.getElementById('stock-filter').value;
    container.innerHTML = '';

    stock.forEach((item, index) => {
        if (search && !item.name.toLowerCase().includes(search)) return;
        if (filter !== 'All' && item.status !== filter) return;

        let statusBadge = '';
        let actionBtn = '';

        if (item.status === 'In Stock') {
            statusBadge = '<span class="stock-status stock-ok">✅ Available</span>';
            actionBtn = `<button class="action-btn" style="width:auto; padding:5px 10px; background:#f1c40f; color:black; font-size:12px;" onclick="setStockStatus(${index}, 'Low Stock')">⚠️ Report Low</button>`;
        } else if (item.status === 'Low Stock') {
            statusBadge = '<span class="stock-status stock-low">⚠️ Low</span>';
            actionBtn = `<button class="action-btn btn-delete" style="width:auto; padding:5px 10px; font-size:12px; margin:0;" onclick="setStockStatus(${index}, 'Out of Stock')">❌ Report Out</button>`;
        } else {
            statusBadge = '<span class="stock-status stock-out">❌ Out of Stock</span>';
            actionBtn = `<button class="action-btn btn-ready" style="width:auto; padding:5px 10px; font-size:12px;" onclick="restockItem(${index})">✅ Restock</button>`;
        }

        container.innerHTML += `
            <tr style="border-bottom:1px solid #444;">
                <td style="padding:15px; font-weight:500;">${item.name}</td>
                <td style="padding:15px;">
                    ${item.level}
                    <i class="fas fa-edit" style="cursor:pointer; color:#aaa; font-size:12px; margin-left:5px;" onclick="editStockLevel(${index})" title="Edit Level"></i>
                </td>
                <td style="padding:15px;">${statusBadge}</td>
                <td style="padding:15px;">${actionBtn}</td>
            </tr>
        `;
    });
}

function updateDashboardStock() {
    const stock = JSON.parse(localStorage.getItem('bambam_stock')) || defaultStock;
    const container = document.getElementById('dashboard-stock-list');
    if (!container) return;
    container.innerHTML = '';

    stock.forEach(item => {
        let statusClass = 'stock-ok';
        let statusText = 'Available';
        if (item.status === 'Low Stock') {
            statusClass = 'stock-low';
            statusText = 'Low Stock';
        } else if (item.status === 'Out of Stock') {
            statusClass = 'stock-out';
            statusText = 'Out of Stock';
        }

        container.innerHTML += `
            <div class="stock-item">
                <span class="stock-name">${item.name}</span>
                <span class="stock-status ${statusClass}">${statusText}</span>
            </div>
        `;
    });
}

function setStockStatus(index, status) {
    const stock = JSON.parse(localStorage.getItem('bambam_stock')) || defaultStock;
    stock[index].status = status;
    if (status === 'Out of Stock') stock[index].level = 0;
    localStorage.setItem('bambam_stock', JSON.stringify(stock));
    loadStock();
    updateDashboardStock();
}

function restockItem(index) {
    const stock = JSON.parse(localStorage.getItem('bambam_stock')) || defaultStock;
    const newLevel = prompt("Enter new stock level:", 50);
    if (newLevel !== null && !isNaN(newLevel)) {
        stock[index].level = parseInt(newLevel);
        stock[index].status = 'In Stock';
        localStorage.setItem('bambam_stock', JSON.stringify(stock));
        loadStock();
        updateDashboardStock();
    }
}

function editStockLevel(index) {
    const stock = JSON.parse(localStorage.getItem('bambam_stock')) || defaultStock;
    const newLevel = prompt("Update stock level:", stock[index].level);
    if (newLevel !== null && !isNaN(newLevel)) {
        stock[index].level = parseInt(newLevel);
        if (stock[index].level === 0) stock[index].status = 'Out of Stock';
        else if (stock[index].level < 10) stock[index].status = 'Low Stock';
        else stock[index].status = 'In Stock';
        localStorage.setItem('bambam_stock', JSON.stringify(stock));
        loadStock();
        updateDashboardStock();
    }
}

function addNewStockItem() {
    const name = prompt("Enter new stock item name (e.g., 'Onions 🧅'):");
    if (name) {
        const stock = JSON.parse(localStorage.getItem('bambam_stock')) || defaultStock;
        stock.push({ name: name, status: 'In Stock', level: 50 });
        localStorage.setItem('bambam_stock', JSON.stringify(stock));
        loadStock();
        updateDashboardStock();
    }
}

function generatePrepSuggestions(orders) {
    const prepCounts = { 'Beef Patties': 0, 'Chicken Patties': 0, 'Lamb Patties': 0, 'Eggs': 0 };

    orders.forEach(order => {
        if (order.status === 'Pending' || order.status === 'Preparing') {
            order.items.forEach(item => {
                let qty = parseInt(item.qty) || 1;
                let name = item.name.toLowerCase();
                let protein = (item.protein || '').toLowerCase();

                if (protein === 'daging' || name.includes('beef') || name.includes('smash') || name.includes('steak')) {
                    prepCounts['Beef Patties'] += qty;
                } else if (protein === 'ayam' || name.includes('ayam') || name.includes('chicken')) {
                    prepCounts['Chicken Patties'] += qty;
                } else if (name.includes('kambing')) {
                    prepCounts['Lamb Patties'] += qty;
                } else if (name.includes('benjo') || name.includes('telur')) {
                    prepCounts['Eggs'] += qty;
                }
            });
        }
    });

    const container = document.getElementById('prep-list');
    container.innerHTML = '';
    let hasSuggestions = false;

    for (const [ingredient, count] of Object.entries(prepCounts)) {
        if (count > 0) {
            hasSuggestions = true;
            container.innerHTML += `<div class="prep-item"><span>${ingredient}</span><span class="prep-count">x${count}</span></div>`;
        }
    }

    if (!hasSuggestions) container.innerHTML = '<p style="color:#777; font-size:13px;">No pending prep needed.</p>';
}

function loadStaffList() {
    const container = document.getElementById('staff-list-container');
    container.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px;">Loading staff...</td></tr>';

    fetch('get_staff_list.php')
        .then(response => response.json())
        .then(result => {
            container.innerHTML = '';

            const staffList = result.data || [];

            if (!result.success || !staffList.length) {
                container.innerHTML = `
                    <tr>
                        <td colspan="4" style="text-align:center; padding:20px;">
                            No staff found for this branch.
                        </td>
                    </tr>
                `;
                return;
            }

            staffList.forEach(staff => {
                const isIn = staff.attendance_status === 'Clock In';
                const statusText = isIn ? 'Clock In' : 'Clock Out';
                const statusClass = isIn ? 'status-in' : 'status-out';
                const safeName = String(staff.name).replace(/'/g, "\\'");

                container.innerHTML += `
                    <tr style="border-bottom:1px solid #444;">
                        <td style="padding:12px;"><strong>${staff.name}</strong></td>
                        <td style="padding:12px;">${staff.role}</td>
                        <td style="padding:12px;">
                            <span class="staff-status-badge ${statusClass}">${statusText}</span>
                        </td>
                        <td style="padding:12px; text-align:right;">
    <div style="display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap;">
        <button
            class="clock-btn btn-in"
            ${isIn ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''}
            onclick="handleStaffClock(${staff.id}, 'clock_in', '${safeName}')">
            Clock In
        </button>

        <button
            class="clock-btn btn-out"
            ${!isIn ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''}
            onclick="handleStaffClock(${staff.id}, 'clock_out', '${safeName}')">
            Clock Out
        </button>
    </div>
</td>
                    </tr>
                `;
            });
        })
        .catch(() => {
            container.innerHTML = `
                <tr>
                    <td colspan="4" style="text-align:center; padding:20px;">Failed to load staff.</td>
                </tr>
            `;
        });
}

function printOrderToKPS(orderId) {
    window.open(`print_kps_order.php?id=${orderId}`, '_blank');
}

function handleStaffClock(staffId, action, staffName) {
    const formData = new FormData();
    formData.append('action', action);
    formData.append('staff_id', staffId);

    fetch('process_attendance.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message + (data.work_hours ? "\nTotal Work: " + data.work_hours + " hrs" : ""));
                selectedStaffId = staffId;
                selectedStaffName = staffName;
                loadStaffAttendance(staffId, staffName);
                loadStaffList();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(() => {
            alert("Failed to process attendance.");
        });
}

function loadStaffAttendance(staffId, staffName) {
    selectedStaffId = staffId;
    selectedStaffName = staffName;

    const container = document.getElementById('staff-attendance-history-list');
    const title = document.getElementById('attendance-history-title');
    const filterDate = document.getElementById('staff-attendance-date-filter').value;

    title.innerText = 'Showing history for: ' + staffName;
    container.innerHTML = '<div style="color:#777; font-size:13px;">Loading attendance history...</div>';

    let url = 'get_attendance_history.php?staff_id=' + encodeURIComponent(staffId);
    if (filterDate) {
        url += '&date=' + encodeURIComponent(filterDate);
    }

    fetch(url)
        .then(res => res.json())
        .then(data => {
            container.innerHTML = '';

            if (!data.success) {
                container.innerHTML = '<div style="color:red; font-size:13px;">' + (data.message || 'Failed to load history') + '</div>';
                return;
            }

            if (!data.records || data.records.length === 0) {
                container.innerHTML = '<div style="color:#777; font-size:13px;">No attendance records found.</div>';
                return;
            }

            let html = `
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#3d3d3d; text-align:left;">
                            <th style="padding:12px;">Date</th>
                            <th style="padding:12px;">Clock In</th>
                            <th style="padding:12px;">Clock Out</th>
                            <th style="padding:12px;">Hours</th>
                            <th style="padding:12px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.records.forEach(entry => {
                html += `
                    <tr style="border-bottom:1px solid #444;">
                        <td style="padding:12px;">${entry.work_date}</td>
                        <td style="padding:12px;">${entry.clock_in}</td>
                        <td style="padding:12px;">${entry.clock_out}</td>
                        <td style="padding:12px;">${entry.total_hours} hrs</td>
                        <td style="padding:12px;">${entry.status}</td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        })
        .catch(() => {
            container.innerHTML = '<div style="color:red; font-size:13px;">Failed to load attendance history.</div>';
        });
}

function reloadSelectedStaffAttendance() {
    if (selectedStaffId) {
        loadStaffAttendance(selectedStaffId, selectedStaffName);
    }
}

function toggleBranchStatus() {
    const btn = document.getElementById('branch-status-btn');
    let currentStatus = btn.dataset.status || 'close';
    let newStatus = currentStatus === 'open' ? 'close' : 'open';

    fetch('update_branch_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            updateBranchStatusUI(data.status);
        } else {
            alert('Failed to update branch status');
        }
    })
    .catch(() => {
        alert('Failed to connect to server');
    });
}

function updateBranchStatusUI(status = 'close') {
    const btn = document.getElementById('branch-status-btn');

    btn.dataset.status = status;

    if (status === 'open') {
        btn.className = 'connection-status status-online';
        btn.innerHTML = '<i class="fas fa-store"></i> Open';
    } else {
        btn.className = 'connection-status status-offline';
        btn.innerHTML = '<i class="fas fa-store-slash"></i> Closed';
    }
}

window.addEventListener('storage', loadOrders);
setInterval(loadOrders, 5000);
</script>
</body>
</html>
