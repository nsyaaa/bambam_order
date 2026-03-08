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
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// SMTP config for PHPMailer
$smtpHost = 'smtp.gmail.com';
$smtpUser = 'bambamburgerperlis@gmail.com'; 
$smtpPass = 'hosufxcgdwzjwgam'; 
$smtpPort = 587;