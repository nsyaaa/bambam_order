<?php
// ===============================
// Bambam Burger - Admin Login Page
// ===============================
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php';

// Redirect if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit;
}

$error = '';

// Handle POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gmail = trim($_POST['gmail']);
    $pass  = $_POST['pass'];

    try {
        $stmt = $pdo->prepare(
            "SELECT id, name, password 
             FROM users 
             WHERE gmail = ? AND role = 'admin'"
        );
        $stmt->execute([$gmail]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($pass, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];

            // Update last login timestamp
            try {
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$admin['id']]);
            } catch (PDOException $e) {
                // Fail silently if this fails, login is more important
            }
            header("Location: admin.php");
            exit;
        } else {
            $error = "Invalid admin login credentials";
        }

    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Bambam Burger</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
/* ===== CSS ORI KAU ===== */
body { 
    margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    background: #1a1a1a; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh;
}
.login-container {
    background: #2d2d2d; padding: 40px; border-radius: 12px; text-align: center;
    width: 100%; max-width: 350px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border-top: 4px solid #ff5100;
}
.login-container h2 { color: #ff5100; margin-top: 0; text-transform: uppercase; letter-spacing: 1px; font-size: 24px; margin-bottom: 30px; }
.input-group { margin-bottom: 15px; position: relative; }
.input-group input {
    width: 100%; padding: 12px; border: 1px solid #444; border-radius: 6px;
    background: #3a3a3a; color: white; box-sizing: border-box; outline: none; transition: border-color 0.3s;
}
.input-group input:focus { border-color: #ff5100; }
.toggle-password { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #aaa; }
.btn-login {
    width: 100%; padding: 12px; background: #ff5100; color: white; border: none; border-radius: 6px;
    font-weight: bold; cursor: pointer; margin-top: 10px; transition: background 0.3s;
}
.btn-login:hover { background: #e04600; }
.back-link { display: block; margin-top: 25px; color: #666; text-decoration: none; font-size: 13px; transition: color 0.3s; }
.back-link:hover { color: #fff; }
.error-msg { color: #ff5555; margin-bottom: 15px; font-size: 14px; }
</style>
</head>
<body>

<div class="login-container">
    <h2>Admin Portal</h2>
    <?php if($error) echo "<div class='error-msg'>$error</div>"; ?>
    <form method="POST">
        <div class="input-group"><input type="email" name="gmail" placeholder="Admin Email" required></div>
        <div class="input-group">
            <input type="password" name="pass" placeholder="Password" required>
            <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility(this)"></i>
        </div> 
        <button type="submit" class="btn-login">Login to Dashboard</button>
    </form>
    <a href="index.php" class="back-link">&larr; Back to Website</a>
</div>

<script>
function togglePasswordVisibility(icon){
    const input = icon.previousElementSibling;
    if(input.type==='password'){
        input.type='text'; icon.classList.replace('fa-eye','fa-eye-slash');
    } else {
        input.type='password'; icon.classList.replace('fa-eye-slash','fa-eye');
    }
}
</script>

</body>
</html>
