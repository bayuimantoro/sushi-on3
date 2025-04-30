<?php
session_start();
include '../db.php';
if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Hapus menu
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM menu_items WHERE id = $id");
    header("Location: menu.php");
    exit();
}

$menus = $conn->query("SELECT mi.*, c.name AS category FROM menu_items mi LEFT JOIN categories c ON mi.category_id = c.id");
?>

<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Kelola Menu</title></head>
<body>
    <h1>Daftar Menu</h1>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th><th>Nama</th><th>Deskripsi</th><th>Harga</th><th>Kategori</th><th>Tersedia</th><th>Aksi</th>
        </tr>
        <?php while ($menu = $menus->fetch_assoc()) : ?>
            <tr>
                <td><?= $menu['id'] ?></td>
                <td><?= htmlspecialchars($menu['name']) ?></td>
                <td><?= htmlspecialchars($menu['description']) ?></td>
                <td>Rp<?= number_format($menu['price'], 0, ',', '.') ?></td>
                <td><?= $menu['category'] ?></td>
                <td><?= $menu['is_available'] ? 'Ya' : 'Tidak' ?></td>
                <td>
                    <a href="?delete=<?= $menu['id'] ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
