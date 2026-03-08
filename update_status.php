<?php
include 'db.php';

$id = $_POST['id'];
$status = $_POST['status'];

$stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
if($stmt->execute([$status, $id])) {
    echo "Success";
}