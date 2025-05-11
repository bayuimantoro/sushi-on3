<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//mengecek apakah pengguna login dan adalah admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    //jika bukan admin, tampilkan akses ditolak
    $_SESSION['message'] = "Akses ditolak. Anda bukan admin.";
    $_SESSION['message_type'] = "danger";
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . '/../db.php';

//mengecek apakah ID pesan ada di URL dan koneksi DB berhasil
if (isset($_GET['id']) && $conn) {
    $message_id = $_GET['id'];

    //Validasi ID
    if (!filter_var($message_id, FILTER_VALIDATE_INT)) {
        $_SESSION['message'] = "ID pesan tidak valid.";
        $_SESSION['message_type'] = "danger";
        header("Location: contact_admin.php");
        exit;
    }

    $sql_delete = "DELETE FROM contact_messages WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);

    if ($stmt) {
        $stmt->bind_param("i", $message_id);
        if ($stmt->execute()) {
            //kika berhasil dihapus
            $_SESSION['message'] = "Pesan berhasil dihapus.";
            $_SESSION['message_type'] = "success";
        } else {
            //jika gagal menghapus
            $_SESSION['message'] = "Gagal menghapus pesan: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Gagal mempersiapkan statement: " . $conn->error;
        $_SESSION['message_type'] = "danger";
    }
    $conn->close();
} else {
    //jika ID tidak ada atau koneksi gagal
    $_SESSION['message'] = "ID pesan tidak ditemukan atau koneksi database gagal.";
    $_SESSION['message_type'] = "warning";
}
header("Location: contact_admin.php");
exit;
?>