<?php
// ===============================
// Bambam Burger - Reset Password
// ===============================
include 'header.php';
include 'db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_GET['token'])){
    echo "<script>alert('Invalid request!');window.location='login.php';</script>";
    exit;
}

$token = $_GET['token'];

try {
    $stmt = $pdo->prepare("SELECT id, name, reset_expire FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if(!$user){
        echo "<script>alert('Invalid token or link already used!');window.location='login.php';</script>";
        exit;
    }

    if(strtotime($user['reset_expire']) < time()){
        // UPDATED LINK NAME HERE
        echo "<script>alert('Token expired!');window.location='forgotpass.php';</script>";
        exit;
    }

    $user_id = $user['id'];

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<style>
body { background: url('images/wall.png') no-repeat center center fixed; background-size: cover; }
.login-container {
    background: white; max-width: 400px; margin: 120px auto 60px; padding: 40px;
    border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    text-align: center; border: 2px solid #ff5100;
}
.login-container h2 { color: #ff5100; margin-bottom: 30px; font-size: 32px; text-transform: uppercase; margin-top: 0; }
.input-group { margin-bottom: 15px; text-align: left; }
.input-group input {
    width: 100%; padding: 15px; border: 1px solid #ccc; border-radius: 50px;
    font-size: 16px; outline: none; background: #f9f9f9;
}
.btn-login {
    width: 100%; padding: 15px; background: #ff5100; color: white; border: none;
    border-radius: 50px; font-size: 18px; font-weight: bold; cursor: pointer; transition: 0.3s;
}
.btn-login:hover { background: #e04600; transform: scale(1.02); }
.switch-link { margin-top: 25px; color: #666; font-size: 14px; }
.switch-link a { color: #ff5100; text-decoration: none; font-weight: bold; }
</style>

<div class="login-container">
    <h2>Reset Password</h2>
    <p>Hi, <strong><?php echo htmlspecialchars($user['name']); ?></strong>. Enter your new password.</p>
    <form method="POST">
        <div class="input-group">
            <input type="password" name="new_pass" placeholder="Enter New Password" required>
        </div>
        <div class="input-group">
            <input type="password" name="confirm_pass" placeholder="Confirm Password" required>
        </div>
        <button type="submit" class="btn-login">Set New Password</button>
    </form>
    <div class="switch-link">Back to <a href="login.php">Login</a></div>
</div>

<?php
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $new = $_POST['new_pass'];
    $confirm = $_POST['confirm_pass'];

    if($new !== $confirm){
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        try {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expire = NULL WHERE id = ?");
            $update->execute([$hash, $user_id]);

            echo "<script>alert('Password updated successfully!');window.location='login.php';</script>";
            exit;
        } catch (PDOException $e) {
            die("Update Error: " . $e->getMessage());
        }
    }
}
?>
<?php include 'footer.php'; ?>