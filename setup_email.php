<?php
// setup_email.php - Run this to test PHPMailer installation

echo "<h2>PHPMailer Installation & SMTP Test</h2>";

// 1. Check if vendor folder exists
if (!file_exists('vendor/autoload.php')) {
    die("<p style='color:red'>❌ Error: 'vendor/autoload.php' not found.<br>Please run <code>composer require phpmailer/phpmailer</code> in your terminal.</p>");
}

echo "<p style='color:green'>✅ 'vendor/autoload.php' found. PHPMailer is installed.</p>";

require 'vendor/autoload.php';
include 'db.php'; // Loads your SMTP settings ($smtpHost, $smtpUser, etc.)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;
    $mail->Password   = $smtpPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $smtpPort;

    // Recipients
    $mail->setFrom($smtpUser, 'BamBam Test');
    $mail->addAddress($smtpUser); // Send to yourself

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from BamBam System';
    $mail->Body    = 'If you are reading this, PHPMailer is working and SMTP is configured correctly! 🚀';

    $mail->send();
    echo "<p style='color:green'>✅ <b>Success!</b> Test email sent to <b>$smtpUser</b>.</p>";
    echo "<p>Check your inbox (and spam folder).</p>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ <b>SMTP Error:</b> Email could not be sent.</p>";
    echo "<pre>Error details: {$mail->ErrorInfo}</pre>";
}
?>