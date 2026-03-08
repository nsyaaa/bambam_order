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
</head>
<body>

<h1>Welcome Admin <?php echo $_SESSION['admin_name']; ?></h1>

<p> Dashboard admin Bambam Burger 🍔</p>

<a href="admin_logout.php">Logout</a>

</body>
</html>
