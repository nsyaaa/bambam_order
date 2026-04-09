<?php
// Database config
$host = 'localhost';
$db   = 'bambam_burger';
$user = 'root'; 
$pass = ''; 
$charset = 'utf8mb4';

$dbHost = $host;
$dbName = $db;
$dbUser = $user;
$dbPass = $pass;

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// SMTP config for PHPMailer
$smtpHost = 'smtp.gmail.com';
$smtpUser = 'bambamburgerperlis@gmail.com'; 
$smtpPass = 'hosufxcgdwzjwgam'; 
$smtpPort = 587;

// ToyyibPay Config
$toyyibpay_secret_key = 'mkrptmnl-i33m-67op-m3wa-oeri0bqap5im'; // Updated Secret Key
$toyyibpay_category_code = 'uhsujxao'; // Updated Category Code
$toyyibpay_url = 'https://toyyibpay.com/'; // Use https://dev.toyyibpay.com/ for sandbox

// Compatibility shim for PHP < 8.0
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}