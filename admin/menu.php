<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//pengecekan login & role ada di admin_header.php

require_once __DIR__ . '/../db.php';
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = "Kelola Menu";

//mengambil dan menghapus pesan dari session
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

require_once 'admin_header.php'; //memuat header, sidebar, dan link CSS
?>

<main class="admin-main-content" id="mainContent">
    <div class="content-header">
        <div class="title-area">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <div class="breadcrumb-area">
                <i class="fas fa-home"></i> <a href="dashboard.php">Home</a>
                <i class="fas fa-angle-right"></i> <span><?php echo htmlspecialchars($page_title); ?></span>
            </div>
        </div>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show mx-3" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="content-panel panel-primary">
        <div class="panel-header">
            <h3 class="panel-title">Daftar Item Menu</h3>
            <div class="panel-actions">
                <a href="add_menu_item.php" class="btn btn-xs btn-outline-primary"><i class="fas fa-plus"></i> Tambah Item</a>
            </div>
        </div>
        <div class="panel-body no-padding">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama Item</th>
                            <th>Kategori</th>
                            <th>Harga (Rp)</th>
                            <th>Deskripsi Singkat</th>
                            <th>Ketersediaan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($conn) {
                            //mengambil data dari tabel 'menu' dan menyertakan
                            $sql_menu = "SELECT id, name, category, price, description, image_path, is_available FROM menu ORDER BY category ASC, name ASC";
                            $result_menu = $conn->query($sql_menu);
                            if ($result_menu && $result_menu->num_rows > 0) {
                                while ($row = $result_menu->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    $image_display_path = "../" . htmlspecialchars($row['image_path']);
                                    $image_display = !empty($row['image_path']) && file_exists($image_display_path)
                                        ? "<img src='" . $image_display_path . "' alt='" . htmlspecialchars($row['name']) . "' style='width: 70px; height: 50px; object-fit: cover; border-radius: 4px;'>"
                                        : "<span class='text-muted' style='font-size:0.8em;'>No Image</span>";
                                    echo "<td>" . $image_display . "</td>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars(ucfirst($row['category'])) . "</td>";
                                    echo "<td>" . number_format($row['price'], 0, ',', '.') . "</td>";
                                    $short_desc = strlen($row['description']) > 50 ? substr(htmlspecialchars($row['description']), 0, 50) . "..." : htmlspecialchars($row['description']);
                                    echo "<td>" . $short_desc . "</td>";
                                    //menampilkan status ketersediaan
                                    $availability_text = $row['is_available'] ? "<span class='badge status-completed'>Tersedia</span>" : "<span class='badge status-cancelled'>Habis</span>";
                                    echo "<td>" . $availability_text . "</td>";
                                    echo "<td>
                                            <a href='edit_menu_item.php?id=" . $row['id'] . "' class='btn btn-xs btn-warning' title='Edit'><i class='fas fa-pencil-alt'></i></a>
                                            <a href='delete_menu_item.php?id=" . $row['id'] . "' class='btn btn-xs btn-danger' title='Hapus' onclick='return confirm(\"Apakah Anda yakin ingin menghapus item ini?\");'><i class='fas fa-trash'></i></a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>Tidak ada item menu. Silakan <a href='add_menu_item.php'>tambahkan item baru</a>.</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>Koneksi database gagal.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="panel-footer">
            <small>Menampilkan <?php echo isset($result_menu) ? $result_menu->num_rows : 0; ?> item menu.</small>
        </div>
    </div>
</main>

<?php
if (isset($conn) && is_object($conn) && method_exists($conn, 'close')) {
    $conn->close();
}
require_once 'admin_footer.php';
?>