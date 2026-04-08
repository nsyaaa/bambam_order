<?php
// ===============================
// Bambam Burger - Login Page
// ===============================
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    echo "<script>
        localStorage.removeItem('user_logged_in');
        localStorage.removeItem('current_user_name');
        localStorage.removeItem('current_user_phone');
        sessionStorage.clear();
        window.location.replace('login.php');
    </script>";
    exit;
}

include 'db.php';

// ===============================
// LOGIN LOGIC (MOVED UP TO FIX LOADER ISSUE)
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gmail = trim($_POST['gmail']);
    $pass  = $_POST['pass'];

    try {
        $stmt = $pdo->prepare(
            "SELECT id, name, password, phone, role 
             FROM users 
             WHERE gmail = ?"
        );
        $stmt->execute([$gmail]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {

            // 🚫 BLOCK ADMIN FROM CUSTOMER LOGIN
            if ($user['role'] === 'admin') {
                session_unset();
                session_destroy();
                echo "<!DOCTYPE html><html><body><script>
                    alert('Admin account detected. Please login via Admin Portal.');
                    window.location.href = 'admin_login.php';
                </script></body></html>";
                exit;
            }

            // ✅ CUSTOMER LOGIN
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $gmail;

            // Safe JS encoding
            $jsName = json_encode($user['name']);
            $jsPhone = json_encode($user['phone']);

            echo "<!DOCTYPE html><html><body><script>
                localStorage.setItem('user_logged_in', 'true');
                localStorage.setItem('current_user_name', $jsName);
                localStorage.setItem('current_user_phone', $jsPhone);
                window.location.href = 'branch_selection.php';
            </script></body></html>";
            exit;

        } else {
            $error_msg = "Invalid Gmail or Password";
        }

    } catch (PDOException $e) {
        die('Login Error: ' . $e->getMessage());
    }
}

include 'header.php';
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
.login-container {
    background: #181818; max-width: 400px; margin: 120px auto 60px; padding: 40px;
    border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    text-align: center; border: 1px solid #333;
}
.login-container h2 { color: #ff5100; margin-bottom: 30px; font-size: 32px; text-transform: uppercase; margin-top: 0; }
.input-group { margin-bottom: 15px; text-align: left; position: relative; }
.input-group input {
    width: 100%; padding: 15px; border: 1px solid #444; border-radius: 8px;
    font-size: 16px; outline: none; background: #222; color: white;
}
.toggle-password { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; }
.forgot-pass { display: block; text-align: right; color: #aaa; font-size: 14px; text-decoration: none; margin-bottom: 25px; margin-right: 10px; }
.btn-login {
    width: 100%; padding: 15px; background: #ff5100; color: white; border: none;
    border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; transition: 0.3s;
}
.btn-login:hover { background: #e04600; transform: scale(1.02); }
.switch-link { margin-top: 25px; color: #aaa; font-size: 14px; }
.switch-link a { color: #ff5100; text-decoration: none; font-weight: bold; }
</style>

<div class="login-container">
    <h2>Login</h2>
    <?php if(isset($error_msg)) echo "<script>alert('$error_msg');</script>"; ?>
    <form method="POST">
        <div class="input-group">
            <input type="email" name="gmail" placeholder="Gmail" required>
        </div>
        <div class="input-group">
            <input type="password" name="pass" placeholder="Password" required style="padding-right: 40px;">
            <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility(this)"></i>
        </div>
        <a href="forgotpass.php" class="forgot-pass">Forgot Password?</a>
        <button type="submit" class="btn-login">Login</button>
    </form>
    <div class="switch-link">
        Don't have an account? <a href="signup.php">Sign Up</a>
    </div>
</div>

<script>
function togglePasswordVisibility(icon){
    const input = icon.previousElementSibling;
    if(input.type==='password'){
        input.type='text';
        icon.classList.replace('fa-eye','fa-eye-slash');
    } else {
        input.type='password';
        icon.classList.replace('fa-eye-slash','fa-eye');
    }
}
</script>

<?php include 'footer.php'; ?>
