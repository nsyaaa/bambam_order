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
    // 1. Summary Totals
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS total_receipts,
            COALESCE(SUM(total_amount), 0) AS total_revenue
        FROM orders
        WHERE status IN ('Served', 'Completed') 
          AND created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$startSql, $endSql]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalRevenue = (float) ($summary['total_revenue'] ?? 0);
    $totalReceipts = (int) ($summary['total_receipts'] ?? 0);

    // 2. Total Items Sold
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(oi.qty), 0) AS total_items_sold
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status IN ('Served', 'Completed') 
          AND o.created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$startSql, $endSql]);
    $totalItemsSold = (int) $stmt->fetchColumn();

    // 3. Sales by Category
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(mi.category, 'Uncategorized') AS category, 
            SUM(oi.qty * oi.price) AS category_sales, 
            SUM(oi.qty) AS items_sold
        FROM order_items oi
        LEFT JOIN menu_items mi ON oi.item_name = mi.name
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status IN ('Served', 'Completed') 
          AND o.created_at BETWEEN ? AND ?
        GROUP BY category 
        ORDER BY category_sales DESC
    ");
    $stmt->execute([$startSql, $endSql]);
    $salesByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Top 10 Products
    $stmt = $pdo->prepare("
        SELECT 
            item_name, 
            SUM(qty) AS total_qty, 
            SUM(qty * price) AS total_revenue
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status IN ('Served', 'Completed') 
          AND o.created_at BETWEEN ? AND ?
        GROUP BY item_name 
        ORDER BY total_qty DESC 
        LIMIT 10
    ");
    $stmt->execute([$startSql, $endSql]);
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Daily Trend
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) AS date, 
            SUM(total_amount) AS revenue, 
            COUNT(*) AS receipts
        FROM orders 
        WHERE status IN ('Served', 'Completed') 
          AND created_at BETWEEN ? AND ?
        GROUP BY DATE(created_at) 
        ORDER BY date ASC
    ");
    $stmt->execute([$startSql, $endSql]);
    $dailyTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Payment Methods
    $stmt = $pdo->prepare("
        SELECT 
            payment_method, 
            COUNT(*) AS count, 
            SUM(total_amount) AS total
        FROM orders 
        WHERE status IN ('Served', 'Completed') 
          AND created_at BETWEEN ? AND ?
        GROUP BY payment_method
    ");
    $stmt->execute([$startSql, $endSql]);
    $salesByPayment = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare Chart Data
    $trendLabels = [];
    $trendRevenue = [];
    foreach ($dailyTrend as $d) {
        $trendLabels[] = date('d M', strtotime($d['date']));
        $trendRevenue[] = $d['revenue'];
    }

    $pieLabels = array_column($salesByCategory, 'category');
    $pieData = array_column($salesByCategory, 'category_sales');

    $topCategory = !empty($salesByCategory) ? $salesByCategory[0]['category'] : '-';
    $bestSeller = !empty($topProducts) ? $topProducts[0]['item_name'] : '-';

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

        .report-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Header */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ff5100;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .brand {
            font-size: 28px;
            font-weight: 800;
            color: #ff5100;
            text-transform: uppercase;
        }

        .report-meta {
            text-align: right;
            color: #888;
            font-size: 14px;
        }

        .report-meta strong {
            color: #fff;
        }

        /* Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: transparent;
        }

        .stat-label {
            color: #888;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #ff5100;
        }

        .stat-value.best-seller {
            font-size: 18px;
            line-height: 1.4;
        }

        /* Charts & Tables */
        .section-title {
            color: #ff5100;
            border-left: 4px solid #ff5100;
            padding-left: 10px;
            margin: 30px 0 15px 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .chart-wrapper {
            background: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #333;
            height: 300px;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 14px;
        }

        th {
            text-align: left;
            padding: 12px;
            background: #2a2a2a;
            color: #ff5100;
            border-bottom: 2px solid #ff5100;
            font-size: 11px;
            text-transform: uppercase;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #333;
            color: #ddd;
        }

        .text-right {
            text-align: right;
        }

        /* Print Button */
        .no-print {
            margin-bottom: 20px;
            text-align: right;
        }

        .btn-print {
            background: #ff5100;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }

        .btn-print:hover {
            background: #e04600;
        }

        /* --- PRINT OPTIMIZED STYLES --- */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            .no-print,
            .charts-grid,
            script {
                display: none !important;
            }

            body {
                background: #fff !important;
                color: #000 !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 9.5pt;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .report-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .report-header {
                border-bottom: 1px solid #f97316 !important;
                padding-bottom: 10px !important;
                margin-bottom: 14px !important;
            }

            .brand {
                color: #f97316 !important;
                font-size: 22px !important;
            }

            .report-meta,
            .report-meta strong {
                color: #000 !important;
                font-size: 11px !important;
            }

            .stats-grid {
                grid-template-columns: repeat(5, 1fr) !important;
                gap: 10px !important;
                margin-bottom: 14px !important;
            }

            .stat-card {
                border: 1px solid #ddd !important;
                background: #fff7ed !important;
                padding: 8px !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .stat-label {
                color: #666 !important;
                font-size: 10px !important;
            }

            .stat-value {
                color: #f97316 !important;
                font-size: 18px !important;
            }

            .stat-value.best-seller {
                font-size: 14px !important;
                line-height: 1.3 !important;
            }

            .panel-card {
                background: #fff !important;
                border: 1px solid #ddd !important;
                border-radius: 8px !important;
                padding: 12px !important;
                margin-bottom: 12px !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .panel-header {
                color: #f97316 !important;
                font-size: 15px !important;
                margin-bottom: 10px !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-bottom: 0 !important;
                font-size: 10px !important;
            }

            th {
                background: #fff3e0 !important;
                color: #f97316 !important;
                border-bottom: 1px solid #f97316 !important;
                padding: 6px 7px !important;
            }

            td {
                color: #000 !important;
                background: #fff !important;
                border-bottom: 1px solid #e5e5e5 !important;
                padding: 6px 7px !important;
            }

            tr {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .text-right {
                text-align: right !important;
            }

            .footer-print {
                margin-top: 10px !important;
                padding-top: 8px !important;
                border-top: 1px solid #ddd !important;
                color: #777 !important;
                font-size: 10px !important;
                text-align: center !important;
            }
        }

        /* Panel style for consistency with admin.php */
        .panel-card {
            background: #1e1e1e;
            padding: 25px;
            border-radius: 15px;
            border: 1px solid #333;
            margin-bottom: 30px;
        }

        .panel-header {
            margin: 0 0 20px 0;
            font-size: 18px;
        }

        .footer-print {
            text-align: center;
            color: #555;
            font-size: 12px;
            margin-top: 40px;
            border-top: 1px solid #333;
            padding-top: 20px;
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
                <div>Report Period: <strong><?php echo date('d M Y', strtotime($start)); ?> -
                        <?php echo date('d M Y', strtotime($end)); ?></strong></div>
                <div>Generated on: <strong><?php echo date('d M Y, h:i A'); ?></strong></div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">RM <?php echo number_format($totalRevenue, 2); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Receipts</div>
                <div class="stat-value"><?php echo number_format($totalReceipts); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Items Sold</div>
                <div class="stat-value"><?php echo number_format($totalItemsSold); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Top Category</div>
                <div class="stat-value"><?php echo htmlspecialchars($topCategory); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Best Seller</div>
                <div class="stat-value best-seller"><?php echo htmlspecialchars($bestSeller); ?></div>
            </div>
        </div>

        <div class="charts-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
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

        <div class="panel-card">
            <h3 class="panel-header">🏆 Top Selling Products</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th class="text-right">Quantity Sold</th>
                        <th class="text-right">Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topProducts as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['item_name']); ?></td>
                            <td class="text-right"><?php echo number_format($p['total_qty']); ?></td>
                            <td class="text-right">RM <?php echo number_format($p['total_revenue'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="panel-card">
            <h3 class="panel-header">💳 Payment Methods</h3>
            <table>
                <thead>
                    <tr>
                        <th>Method</th>
                        <th class="text-right">Transactions</th>
                        <th class="text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($salesByPayment as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['payment_method']); ?></td>
                            <td class="text-right"><?php echo number_format($p['count']); ?></td>
                            <td class="text-right">RM <?php echo number_format($p['total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="footer-print">
            &copy; <?php echo date('Y'); ?> BamBam Burger Admin System. Internal Use Only.
        </div>

    </div> <!-- end report-container -->

    <script>
        // Trend Chart
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trendLabels); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($trendRevenue); ?>,
                    borderColor: '#ff5100',
                    backgroundColor: 'rgba(255, 81, 0, 0.1)',
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