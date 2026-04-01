<?php
session_start();

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: staff_login.php");
    exit;
}

// Handle Login
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';

    // Password ikut branch
    $branches = [
        [
            'id' => 1,
            'name' => 'Kangar',
            'password' => 'kangar1234'
        ],
        [
            'id' => 2,
            'name' => 'Arau',
            'password' => 'arau1234'
        ],
        [
            'id' => 3,
            'name' => 'Jejawi',
            'password' => 'jejawi1234'
        ],
        [
            'id' => 4,
            'name' => 'Kuala Perlis',
            'password' => 'kualaperlis1234'
        ],
        [
            'id' => 5,
            'name' => 'Beseri',
            'password' => 'beseri1234'
        ]
    ];

    $matchedBranch = null;

    foreach ($branches as $branch) {
        if ($password === $branch['password']) {
            $matchedBranch = $branch;
            break;
        }
    }

    if ($matchedBranch) {
        $_SESSION['staff_logged_in'] = true;
        $_SESSION['branch_id'] = $matchedBranch['id'];
        $_SESSION['branch_name'] = $matchedBranch['name'];

        header("Location: staff_dashboard.php");
        exit;
    } else {
        $error = "Invalid Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Portal - Bambam Burger</title>
<style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #1a1a1a;
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .login-box {
        background: #2d2d2d;
        padding: 40px;
        border-radius: 12px;
        text-align: center;
        width: 100%;
        max-width: 350px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        border-top: 4px solid #ff5100;
    }
    .login-box h2 { color: #ff5100; margin-top: 0; text-transform: uppercase; letter-spacing: 1px; font-size: 24px; }
    .login-box p { color: #aaa; font-size: 14px; margin-bottom: 30px; }
    .login-box input {
        width: 100%; padding: 12px; margin: 10px 0;
        border: 1px solid #444; border-radius: 6px;
        background: #3a3a3a; color: white; box-sizing: border-box;
        outline: none; transition: border-color 0.3s;
    }
    .login-box input:focus { border-color: #ff5100; }
    .login-box button {
        width: 100%; padding: 12px; background: #ff5100;
        color: white; border: none; border-radius: 6px;
        font-weight: bold; cursor: pointer; margin-top: 10px;
        transition: background 0.3s;
    }
    .login-box button:hover { background: #e04600; }
    .error { background: rgba(255, 68, 68, 0.1); color: #ff4444; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; border: 1px solid #ff4444; }
    .back-link { display: block; margin-top: 25px; color: #666; text-decoration: none; font-size: 13px; transition: color 0.3s; }
    .back-link:hover { color: #fff; }
</style>
</head>
<body>

<div class="login-box">
    <h2>Staff Portal</h2>
    <p>Authorized Personal Only</p>

    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="password" name="password" placeholder="Enter Access Code" required>
        <button type="submit">Access Dashboard</button>
    </form>
    
    <div style="font-size:12px; color:#555; margin-top:20px;">Enter your branch access code</div>
</div>

</body>
</html>