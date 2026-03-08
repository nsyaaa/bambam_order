<?php
// ===============================
// Bambam Burger - Signup Page
// ===============================
include 'db.php';

// ===============================
// SIGNUP LOGIC (MOVED UP)
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $gmail = trim($_POST['gmail']);
    $pass  = password_hash($_POST['pass'], PASSWORD_DEFAULT);

    try {
        // Check if Gmail exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE gmail = ?");
        $stmt->execute([$gmail]);
        
        if ($stmt->fetch()) {
            echo "<!DOCTYPE html><html><body><script>alert('Gmail already registered. Please login.');window.location='login.php';</script></body></html>";
            exit;
        } else {
            // Insert user
            $sql = "INSERT INTO users (name, phone, gmail, password) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $phone, $gmail, $pass]);
            echo "<!DOCTYPE html><html><body><script>alert('Account created successfully! Please login.');window.location='login.php';</script></body></html>";
            exit;
        }
    } catch (PDOException $e) {
        die("Registration Error: " . $e->getMessage());
    }
}

include 'header.php';
?>

<style>
.login-container {
    background: #181818; max-width: 400px; margin: 120px auto 60px; padding: 40px;
    border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    text-align: center; border: 1px solid #333;
}
.login-container h2 { color: #ff5100; margin-bottom: 30px; font-size: 32px; text-transform: uppercase; margin-top: 0; }
.input-group { margin-bottom: 15px; text-align: left; }
.input-group input {
    width: 100%; padding: 15px; border: 1px solid #444; border-radius: 8px;
    font-size: 16px; outline: none; background: #222; color: white;
}
.btn-login {
    width: 100%; padding: 15px; background: #ff5100; color: white; border: none;
    border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; transition: 0.3s;
}
.btn-login:hover { background: #e04600; transform: scale(1.02); }
.switch-link { margin-top: 20px; color: #aaa; font-size: 14px; }
.switch-link a { color: #ff5100; text-decoration: none; font-weight: bold; }
</style>

<div class="login-container">
    <h2>Sign Up</h2>
    <form method="POST">
        <div class="input-group"><input type="text" name="name" placeholder="Full Name" required></div>
        <div class="input-group">
            <input type="tel" name="phone" placeholder="Phone Number" required oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>
        <div class="input-group"><input type="email" name="gmail" placeholder="Gmail" required></div>
        <div class="input-group"><input type="password" name="pass" placeholder="Password" required></div>
        <button type="submit" class="btn-login">Create Account</button>
    </form>
    <div class="switch-link">Already have an account? <a href="login.php">Login here</a></div>
</div>

<?php include 'footer.php'; ?>
