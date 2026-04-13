<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$page_title = ($current_page == 'admin') ? 'Dashboard' : ucwords(str_replace(['_', '-'], ' ', $current_page));
?>
<?php
$current_view = $_GET['view'] ?? 'dashboard';
?>
<?php
// ===============================
// Bambam Burger - Admin Dashboard
// ===============================
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit;
}

include 'db.php';

// --- SYSTEM SETUP (Auto-Create Tables) ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, user_name VARCHAR(50), action VARCHAR(50), details TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (setting_key VARCHAR(50) PRIMARY KEY, setting_value VARCHAR(255))");
    // Add last_login column to users if it doesn't exist
    try {
        $pdo->query("SELECT last_login FROM users LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL");
    }
    // Add payment status columns to orders if they don't exist
    try {
        $pdo->query("SELECT payment_status FROM orders LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(50) NOT NULL DEFAULT 'Pending' AFTER payment_method");
    }
    try {
        $pdo->query("SELECT paid_at FROM orders LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN paid_at TIMESTAMP NULL DEFAULT NULL AFTER payment_status");
    }
    try {
        $pdo->query("SELECT processed_by_staff_id FROM orders LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN processed_by_staff_id INT NULL AFTER paid_at");
    }
    // Add columns to menu_items if they don't exist
    try {
        $pdo->query("SELECT is_available FROM menu_items LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE menu_items ADD COLUMN is_available TINYINT(1) NOT NULL DEFAULT 1");
    }
    try {
        $pdo->query("SELECT cost_price FROM menu_items LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE menu_items ADD COLUMN cost_price DECIMAL(10, 2) DEFAULT 0.00");
    }
    try {
        $pdo->query("SELECT image FROM menu_items LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE menu_items ADD COLUMN image VARCHAR(255) NULL AFTER variants");
    }
    // Add columns for review moderation
    try {
        $pdo->query("SELECT review_is_approved FROM orders LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN review_is_approved TINYINT(1) NOT NULL DEFAULT 1 AFTER admin_reply");
    }
    // Ensure global_store_status exists
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'global_store_status'");
    $stmt->execute();
    if ($stmt->rowCount() == 0)
        $pdo->exec("INSERT INTO system_settings (setting_key, setting_value) VALUES ('global_store_status', 'open')");
} catch (PDOException $e) {
}

// Helper: Log Activity
function logActivity($pdo, $uid, $uname, $action, $details)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, user_name, action, details) VALUES (?, ?, ?, ?)");
        $stmt->execute([$uid, $uname, $action, $details]);
    } catch (Exception $e) {
    }
}

// Helper: Time Since
function time_since($since)
{
    $chunks = array(
        array(60 * 60 * 24 * 365, 'year'),
        array(60 * 60 * 24 * 30, 'month'),
        array(60 * 60 * 24 * 7, 'week'),
        array(60 * 60 * 24, 'day'),
        array(60 * 60, 'hour'),
        array(60, 'min'),
        array(1, 'sec')
    );
    $since = time() - $since;
    if ($since <= 0)
        return 'Just now';
    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];
        if (($count = floor($since / $seconds)) != 0) {
            break;
        }
    }
    $print = ($count == 1) ? '1 ' . $name : "$count {$name}s";
    return $print . ' ago';
}

// Check if user is admin
$isAdmin = false;
$currentUserId = null;
$currentUserName = 'Admin';

// 1. Check explicit admin session (from admin_login.php)
if (isset($_SESSION['admin_id'])) {
    $isAdmin = true;
    $currentUserId = $_SESSION['admin_id'];
    $currentUserName = $_SESSION['admin_name'] ?? 'Admin';
    $currentUserRole = 'admin';
}
// 2. Check regular user session for admin role
elseif (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userRole = $stmt->fetchColumn();
        if (in_array($userRole, ['admin', 'staff'])) {
            $isAdmin = true;
            $currentUserId = $_SESSION['user_id'];
            $currentUserName = $_SESSION['user_name'] ?? 'Admin';
            $currentUserRole = $userRole;
        }
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}

if (!$isAdmin) {
    // If not logged in, redirect to Admin Login instead of Home
    if (!isset($_SESSION['user_id'])) {
        header("Location: admin_login.php");
        exit;
    }
    header("Location: index.php");
    exit;
}

// Role-Based Access Control
$isSuperAdmin = ($currentUserRole === 'admin');
$canViewReports = $isSuperAdmin;
$canManageStaff = $isSuperAdmin;

// Handle admin actions
$message = '';
// 0. Retrieve message from URL if present
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
// 1. Retrieve message from session if redirected
if (isset($_SESSION['admin_msg'])) {
    $message = $_SESSION['admin_msg'];
    unset($_SESSION['admin_msg']);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = '';
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_user_role':
                if (isset($_POST['user_id'], $_POST['new_role'])) {
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                        $stmt->execute([$_POST['new_role'], $_POST['user_id']]);
                        $message = "User role updated successfully!";
                        logActivity($pdo, $currentUserId, $currentUserName, "Update Role", "User ID {$_POST['user_id']} -> {$_POST['new_role']}");
                    } catch (PDOException $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'create_user':
                if (isset($_POST['name'], $_POST['email'], $_POST['role'], $_POST['branch'])) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO staff (name, email, phone, role, branch) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$_POST['name'], $_POST['email'], $_POST['phone'] ?? '', $_POST['role'], $_POST['branch']]);
                        $message = "Staff added successfully!";
                        logActivity($pdo, $currentUserId, $currentUserName, "Create Staff", "Added staff {$_POST['name']}");
                    } catch (PDOException $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'delete_user':
                if (isset($_POST['user_id'])) {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
                        $stmt->execute([$_POST['user_id']]);
                        $message = "Staff deleted successfully!";
                        logActivity($pdo, $currentUserId, $currentUserName, "Delete Staff", "Deleted Staff ID {$_POST['user_id']}");
                    } catch (PDOException $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'reset_user_password':
                if ($isSuperAdmin && isset($_POST['user_id'], $_POST['new_password'])) {
                    try {
                        $passHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE staff SET password = ? WHERE id = ?");
                        $stmt->execute([$passHash, $_POST['user_id']]);
                        $message = "Password reset successfully!";
                    } catch (PDOException $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'mark_as_paid':
                if (isset($_POST['order_id'])) {
                    try {
                        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Paid', paid_at = NOW(), processed_by_staff_id = ? WHERE id = ?");
                        $stmt->execute([$currentUserId, $_POST['order_id']]);
                        $message = "Order #{$_POST['order_id']} marked as paid.";
                        logActivity($pdo, $currentUserId, $currentUserName, "Mark Paid", "Order #{$_POST['order_id']}");
                    } catch (PDOException $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'update_order_status':
                if (isset($_POST['order_id'], $_POST['new_status'])) {
                    try {
                        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                        $stmt->execute([$_POST['new_status'], $_POST['order_id']]);
                        $message = "Order #{$_POST['order_id']} updated.";
                    } catch (PDOException $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'delete_order':
                if (isset($_POST['order_id'])) {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
                        $stmt->execute([$_POST['order_id']]);
                        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                        $stmt->execute([$_POST['order_id']]);
                        $message = "Order #{$_POST['order_id']} deleted successfully!";
                        logActivity($pdo, $currentUserId, $currentUserName, "Delete Order", "Deleted Order #{$_POST['order_id']}");
                    } catch (PDOException $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'bulk_update_order_status':
                if (isset($_POST['order_ids'], $_POST['new_status'])) {
                    try {
                        $ids = explode(',', $_POST['order_ids']);
                        $inQuery = implode(',', array_fill(0, count($ids), '?'));
                        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id IN ($inQuery)");
                        $stmt->execute(array_merge([$_POST['new_status']], $ids));
                        $message = count($ids) . " orders updated to " . $_POST['new_status'];
                    } catch (PDOException $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'toggle_availability':
                if (isset($_POST['item_id'], $_POST['status'])) {
                    try {
                        $status = $_POST['status'] == '1' ? 1 : 0;
                        $stmt = $pdo->prepare("UPDATE menu_items SET is_available = ? WHERE id = ?");
                        $stmt->execute([$status, $_POST['item_id']]);
                        $message = "Item visibility updated.";
                    } catch (PDOException $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'create_menu_item':
                if (isset($_POST['name'], $_POST['category'], $_POST['price'])) {
                    try {
                        // Handle Variants
                        $variantsData = [];
                        if (isset($_POST['variant_name']) && is_array($_POST['variant_name'])) {
                            foreach ($_POST['variant_name'] as $idx => $vname) {
                                if (!empty(trim($vname))) {
                                    $variantsData[] = ['name' => trim($vname), 'price' => (float) ($_POST['variant_price'][$idx] ?? 0)];
                                }
                            }
                        }
                        $variants = !empty($variantsData) ? json_encode($variantsData) : null;

                        // Image Upload
                        $image_name = null;
                        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === 0) {
                            $ext = strtolower(pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION));
                            $image_name = time() . '_' . uniqid() . '.' . $ext;
                            move_uploaded_file($_FILES['item_image']['tmp_name'], 'uploads/menu/' . $image_name);
                        }

                        $stmt = $pdo->prepare("INSERT INTO menu_items (category, name, description, price, cost_price, has_protein, variants, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$_POST['category'], $_POST['name'], $_POST['description'], $_POST['price'], $_POST['cost_price'] ?? 0, isset($_POST['has_protein']) ? 1 : 0, $variants, $image_name]);
                        $message = "Menu item created!";
                    } catch (Exception $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'update_menu_item':
                if (isset($_POST['item_id'], $_POST['name'], $_POST['category'], $_POST['price'])) {
                    try {
                        $variantsData = [];
                        if (isset($_POST['variant_name']) && is_array($_POST['variant_name'])) {
                            foreach ($_POST['variant_name'] as $idx => $vname) {
                                if (!empty(trim($vname))) {
                                    $variantsData[] = [
                                        'name' => trim($vname),
                                        'price' => (float) ($_POST['variant_price'][$idx] ?? 0)
                                    ];
                                }
                            }
                        }
                        $variants = !empty($variantsData) ? json_encode($variantsData) : null;

                        $imageSql = "";
                        $params = [
                            $_POST['category'],
                            $_POST['name'],
                            $_POST['description'],
                            $_POST['price'],
                            $_POST['cost_price'] ?? 0,
                            isset($_POST['has_protein']) ? 1 : 0,
                            $variants
                        ];

                        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === 0) {
                            $ext = strtolower(pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION));
                            $image_name = time() . '_' . uniqid() . '.' . $ext;
                            move_uploaded_file($_FILES['item_image']['tmp_name'], 'uploads/menu/' . $image_name);
                            $imageSql = ", image = ?";
                            $params[] = $image_name;
                        }

                        $params[] = $_POST['item_id'];

                        $stmt = $pdo->prepare("
                            UPDATE menu_items
                            SET category = ?, name = ?, description = ?, price = ?, cost_price = ?, has_protein = ?, variants = ?
                            $imageSql
                            WHERE id = ?
                        ");
                        $stmt->execute($params);

                        $message = "Menu item updated!";
                    } catch (Exception $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'delete_menu_item':
                if (isset($_POST['item_id'])) {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
                        $stmt->execute([$_POST['item_id']]);
                        $message = "Item deleted!";
                    } catch (PDOException $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'add_stock':
                if (isset($_POST['item_name'], $_POST['quantity'])) {
                    try {
                        $qty = (int) $_POST['quantity'];
                        $status = $qty == 0 ? 'Out of Stock' : ($qty < 10 ? 'Low Stock' : 'In Stock');
                        $stmt = $pdo->prepare("INSERT INTO inventory (item_name, quantity, unit, status) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$_POST['item_name'], $qty, $_POST['unit'] ?? 'units', $status]);
                        $message = "Stock added!";
                    } catch (PDOException $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'toggle_store':
                $newStatus = isset($_POST['status']) ? 'open' : 'closed';
                $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('global_store_status', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$newStatus, $newStatus]);
                $message = "Store is now " . strtoupper($newStatus);
                break;

            case 'update_branch':
                // Guna var_dump untuk tengok apa yang form tu hantar sebenarnya
                // Kalau lepas tekan update page jadi putih & keluar data, baru kita tahu apa masalah
                // var_dump($_POST); die(); 

                $b_id = $_POST['branch_id'] ?? null;
                $b_name = $_POST['name'] ?? null;
                $b_phone = $_POST['phone'] ?? null;

                if ($b_id && $b_name && $b_phone) {
                    try {
                        $stmt = $pdo->prepare("UPDATE branches SET name = ?, phone = ? WHERE id = ?");
                        $stmt->execute([$b_name, $b_phone, $b_id]);

                        // Check kalau ada baris yang berubah
                        if ($stmt->rowCount() > 0) {
                            $_SESSION['admin_msg'] = "Branch $b_name updated!";
                        } else {
                            $_SESSION['admin_msg'] = "No changes made or ID $b_id not found.";
                        }
                    } catch (PDOException $e) {
                        $_SESSION['admin_msg'] = "DB Error: " . $e->getMessage();
                    }
                } else {
                    $_SESSION['admin_msg'] = "Data missing! ID: $b_id, Name: $b_name, Phone: $b_phone";
                }

                header("Location: admin.php?view=branches&status=success");
                exit;

            // (Case lain seperti reply_review dsb boleh dikekalkan dengan format yang sama)
        }

        // --- GLOBAL REDIRECT (Satu tempat je) ---
        $action = $_POST['action'] ?? '';
        $view = $_POST['view'] ?? '';

        // Auto-detect view if not explicitly sent by form to ensure persistence
        if (empty($view)) {
            if (in_array($action, ['delete_order', 'bulk_update_order_status', 'update_order_status', 'mark_as_paid']))
                $view = 'orders';
            elseif (in_array($action, ['create_menu_item', 'update_menu_item', 'delete_menu_item', 'toggle_availability']))
                $view = 'menu';
            elseif (in_array($action, ['create_user', 'delete_user', 'update_user_role', 'reset_user_password']))
                $view = 'staff';
            elseif (in_array($action, ['add_stock', 'update_stock', 'delete_stock']))
                $view = 'inventory';
        }

        $redirectUrl = "admin.php";
        $params = [];
        if ($view)
            $params[] = "view=" . urlencode($view);
        if ($message)
            $params[] = "message=" . urlencode($message);

        // Add status=success for all successful operations
        if ($message && strpos(strtolower($message), 'error') === false) {
            $params[] = "status=success";
        }

        if (!empty($params)) {
            $redirectUrl .= "?" . implode('&', $params);
        }

        header("Location: " . $redirectUrl);
        exit;
    }
}

// Fetch statistics
$totalUsers = $adminUsers = $regularUsers = $totalOrders = $totalRevenue = $pendingOrdersCount = $totalSalesToday = $activeOrdersCount = $newReviewsCount = 0;
$allUsers = $recentOrders = $inventoryItems = $ordersItemsMap = [];
$allUsers = $recentOrders = $inventoryItems = $ordersItemsMap = $branchesList = [];
$chartLabels = [];
$chartValues = [];
$reviews = [];
$bestSellers = [];
$salesByCategory = [];
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmt->fetchColumn();
    // Admin users count
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $adminUsers = $stmt->fetchColumn();
    // Regular users count
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $regularUsers = $stmt->fetchColumn();
    // Get staff for management from staff table
    $stmt = $pdo->query("SELECT id, name, email, phone, role, branch, created_at FROM staff ORDER BY created_at DESC");
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Fetch order statistics from the database
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $totalOrders = $stmt->fetchColumn();
    // Calculate total revenue (assuming revenue is from completed/served orders)
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status IN ('Served', 'Completed')");
    $totalRevenue = $stmt->fetchColumn() ?? 0;

    // Stats for new cards
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = CURDATE() AND status IN ('Served', 'Completed')");
    $totalSalesToday = $stmt->fetchColumn() ?? 0;

    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('Pending', 'Preparing', 'Ready')");
    $activeOrdersCount = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE rating IS NOT NULL AND admin_reply IS NULL");
    $newReviewsCount = $stmt->fetchColumn();

    // --- REPORT FILTER LOGIC ---
    $reportStart = $_GET['report_start'] ?? date('Y-m-01');
    $reportEnd = $_GET['report_end'] ?? date('Y-m-d');
    $reportStartSql = $reportStart . ' 00:00:00';
    $reportEndSql = $reportEnd . ' 23:59:59';

    // 1. Sales by Category (Filtered)
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
    $stmt->execute([$reportStartSql, $reportEndSql]);
    $salesByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Top 10 Products
    $stmt = $pdo->prepare("
        SELECT item_name, SUM(qty) as total_qty, SUM(qty * price) as total_revenue
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status IN ('Served', 'Completed')
        AND o.created_at BETWEEN ? AND ?
        GROUP BY item_name
        ORDER BY total_qty DESC
        LIMIT 10
    ");
    $stmt->execute([$reportStartSql, $reportEndSql]);
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Sales by Payment Method
    $stmt = $pdo->prepare("
        SELECT payment_method, COUNT(*) as count, SUM(total_amount) as total
        FROM orders
        WHERE status IN ('Served', 'Completed')
        AND created_at BETWEEN ? AND ?
        GROUP BY payment_method
    ");
    $stmt->execute([$reportStartSql, $reportEndSql]);
    $salesByPayment = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Daily Trend (Revenue & Orders)
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders
        FROM orders
        WHERE status IN ('Served', 'Completed')
        AND created_at BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$reportStartSql, $reportEndSql]);
    $dailyTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Highlights Calculation
    $highestSalesDay = ['date' => '-', 'amount' => 0];
    $lowestSalesDay = ['date' => '-', 'amount' => 0];
    $bestProduct = $topProducts[0] ?? ['item_name' => '-', 'total_qty' => 0];
    $bestCategory = $salesByCategory[0] ?? ['category' => '-', 'category_sales' => 0];

    if (!empty($dailyTrend)) {
        $revs = array_column($dailyTrend, 'revenue');
        $maxRev = max($revs);
        $minRev = min($revs);
        foreach ($dailyTrend as $d) {
            if ($d['revenue'] == $maxRev)
                $highestSalesDay = ['date' => date('d M', strtotime($d['date'])), 'amount' => $d['revenue']];
            if ($d['revenue'] == $minRev)
                $lowestSalesDay = ['date' => date('d M', strtotime($d['date'])), 'amount' => $d['revenue']];
        }
    }

    // 6. Cancelled Rate
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE created_at BETWEEN ? AND ?");
    $stmt->execute([$reportStartSql, $reportEndSql]);
    $totalRangeOrders = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = 'Cancelled' AND created_at BETWEEN ? AND ?");
    $stmt->execute([$reportStartSql, $reportEndSql]);
    $cancelledOrders = $stmt->fetchColumn();

    $cancelledRate = $totalRangeOrders > 0 ? round(($cancelledOrders / $totalRangeOrders) * 100, 1) : 0;

    // Prepare Chart Data Arrays
    $trendLabels = [];
    $trendRevenue = [];
    $trendOrders = [];
    foreach ($dailyTrend as $d) {
        $trendLabels[] = date('d M', strtotime($d['date']));
        $trendRevenue[] = $d['revenue'];
        $trendOrders[] = $d['orders'];
    }
    $pieLabels = array_column($salesByCategory, 'category');
    $pieData = array_column($salesByCategory, 'category_sales');
    $payLabels = array_column($salesByPayment, 'payment_method');
    $payData = array_column($salesByPayment, 'total');

    // Recent orders (Increased limit and added fields for better management)
    $stmt = $pdo->query("SELECT id, customer_name, customer_phone, branch, order_type, payment_method, payment_status, total_amount AS total, status, created_at FROM orders ORDER BY created_at DESC LIMIT 50");
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch items for these orders to display in modal
    $orderIds = array_column($recentOrders, 'id');
    if (!empty($orderIds)) {
        $inQuery = implode(',', array_fill(0, count($orderIds), '?'));
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($inQuery)");
        $stmt->execute($orderIds);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ordersItemsMap[$row['order_id']][] = $row;
        }
    }

    // Pending Orders Count (For Badge)
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'");
    $pendingOrdersCount = $stmt->fetchColumn();

    // Fetch Menu Items for Admin List
    $stmt = $pdo->query("SELECT * FROM menu_items ORDER BY category, name");
    $adminMenuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Inventory
    $stmt = $pdo->query("SELECT * FROM inventory ORDER BY item_name");
    $inventoryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Sales Chart Data (Last 7 Days)
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, SUM(total_amount) as daily_total 
        FROM orders 
        WHERE status IN ('Served', 'Completed') AND created_at >= DATE(NOW()) - INTERVAL 7 DAY 
        GROUP BY DATE(created_at) 
        ORDER BY date ASC
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $chartLabels[] = date('d M', strtotime($row['date']));
        $chartValues[] = (float) $row['daily_total'];
    }

    // Fetch Reviews for Admin
    $reviews = [];
    $totalRatings = 0;
    $averageRating = 0;
    $ratingDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

    try {
        $stmt = $pdo->query("SELECT id, customer_name, rating, review, reaction, review_image, admin_reply, review_is_approved, created_at FROM orders WHERE rating IS NOT NULL ORDER BY created_at DESC");
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch items for reviewed orders
        $reviewOrderIds = array_column($reviews, 'id');
        $reviewItemsMap = [];
        if (!empty($reviewOrderIds)) {
            $inQuery = implode(',', array_fill(0, count($reviewOrderIds), '?'));
            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($inQuery)");
            $stmt->execute($reviewOrderIds);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $reviewItemsMap[$row['order_id']][] = $row;
            }
        }

        // Fetch Best Sellers
        $stmt = $pdo->query("SELECT item_name FROM order_items GROUP BY item_name ORDER BY SUM(qty) DESC LIMIT 3");
        $bestSellers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Calculate review stats
        $totalRatings = count($reviews);
        if ($totalRatings > 0) {
            $sumOfRatings = array_sum(array_column($reviews, 'rating'));
            $averageRating = $sumOfRatings / $totalRatings;
            foreach ($reviews as $r) {
                if (isset($ratingDistribution[(int) $r['rating']])) {
                    $ratingDistribution[(int) $r['rating']]++;
                }
            }
        }
    } catch (Exception $e) {
    }

    // Fetch Activity Logs
    $stmt = $pdo->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 50");
    $activityLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $newLogsCount = count($activityLogs);


    // Fetch Global Store Status
    $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'global_store_status'");
    $storeStatus = trim(strtolower($stmt->fetchColumn() ?: 'open'));

    // Fetch last login for current user
    $currentUserLastLogin = 'Never';
    if ($currentUserId) {
        $stmt = $pdo->prepare("SELECT last_login FROM users WHERE id = ?");
        $stmt->execute([$currentUserId]);
        $lastLoginTimestamp = $stmt->fetchColumn();
        if ($lastLoginTimestamp) {
            $currentUserLastLogin = time_since(strtotime($lastLoginTimestamp));
        }
    }
} catch (PDOException $e) {
    $message = "Error fetching statistics: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bambam Burger</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        (function () {
            const root = document.documentElement;

            const ordersSeen = localStorage.getItem('ordersBadgeSeen');
            if (ordersSeen === 'true') {
                root.classList.add('orders-badge-hidden');
            } else {
                root.classList.remove('orders-badge-hidden');
            }

            const reviewsSeen = localStorage.getItem('reviewsBadgeSeen');
            if (reviewsSeen === 'true') {
                root.classList.add('reviews-badge-hidden');
            } else {
                root.classList.remove('reviews-badge-hidden');
            }

            const logsSeen = localStorage.getItem('logsBadgeSeen');
            if (logsSeen === 'true') {
                root.classList.add('logs-badge-hidden');
            } else {
                root.classList.remove('logs-badge-hidden');
            }
        })();
    </script>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #121212;
            /* Black */
            color: #ddd;
            /* Light grey text */
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .orders-badge-hidden #ordersBadge {
            display: none !important;
        }

        .reviews-badge-hidden #reviewsBadge {
            display: none !important;
        }

        .logs-badge-hidden #logsBadge {
            display: none !important;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: #1a1a1a;
            /* Dark Grey */
            border-right: 1px solid #333;
            display: flex;
            flex-direction: column;
            padding: 20px;
            flex-shrink: 0;
            position: fixed;
            /* Keeps it on the left while page scrolls */
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            /* THIS ENABLES THE SCROLLBAR */
            overflow-x: hidden;
            padding-bottom: 20px;
            /* Space at the bottom so Logout isn't cramped */
        }

        /* Sidebar Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 10px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            background: transparent;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Center the logo */
            gap: 10px;
        }

        .nav-item {
            border-left: 3px solid transparent;
            padding: 15px;
            color: #a0aec0;
            /* Muted light text */
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: 0.3s;
            font-weight: 500;
            cursor: pointer;
        }

        .nav-item:hover {
            background: #333;
            color: #ffffff;
        }

        .nav-item.active {
            background: #ff5100;
            /* Orange */
            color: #ffffff;
            border-left-color: transparent;
            font-weight: 700;
            box-shadow: 0 5px 15px rgba(255, 81, 0, 0.2);
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        .badge {
            background: #ffffff;
            color: #ff5100;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            margin-left: auto;
        }

        .nav-item.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* CONTENT WRAPPER */
        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            margin-left: 260px;
            /* Offset for the fixed sidebar */
        }

        /* MAIN CONTENT (Scrollable Area) */
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            /* Change from 'scroll' to 'auto' for better behavior */
            background: #121212;
            /* Black */
            scroll-behavior: smooth;
            height: 100%;
            /* Ensure it takes full height */
        }

        /* Custom Scrollbar for modern look and feel */
        .main-content::-webkit-scrollbar {
            width: 10px;
        }

        .main-content::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .main-content::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .main-content::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* HEADER */
        .top-header {
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            /* Add gap */
            background: rgba(26, 26, 26, 0.95);
            /* Slightly darker shade */
            backdrop-filter: blur(10px);
            /* Subtle glass effect */
            border-bottom: 1px solid rgba(255, 122, 0, 0.2);
            /* Faint orange border */
            flex-shrink: 0;
        }

        .header-title-section {
            flex-shrink: 0;
        }

        .header-title-section h2 {
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 0 2px 10px rgba(255, 81, 0, 0.2);
            /* Subtle orange glow */
        }

        .header-title-section h2::after {
            content: '';
            width: 6px;
            height: 6px;
            background: #ff5100;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px #ff5100;
        }

        .breadcrumbs {
            font-size: 12px;
            color: #718096;
            margin-top: 4px;
        }

        .breadcrumbs a {
            color: #aaa;
            text-decoration: none;
        }

        .breadcrumbs a:hover {
            color: #ff5100;
        }

        .header-search {
            flex-grow: 1;
            max-width: 450px;
            position: relative;
        }

        .header-search input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: #2b2744;
            font-size: 14px;
            color: #ffffff;
        }

        .header-search .fa-search {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-left: auto;
        }

        .icon-btn {
            background: #2a2a2a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
        }

        .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid #1e1e1e;
        }

        .profile-section {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #121212;
            padding: 5px 12px 5px 5px;
            border-radius: 50px;
            border: 1px solid transparent;
        }

        .profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            background: #333;
        }

        .profile-name {
            font-weight: 600;
            color: #ffffff;
        }

        .profile-role {
            font-size: 12px;
            color: #aaa;
            line-height: 1;
            margin-top: 2px;
        }

        /* STATS */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .premium-card {
            background: #1e1e1e;
            /* Dark Grey */
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid #333;
        }

        .stat-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            min-height: 200px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 100%;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            /* Circular icons */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }

        .icon-green {
            background: #10b981;
        }

        .icon-blue {
            background: #ff5100;
        }

        .icon-orange {
            background: #f97316;
        }

        .icon-purple {
            background: #ff5100;
        }

        .card-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-label {
            margin: 0;
            color: #aaa;
            font-size: 14px;
            font-weight: 500;
        }

        .stat-value {
            margin: 5px 0;
            font-size: 32px;
            color: #ffffff;
            font-weight: 700;
        }

        .stat-trend {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
        }

        .stat-trend.positive {
            color: #48bb78;
        }

        .stat-trend.negative {
            color: #f56565;
        }

        .sparkline-container {
            width: 100%;
            height: 50px;
            margin-top: 10px;
        }

        /* SECTIONS */
        .view-section {
            display: none;
        }

        .view-section.active {
            display: block;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes toastIn {
            from {
                top: 0;
                opacity: 0;
            }

            to {
                top: 30px;
                opacity: 1;
            }
        }

        @keyframes toastOut {
            from {
                top: 30px;
                opacity: 1;
            }

            to {
                top: 0;
                opacity: 0;
            }
        }

        .panel-card {
            background: #1e1e1e;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid #333;
            margin-bottom: 20px;
        }

        /* TABLES & FORMS */
        .admin-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .admin-table th {
            text-align: left;
            padding: 16px 20px;
            color: #ff5100;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #ff5100;
            background: #2a2a2a;
        }

        .admin-table td {
            padding: 22px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #333;
            transition: background 0.2s;
        }

        .admin-table tr:hover td {
            background-color: #2a2a2a;
        }

        .admin-table tr:last-child td {
            border-bottom: none;
        }

        .btn-primary {
            background: #ff5100;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        /* Ghost/Outline Delete Button */
        .btn-danger {
            background: transparent;
            border: 1px solid #ef4444;
            color: #ef4444;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-danger:hover {
            background: #fee2e2;
            color: #b91c1c;
        }

        .message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 25px;
            border-radius: 50px;
            margin: 0;
            max-width: 500px;
            text-align: center;
            z-index: 9999;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s ease;
        }

        .message.show {
            top: 30px;
            opacity: 1;
            visibility: visible;
        }

        .message.success {
            background: #27211c;
            color: #f3a37b;
            border: 1px solid #5c3821;
        }

        .message.error {
            background: #4a1d1d;
            color: #fecaca;
            border: 1px solid #7f1d1d;
        }

        /* STATUS BADGES */
        .btn-ghost {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #cbd5e0;
            border-radius: 10px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .btn-ghost.delete:hover {
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.35);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            letter-spacing: 0.5px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .status-badge::before {
            content: '•';
            font-size: 16px;
            line-height: 0;
            margin-bottom: 2px;
        }

        .status-pending {
            background: rgba(234, 179, 8, 0.1);
            color: #facc15;
            border: 1px solid rgba(234, 179, 8, 0.3);
        }

        .status-preparing {
            background: rgba(251, 146, 60, 0.1);
            color: #fb923c;
            border: 1px solid rgba(251, 146, 60, 0.3);
        }

        .status-ready {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .status-completed,
        .status-served {
            background: #374151;
            color: #9ca3af;
            border: 1px solid #4b5563;
        }


        /* TYPOGRAPHY & ELEMENTS */
        .order-id-badge {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #ddd;
            background: #374151;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 13px;
            letter-spacing: -0.5px;
        }

        .customer-name {
            font-weight: 700;
            color: #ffffff;
            font-size: 14px;
            display: block;
            margin-bottom: 3px;
        }

        .customer-meta {
            font-size: 12px;
            color: #a0aec0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .chart-filter-btn {
            background: #373359;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #a0aec0;
            padding: 5px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            margin-right: 5px;
            transition: 0.2s;
        }

        .chart-filter-btn:hover {
            background: #4a456e;
        }

        .chart-filter-btn.active {
            background: #ff5100;
            color: white;
            border-color: #ff5100;
        }

        .branch-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .branch-card {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #333;
        }

        .branch-card-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 15px;
        }

        .btn-success {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: background 0.2s;
        }

        .btn-success:hover {
            background: #218838;
        }

        .branch-card-actions .btn-primary {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
        }

        /* KITCHEN VIEW */
        .kitchen-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .kitchen-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            border-left: 5px solid #ccc;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .kitchen-card.status-pending {
            border-left-color: #f97316;
        }

        .kitchen-card.status-preparing {
            border-left-color: #3498db;
        }

        .kitchen-card h4 {
            margin: 0 0 10px 0;
            display: flex;
            justify-content: space-between;
        }

        .kitchen-items {
            margin: 10px 0;
            font-size: 14px;
            line-height: 1.4;
        }

        .kitchen-btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            color: white;
        }

        /* SEARCH BAR */
        .search-wrapper {
            position: relative;
        }

        .search-wrapper .fa-search {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .search-bar {
            width: 100%;
            padding: 12px 15px 12px 40px;
            /* Add left padding for icon */
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-bar:focus {
            outline: none;
            border-color: #ff5100;
            box-shadow: 0 0 0 3px rgba(255, 81, 0, 0.2);
        }

        /* TOGGLE SWITCH */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #333;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #ff5100;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        /* EDIT MODAL */
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .modal-content {
            background-color: #2b2744;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 80%;
            max-width: 600px;
            border-radius: 15px;
        }

        .close-btn {
            color: #a0aec0;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-content form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .modal-content h3 {
            margin-top: 0;
        }
    </style>
    <style>
        /* Form labels for clarity */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-weight: 600;
            font-size: 13px;
            color: #a0aec0;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            /* More padding for aesthetic feel */
            border: 1px solid #333;
            border-radius: 8px;
            /* Softer radius */
            background: #2a2a2a;
            color: #ffffff;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff5100;
            background: #333;
            box-shadow: 0 0 0 3px rgba(255, 81, 0, 0.2);
        }

        /* Professional Role Badges */
        .role-badge {
            padding: 6px 14px;
            border-radius: 50px;
            /* Full round pill shape */
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .role-admin {
            background-color: rgba(255, 81, 0, 0.2);
            color: #ff9c70;
        }

        /* Soft Orange */
        .role-staff {
            background-color: rgba(251, 146, 60, 0.2);
            color: #fb923c;
        }

        /* Lighter Orange */
        .role-user {
            background: #374151;
            color: #9ca3af;
        }

        /* Neutral Action Button */
        .btn-neutral {
            background: #374151;
            color: #cbd5e0;
            padding: 5px 10px;
            font-size: 12px;
            border: 1px solid #4a5568;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-neutral:hover {
            background: #4a5568;
            color: #ffffff;
        }

        .action-btn {
            min-width: 95px;
            text-align: center;
        }

        .btn-refresh {
            background: #2a2a2a;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-refresh:hover {
            background: #3a3a3a;
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Logout Button in Header */
        .btn-logout {
            background: #ff5100;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s ease;
        }

        .btn-logout:hover {
            background: #e04600;
        }
    </style>
    <style>
        /* New Styles for Enhanced Orders View */
        .filter-pills {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .filter-pill {
            padding: 8px 16px;
            border-radius: 20px;
            background: #333;
            border: 1px solid #444;
            color: #aaa;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s;
        }

        .filter-pill:hover {
            background: #444;
        }

        .filter-pill.active {
            background: #ff5100;
            color: white;
            border-color: #ff5100;
        }

        /* Urgency & Stale Animations */
        @keyframes pulse-border {
            0% {
                border-left-color: #ff5100;
            }

            50% {
                border-left-color: #ff9466;
            }

            100% {
                border-left-color: #ff5100;
            }
        }

        .urgency-stale {
            border-left: 4px solid #e74c3c !important;
            background: rgba(231, 76, 60, 0.05);
            animation: pulse-border 1.5s infinite;
        }

        .urgency-high {
            border-left: 4px solid #e74c3c !important;
            background: rgba(231, 76, 60, 0.05);
        }

        .urgency-medium {
            border-left: 4px solid #f1c40f !important;
            background: rgba(241, 196, 15, 0.05);
        }

        .payment-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            margin-left: 6px;
            vertical-align: middle;
        }

        .pay-paid {
            background: #166534;
            color: #dcfce7;
        }

        .pay-unpaid {
            background: #4b5563;
            color: #f3f4f6;
        }

        /* Payment Badges from user request */
        .payment-badge {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 8px;
            vertical-align: middle;
        }

        .pay-paid {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
        }

        /* Green */
        .pay-unpaid {
            background: rgba(234, 179, 8, 0.15);
            color: #facc15;
        }

        /* Yellow */

        .bulk-actions {
            display: none;
            align-items: center;
            gap: 10px;
            background: rgba(255, 81, 0, 0.1);
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 81, 0, 0.3);
        }

        .bulk-actions.active {
            display: flex;
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #a0aec0;
        }

        .empty-icon {
            font-size: 40px;
            margin-bottom: 10px;
            opacity: 0.4;
            display: block;
        }

        .empty-state h3 {
            color: #ffffff;
            margin: 0 0 8px 0;
            font-size: 18px;
        }

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        /* Polished Elements */
        .text-right {
            text-align: right;
        }

        .time-elapsed {
            font-size: 11px;
            margin-top: 3px;
        }

        .time-elapsed.urgent {
            color: #ef4444;
            font-weight: 600;
        }

        .time-elapsed.normal {
            color: #a0aec0;
        }

        /* NEW MENU GRID STYLES */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .menu-card {
            background: #2b2744;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            position: relative;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .menu-card-img {
            height: 160px;
            background-color: #1d1a2f;
            position: relative;
            overflow: hidden;
        }

        .menu-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .menu-card-img .category-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            backdrop-filter: blur(5px);
        }

        .bestseller-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #e74c3c;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .menu-card-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .menu-card-content h4 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #ffffff;
        }

        .menu-card-content .description {
            font-size: 13px;
            color: #a0aec0;
            line-height: 1.5;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .price-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 15px;
        }

        .price-info .selling-price {
            font-size: 24px;
            font-weight: 800;
            color: #ff5100;
        }

        .price-info .cost-info {
            font-size: 12px;
            color: #a0aec0;
            text-align: right;
        }

        .cost-info .profit-margin {
            font-weight: bold;
            color: #16a34a;
        }

        .menu-card-actions {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1d1a2f;
        }

        .availability-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #a0aec0;
        }

        .availability-toggle .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            transition: background 0.3s;
        }

        .availability-toggle .dot.available {
            background: #22c55e;
        }

        .availability-toggle .dot.unavailable {
            background: #ef4444;
        }

        .menu-card-buttons .btn-ghost {
            width: 36px;
            height: 36px;
        }

        .menu-card-buttons .btn-ghost.delete:hover {
            color: #ef4444;
            background: #fee2e2;
        }

        .menu-card-actions .switch {
            width: 40px;
            height: 20px;
        }

        .menu-card-actions .slider {
            border-radius: 20px;
            background-color: #4b5563;
        }

        .menu-card-actions .slider:before {
            height: 14px;
            width: 14px;
            left: 3px;
            bottom: 3px;
        }

        .menu-card-actions input:checked+.slider:before {
            transform: translateX(20px);
        }

        .menu-card.sold-out {
            opacity: 0.6;
        }

        .menu-card.sold-out .menu-card-img {
            filter: grayscale(80%);
        }
    </style>
    <style>
        /* NEW REVIEW PAGE STYLES */
        .review-overview-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .avg-rating-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .avg-rating-score {
            font-size: 48px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1;
        }

        .avg-rating-stars {
            font-size: 24px;
            color: #ffc107;
            margin: 5px 0;
        }

        .avg-rating-total {
            font-size: 14px;
            color: #a0aec0;
        }

        .rating-dist-chart {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .dist-bar-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dist-bar-label {
            font-size: 12px;
            font-weight: 600;
            color: #a0aec0;
            width: 50px;
        }

        .dist-bar-bg {
            flex-grow: 1;
            background: #374151;
            border-radius: 5px;
            height: 10px;
            overflow: hidden;
        }

        .dist-bar-fill {
            height: 100%;
            background: #ff5100;
            border-radius: 5px;
            transition: width 0.5s;
        }

        .dist-bar-count {
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
            width: 30px;
            text-align: right;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .review-card {
            background: #2b2744;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 20px;
            padding: 20px;
            transition: all 0.2s;
        }

        .review-card.hidden {
            opacity: 0.5;
            background: #25223c;
        }

        .review-card-meta {
            width: 200px;
            flex-shrink: 0;
        }

        .review-card-content {
            flex-grow: 1;
        }

        .review-card-actions {
            width: 220px;
            flex-shrink: 0;
        }

        .review-customer-name {
            font-weight: 700;
            font-size: 15px;
            color: #ffffff;
        }

        .review-date {
            font-size: 12px;
            color: #a0aec0;
            margin-top: 2px;
        }

        .sentiment-badge {
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .sentiment-positive {
            background: #166534;
            color: #dcfce7;
        }

        .sentiment-neutral {
            background: #4b5563;
            color: #f3f4f6;
        }

        .sentiment-critical {
            background: #991b1b;
            color: #fee2e2;
        }

        .review-stars {
            color: #ffc107;
            font-size: 16px;
            margin: 10px 0;
        }

        .review-text {
            font-size: 14px;
            color: #e2e8f0;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .review-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 10px;
            cursor: pointer;
        }

        .review-items-list {
            font-size: 12px;
            color: #a0aec0;
            margin-top: 15px;
            border-top: 1px dashed #4a5568;
            padding-top: 10px;
        }

        .reply-form textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #4a5568;
            border-radius: 6px;
            font-size: 13px;
            resize: vertical;
            min-height: 60px;
            background: #1d1a2f;
            color: #e2e8f0;
        }

        .reply-form button {
            width: 100%;
            margin-top: 8px;
            padding: 8px;
            font-size: 13px;
        }

        .admin-reply-display {
            background: #1d1a2f;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            color: #a0aec0;
            border-left: 3px solid #718096;
        }

        .visibility-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #4a5568;
        }

        .visibility-control span {
            font-size: 12px;
            font-weight: 600;
            color: #e2e8f0;
        }

        .visibility-control .switch {
            margin-left: auto;
        }

        /* Image Modal */
        #image-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            justify-content: center;
            align-items: center;
        }

        #image-modal img {
            max-width: 90%;
            max-height: 90%;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.5);
        }

        #image-modal.active {
            display: flex;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo"><i class="fas fa-hamburger"></i> BamBam Admin</div>

        <div class="nav-item <?php echo $current_view === 'dashboard' ? 'active' : ''; ?>" data-view="dashboard"
            onclick="switchView('dashboard', this)">
            <i class="fas fa-th-large"></i> Dashboard
        </div>

        <div class="nav-item <?php echo $current_view === 'orders' ? 'active' : ''; ?>" data-view="orders"
            onclick="switchView('orders', this)">
            <i class="fas fa-receipt"></i> Orders
            <?php if ($pendingOrdersCount > 0): ?>
                <span class="badge" id="ordersBadge"><?php echo $pendingOrdersCount; ?></span>
            <?php endif; ?>
        </div>

        <?php if ($canManageStaff): ?>
            <div class="nav-item <?php echo $current_view === 'staff' ? 'active' : ''; ?>" data-view="staff"
                onclick="switchView('staff', this)">
                <i class="fas fa-users"></i> Staff
            </div>
        <?php endif; ?>

        <div class="nav-item <?php echo $current_view === 'menu' ? 'active' : ''; ?>" data-view="menu"
            onclick="switchView('menu', this)">
            <i class="fas fa-utensils"></i> Menu
        </div>

        <div class="nav-item <?php echo $current_view === 'reviews' ? 'active' : ''; ?>" data-view="reviews"
            onclick="switchView('reviews', this)">
            <i class="fas fa-star"></i> Reviews
            <?php if ($newReviewsCount > 0): ?>
                <span class="badge" id="reviewsBadge"><?php echo $newReviewsCount; ?></span>
            <?php endif; ?>
        </div>

        <?php if ($canViewReports): ?>
            <div class="nav-item <?php echo $current_view === 'inventory' ? 'active' : ''; ?>" data-view="inventory"
                onclick="switchView('inventory', this)">
                <i class="fas fa-boxes"></i> Inventory
            </div>
        <?php endif; ?>

        <?php if ($canViewReports): ?>
            <div class="nav-item <?php echo $current_view === 'reports' ? 'active' : ''; ?>" data-view="reports"
                onclick="switchView('reports', this)">
                <i class="fas fa-chart-line"></i> Reports
            </div>
        <?php endif; ?>

        <div class="nav-item <?php echo $current_view === 'branches' ? 'active' : ''; ?>" data-view="branches"
            onclick="switchView('branches', this)">
            <i class="fas fa-store"></i> Branches
        </div>

        <div class="nav-item <?php echo $current_view === 'settings' ? 'active' : ''; ?>" data-view="settings"
            onclick="switchView('settings', this)">
            <i class="fas fa-cog"></i> Settings
        </div>

        <div class="nav-item <?php echo $current_view === 'logs' ? 'active' : ''; ?>" data-view="logs"
            onclick="switchView('logs', this)">
            <i class="fas fa-user-shield"></i> Activity Logs
            <?php if ($newLogsCount > 0): ?>
                <span class="badge" id="logsBadge"><?php echo $newLogsCount; ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- CONTENT WRAPPER -->
    <div class="content-wrapper">
        <!-- TOP HEADER -->
        <header class="top-header">
            <div class="header-title-section">
                <h2 style="margin:0; font-weight: 700; color: #ffffff;"><?php echo $page_title; ?></h2>
            </div>
            <div class="header-actions">
                <div class="profile-section" style="cursor: default;">
                    <img src="images/default-avatar.png" alt="Avatar" class="profile-avatar"
                        onerror="this.src='https://i.pravatar.cc/40'">
                    <div>
                        <div class="profile-name"><?php echo htmlspecialchars($currentUserName); ?></div>
                        <div class="profile-role"><?php echo ucfirst($currentUserRole); ?></div>
                    </div>
                </div>

                <button type="button" class="btn-refresh" onclick="refreshCurrentView()">
                    <i class="fas fa-rotate-right"></i> Refresh
                </button>

                <a href="admin_logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </header>

        <!-- MAIN CONTENT (Scrollable) -->
        <div class="main-content">
            <?php if (!empty(trim($message))): ?>
                <div id="toast-message"
                    class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- VIEW: DASHBOARD -->
            <div id="view-dashboard" class="view-section <?php echo $current_view === 'dashboard' ? 'active' : ''; ?>">
                <div class="dashboard-grid">
                    <!-- Stat Cards -->
                    <div class="premium-card stat-card" style="grid-column: span 3;"
                        onclick="switchView('reports', document.querySelector('[data-view=reports]'))">
                        <div class="card-header">
                            <div class="card-icon icon-green"><i class="fas fa-dollar-sign"></i></div>
                        </div>
                        <div class="card-body">
                            <p class="stat-label">Sales Today</p>
                            <p class="stat-value">RM <?php echo number_format($totalSalesToday, 2); ?></p>
                            <p class="stat-trend positive">+4% vs yesterday</p>
                        </div>
                        <div class="sparkline-container"><canvas id="sparkline-sales"></canvas></div>
                    </div>
                    <div class="premium-card stat-card" style="grid-column: span 3;"
                        onclick="switchView('orders', document.querySelector('[data-view=orders]'))">
                        <div class="card-header">
                            <div class="card-icon icon-blue"><i class="fas fa-receipt"></i></div>
                        </div>
                        <div class="card-body">
                            <p class="stat-label">Active Orders</p>
                            <p class="stat-value"><?php echo $activeOrdersCount; ?></p>
                            <p class="stat-trend positive">+12% vs last hour</p>
                        </div>
                        <div class="sparkline-container"><canvas id="sparkline-orders"></canvas></div>
                    </div>
                    <div class="premium-card stat-card" style="grid-column: span 3;"
                        onclick="switchView('reviews', document.querySelector('[data-view=reviews]'))">
                        <div class="card-header">
                            <div class="card-icon icon-orange"><i class="fas fa-star"></i></div>
                        </div>
                        <div class="card-body">
                            <p class="stat-label">New Reviews</p>
                            <p class="stat-value"><?php echo $newReviewsCount; ?></p>
                            <p class="stat-trend negative">-2% vs yesterday</p>
                        </div>
                        <div class="sparkline-container"><canvas id="sparkline-reviews"></canvas></div>
                    </div>
                    <div class="premium-card stat-card" style="grid-column: span 3;"
                        onclick="switchView('reports', document.querySelector('[data-view=reports]'))">
                        <div class="card-header">
                            <div class="card-icon icon-purple"><i class="fas fa-coins"></i></div>
                        </div>
                        <div class="card-body">
                            <p class="stat-label">Total Revenue</p>
                            <p class="stat-value">RM <?php echo number_format($totalRevenue, 2); ?></p>
                            <p class="stat-trend positive">+8% vs last month</p>
                        </div>
                        <div class="sparkline-container"><canvas id="sparkline-revenue"></canvas></div>
                    </div>

                    <!-- Main Chart -->
                    <div class="premium-card" style="grid-column: span 8;">
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h3 style="margin:0; color: #ffffff;">📈 Sales Analytics</h3>
                            <div>
                                <button onclick="updateChart('day', this)" class="chart-filter-btn">Today</button>
                                <button onclick="updateChart('week', this)" class="chart-filter-btn active">This
                                    Week</button>
                                <button onclick="updateChart('month', this)" class="chart-filter-btn">This
                                    Month</button>
                                <button onclick="updateChart('year', this)" class="chart-filter-btn">This Year</button>
                                <button onclick="toggleDateRangePicker(this)" class="chart-filter-btn"
                                    title="Select Date Range"><i class="fas fa-calendar-alt"></i></button>
                            </div>
                        </div>
                        <div id="custom-date-picker-container"
                            style="display: none; background: #2a2a2a; padding: 10px; border-radius: 8px; margin-top: 10px; max-width: fit-content; gap: 10px; align-items: center;">
                            <label for="chart-start-date" style="font-size:12px;">From:</label>
                            <input type="date" id="chart-start-date"
                                style="background:#333; border:1px solid #444; color:white; padding:5px; border-radius:4px; color-scheme: dark;">
                            <label for="chart-end-date" style="font-size:12px;">To:</label>
                            <input type="date" id="chart-end-date"
                                style="background:#333; border:1px solid #444; color:white; padding:5px; border-radius:4px; color-scheme: dark;">
                        </div>
                        <div style="height: 400px; width: 100%;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>

                    <!-- Side Card -->
                    <div class="premium-card" style="grid-column: span 4;">
                        <h3 style="margin:0 0 20px 0; color: #ffffff;">📊 Sales by Category</h3>
                        <div style="height: 400px; width: 100%; position: relative;">
                            <?php if (empty($pieData) || array_sum($pieData) == 0): ?>
                                <div class="empty-state" style="padding: 80px 0;">
                                    <div class="empty-icon" style="font-size: 50px;">🍩</div>
                                    <h3>No Sales Data</h3>
                                    <p>No sales in the selected period.</p>
                                </div>
                            <?php else: ?>
                                <canvas id="categoryDonutChart"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Daily Sales Visualization -->
                <div class="panel-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                        <h3 style="margin:0; color: #ffffff;">📅 Daily Sales Snapshot</h3>
                        <input type="date" id="sales-date-picker" value="<?php echo date('Y-m-d'); ?>"
                            style="padding:8px; border-radius:5px; border:1px solid #444; background:#2a2a2a; color:white; color-scheme:dark;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <!-- Left Column: Chart and Totals -->
                        <div>
                            <div style="height: 300px; width: 100%; position: relative; margin-bottom: 20px;">
                                <canvas id="dailySalesDonutChart"></canvas>
                                <div id="donut-no-data" class="empty-state"
                                    style="display:none; position:absolute; top:0; left:0; width:100%; height:100%; background: #1e1e1e; align-items:center; justify-content:center; flex-direction:column;">
                                    <div class="empty-icon" style="font-size: 50px;">🍩</div>
                                    <h3>No Sales Data</h3>
                                    <p>No sales recorded for this day.</p>
                                </div>
                            </div>
                            <div style="display:flex; gap: 20px; text-align:center;">
                                <div style="flex:1; background: #2a2a2a; padding:15px; border-radius:10px;">
                                    <p style="margin:0; font-size:12px; color:#aaa;">TOTAL SALES</p>
                                    <p id="daily-total-sales"
                                        style="margin:5px 0 0 0; font-size:24px; font-weight:bold; color:#ff5100;">RM
                                        0.00</p>
                                </div>
                                <div style="flex:1; background: #2a2a2a; padding:15px; border-radius:10px;">
                                    <p style="margin:0; font-size:12px; color:#aaa;">ITEMS SOLD</p>
                                    <p id="daily-total-quantity"
                                        style="margin:5px 0 0 0; font-size:24px; font-weight:bold; color:#ff5100;">0</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Hot Items -->
                        <div>
                            <h4 style="margin-top:0; color: #ffffff;">🔥 Hot Selling Items</h4>
                            <div id="hot-items-list">
                                <!-- JS will populate this -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIEW: STAFF -->
            <div id="view-staff" class="view-section <?php echo $current_view === 'staff' ? 'active' : ''; ?>">
                <div class="panel-card">
                    <h3 style="margin-top:0; color: #ffffff;">➕ Add New Admin/Staff</h3>
                    <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <input type="hidden" name="action" value="create_user">


                        <div class="form-group">
                            <label for="new-name">Full Name</label>
                            <input type="text" id="new-name" name="name" placeholder="e.g. John Doe" required>
                        </div>

                        <div class="form-group">
                            <label for="new-email">Email Address</label>
                            <input type="email" id="new-email" name="email" placeholder="e.g. john@example.com"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="new-phone">Phone Number</label>
                            <input type="tel" id="new-phone" name="phone" placeholder="0123456789" required
                                pattern="01[0-9]{8,9}" title="Enter a valid Malaysian phone number (e.g., 0123456789)">
                        </div>

                        <div class="form-group">
                            <label for="new-role">Role</label>
                            <select id="new-role" name="role" required>
                                <option value="kitchen">Kitchen</option>
                                <option value="cashier">Cashier</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="new-branch">Branch</label>
                            <select id="new-branch" name="branch" required>
                                <option value="">Select Branch</option>
                                <option value="Kangar">Kangar</option>
                                <option value="Jejawi">Jejawi</option>
                                <option value="Arau">Arau</option>
                                <option value="Kuala Perlis">Kuala Perlis</option>
                                <option value="Beseri">Beseri</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-primary" style="grid-column: span 2;">Create Account</button>
                    </form>
                </div>

                <div class="panel-card">
                    <h3 style="margin-top:0; color: #ffffff;">👥 Staff List</h3>
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchStaff" class="search-bar"
                            onkeyup="filterTable('searchStaff', 'staffTable')" placeholder="Search by name or email...">
                    </div>
                    <?php if (empty($allUsers)): ?>
                        <p>No staff found.</p>
                    <?php else: ?>
                        <table class="admin-table" id="staffTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Branch</th>
                                    <th>Role</th>
                                    <th>Created At</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allUsers as $user):
                                    $roleClass = 'role-' . strtolower($user['role']);
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($user['branch'] ?: '-'); ?></td>
                                        <td>
                                            <span class="role-badge <?php echo 'role-' . strtolower($user['role']); ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                        <td style="text-align:right;">
                                            <form method="POST" style="display: inline;"
                                                onsubmit="return confirm('Are you sure you want to delete this staff? This action cannot be undone.');">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn-danger action-btn">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- VIEW: ORDERS -->
            <div id="view-orders" class="view-section <?php echo $current_view === 'orders' ? 'active' : ''; ?>">
                <div class="panel-card">
                    <h3 style="margin-top:0; color: #ffffff;">📋 Recent Orders</h3>

                    <div class="filter-pills">
                        <button class="filter-pill active" onclick="filterOrders('all', this)">All</button>
                        <button class="filter-pill" onclick="filterOrders('Pending', this)">Pending</button>
                        <button class="filter-pill" onclick="filterOrders('Preparing', this)">Preparing</button>
                        <button class="filter-pill" onclick="filterOrders('Ready', this)">Ready</button>
                        <button class="filter-pill" onclick="filterOrders('Served', this)">Served</button>
                    </div>

                    <div id="bulk-actions-bar" class="bulk-actions">
                        <span style="font-weight:bold; color:#93c5fd;"><span id="selected-count">0</span>
                            Selected</span>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="view" value="orders">
                            <input type="hidden" name="action" value="bulk_update_order_status">
                            <input type="hidden" name="order_ids" id="bulk-order-ids">
                            <select name="new_status" style="padding:5px; border-radius:5px; border:1px solid #ccc;">
                                <option value="Preparing">Mark as Preparing</option>
                                <option value="Ready">Mark as Ready</option>
                                <option value="Served">Mark as Served</option>
                                <option value="Completed">Mark as Completed</option>
                            </select>
                            <button type="submit" class="btn-primary"
                                style="padding:5px 10px; font-size:12px;">Update</button>
                        </form>
                    </div>

                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchOrders" class="search-bar"
                            onkeyup="filterTable('searchOrders', 'ordersTable')"
                            placeholder="Search by Order ID, Customer, Branch, or Status...">
                    </div>

                    <?php if (empty($recentOrders)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <h3>No Active Orders</h3>
                            <p>Looks like things are quiet for now.</p>
                        </div>
                    <?php else: ?>
                        <table class="admin-table" id="ordersTable">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" onclick="toggleAllCheckboxes(this)">
                                    </th>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th style="min-width: 100px;">Branch</th>
                                    <th class="text-right">Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order):
                                    $created = strtotime($order['created_at']);
                                    $diff = time() - $created;
                                    $mins = floor($diff / 60);

                                    // Sync terus dengan column 'status' database
                                    $currentStatus = $order['status'] ?? 'Pending';
                                    $itemsJson = isset($ordersItemsMap[$order['id']]) ? json_encode($ordersItemsMap[$order['id']]) : '[]';
                                    $typeIcon = ($order['order_type'] == 'Delivery') ? '🛵' : '🛍️';

                                    // Logic paparan badge ikut status database
                                    $statusClass = strtolower($currentStatus);
                                    $statusDisplay = '<span class="status-badge status-' . $statusClass . '">' . htmlspecialchars($currentStatus) . '</span>';

                                    // Tambah timer kalau masih Pending
                                    if ($currentStatus == 'Pending') {
                                        $statusDisplay .= '<div class="time-elapsed urgent">' . $mins . ' mins ago</div>';
                                    }
                                    ?>
                                    <tr class="order-row" data-status="<?php echo $currentStatus; ?>" style="height: 85px;">
                                        <td style="vertical-align: middle; padding: 15px 20px;">
                                            <input type="checkbox" class="order-checkbox" value="<?php echo $order['id']; ?>"
                                                onchange="updateBulkActions()">
                                        </td>
                                        <td style="vertical-align: middle; padding: 15px 20px;">
                                            <span
                                                class="order-id-badge">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                        </td>
                                        <td style="vertical-align: middle; padding: 15px 20px;">
                                            <span
                                                class="customer-name"><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></span>
                                            <div class="customer-meta">
                                                <span><?php echo htmlspecialchars($order['customer_phone'] ?? '-'); ?></span>
                                                <span>•</span>
                                                <span><?php echo $typeIcon; ?>
                                                    <?php echo htmlspecialchars($order['order_type']); ?></span>
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle; padding: 15px 20px;">
                                            <?php echo htmlspecialchars($order['branch'] ?? 'Main'); ?>
                                        </td>
                                        <td class="text-right" style="vertical-align: middle; padding: 15px 20px;">
                                            <span style="font-weight:700; color:#ffffff;">RM
                                                <?php echo number_format($order['total'] ?? 0, 2); ?></span>
                                        </td>
                                        <td style="vertical-align: middle; padding: 15px 20px;"><?php echo $statusDisplay; ?>
                                        </td>
                                        <td style="vertical-align: middle; padding: 15px 20px;">
                                            <div class="action-group" style="display: flex; gap: 8px;">
                                                <button type="button" class="btn-neutral" title="View Details"
                                                    onclick='openOrderDetails(<?php echo $order['id']; ?>, <?php echo htmlspecialchars($itemsJson, ENT_QUOTES, 'UTF-8'); ?>, "<?php echo htmlspecialchars($order['customer_name']); ?>")'>
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <form method="POST" style="display:inline;"
                                                    onsubmit="return confirm('Betul ke nak buang order ni?');">
                                                    <input type="hidden" name="view" value="orders">
                                                    <input type="hidden" name="action" value="delete_order">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <button type="submit" class="btn-neutral" title="Delete"
                                                        style="color: #ef4444; border-color: #ef4444;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            <!-- VIEW: REVIEWS -->
            <div id="view-reviews" class="view-section <?php echo $current_view === 'reviews' ? 'active' : ''; ?>">
                <!-- Review Overview -->
                <div class="review-overview-grid">
                    <div class="panel-card avg-rating-card">
                        <div class="avg-rating-score"><?php echo number_format($averageRating, 1); ?></div>
                        <div class="avg-rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++) {
                                echo $i <= round($averageRating) ? '★' : '☆';
                            } ?>
                        </div>
                        <div class="avg-rating-total">Based on <?php echo $totalRatings; ?> reviews</div>
                    </div>
                    <div class="panel-card">
                        <h4 style="margin:0 0 15px 0; color: #ffffff;">Rating Distribution</h4>
                        <div class="rating-dist-chart">
                            <?php for ($i = 5; $i >= 1; $i--):
                                $count = $ratingDistribution[$i];
                                $percentage = $totalRatings > 0 ? ($count / $totalRatings) * 100 : 0;
                                ?>
                                <div class="dist-bar-row">
                                    <div class="dist-bar-label"><?php echo $i; ?> ★</div>
                                    <div class="dist-bar-bg">
                                        <div class="dist-bar-fill"
                                            style="width: <?php echo $percentage; ?>%; background: #ff5100;"></div>
                                    </div>
                                    <div class="dist-bar-count"><?php echo $count; ?></div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Review List -->
                <div class="panel-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                        <h3 style="margin:0; color: #ffffff;">⭐ Customer Reviews</h3>
                        <div class="filter-pills" id="review-filter-pills">
                            <button class="filter-pill active" onclick="filterReviews('all', this)">All</button>
                            <button class="filter-pill" onclick="filterReviews('5', this)">5 Stars</button>
                            <button class="filter-pill" onclick="filterReviews('1', this)">1 Star</button>
                            <button class="filter-pill" onclick="filterReviews('pending', this)">Pending Reply</button>
                            <button class="filter-pill" onclick="filterReviews('hidden', this)">Hidden</button>
                        </div>
                    </div>

                    <div class="reviews-grid">
                        <?php if (empty($reviews)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">💬</div>
                                <h3>No Reviews Yet</h3>
                                <p>Once customers start sharing their experience, it'll appear here!</p>
                            </div>
                        <?php else:
                            foreach ($reviews as $r):
                                $sentimentClass = 'sentiment-neutral';
                                if ($r['rating'] >= 4)
                                    $sentimentClass = 'sentiment-positive';
                                if ($r['rating'] <= 2)
                                    $sentimentClass = 'sentiment-critical';
                                $isApproved = (int) $r['review_is_approved'] === 1;
                                ?>
                                <div class="review-card <?php echo !$isApproved ? 'hidden' : ''; ?>"
                                    data-rating="<?php echo $r['rating']; ?>"
                                    data-replied="<?php echo empty($r['admin_reply']) ? 'false' : 'true'; ?>"
                                    data-approved="<?php echo $isApproved ? 'true' : 'false'; ?>">
                                    <div class="review-card-meta">
                                        <div class="review-customer-name"><?php echo htmlspecialchars($r['customer_name']); ?>
                                        </div>
                                        <div class="review-date">
                                            <?php echo date('d M Y, h:i A', strtotime($r['created_at'])); ?>
                                        </div>
                                        <div style="margin-top:10px;"><span
                                                class="sentiment-badge <?php echo $sentimentClass; ?>"><?php echo str_replace('sentiment-', '', $sentimentClass); ?></span>
                                        </div>
                                    </div>
                                    <div class="review-card-content">
                                        <div class="review-stars">
                                            <?php for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $r['rating'] ? '★' : '☆';
                                            } ?>
                                        </div>
                                        <p class="review-text">
                                            <?php echo htmlspecialchars($r['review'] ?: 'No comment provided.'); ?>
                                        </p>

                                        <?php if ($r['reaction']): ?><span class="status-badge status-preparing"
                                                style="width:fit-content;"><?php echo $r['reaction']; ?></span><?php endif; ?>

                                        <?php if ($r['review_image']): ?>
                                            <img src="uploads/reviews/<?php echo htmlspecialchars($r['review_image']); ?>"
                                                class="review-image" onclick="showImageModal(this.src)">
                                        <?php endif; ?>

                                        <?php if (isset($reviewItemsMap[$r['id']])): ?>
                                            <div class="review-items-list">
                                                <strong>Order Items:</strong>
                                                <?php echo implode(', ', array_map(function ($item) {
                                                    return $item['item_name'];
                                                }, $reviewItemsMap[$r['id']])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="review-card-actions">
                                        <?php if (empty($r['admin_reply'])): ?>
                                            <form method="POST" class="reply-form">
                                                <input type="hidden" name="action" value="reply_review">
                                                <input type="hidden" name="order_id" value="<?php echo $r['id']; ?>">
                                                <textarea name="reply_text" placeholder="Write a public reply..."></textarea>
                                                <button type="submit" class="btn-primary">Send Reply</button>
                                            </form>
                                        <?php else: ?>
                                            <p style="font-size:12px; font-weight:bold; color:#6b7280;">Your Reply:</p>
                                            <div class="admin-reply-display"><?php echo htmlspecialchars($r['admin_reply']); ?>
                                            </div>
                                            <!-- Allow editing reply -->
                                            <form method="POST" class="reply-form" style="margin-top:10px;">
                                                <input type="hidden" name="action" value="reply_review">
                                                <input type="hidden" name="order_id" value="<?php echo $r['id']; ?>">
                                                <textarea name="reply_text"
                                                    placeholder="Edit your reply..."><?php echo htmlspecialchars($r['admin_reply']); ?></textarea>
                                                <button type="submit" class="btn-primary" style="background:#3498db;">Update
                                                    Reply</button>
                                            </form>
                                        <?php endif; ?>

                                        <div class="visibility-control">
                                            <span style="color: #e2e8f0;">Publicly Visible</span>
                                            <form method="POST" style="display:inline-block;">
                                                <input type="hidden" name="action" value="toggle_review_visibility">
                                                <input type="hidden" name="order_id" value="<?php echo $r['id']; ?>">
                                                <input type="hidden" name="is_approved"
                                                    value="<?php echo $isApproved ? '0' : '1'; ?>">
                                                <label class="switch"><input type="checkbox" onchange="this.form.submit()" <?php echo $isApproved ? 'checked' : ''; ?>><span
                                                        class="slider"></span></label>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>

            <!-- VIEW: MENU MANAGEMENT -->
            <div id="view-menu" class="view-section <?php echo $current_view === 'menu' ? 'active' : ''; ?>">
                <div class="panel-card">
                    <h3 style="margin-top:0; color: #ffffff;">➕ Add New Menu Item</h3>
                    <form method="POST" enctype="multipart/form-data"
                        style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <input type="hidden" name="action" value="create_menu_item">
                        <div class="form-group"><label>Category</label><select name="category" required>
                                <option value="burger">Burger</option>
                                <option value="special">Special</option>
                                <option value="add-on">Add-On</option>
                                <option value="minuman">Minuman</option>
                            </select></div>
                        <div class="form-group" style="grid-column: span 2;"><label>Item Name</label><input type="text"
                                name="name" placeholder="e.g. Lava Cheese Burger" required></div>
                        <div class="form-group" style="grid-column: span 3;"><label>Description</label><input
                                type="text" name="description" placeholder="e.g. Juicy beef patty with melted cheese">
                        </div>
                        <div class="form-group"><label>Selling Price (RM)</label><input type="number" step="0.01"
                                name="price" placeholder="12.00" required></div>
                        <div class="form-group"><label>Cost Price (RM)</label><input type="number" step="0.01"
                                name="cost_price" placeholder="5.50"></div>
                        <div class="form-group" style="grid-column: span 3;">
                            <label>Variants</label>
                            <div id="add-variants-container">
                                <!-- Dynamic variants will be added here by JS -->
                            </div>
                            <button type="button" onclick="addVariantRow('add-variants-container')" class="btn-neutral"
                                style="margin-top: 10px; width: fit-content; padding: 8px 12px;">+ Add Variant</button>
                        </div>
                        <div class="form-group" style="grid-column: span 3;">
                            <label>Menu Image (JPG, PNG, JPEG)</label>
                            <input type="file" name="item_image" accept=".jpg,.jpeg,.png"
                                onchange="previewImage(this, 'add-item-preview')" required>
                            <img id="add-item-preview"
                                style="max-width: 150px; margin-top: 10px; border-radius: 8px; display: none;">
                        </div>
                        <button type="submit" class="btn-primary" style="grid-column: span 3;">Add Item</button>
                    </form>
                </div>

                <div class="panel-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                        <h3 style="margin:0; color: #ffffff;">🍔 Current Menu</h3>
                        <div class="filter-pills" id="menu-filter-pills">
                            <button class="filter-pill active" onclick="filterMenu('all', this)">All</button>
                            <button class="filter-pill" onclick="filterMenu('burger', this)">Burgers</button>
                            <button class="filter-pill" onclick="filterMenu('special', this)">Specials</button>
                            <button class="filter-pill" onclick="filterMenu('add-on', this)">Add-Ons</button>
                            <button class="filter-pill" onclick="filterMenu('minuman', this)">Drinks</button>
                        </div>
                    </div>

                    <div class="menu-grid" id="menuGrid">
                        <?php foreach ($adminMenuItems as $item):
                            $itemJson = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
                            $isAvailable = (int) $item['is_available'] === 1;

                            $profitMargin = 0;
                            if ((float) $item['price'] > 0) {
                                $profitMargin = (((float) $item['price'] - (float) $item['cost_price']) / (float) $item['price']) * 100;
                            }

                            $lowerName = strtolower(trim($item['name']));
                            $imgFilename = str_replace(' ', '', $lowerName) . '.png';

                            // Match menu.php mapping
                            if ($lowerName === 'ayam goreng krup krap')
                                $imgFilename = 'ayamkrupkrap.jpg';
                            if ($lowerName === 'burger mix xl')
                                $imgFilename = 'xl.jpg';
                            if ($lowerName === 'lava cheese burger')
                                $imgFilename = 'lavacheese.jpg';
                            if ($lowerName === 'cheese steak')
                                $imgFilename = 'cheesesteak.jpg';
                            if ($lowerName === 'chicken grill burger')
                                $imgFilename = 'grill.jpg';
                            if ($lowerName === 'hawaiian spicy')
                                $imgFilename = 'hawaii.jpg';
                            if ($lowerName === 'burger kambing')
                                $imgFilename = 'kambing.jpg';
                            if ($lowerName === 'burger sate ayam')
                                $imgFilename = 'sate.jpg';
                            if ($lowerName === 'smash burger' || $lowerName === 'beef smash burger')
                                $imgFilename = 'smash.jpg';
                            if ($lowerName === 'mozzarella cheese')
                                $imgFilename = 'mozz.jpg';
                            if ($lowerName === 'telur')
                                $imgFilename = 'egg.jpg';
                            if ($lowerName === 'cheddar cheese')
                                $imgFilename = 'cheddar.jpg';
                            if ($lowerName === 'green tea')
                                $imgFilename = 'green.jpg';
                            if ($lowerName === 'chocolate')
                                $imgFilename = 'milo.jpg';
                            if ($lowerName === 'indocafe')
                                $imgFilename = 'kopi.jpg';
                            if ($lowerName === 'teh')
                                $imgFilename = 'tea.jpg';
                            if ($lowerName === 'kopi')
                                $imgFilename = 'black.jpg';
                            if ($lowerName === 'jus buah')
                                $imgFilename = 'oren.jpg';
                            if ($lowerName === 'teh o limau')
                                $imgFilename = 'limau.jpg';
                            if ($lowerName === 'minuman bergas')
                                $imgFilename = 'aw.jpg';
                            if ($lowerName === 'burger wagyu' || $lowerName === 'wagyu burger')
                                $imgFilename = 'wagyu.jpg';
                            if ($lowerName === 'burger itik' || $lowerName === 'itik burger')
                                $imgFilename = 'itik.jpg';
                            if ($lowerName === 'nugget tempura')
                                $imgFilename = 'nug.jpg';
                            if ($lowerName === 'cheezy wedges')
                                $imgFilename = 'wedgesss.jpg';
                            if ($lowerName === 'ayam popcorn')
                                $imgFilename = 'pop.jpg';
                            if ($lowerName === 'teh o laici')
                                $imgFilename = 'laici.png';
                            if ($lowerName === 'limau asam boi')
                                $imgFilename = 'asam.jpg';
                            if ($lowerName === 'sirap bandung')
                                $imgFilename = 'bandung.jpg';
                            if ($lowerName === 'sirap limau')
                                $imgFilename = 'sirap.png';
                            if ($lowerName === 'oren sunquick')
                                $imgFilename = 'oren.jpg';
                            if ($lowerName === 'extrajoss susu')
                                $imgFilename = 'extra.jpg';
                            if ($lowerName === 'extrajoss')
                                $imgFilename = 'joss.jpg';
                            if ($lowerName === 'sirap')
                                $imgFilename = 'rose.jpg';


                            // Prefer uploaded DB image if exists
                            $cardImage = 'images/' . $imgFilename;
                            if (!empty($item['image']) && file_exists(__DIR__ . '/uploads/menu/' . $item['image'])) {
                                $cardImage = 'uploads/menu/' . $item['image'];
                            }
                            ?>
                            <div class="menu-card <?php echo !$isAvailable ? 'sold-out' : ''; ?>"
                                data-category="<?php echo htmlspecialchars($item['category']); ?>">

                                <div class="menu-card-img">
                                    <img src="<?php echo htmlspecialchars($cardImage); ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>"
                                        style="width:100%; height:100%; object-fit:cover; display:block;"
                                        onerror="this.src='images/hero_burger.png'">
                                    <span class="category-badge"><?php echo ucfirst($item['category']); ?></span>
                                    <?php if (in_array($item['name'], $bestSellers)): ?>
                                        <span class="bestseller-badge"><i class="fas fa-star"></i> Best Seller</span>
                                    <?php endif; ?>
                                </div>
                                <div class="menu-card-content">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p class="description"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <div class="price-info">
                                        <span class="selling-price">RM
                                            <?php echo number_format($item['price'], 2); ?></span>
                                        <span class="cost-info">
                                            Cost: RM <?php echo number_format($item['cost_price'], 2); ?><br>
                                            <span class="profit-margin"><?php echo number_format($profitMargin, 0); ?>%
                                                Margin</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="menu-card-actions">
                                    <div class="availability-toggle">
                                        <div class="dot <?php echo $isAvailable ? 'available' : 'unavailable'; ?>"></div>
                                        <span><?php echo $isAvailable ? 'Available' : 'Sold Out'; ?></span>
                                        <form method="POST" style="display:inline-block; margin-left: 10px;">
                                            <input type="hidden" name="action" value="toggle_availability">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="status"
                                                value="<?php echo $isAvailable ? '0' : '1'; ?>">
                                            <label class="switch">
                                                <input type="checkbox" onchange="this.form.submit()" <?php echo $isAvailable ? 'checked' : ''; ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </form>
                                    </div>
                                    <div class="menu-card-buttons action-group">
                                        <button class="btn-ghost view" title="Edit"
                                            onclick='openEditModal(<?php echo $itemJson; ?>)'><i
                                                class="fas fa-pencil-alt"></i></button>
                                        <form method="POST" onsubmit="return confirm('Delete this item?');"
                                            style="display:inline-block;">
                                            <input type="hidden" name="action" value="delete_menu_item">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn-ghost delete" title="Delete"><i
                                                    class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- VIEW: INVENTORY -->
            <!-- VIEW: INVENTORY -->
            <div id="view-inventory" class="view-section <?php echo $current_view === 'inventory' ? 'active' : ''; ?>">
                <div class="panel-card">
                    <div
                        style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                        <h3 style="margin:0; color: #ffffff;">📦 Inventory Management</h3>
                        <span style="font-size:13px; color:#a0aec0;">Track stock like staff view</span>
                    </div>

                    <!-- Add Stock Form -->
                    <form method="POST"
                        style="display:flex; gap:10px; margin-bottom:20px; background:#1d1a2f; padding:15px; border-radius:12px; flex-wrap:wrap;">
                        <input type="hidden" name="action" value="add_stock">

                        <input type="text" name="item_name" placeholder="Item Name (e.g. Burger Buns)" required
                            style="flex:2; min-width:240px; padding:12px; border:1px solid #4a5568; border-radius:8px; background:#373359; color:white;">

                        <input type="number" name="quantity" placeholder="Qty" required
                            style="width:100px; padding:12px; border:1px solid #4a5568; border-radius:8px; background:#373359; color:white;">

                        <input type="text" name="unit" placeholder="Unit (e.g. packs)"
                            style="width:140px; padding:12px; border:1px solid #4a5568; border-radius:8px; background:#373359; color:white;">

                        <button type="submit" class="btn-primary">Add Item</button>
                    </form>

                    <div style="overflow-x:auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Stock Level</th>
                                    <th>Status</th>
                                    <th>Controls</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($inventoryItems)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center; color:#a0aec0;">No inventory items found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($inventoryItems as $item): ?>
                                        <?php
                                        $itemName = trim($item['item_name']);
                                        $lowerItemName = strtolower($itemName);
                                        $qty = (int) $item['quantity'];
                                        $unit = htmlspecialchars($item['unit'] ?: 'units');

                                        $emoji = '📦';
                                        if (str_contains($lowerItemName, 'bun'))
                                            $emoji = '🍞';
                                        elseif (str_contains($lowerItemName, 'patty') || str_contains($lowerItemName, 'beef'))
                                            $emoji = '🥩';
                                        elseif (str_contains($lowerItemName, 'chicken'))
                                            $emoji = '🍗';
                                        elseif (str_contains($lowerItemName, 'cheese'))
                                            $emoji = '🧀';
                                        elseif (str_contains($lowerItemName, 'lettuce'))
                                            $emoji = '🥬';
                                        elseif (str_contains($lowerItemName, 'tomato'))
                                            $emoji = '🍅';
                                        elseif (str_contains($lowerItemName, 'onion'))
                                            $emoji = '🧅';
                                        elseif (str_contains($lowerItemName, 'sauce') || str_contains($lowerItemName, 'mayo') || str_contains($lowerItemName, 'chili'))
                                            $emoji = '🥣';
                                        elseif (str_contains($lowerItemName, 'egg'))
                                            $emoji = '🥚';
                                        elseif (str_contains($lowerItemName, 'potato') || str_contains($lowerItemName, 'fries'))
                                            $emoji = '🍟';
                                        elseif (str_contains($lowerItemName, 'drink') || str_contains($lowerItemName, 'cup'))
                                            $emoji = '🥤';

                                        $statusText = $item['status'];
                                        $statusClass = 'status-served';
                                        $statusIcon = '✅';

                                        if ($statusText === 'In Stock') {
                                            $statusClass = 'status-ready';
                                            $statusIcon = '✅';
                                        } elseif ($statusText === 'Low Stock') {
                                            $statusClass = 'status-pending';
                                            $statusIcon = '⚠️';
                                        } elseif ($statusText === 'Out of Stock') {
                                            $statusClass = 'status-out';
                                            $statusIcon = '❌';
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div style="display:flex; align-items:center; gap:10px;">
                                                    <span style="font-size:20px;"><?php echo $emoji; ?></span>
                                                    <div>
                                                        <div style="font-weight:700; color:#ffffff;">
                                                            <?php echo htmlspecialchars($itemName); ?>
                                                        </div>
                                                        <div style="font-size:12px; color:#a0aec0;">
                                                            Unit: <?php echo $unit; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <div style="font-size:18px; font-weight:800; color:#ffffff;">
                                                    <?php echo $qty; ?>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="status-badge <?php echo $statusClass; ?>"
                                                    style="<?php echo $statusText === 'Out of Stock' ? 'background:rgba(220,38,38,0.12); color:#f87171; border-color:rgba(220,38,38,0.35);' : ''; ?>">
                                                    <?php echo $statusIcon . ' ' . htmlspecialchars(strtoupper($statusText)); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                                                    <form method="POST"
                                                        style="display:flex; gap:8px; align-items:center; margin:0;">
                                                        <input type="hidden" name="action" value="update_stock">
                                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">

                                                        <input type="number" name="quantity" value="<?php echo $qty; ?>"
                                                            style="width:90px; padding:8px; border:1px solid #4a5568; border-radius:6px; background:#373359; color:white;">

                                                        <button type="submit" class="btn-primary"
                                                            style="padding:8px 14px; font-size:12px;">Update</button>
                                                    </form>

                                                    <form method="POST" onsubmit="return confirm('Delete this item?');"
                                                        style="margin:0;">
                                                        <input type="hidden" name="action" value="delete_stock">
                                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <style>
                .status-out {
                    background: rgba(220, 38, 38, 0.12);
                    color: #f87171;
                    border: 1px solid rgba(220, 38, 38, 0.35);
                }
            </style>

            <!-- VIEW: REPORTS -->
            <div id="view-reports" class="view-section <?php echo $current_view === 'reports' ? 'active' : ''; ?>">

                <!-- 1. DATE FILTER BAR -->
                <div class="panel-card"
                    style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
                    <form method="GET" style="display:flex; align-items:center; gap:10px;">
                        <input type="hidden" name="view" value="reports">
                        <div style="display:flex; flex-direction:column;">
                            <label style="font-size:10px; color:#a0aec0; font-weight:bold;">START DATE</label>
                            <input type="date" name="report_start" value="<?php echo $reportStart; ?>"
                                style="padding:8px; border-radius:5px; border:1px solid rgba(255,255,255,0.1); background:#373359; color:white; color-scheme:dark;">
                        </div>
                        <div style="display:flex; flex-direction:column;">
                            <label style="font-size:10px; color:#a0aec0; font-weight:bold;">END DATE</label>
                            <input type="date" name="report_end" value="<?php echo $reportEnd; ?>"
                                style="padding:8px; border-radius:5px; border:1px solid rgba(255,255,255,0.1); background:#373359; color:white; color-scheme:dark;">
                        </div>
                        <button type="submit" class="btn-primary"
                            style="height:38px; margin-top:14px; margin-right: 5px;">Filter</button>
                        <button type="button"
                            onclick="window.location.href='export_orders.php?report_start=<?php echo $reportStart; ?>&report_end=<?php echo $reportEnd; ?>'"
                            class="btn-primary"
                            style="height:38px; margin-top:14px; background: #166534; border-color: #166534; margin-right: 5px;"><i
                                class="fas fa-file-excel"></i> Export Excel</button>
                        <button type="button"
                            onclick="window.open('print_report.php?start=<?php echo $reportStart; ?>&end=<?php echo $reportEnd; ?>', '_blank')"
                            class="btn-primary"
                            style="height:38px; margin-top:14px; background: #3498db; border-color: #3498db;"><i
                                class="fas fa-print"></i> Print Report</button>
                    </form>
                    <div class="filter-pills" style="margin:0;">
                        <a href="?view=reports&report_start=<?php echo date('Y-m-d'); ?>&report_end=<?php echo date('Y-m-d'); ?>"
                            class="filter-pill">Today</a>
                        <a href="?view=reports&report_start=<?php echo date('Y-m-d', strtotime('-7 days')); ?>&report_end=<?php echo date('Y-m-d'); ?>"
                            class="filter-pill">Last 7 Days</a>
                        <a href="?view=reports&report_start=<?php echo date('Y-m-01'); ?>&report_end=<?php echo date('Y-m-d'); ?>"
                            class="filter-pill">This Month</a>
                    </div>
                </div>

                <!-- 2. HIGHLIGHTS (Best Performing) -->
                <div class="dashboard-grid">
                    <div class="premium-card stat-card" style="grid-column: span 3;">
                        <div class="card-header">
                            <div class="card-icon icon-orange"><i class="fas fa-trophy"></i></div>
                        </div>
                        <div class="card-body">
                            <p class="stat-label">Best Selling Product</p>
                            <p class="stat-value" style="font-size:20px;">
                                <?php echo htmlspecialchars($bestProduct['item_name']); ?>
                            </p>
                            <p class="stat-trend positive"><?php echo $bestProduct['total_qty']; ?> sold</p>
                        </div>
                    </div>
                    <div class="premium-card stat-card" style="grid-column: span 3;">
                        <div class="card-header">
                            <div class="card-icon icon-purple"><i class="fas fa-layer-group"></i></div>
                        </div>
                        <div class="card-body">
                            <p class="stat-label">Top Category</p>
                            <p class="stat-value" style="font-size:20px;">
                                <?php echo htmlspecialchars($bestCategory['category']); ?>
                            </p>
                            <p class="stat-trend positive">RM
                                <?php echo number_format($bestCategory['category_sales'], 2); ?>
                            </p>
                        </div>
                    </div>
                    <div class="premium-card stat-card" style="grid-column: span 3;">
                        <div class="card-header">
                            <div class="card-icon icon-green"><i class="fas fa-calendar-check"></i></div>
                        </div>
                        <div class="card-body">
                            <p class="stat-label">Highest Sales Day</p>
                            <p class="stat-value" style="font-size:20px;"><?php echo $highestSalesDay['date']; ?></p>
                            <p class="stat-trend positive">RM
                                <?php echo number_format($highestSalesDay['amount'], 2); ?>
                            </p>
                        </div>
                    </div>
                    <div class="premium-card stat-card" style="grid-column: span 3;">
                        <div class="card-header">
                            <div class="card-icon icon-blue"><i class="fas fa-ban"></i></div>
                        </div>
                        <div class="card-body">
                            <p class="stat-label">Cancellation Rate</p>
                            <p class="stat-value" style="font-size:20px;"><?php echo $cancelledRate; ?>%</p>
                            <p class="stat-trend negative"><?php echo $cancelledOrders; ?> orders</p>
                        </div>
                    </div>
                </div>

                <!-- 3. CHARTS -->
                <div class="dashboard-grid">
                    <div class="panel-card" style="grid-column: span 8;">
                        <h3 style="margin-top:0; color: #ffffff;">📊 Revenue & Orders Trend</h3>
                        <div style="height: 300px;">
                            <canvas id="reportsBarChart"></canvas>
                        </div>
                    </div>
                    <div class="panel-card" style="grid-column: span 4;">
                        <h3 style="margin-top:0; color: #ffffff;">🍰 Sales Distribution</h3>
                        <div style="height: 300px;">
                            <canvas id="categoryPieChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 4. DATA TABLES -->
                <div class="panel-card">
                    <h3 style="margin-top:0; color: #ffffff;">🏆 Top 10 Products</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th class="text-right">Qty Sold</th>
                                <th class="text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $prod): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prod['item_name']); ?></td>
                                    <td class="text-right"><?php echo number_format($prod['total_qty']); ?></td>
                                    <td class="text-right" style="color:#ffffff; font-weight:bold;">RM
                                        <?php echo number_format($prod['total_revenue'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="panel-card">
                    <h3 style="margin-top:0; color: #ffffff;">📂 Category Breakdown</h3>
                    <?php if (empty($salesByCategory)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">💰</div>
                            <h3>No Sales Data</h3>
                            <p>No completed sales found to generate a category report.</p>
                        </div>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Category</th>
                                    <th>Items Sold</th>
                                    <th class="text-right">Total Sales (RM)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalCategorySales = array_sum(array_column($salesByCategory, 'category_sales'));
                                foreach ($salesByCategory as $cat):
                                    $percentage = $totalCategorySales > 0 ? ($cat['category_sales'] / $totalCategorySales) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="role-badge"
                                                style="text-transform: capitalize; background: rgba(99, 102, 241, 0.2); color: #a5b4fc;"><?php echo htmlspecialchars($cat['category']); ?></span>
                                            <div style="width: 100%; background: #374151; border-radius: 5px; height: 8px; margin-top: 8px;"
                                                title="<?php echo number_format($percentage, 1); ?>% of total sales">
                                                <div
                                                    style="width: <?php echo $percentage; ?>%; background: #818cf8; height: 100%; border-radius: 5px;">
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo number_format($cat['items_sold']); ?></td>
                                        <td class="text-right" style="font-weight: 700; font-size: 15px; color: #ffffff;">RM
                                            <?php echo number_format($cat['category_sales'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            </div>

            <!-- VIEW: BRANCHES -->
            <div id="view-branches" class="view-section <?php echo $current_view === 'branches' ? 'active' : ''; ?>">
                <div class="panel-card">
                    <h3 style="margin-top:0; color: #ffffff;">🏪 Branch Management</h3>

                    <div class="branch-grid">
                        <?php
                        // Ambil data terus dari database supaya ID sentiasa sync
                        $stmt = $pdo->query("SELECT * FROM branches ORDER BY name ASC");
                        $branchesList = $stmt->fetchAll();

                        if (!empty($branchesList)):
                            foreach ($branchesList as $branch): ?>
                                <div class="management-card">
                                    <h3><?php echo htmlspecialchars($branch['name']); ?></h3>
                                    <p>📞 <?php echo htmlspecialchars($branch['phone']); ?></p>
                                    <div class="management-actions">
                                        <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $branch['phone']); ?>"
                                            class="btn-call">
                                            <i class="fas fa-phone-alt"></i> Call
                                        </a>
                                        <button type="button" class="btn-edit" onclick='openBranchModal(
                                    <?php echo (int) $branch["id"]; ?>,
                                    <?php echo json_encode($branch["name"]); ?>,
                                    <?php echo json_encode($branch["phone"]); ?>
                                )'>
                                            Edit
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach;
                        else: ?>
                            <p style="color: #a0aec0;">No branches found in database.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <style>
                /* Pakai style asal kau cuma aku kemaskan sikit grid dia */
                .branch-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                    gap: 20px;
                    margin-top: 20px;
                }

                .management-card {
                    background: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 12px;
                    padding: 20px;
                    text-align: center;
                    transition: all 0.2s ease;
                }

                .management-card:hover {
                    background: rgba(255, 255, 255, 0.08);
                    border-color: #ff6600;
                    transform: translateY(-5px);
                }

                .management-card h3 {
                    color: #ffffff;
                    margin: 0 0 10px 0;
                    font-size: 18px;
                }

                .management-card p {
                    color: #a0aec0;
                    margin: 0 0 20px 0;
                    font-size: 14px;
                }

                .management-actions {
                    display: flex;
                    gap: 10px;
                    justify-content: center;
                }

                .btn-call,
                .btn-edit {
                    padding: 8px 12px;
                    border-radius: 6px;
                    text-decoration: none;
                    font-size: 13px;
                    font-weight: bold;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    border: none;
                    cursor: pointer;
                    flex: 1;
                    justify-content: center;
                    color: white !important;
                }

                .btn-call {
                    background-color: #2ecc71;
                }

                .btn-edit {
                    background-color: #ff6600;
                }

                .btn-call:hover {
                    background-color: #27ae60;
                }

                .btn-edit:hover {
                    background-color: #e65c00;
                }
            </style>
            <!-- VIEW: ACTIVITY LOGS -->
            <div id="view-logs" class="view-section <?php echo $current_view === 'logs' ? 'active' : ''; ?>">
                <div class="panel-card">
                    <h3 style="margin-top:0; color: #ffffff;">📜 System Activity Logs</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activityLogs as $log): ?>
                                <tr>
                                    <td style="font-size:12px; color:#a0aec0;"><?php echo $log['created_at']; ?></td>
                                    <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                                    <td><span
                                            class="status-badge status-preparing"><?php echo htmlspecialchars($log['action']); ?></span>
                                    </td>
                                    <td style="font-size:13px;"><?php echo htmlspecialchars($log['details']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: SETTINGS -->
            <style>
                /* Styling untuk toggle warna hijau bila ON */
                .switch input:checked+.slider {
                    background-color: #2ecc71 !important;
                }

                /* Card untuk setiap cawangan */
                .branch-card {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 15px 20px;
                    background: rgba(255, 255, 255, 0.05);
                    border-radius: 12px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    transition: all 0.3s ease;
                }

                /* Highlight border hijau bila checkbox dalam card tu checked */
                .branch-card.active-border {
                    border-color: #2ecc71;
                    background: rgba(46, 204, 113, 0.05);
                }
            </style>

            <div id="view-settings" class="view-section <?php echo $current_view === 'settings' ? 'active' : ''; ?>">
                <div class="panel-card">
                    <h3 style="margin-top:0; color: #ffffff;">⚙️ System Settings</h3>

                    <div
                        style="display:flex; align-items:center; gap:20px; padding:20px; background:#1d1a2f; border-radius:10px; border:1px solid rgba(255,255,255,0.1); margin-bottom: 25px;">
                        <div>
                            <h4 style="margin:0; color: #ffffff;">Global Store Status</h4>
                            <p style="margin:5px 0 0 0; font-size:13px; color:#a0aec0;">Tutup semua cawangan serentak.
                            </p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="store-toggle" <?php echo ($storeStatus == 'open') ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <span id="status-label"
                            style="font-weight: 800; font-size: 14px; color: <?php echo ($storeStatus == 'open') ? '#2ecc71' : '#e74c3c'; ?>;">
                            STORE IS <?php echo strtoupper($storeStatus); ?>
                        </span>
                    </div>

                    <h4 style="color: #ffffff; margin-bottom: 15px; padding-left: 5px;">Branch Status</h4>
                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px;">

                        <?php
                        $branches = ['Arau', 'Jejawi', 'Kuala Perlis', 'Kangar', 'Beseri'];
                        foreach ($branches as $branch):
                            $branchID = strtolower(str_replace(' ', '-', $branch));
                            $isBranchOpen = true; // Nanti tukar dengan logic database kau
                            ?>
                            <div class="branch-card <?php echo $isBranchOpen ? 'active-border' : ''; ?>">
                                <span
                                    style="color: #ffffff; font-weight: 600; font-size: 14px;"><?php echo $branch; ?></span>
                                <label class="switch">
                                    <input type="checkbox" class="branch-toggle" data-branch="<?php echo $branchID; ?>"
                                        <?php echo $isBranchOpen ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>

            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script>
                $(document).ready(function () {
                });
            </script>

        </div> <!-- End .main-content -->
    </div>

    <!-- EDIT MENU ITEM MODAL -->
    <div id="edit-item-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeEditModal()">&times;</span>
            <h3>Edit Menu Item</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Update Image</label>
                    <input type="file" name="item_image" accept=".jpg,.jpeg,.png">
                </div>
                <input type="hidden" name="action" value="update_menu_item">
                <input type="hidden" id="edit-item-id" name="item_id">
                <div class="form-group"><label>Category</label><select id="edit-item-category" name="category" required>
                        <option value="burger">Burger</option>
                        <option value="special">Special</option>
                        <option value="add-on">Add-On</option>
                        <option value="minuman">Minuman</option>
                    </select></div>
                <div class="form-group" style="grid-column: span 2;"><label>Item Name</label><input type="text"
                        id="edit-item-name" name="name" required></div>
                <div class="form-group" style="grid-column: span 3;"><label>Description</label><input type="text"
                        id="edit-item-description" name="description"></div>
                <div class="form-group"><label>Selling Price (RM)</label><input type="number" step="0.01"
                        id="edit-item-price" name="price" required></div>
                <div class="form-group"><label>Cost Price (RM)</label><input type="number" step="0.01"
                        id="edit-item-cost-price" name="cost_price"></div>
                <div class="form-group" style="grid-column: span 3;">
                    <label>Variants</label>
                    <div id="edit-variants-container">
                        <!-- Dynamic variants will be added here by JS -->
                    </div>
                    <button type="button" onclick="addVariantRow('edit-variants-container')" class="btn-neutral"
                        style="margin-top: 10px; width: fit-content; padding: 8px 12px;">+ Add Variant</button>
                </div>
                <button type="submit" class="btn-primary" style="grid-column: span 2;">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- EDIT BRANCH MODAL -->
    <div id="edit-branch-modal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <span class="close-btn"
                onclick="document.getElementById('edit-branch-modal').style.display='none'">&times;</span>
            <h3>Edit Branch Details</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_branch">
                <input type="hidden" name="view" value="branches">
                <input type="hidden" id="edit-branch-id" name="branch_id">
                <div class="form-group">
                    <label>Branch Name</label>
                    <input type="text" id="edit-branch-name" name="name" required>
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Phone Number</label>
                    <input type="text" id="edit-branch-phone" name="phone" required>
                </div>
                <button type="submit" class="btn-primary"
                    style="grid-column: span 2; width: 100%; margin-top: 20px;">Update Branch</button>
            </form>
        </div>
    </div>

    <!-- RESET PASSWORD MODAL -->
    <div id="reset-password-modal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <span class="close-btn" onclick="closeResetPasswordModal()">&times;</span>
            <h3 id="reset-password-title">Reset Password</h3>
            <form method="POST">
                <input type="hidden" name="action" value="reset_user_password">
                <input type="hidden" id="reset-user-id" name="user_id">
                <div class="form-group" style="grid-column: span 2;">
                    <label for="new-pass-input">New Password</label>
                    <input type="password" id="new-pass-input" name="new_password" placeholder="Enter new password"
                        required>
                </div>
                <button type="submit" class="btn-primary" style="grid-column: span 2;">Set New Password</button>
            </form>
        </div>
    </div>

    <!-- ORDER DETAILS MODAL -->
    <div id="order-details-modal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <span class="close-btn"
                onclick="document.getElementById('order-details-modal').style.display='none'">&times;</span>
            <h3 id="modal-order-title">Order Details</h3>
            <div id="modal-order-items" style="margin-top:15px;"></div>
        </div>
    </div>

    <!-- IMAGE MODAL -->
    <div id="image-modal" onclick="this.classList.remove('active')">
        <img id="modal-img-src" src="">
    </div>

    <script>
        function switchView(viewId, element) {
            const titles = {
                dashboard: 'Dashboard',
                orders: 'Orders',
                staff: 'Staff Management',
                reviews: 'Customer Reviews',
                menu: 'Menu Management',
                inventory: 'Inventory',
                reports: 'Sales Reports',
                branches: 'Our Branches',
                settings: 'System Settings',
                logs: 'Activity Logs'
            };

            const headerTitle = document.querySelector('.top-header h2');
            if (headerTitle && titles[viewId]) {
                headerTitle.innerText = titles[viewId];
            }

            if (viewId === 'orders') {
                const badge = document.getElementById('ordersBadge');
                if (badge) {
                    localStorage.setItem('ordersBadgeSeen', 'true');
                    localStorage.setItem('ordersBadgeSeenCount', badge.textContent.trim());
                    badge.style.display = 'none';
                    document.documentElement.classList.add('orders-badge-hidden');
                }
            }

            if (viewId === 'reviews') {
                const badge = document.getElementById('reviewsBadge');
                if (badge) {
                    localStorage.setItem('reviewsBadgeSeen', 'true');
                    localStorage.setItem('reviewsBadgeSeenCount', badge.textContent.trim());
                    badge.style.display = 'none';
                    document.documentElement.classList.add('reviews-badge-hidden');
                }
            }

            if (viewId === 'logs') {
                const badge = document.getElementById('logsBadge');
                if (badge) {
                    localStorage.setItem('logsBadgeSeen', 'true');
                    localStorage.setItem('logsBadgeSeenCount', badge.textContent.trim());
                    badge.style.display = 'none';
                    document.documentElement.classList.add('logs-badge-hidden');
                }
            }

            document.querySelectorAll('.view-section').forEach(section => {
                section.classList.remove('active');
            });

            const targetView = document.getElementById('view-' + viewId);
            if (targetView) {
                targetView.classList.add('active');
            }

            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });

            if (element) {
                element.classList.add('active');
            }

            // simpan current view
            localStorage.setItem('adminActiveView', viewId);

            // update URL tanpa reload
            const url = new URL(window.location);
            url.searchParams.set('view', viewId);
            window.history.replaceState({}, '', url);
        }

        function refreshCurrentView() {
            const activeView =
                localStorage.getItem('adminActiveView') ||
                new URLSearchParams(window.location.search).get('view') ||
                'dashboard';

            const url = new URL(window.location);
            url.searchParams.set('view', activeView);
            window.location.href = url.toString();
        }

        window.addEventListener('DOMContentLoaded', () => {
            const checkBadge = (badgeId, seenKey, countKey, hiddenClass) => {
                const badge = document.getElementById(badgeId);
                if (!badge) return;

                const currentCount = badge.textContent.trim();
                const seen = localStorage.getItem(seenKey);
                const seenCount = localStorage.getItem(countKey);

                if (seen === 'true' && seenCount === currentCount) {
                    badge.style.display = 'none';
                    document.documentElement.classList.add(hiddenClass);
                } else {
                    localStorage.removeItem(seenKey);
                    localStorage.setItem(countKey, currentCount);
                    badge.style.display = '';
                    document.documentElement.classList.remove(hiddenClass);
                }
            };

            checkBadge('ordersBadge', 'ordersBadgeSeen', 'ordersBadgeSeenCount', 'orders-badge-hidden');
            checkBadge('reviewsBadge', 'reviewsBadgeSeen', 'reviewsBadgeSeenCount', 'reviews-badge-hidden');
            checkBadge('logsBadge', 'logsBadgeSeen', 'logsBadgeSeenCount', 'logs-badge-hidden');
        });

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        function addVariantRow(containerId, name = '', price = '') {
            const container = document.getElementById(containerId);
            const variantRow = document.createElement('div');
            variantRow.style.display = 'flex';
            variantRow.style.gap = '10px';
            variantRow.style.marginBottom = '10px';
            variantRow.style.alignItems = 'center';
            variantRow.innerHTML = `
        <input type="text" name="variant_name[]" placeholder="Variant Name (e.g. Double)" value="${name}" style="flex:2;">
        <input type="number" step="0.01" name="variant_price[]" placeholder="Price" value="${price}" style="flex:1;">
        <button type="button" onclick="this.parentElement.remove()" class="btn-danger" style="padding: 0 15px; height: 44px;">&times;</button>
    `;
            container.appendChild(variantRow);
        }

        function openEditModal(item) {
            document.getElementById('edit-item-id').value = item.id;
            document.getElementById('edit-item-name').value = item.name;
            document.getElementById('edit-item-category').value = item.category;
            document.getElementById('edit-item-description').value = item.description;
            document.getElementById('edit-item-price').value = item.price;
            document.getElementById('edit-item-cost-price').value = item.cost_price;


            const variantsContainer = document.getElementById('edit-variants-container');
            variantsContainer.innerHTML = ''; // Clear previous variants
            if (item.variants) {
                try {
                    const variants = JSON.parse(item.variants);
                    if (Array.isArray(variants)) {
                        variants.forEach(v => addVariantRow('edit-variants-container', v.name, v.price));
                    }
                } catch (e) { console.error("Could not parse variants JSON:", item.variants); }
            }

            document.getElementById('edit-item-modal').style.display = 'block';
        }

        function openBranchModal(id, name, phone) {
            document.getElementById('edit-branch-id').value = id;
            document.getElementById('edit-branch-name').value = name;
            document.getElementById('edit-branch-phone').value = phone;
            document.getElementById('edit-branch-modal').style.display = 'block';
        }

        function openResetPasswordModal(userId, userName) {
            document.getElementById('reset-user-id').value = userId;
            document.getElementById('reset-password-title').innerText = 'Reset Password for ' + userName;
            document.getElementById('reset-password-modal').style.display = 'block';
        }

        function closeResetPasswordModal() {
            document.getElementById('reset-password-modal').style.display = 'none';
        }

        function openOrderDetails(id, items, customer) {
            document.getElementById('modal-order-title').innerText = `Order #${id} - ${customer}`;
            let html = '<ul style="list-style:none; padding:0;">';
            if (items.length === 0) {
                html += '<li>No items found.</li>';
            } else {
                items.forEach(item => {
                    let variant = item.variant ? `(${item.variant})` : '';
                    let note = item.customization ? `<br><small style="color:red;">Note: ${item.customization}</small>` : '';
                    let protein = item.protein ? ` - ${item.protein}` : '';
                    html += `<li style="border-bottom:1px solid #eee; padding:10px 0;">
                        <strong>${item.qty}x</strong> ${item.item_name} ${variant} ${protein} ${note}
                     </li>`;
                });
            }
            html += '</ul>';
            document.getElementById('modal-order-items').innerHTML = html;
            document.getElementById('order-details-modal').style.display = 'block';
        }

        function showImageModal(src) {
            document.getElementById('modal-img-src').src = src;
            document.getElementById('image-modal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('edit-item-modal').style.display = 'none';
        }
        window.onclick = function (event) {
            if (event.target == document.getElementById('edit-item-modal')) closeEditModal();
            if (event.target == document.getElementById('edit-branch-modal')) document.getElementById('edit-branch-modal').style.display = 'none';
            if (event.target == document.getElementById('reset-password-modal')) closeResetPasswordModal();
            if (event.target == document.getElementById('order-details-modal')) document.getElementById('order-details-modal').style.display = 'none';
            if (event.target.id == 'image-modal') event.target.classList.remove('active');
        }

        function filterOrders(status, btn) {
            // Update pills
            document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const rows = document.querySelectorAll('.order-row');
            let visibleCount = 0;
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            const noOrdersRow = document.getElementById('no-orders-row');
            if (noOrdersRow) noOrdersRow.style.display = visibleCount === 0 ? 'table-row' : 'none';
        }

        function toggleAllCheckboxes(source) {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(cb => {
                // Only check visible rows
                if (cb.closest('tr').style.display !== 'none') {
                    cb.checked = source.checked;
                }
            });
            updateBulkActions();
        }

        function updateBulkActions() {
            const checked = document.querySelectorAll('.order-checkbox:checked');
            const bar = document.getElementById('bulk-actions-bar');
            const countSpan = document.getElementById('selected-count');
            const idsInput = document.getElementById('bulk-order-ids');

            if (checked.length > 0) {
                bar.classList.add('active');
                countSpan.innerText = checked.length;
                idsInput.value = Array.from(checked).map(cb => cb.value).join(',');
            } else {
                bar.classList.remove('active');
            }
        }

        function filterTable(inputId, tableId) {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById(inputId);
            filter = input.value.toUpperCase();
            table = document.getElementById(tableId);
            tr = table.getElementsByTagName("tr");
            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (var j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        }

        function filterMenu(category, btn) {
            // Update pills
            document.querySelectorAll('#menu-filter-pills .filter-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const cards = document.querySelectorAll('#menuGrid .menu-card');
            cards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function filterReviews(filter, btn) {
            // Update pills
            document.querySelectorAll('#review-filter-pills .filter-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const cards = document.querySelectorAll('.reviews-grid .review-card');
            cards.forEach(card => {
                let show = false;
                if (filter === 'all') {
                    show = true;
                } else if (filter === 'pending') {
                    show = card.dataset.replied === 'false';
                } else if (filter === 'hidden') {
                    show = card.dataset.approved === 'false';
                } else { // Filter by rating
                    show = card.dataset.rating === filter;
                }

                card.style.display = show ? 'flex' : 'none';
            });
        }

        // Initialize Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Revenue (RM)',
                    data: <?php echo json_encode($chartValues); ?>,
                    borderColor: '#ff5100',
                    backgroundColor: 'rgba(255, 81, 0, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: {
                            color: '#aaa',
                            callback: function (value) { return 'RM ' + value; }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#a0aec0' }
                    }
                },
                plugins: {
                    legend: { display: false }
                },
                interaction: { intersect: false, mode: 'index' }
            }
        });

        function toggleDateRangePicker(btn) {
            const picker = document.getElementById('custom-date-picker-container');
            const isActive = picker.style.display === 'flex';
            if (isActive) {
                picker.style.display = 'none';
                btn.classList.remove('active');
            } else {
                picker.style.display = 'flex';
                document.querySelectorAll('.chart-filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            }
        }

        function updateChart(period, btn, startDate = null, endDate = null) {
            if (btn) {
                document.querySelectorAll('.chart-filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                // Hide custom date picker if a preset button is clicked
                if (period !== 'custom') {
                    document.getElementById('custom-date-picker-container').style.display = 'none';
                }
            }

            const formData = new FormData();
            formData.append('action', 'fetch_chart_data');
            formData.append('period', period);
            if (period === 'custom' && startDate && endDate) {
                formData.append('start_date', startDate);
                formData.append('end_date', endDate);
            }

            fetch('admin.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    salesChart.data.labels = data.labels;
                    salesChart.data.datasets[0].data = data.data;
                    salesChart.update();
                })
                .catch(err => console.error('Error updating chart:', err));
        }

        // Sparklines
        function createSparkline(canvasId, data) {
            const ctx = document.getElementById(canvasId).getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 50);
            gradient.addColorStop(0, 'rgba(255, 81, 0, 0.2)');
            gradient.addColorStop(1, 'rgba(255, 81, 0, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.from(Array(data.length).keys()), // Dummy labels
                    datasets: [{
                        data: data,
                        borderColor: '#ff5100',
                        borderWidth: 2,
                        fill: true,
                        backgroundColor: gradient,
                        tension: 0.4,
                        pointRadius: 0 // No dots
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { display: false },
                        x: { display: false }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    elements: {
                        line: {
                            borderJoinStyle: 'round'
                        }
                    }
                }
            });
        }

        // Reports Charts
        const ctxPie = document.getElementById('categoryPieChart');
        if (ctxPie) {
            new Chart(ctxPie.getContext('2d'), {
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
                    plugins: {
                        legend: { position: 'right', labels: { color: '#aaa', font: { size: 12 } } }
                    }
                }
            });
        }

        const ctxBar = document.getElementById('reportsBarChart');
        if (ctxBar) {
            new Chart(ctxBar.getContext('2d'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($trendLabels); ?>,
                    datasets: [{
                        label: 'Revenue (RM)',
                        data: <?php echo json_encode($trendRevenue); ?>,
                        borderColor: '#ff5100',
                        backgroundColor: 'rgba(255, 81, 0, 0.1)',
                        fill: true,
                        yAxisID: 'y'
                    }, {
                        label: 'Orders',
                        data: <?php echo json_encode($trendOrders); ?>,
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#a0aec0' } },
                        y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#10b981' } },
                        x: { grid: { display: false }, ticks: { color: '#a0aec0' } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        // Consistent Color Mapping for Categories
        const categoryColorMap = {
            'burger': '#ff5100',      // Main Orange
            'special': '#9b59b6',     // Purple
            'add-on': '#2ecc71',       // Green
            'minuman': '#3498db',     // Blue
            'uncategorized': '#95a5a6' // Grey
        };
        const defaultColor = '#bdc3c7'; // A fallback color for new categories

        // === DAILY SALES DONUT CHART LOGIC ===
        let dailySalesChart;

        function initializeDailySalesChart() {
            const ctx = document.getElementById('dailySalesDonutChart').getContext('2d');
            dailySalesChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Sales',
                        data: [],
                        backgroundColor: [], // Will be populated dynamically
                        borderColor: '#1e1e1e',
                        borderWidth: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#aaa', font: { size: 12 }, padding: 15 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const value = context.raw;
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                                    return ` ${context.label}: RM ${value.toFixed(2)} (${percentage})`;
                                }
                            }
                        }
                    }
                }
            });
        }

        async function fetchDailySales(date) {
            try {
                const response = await fetch(`get_daily_sales_data.php?date=${date}`);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const data = await response.json();
                updateDailySalesUI(data);
            } catch (error) {
                console.error('Failed to fetch daily sales data:', error);
                document.getElementById('daily-total-sales').innerText = 'Error';
                document.getElementById('daily-total-quantity').innerText = 'Error';
                document.getElementById('hot-items-list').innerHTML = '<p style="color:red;">Failed to load data.</p>';
            }
        }

        function updateDailySalesUI(data) {
            document.getElementById('daily-total-sales').innerText = `RM ${data.totalSales.toFixed(2)}`;
            document.getElementById('daily-total-quantity').innerText = data.totalQuantity;

            const hotItemsList = document.getElementById('hot-items-list');
            hotItemsList.innerHTML = '';
            if (data.hotItems.length > 0) {
                let listHtml = '<table class="admin-table" style="margin:0;">';
                data.hotItems.forEach((item, index) => {
                    const icon = index === 0 ? '🥇' : (index === 1 ? '🥈' : (index === 2 ? '🥉' : '🔥'));
                    listHtml += `
                <tr style="background:none;">
                    <td style="padding: 12px 0; border-color: #333;">${icon} ${item.item_name}</td>
                    <td style="padding: 12px 0; text-align:right; border-color: #333; font-weight:bold;">${item.quantity_sold} sold</td>
                </tr>
            `;
                });
                listHtml += '</table>';
                hotItemsList.innerHTML = listHtml;
            } else {
                hotItemsList.innerHTML = `
            <div class="empty-state" style="padding: 40px 0;">
                <div class="empty-icon">🤷</div>
                <p>No items sold on this day.</p>
            </div>`;
            }

            const noDataEl = document.getElementById('donut-no-data');
            if (data.categoryData.data.length > 0) {
                noDataEl.style.display = 'none';
                dailySalesChart.data.labels = data.categoryData.labels;
                dailySalesChart.data.datasets[0].data = data.categoryData.data;
                // Dynamically assign colors based on labels
                dailySalesChart.data.datasets[0].backgroundColor = data.categoryData.labels.map(label => categoryColorMap[label.toLowerCase()] || defaultColor);
                dailySalesChart.update();
            } else {
                noDataEl.style.display = 'flex';
                dailySalesChart.data.labels = [];
                dailySalesChart.data.datasets[0].data = [];
                dailySalesChart.update();
            }
        }

        // Dashboard Main Donut Chart (Sales by Category)
        const ctxDonut = document.getElementById('categoryDonutChart');
        if (ctxDonut) {
            const donutLabels = <?php echo json_encode($pieLabels); ?>;
            // Dynamically assign colors based on labels from PHP
            const donutColors = donutLabels.map(label => categoryColorMap[label.toLowerCase()] || defaultColor);

            new Chart(ctxDonut.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: donutLabels,
                    datasets: [{
                        label: 'Sales',
                        data: <?php echo json_encode($pieData); ?>,
                        backgroundColor: donutColors,
                        borderColor: '#1e1e1e',
                        borderWidth: 4,
                        hoverBorderColor: '#333'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#aaa', font: { size: 12 }, padding: 20, usePointStyle: true, pointStyle: 'rectRounded' }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const value = context.raw;
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                                    return ` ${context.label}: RM ${value.toFixed(2)} (${percentage})`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Dummy data for sparklines
        createSparkline('sparkline-sales', [5, 10, 8, 15, 12, 18, 17]);
        createSparkline('sparkline-orders', [2, 3, 2, 5, 4, 6, 5]);
        createSparkline('sparkline-reviews', [1, 0, 2, 1, 3, 1, 2]);
        createSparkline('sparkline-revenue', [100, 150, 130, 200, 180, 250, 240]);

        document.addEventListener('DOMContentLoaded', function () {
            const toast = document.getElementById('toast-message');
            if (toast) {
                // Show toast
                setTimeout(() => {
                    toast.classList.add('show');
                }, 100); // small delay

                // Hide after 5 seconds
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 5100);
            }

            // Initialize Daily Sales Chart
            const datePicker = document.getElementById('sales-date-picker');
            if (datePicker) {
                initializeDailySalesChart();
                fetchDailySales(datePicker.value); // Initial load

                datePicker.addEventListener('change', (event) => {
                    fetchDailySales(event.target.value);
                });

                setInterval(() => {
                    fetchDailySales(datePicker.value);
                }, 30000); // Auto-update every 30 seconds
            }

            // Initialize Sales Analytics Date Range Picker
            const startDateInput = document.getElementById('chart-start-date');
            const endDateInput = document.getElementById('chart-end-date');
            if (startDateInput && endDateInput) {
                const updateFromRange = () => {
                    if (startDateInput.value && endDateInput.value) {
                        // The button is the calendar icon, which should be active
                        updateChart('custom', document.querySelector('.chart-filter-btn[title="Select Date Range"]'), startDateInput.value, endDateInput.value);
                    }
                };
                startDateInput.addEventListener('change', updateFromRange);
                endDateInput.addEventListener('change', updateFromRange);
            }
        });

        // Auto-switch to section view if URL param is present
        const urlParams = new URLSearchParams(window.location.search);
        const viewParam = urlParams.get('view');
        const savedView = localStorage.getItem('adminActiveView');
        const initialView = viewParam || savedView || 'dashboard';

        const navItem = document.querySelector(`[data-view="${initialView}"]`);
        if (navItem) {
            switchView(initialView, navItem);
        }

        function filterGlobal(query) {
            const upperQuery = query.toUpperCase();
            // In a real app, you'd likely want to switch to the relevant tab
            // and then filter. For this demo, we'll just filter the active view.
            const activeViewId = document.querySelector('.view-section.active').id;

            if (activeViewId === 'view-orders') {
                filterTable('searchOrders', 'ordersTable');
            } else if (activeViewId === 'view-staff') {
                filterTable('searchStaff', 'staffTable');
            }
        }
        // STEP 2: AJAX Toggle Handler with Force Alert
        $(document).on('change', '#store-toggle', function () {
            const status = $(this).is(':checked') ? 'open' : 'closed';
            $.post('update_status.php', { status: status }, function (response) {
                alert("Server says: " + response);
                if (response.includes("SUCCESS")) {
                    const label = $('#status-label');
                    if (status === 'open') {
                        label.text("STORE IS OPEN").css('color', '#2ecc71');
                    } else {
                        label.text("STORE IS CLOSED").css('color', '#e74c3c');
                    }
                }
            }).fail(function (xhr) {
                alert("AJAX Error: " + xhr.statusText);
            });
        });
    </script>

</body>

</html>