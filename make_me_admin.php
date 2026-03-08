<?php
include 'db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE gmail = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        // Update role to admin
        $update = $pdo->prepare("UPDATE users SET role = 'admin' WHERE gmail = ?");
        $update->execute([$email]);
        $message = "<div style='color:green; margin-bottom:15px;'>Success! <b>$email</b> is now an Admin. <a href='admin_login.php'>Login Here</a></div>";
    } else {
        $message = "<div style='color:red; margin-bottom:15px;'>Error: Email <b>$email</b> not found in database.</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Promote to Admin</title></head>
<body style="font-family:sans-serif; padding:50px; text-align:center; background:#f4f4f4;">
    <h2>Promote User to Admin</h2>
    <?php echo $message; ?>
    <form method="POST" style="background:white; padding:30px; border-radius:10px; display:inline-block; box-shadow:0 5px 15px rgba(0,0,0,0.1);">
        <label>Enter User Email:</label><br>
        <input type="email" name="email" placeholder="e.g. lunaa@gmail.com" required style="padding:10px; width:250px; margin:10px 0; border:1px solid #ccc; border-radius:5px;"><br>
        <button type="submit" style="padding:10px 20px; background:#ff5100; color:white; border:none; cursor:pointer; border-radius:5px; font-weight:bold;">Make Admin</button>
    </form>
</body>
</html>