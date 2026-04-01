<?php
include 'db.php';

echo "<h2>Database Update Status</h2>";

function addColumn($pdo, $table, $column, $definition) {
    try {
        $pdo->exec("ALTER TABLE $table ADD COLUMN $column $definition");
        echo "<p style='color:green'>✅ Added column <strong>$column</strong> to table <strong>$table</strong>.</p>";
    } catch (PDOException $e) {
        // Error 42S21 means column already exists
        if ($e->getCode() == '42S21') {
            echo "<p style='color:orange'>ℹ️ Column <strong>$column</strong> already exists in <strong>$table</strong>.</p>";
        } else {
            echo "<p style='color:red'>❌ Error adding <strong>$column</strong>: " . $e->getMessage() . "</p>";
        }
    }
}

addColumn($pdo, 'orders', 'user_id', 'INT DEFAULT NULL AFTER id');
addColumn($pdo, 'orders', 'branch', "VARCHAR(50) DEFAULT 'Main' AFTER user_id");
addColumn($pdo, 'orders', 'order_type', "VARCHAR(50) DEFAULT 'Dine-in' AFTER branch");
addColumn($pdo, 'orders', 'customer_phone', "VARCHAR(20) DEFAULT NULL AFTER customer_name");
addColumn($pdo, 'orders', 'receipt_img', "VARCHAR(255) DEFAULT NULL");
addColumn($pdo, 'orders', 'payment_status', "VARCHAR(20) DEFAULT 'Pending'");
addColumn($pdo, 'orders', 'paid_at', "TIMESTAMP NULL DEFAULT NULL");
addColumn($pdo, 'order_items', 'protein', "VARCHAR(50) DEFAULT NULL AFTER item_name");
addColumn($pdo, 'order_items', 'customization', "TEXT DEFAULT NULL");
addColumn($pdo, 'menu_items', 'has_protein', "TINYINT(1) DEFAULT 0");

echo "<p><strong>Database update complete. You can now place orders.</strong></p>";
?>