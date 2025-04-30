<?php
include 'db.php';

// Data admin
$username = 'admin';
$password = 'admin1234';
$email = 'admin@example.com';
$role = 'admin';

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Cek apakah admin sudah ada
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Tambahkan admin
    $insert = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    $insert->bind_param("ssss", $username, $hashedPassword, $email, $role);
    if ($insert->execute()) {
        echo "✅ Admin berhasil ditambahkan!";
    } else {
        echo "❌ Gagal menambahkan admin: " . $conn->error;
    }
} else {
    echo "⚠️ Admin sudah ada.";
}
?>