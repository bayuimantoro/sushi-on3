<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// ...(Cek login dan role admin)...
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak.";
    header("Location: ../views/login.php");
    exit;
}

require_once __DIR__ . '/../db.php';

$user_id_to_delete = $_GET['id'] ?? null;

if (!$user_id_to_delete || !is_numeric($user_id_to_delete)) {
    $_SESSION['error_message'] = "ID pengguna tidak valid untuk dihapus.";
    header("Location: users.php");
    exit;
}

//agar admin tidak dapat menghapus akunya sendiri
if ($user_id_to_delete == $_SESSION['user_id']) {
    $_SESSION['error_message'] = "Anda tidak dapat menghapus akun Anda sendiri.";
    header("Location: users.php");
    exit;
}

if ($conn) {
    $stmt_delete = $conn->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $user_id_to_delete);
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $_SESSION['success_message'] = "Pengguna berhasil dihapus!";
            } else {
                $_SESSION['error_message'] = "Pengguna tidak ditemukan atau sudah dihapus.";
            }
        } else {
            $_SESSION['error_message'] = "Gagal menghapus pengguna: " . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        $_SESSION['error_message'] = "Gagal menyiapkan statement delete: " . $conn->error;
    }
} else {
    $_SESSION['error_message'] = "Koneksi database gagal.";
}

header("Location: users.php");
exit;
?>