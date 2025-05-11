<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak.";
    header("Location: ../views/login.php");
    exit;
}

require_once __DIR__ . '/../db.php';

$item_id = $_GET['id'] ?? null;

if (!$item_id || !is_numeric($item_id)) {
    $_SESSION['error_message'] = "ID item tidak valid untuk dihapus.";
    header("Location: menu.php");
    exit;
}

if ($conn) {
    $stmt_select_image = $conn->prepare("SELECT image_path FROM menu WHERE id = ?");
    $image_to_delete = null;
    if ($stmt_select_image) {
        $stmt_select_image->bind_param("i", $item_id);
        $stmt_select_image->execute();
        $result_image = $stmt_select_image->get_result();
        if ($row_image = $result_image->fetch_assoc()) {
            $image_to_delete = $row_image['image_path'];
        }
        $stmt_select_image->close();
    }

    $stmt_delete = $conn->prepare("DELETE FROM menu WHERE id = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $item_id);
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                // Hapus file gambar dari server jika ada
                if ($image_to_delete && file_exists("../" . $image_to_delete)) {
                    unlink("../" . $image_to_delete);
                }
                $_SESSION['success_message'] = "Item menu berhasil dihapus!";
            } else {
                $_SESSION['error_message'] = "Item menu tidak ditemukan atau sudah dihapus.";
            }
        } else {
            $_SESSION['error_message'] = "Gagal menghapus item menu: " . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        $_SESSION['error_message'] = "Gagal menyiapkan statement delete: " . $conn->error;
    }
} else {
    $_SESSION['error_message'] = "Koneksi database gagal.";
}

header("Location: menu.php");
exit;
?>