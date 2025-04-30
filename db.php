<?php
$host = "localhost";
$user = "root";
$password = ""; // atau password kamu jika ada
$dbname = "SushiOn3";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
