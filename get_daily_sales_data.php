<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

// Security check: ensure an admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Get date from request, default to today
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$startOfDay = $selectedDate . ' 00:00:00';
$endOfDay = $selectedDate . ' 23:59:59';

$response = [
    'totalSales' => 0,
    'totalQuantity' => 0,
    'categoryData' => [
        'labels' => [],
        'data' => []
    ],
    'hotItems' => []
];

try {
    // 1. Total Sales and Quantity for the day
    $stmtTotals = $pdo->prepare(
        "SELECT 
            SUM(o.total_amount) as totalSales, 
            SUM(oi.qty) as totalQuantity
         FROM orders o
         JOIN order_items oi ON o.id = oi.order_id
         WHERE o.created_at BETWEEN ? AND ? AND o.status IN ('Served', 'Completed')"
    );
    $stmtTotals->execute([$startOfDay, $endOfDay]);
    $totals = $stmtTotals->fetch(PDO::FETCH_ASSOC);
    if ($totals) {
        $response['totalSales'] = (float)($totals['totalSales'] ?? 0);
        $response['totalQuantity'] = (int)($totals['totalQuantity'] ?? 0);
    }

    // 2. Sales by Category (for Donut Chart)
    $stmtCategories = $pdo->prepare(
        "SELECT 
            COALESCE(mi.category, 'Uncategorized') AS category, 
            SUM(oi.qty * oi.price) AS category_sales
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         LEFT JOIN menu_items mi ON oi.item_name = mi.name
         WHERE o.created_at BETWEEN ? AND ? AND o.status IN ('Served', 'Completed')
         GROUP BY category
         ORDER BY category_sales DESC"
    );
    $stmtCategories->execute([$startOfDay, $endOfDay]);
    $categorySales = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($categorySales as $cat) {
        $response['categoryData']['labels'][] = ucfirst($cat['category']);
        $response['categoryData']['data'][] = (float)$cat['category_sales'];
    }

    // 3. Hot Selling Items (Top 5)
    $stmtHotItems = $pdo->prepare(
        "SELECT oi.item_name, SUM(oi.qty) as quantity_sold
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         WHERE o.created_at BETWEEN ? AND ? AND o.status IN ('Served', 'Completed')
         GROUP BY oi.item_name ORDER BY quantity_sold DESC LIMIT 5"
    );
    $stmtHotItems->execute([$startOfDay, $endOfDay]);
    $response['hotItems'] = $stmtHotItems->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed: ' . $e->getMessage()]);
}
?>