<?php
session_start();
include '../db.php';
if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$userCount = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$menuCount = $conn->query("SELECT COUNT(*) FROM menu_items")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - SushiOn3</title>
</head>
<body>
    <h1>Dashboard Admin</h1>
    <p>Total User: <?= $userCount ?></p>
    <p>Total Menu: <?= $menuCount ?></p>
    <a href="users.php">Kelola User</a> | <a href="menu.php">Kelola Menu</a>
</body>
</html>