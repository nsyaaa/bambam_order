<?php
// ===============================
// Bambam Burger - Forgot Pass (Full Fixed Version)
// ===============================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/vendor/autoload.php')) { require_once __DIR__ . '/vendor/autoload.php'; }

if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'db.php';
include 'header.php'; // Included at top so design shows up

$msg = ""; 
$debug_info = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $gmail = trim($_POST['gmail']);

        try {
            // 1. Check user
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE gmail = ?");
            $stmt->execute([$gmail]);
            $user = $stmt->fetch();

            if (!$user) {
                $msg = "<span style='color:red'>Email not found in our system.</span>";
            } else {
                // 2. Token generation
                $token = bin2hex(random_bytes(16));
                $expire = date("Y-m-d H:i:s", strtotime("+30 minutes"));

                // 3. Update DB
                $stmt2 = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expire = ? WHERE id = ?");
                $stmt2->execute([$token, $expire, $user['id']]);

                // 4. Send Email
                try {
                    $mail = new PHPMailer(true);

                    // --- PREVENT INFINITE LOADING ---
                    $mail->Timeout = 10;
                    $mail->SMTPConnectTimeout = 10;
                    
                    // --- DEBUG SETTINGS ---
                    $mail->SMTPDebug = 2; 
                    $mail->Debugoutput = function($str, $level) { $GLOBALS['debug_info'] .= $str . "<br>"; };

                    // --- SERVER SETTINGS ---
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com'; 
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $smtpUser;
                    $mail->Password   = str_replace(' ', '', $smtpPass); // Auto-fix spaces in App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );

                    // --- RECIPIENTS ---
                    $mail->setFrom($smtpUser, 'BamBam Burger');
                    $mail->addAddress($gmail, $user['name']);

                    // --- CONTENT ---
                    $mail->isHTML(true);
                    $mail->Subject = 'Reset Your Password';
                    
                    $path = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
                    $link = "http://" . $_SERVER['HTTP_HOST'] . rtrim($path, '/') . "/resetpass.php?token=$token";

                    $mail->Body = "Hi " . htmlspecialchars($user['name']) . ",<br><br>Click the link below to reset your password:<br><a href='$link'>$link</a><br><br>This link expires in 30 minutes.";

                    $mail->send();
                    $msg = "<span style='color:green'><b>Success!</b> Reset link sent to your Gmail.</span>";
                } catch (Throwable $e) {
                    $msg = "<span style='color:red'><b>Mailer Error:</b> " . $e->getMessage() . "</span>";
                }
            }
        } catch (PDOException $e) {
            $msg = "<span style='color:red'>Database Error: " . $e->getMessage() . "</span>";
        }
    } else {
        $msg = "<span style='color:red'>Email library missing. Run 'composer require phpmailer/phpmailer'</span>";
    }
}
?>

<style>
body { background: url('images/wall.png') no-repeat center center fixed; background-size: cover; }
.login-container {
    background: white; max-width: 400px; margin: 80px auto; padding: 40px;
    border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    text-align: center; border: 2px solid #ff5100;
}
.login-container h2 { color: #ff5100; margin-bottom: 20px; font-size: 32px; text-transform: uppercase; }
.input-group { margin-bottom: 15px; }
.input-group input {
    width: 100%; padding: 15px; border: 1px solid #ccc; border-radius: 50px; font-size: 16px; outline: none; background: #f9f9f9;
}
.btn-login {
    width: 100%; padding: 15px; background: #ff5100; color: white; border: none;
    border-radius: 50px; font-size: 18px; font-weight: bold; cursor: pointer;
}
.debug-box {
    background: #000; color: #0f0; text-align: left; padding: 15px; 
    margin: 20px auto; max-width: 800px; font-family: monospace; font-size: 12px;
    border-radius: 10px; border: 2px solid white;
}
</style>

<div class="login-container">
    <h2>Forgot Password</h2>
    
    <?php if(!empty($msg)): ?>
        <div style="background: #fff3cd; border: 1px solid #ffeeba; padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #856404;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group"><input type="email" name="gmail" placeholder="Enter your Gmail" required></div>
        <button type="submit" class="btn-login">Send Reset Link</button>
    </form>
    <div style="margin-top:25px;">Back to <a href="login.php" style="color: #ff5100; font-weight:bold; text-decoration:none;">Login</a></div>
</div>

<?php if(!empty($debug_info)): ?>
    <div class="debug-box">
        <strong>Technical Logs:</strong><br><br>
        <?php echo $debug_info; ?>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>