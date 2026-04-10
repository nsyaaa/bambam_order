<?php
session_start();

// block kalau bukan admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        /* Sidebar Fix */
        .sidebar {
            height: 100vh !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        /* Cleaner scrollbar for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #444; 
            border-radius: 10px;
        }

        /* This is the styling for your scrollable area */
        .scroll-container {
            width: 80%;
            height: 400px; /* Fixed height is required for a scrollbar to appear */
            overflow-y: auto; /* Adds vertical scrollbar only when needed */
            border: 2px solid #333;
            padding: 20px;
            background-color: #f9f9f9;
            margin-top: 20px;
        }

        .content-block {
            height: 1000px; /* Example long content to force scrolling */
            background: linear-gradient(white, #ffd700); /* Just to visualize the length */
        }
    </style>
</head>
<body>

    <style>
        /* This targets almost any sidebar layout */
        aside, .sidebar, .sidebar-wrapper, .nav-container {
            height: 100vh !important;       /* Forces it to be screen height */
            position: fixed !important;     /* Keeps it stuck to the left */
            top: 0;
            left: 0;
            overflow-y: auto !important;    /* Forces the scrollbar */
            overflow-x: hidden !important;
            display: flex !important;
            flex-direction: column !important;
        }

        /* This makes the scrollbar visible even if your system hides them */
        .sidebar::-webkit-scrollbar {
            width: 8px !important;
            display: block !important;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #ff5722 !important; /* Matches your orange theme */
            border-radius: 10px;
        }
    </style>

    <h1>Welcome Admin <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h1>
    <p>Dashboard admin Bambam Burger 🍔</p>

    <div class="scroll-container">
        <h3>Manage Orders / Data</h3>
        <div class="content-block">
            <p>Your long list of orders or database content will go here...</p>
            <p>(Scroll down to see the effect!)</p>
        </div>
    </div>

    <br>
    <a href="admin_logout.php">Logout</a>

</body>
</html>
