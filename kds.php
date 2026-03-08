<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'db.php';

// Check if user is admin or staff
$isAllowed = false;
if (isset($_SESSION['admin_id'])) {
    $isAllowed = true;
} elseif (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userRole = $stmt->fetchColumn();
        if (in_array($userRole, ['admin', 'staff'])) {
            $isAllowed = true;
        }
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}

if (!$isAllowed) {
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔥 Kitchen Display System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #34495e; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 20px; }
        h1 { color: white; text-align: center; margin-bottom: 20px; }
        .kitchen-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .kitchen-card { background: white; border-radius: 10px; padding: 15px; border-left: 8px solid #ccc; box-shadow: 0 4px 10px rgba(0,0,0,0.2); display: flex; flex-direction: column; }
        .kitchen-card.status-pending { border-left-color: #e67e22; }
        .kitchen-card.status-preparing { border-left-color: #3498db; }
        .kitchen-card h4 { margin: 0 0 10px 0; display: flex; justify-content: space-between; font-size: 1.5rem; }
        .kitchen-items { margin: 10px 0; font-size: 1rem; line-height: 1.5; flex-grow: 1; }
        .kitchen-items div { border-bottom: 1px solid #f0f0f0; padding: 5px 0; }
        .kitchen-btn { width: 100%; padding: 15px; border: none; border-radius: 8px; font-weight: bold; font-size: 1rem; cursor: pointer; margin-top: 10px; color: white; transition: background 0.2s; }
        .kitchen-btn:hover { transform: translateY(-2px); }
        .no-orders { color: #95a5a6; text-align: center; font-size: 1.5rem; padding: 50px; }
        .timer { font-size: 12px; color: #777; background: #eee; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>

    <h1><i class="fas fa-fire"></i> Live Kitchen Monitor</h1>

    <div id="kitchen-grid">
        <!-- Orders will be loaded here by AJAX -->
    </div>

<script>
    const kitchenGrid = document.getElementById('kitchen-grid');

    // Function to calculate and display time since order
    function timeSince(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        let interval = seconds / 31536000;
        if (interval > 1) return Math.floor(interval) + " years";
        interval = seconds / 2592000;
        if (interval > 1) return Math.floor(interval) + " months";
        interval = seconds / 86400;
        if (interval > 1) return Math.floor(interval) + " days";
        interval = seconds / 3600;
        if (interval > 1) return Math.floor(interval) + " hours";
        interval = seconds / 60;
        if (interval > 1) return Math.floor(interval) + " mins";
        return Math.floor(seconds) + " secs";
    }

    async function fetchAndRenderOrders() {
        try {
            const response = await fetch('get_kds_orders.php');
            if (!response.ok) throw new Error('Network response was not ok');
            const orders = await response.json();

            // Clear current grid
            kitchenGrid.innerHTML = '';

            if (orders.length === 0) {
                kitchenGrid.innerHTML = `<p class="no-orders">No active orders. Well done!</p>`;
                return;
            }

            orders.forEach(order => {
                const card = document.createElement('div');
                card.className = `kitchen-card status-${order.status.toLowerCase()}`;

                const orderTime = new Date(order.created_at);
                const timeAgo = timeSince(orderTime);

                let itemsHtml = '';
                order.items.forEach(item => {
                    const note = item.customization ? `<div style="color:red; font-size:12px; padding-left:10px;">Note: ${item.customization}</div>` : '';
                    itemsHtml += `<div>• <b>${item.qty}x</b> ${item.item_name} <small>${item.variant || ''}</small>${note}</div>`;
                });

                const formAction = order.status === 'Pending'
                    ? `<input type="hidden" name="new_status" value="Preparing"><button class="kitchen-btn" style="background:#e67e22;">Start Cooking</button>`
                    : `<input type="hidden" name="new_status" value="Ready"><button class="kitchen-btn" style="background:#2ecc71;">Mark Ready</button>`;

                card.innerHTML = `
                    <h4>#${order.id} <span class="timer">${timeAgo} ago</span></h4>
                    <div style="font-weight:bold; color:#555; font-size:12px; margin-bottom:5px;">${order.order_type}</div>
                    <div class="kitchen-items">${itemsHtml}</div>
                    <form class="update-status-form">
                        <input type="hidden" name="action" value="update_order_status">
                        <input type="hidden" name="order_id" value="${order.id}">
                        ${formAction}
                    </form>
                `;
                kitchenGrid.appendChild(card);
            });

            // Add event listeners to new forms
            document.querySelectorAll('.update-status-form').forEach(form => {
                form.addEventListener('submit', handleStatusUpdate);
            });

        } catch (error) {
            console.error('Failed to fetch orders:', error);
            kitchenGrid.innerHTML = `<p class="no-orders" style="color: #e74c3c;">Error fetching orders. Check connection.</p>`;
        }
    }

    async function handleStatusUpdate(event) {
        event.preventDefault();
        const form = event.target;
        const button = form.querySelector('button');
        button.disabled = true;
        button.innerText = 'Updating...';

        const formData = new FormData(form);

        try {
            await fetch('admin.php', { method: 'POST', body: formData });
            // Refresh the view immediately after successful update
            fetchAndRenderOrders();
        } catch (error) {
            console.error('Failed to update status:', error);
            button.disabled = false;
            button.innerText = 'Try Again';
        }
    }

    // Initial load and set interval to poll every 5 seconds
    document.addEventListener('DOMContentLoaded', () => {
        fetchAndRenderOrders();
        setInterval(fetchAndRenderOrders, 5000);
    });
</script>

</body>
</html>