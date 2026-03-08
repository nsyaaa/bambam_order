<?php
// ===============================
// Bambam Burger - Printable Report
// Theme: Orange & Black
// ===============================
session_start();
include 'db.php';

// Security Check
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    die("Access Denied. Please login as admin.");
}

// Date Filter Logic
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-d');
$startSql = $start . ' 00:00:00';
$endSql = $end . ' 23:59:59';

try {
    // 1. Sales by Category
    $stmt = $pdo->prepare("
        SELECT COALESCE(mi.category, 'Uncategorized') AS category, SUM(oi.qty * oi.price) AS category_sales, SUM(oi.qty) AS items_sold
        FROM order_items oi
        LEFT JOIN menu_items mi ON oi.item_name = mi.name
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status IN ('Served', 'Completed') AND o.created_at BETWEEN ? AND ?
        GROUP BY category ORDER BY category_sales DESC
    ");
    $stmt->execute([$startSql, $endSql]);
    $salesByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Top 10 Products
    $stmt = $pdo->prepare("
        SELECT item_name, SUM(qty) as total_qty, SUM(qty * price) as total_revenue
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status IN ('Served', 'Completed') AND o.created_at BETWEEN ? AND ?
        GROUP BY item_name ORDER BY total_qty DESC LIMIT 10
    ");
    $stmt->execute([$startSql, $endSql]);
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Daily Trend
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders
        FROM orders WHERE status IN ('Served', 'Completed') AND created_at BETWEEN ? AND ?
        GROUP BY DATE(created_at) ORDER BY date ASC
    ");
    $stmt->execute([$startSql, $endSql]);
    $dailyTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Payment Methods
    $stmt = $pdo->prepare("
        SELECT payment_method, COUNT(*) as count, SUM(total_amount) as total
        FROM orders WHERE status IN ('Served', 'Completed') AND created_at BETWEEN ? AND ?
        GROUP BY payment_method
    ");
    $stmt->execute([$startSql, $endSql]);
    $salesByPayment = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Totals
    $totalRevenue = array_sum(array_column($dailyTrend, 'revenue'));
    $totalOrders = array_sum(array_column($dailyTrend, 'orders'));

    // Prepare Chart Data
    $trendLabels = []; $trendRevenue = [];
    foreach($dailyTrend as $d) {
        $trendLabels[] = date('d M', strtotime($d['date']));
        $trendRevenue[] = $d['revenue'];
    }
    $pieLabels = array_column($salesByCategory, 'category');
    $pieData = array_column($salesByCategory, 'category_sales');

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report - <?php echo "$start to $end"; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            -webkit-print-color-adjust: exact;
        }
        .report-container { max-width: 1000px; margin: 0 auto; }
        
        /* Header */
        .report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ff5100; padding-bottom: 20px; margin-bottom: 30px; }
        .brand { font-size: 28px; font-weight: 800; color: #ff5100; text-transform: uppercase; }
        .report-meta { text-align: right; color: #888; font-size: 14px; }
        .report-meta strong { color: #fff; }

        /* Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #1e1e1e; padding: 20px; border-radius: 10px; border: 1px solid #333; text-align: center; }
        .stat-label { color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .stat-value { font-size: 24px; font-weight: bold; color: #ff5100; }

        /* Charts & Tables */
        .section-title { color: #ff5100; border-left: 4px solid #ff5100; padding-left: 10px; margin: 30px 0 15px 0; font-size: 18px; text-transform: uppercase; }
        .chart-wrapper { background: #1e1e1e; padding: 20px; border-radius: 10px; border: 1px solid #333; height: 300px; margin-bottom: 30px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 14px; }
        th { text-align: left; padding: 12px; background: #2a2a2a; color: #ff5100; border-bottom: 2px solid #ff5100; }
        td { padding: 12px; border-bottom: 1px solid #333; color: #ddd; }
        tr:nth-child(even) { background: #181818; }
        .text-right { text-align: right; }

        /* Print Button */
        .no-print { margin-bottom: 20px; text-align: right; }
        .btn-print { background: #ff5100; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 14px; }
        .btn-print:hover { background: #e04600; }

        @media print {
            .no-print { display: none; }
            body { background-color: #fff; color: #000; }
            .stat-card, .chart-wrapper { background: #fff; border: 1px solid #ccc; color: #000; }
            .stat-value, .brand, .section-title, th { color: #000 !important; }
            .report-header, th { border-color: #000 !important; }
            td { border-color: #eee; color: #000; }
            tr:nth-child(even) { background: #f9f9f9; }
        }
    </style>
</head>
<body>

<div class="report-container">
    <div class="no-print">
        <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print to PDF</button>
        <button onclick="window.close()" class="btn-print" style="background:#333; margin-left:10px;">Close</button>
    </div>

    <div class="report-header">
        <div class="brand"><i class="fas fa-hamburger"></i> BamBam Burger</div>
        <div class="report-meta">
            <div>Report Period: <strong><?php echo date('d M Y', strtotime($start)); ?> - <?php echo date('d M Y', strtotime($end)); ?></strong></div>
            <div>Generated on: <strong><?php echo date('d M Y, h:i A'); ?></strong></div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">RM <?php echo number_format($totalRevenue, 2); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?php echo number_format($totalOrders); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Top Category</div>
            <div class="stat-value"><?php echo !empty($salesByCategory) ? htmlspecialchars($salesByCategory[0]['category']) : '-'; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Best Seller</div>
            <div class="stat-value" style="font-size:18px; line-height:1.4;"><?php echo !empty($topProducts) ? htmlspecialchars($topProducts[0]['item_name']) : '-'; ?></div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div>
            <div class="section-title">Revenue Trend</div>
            <div class="chart-wrapper">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
        <div>
            <div class="section-title">Sales by Category</div>
            <div class="chart-wrapper">
                <canvas id="catChart"></canvas>
            </div>
        </div>
    </div>

    <div class="section-title">Top Selling Products</div>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th class="text-right">Quantity Sold</th>
                <th class="text-right">Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($topProducts as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['item_name']); ?></td>
                <td class="text-right"><?php echo number_format($p['total_qty']); ?></td>
                <td class="text-right">RM <?php echo number_format($p['total_revenue'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="section-title">Payment Methods</div>
    <table>
        <thead>
            <tr>
                <th>Method</th>
                <th class="text-right">Transactions</th>
                <th class="text-right">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($salesByPayment as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['payment_method']); ?></td>
                <td class="text-right"><?php echo number_format($p['count']); ?></td>
                <td class="text-right">RM <?php echo number_format($p['total'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div style="text-align:center; color:#555; font-size:12px; margin-top:40px; border-top:1px solid #333; padding-top:20px;">
        &copy; <?php echo date('Y'); ?> BamBam Burger Admin System. Internal Use Only.
    </div>
</div>

<script>
    // Trend Chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($trendLabels); ?>,
            datasets: [{
                label: 'Revenue (RM)',
                data: <?php echo json_encode($trendRevenue); ?>,
                borderColor: '#ff5100',
                backgroundColor: 'rgba(255, 81, 0, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#333' }, ticks: { color: '#888' } },
                x: { grid: { display: false }, ticks: { color: '#888' } }
            }
        }
    });

    // Category Chart
    new Chart(document.getElementById('catChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($pieLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($pieData); ?>,
                backgroundColor: ['#ff5100', '#e67e22', '#f39c12', '#d35400', '#c0392b'],
                borderColor: '#1e1e1e',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { color: '#aaa', boxWidth: 10 } } }
        }
    });
</script>

</body>
</html>