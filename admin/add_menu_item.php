<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Pengecekan login & role ada di admin_header.php

require_once __DIR__ . '/../db.php';
$current_page = 'menu.php'; // Agar link "Kelola Menu" di sidebar tetap aktif
$page_title = "Tambah Item Menu";

$errors = [];
$name = '';
$category = '';
$price = '';
$description = '';
$is_available = 1; // Default ketersediaan item adalah "Tersedia"

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_available = isset($_POST['is_available']) ? (int)$_POST['is_available'] : 0; // Ambil nilai is_available, default ke 0 jika tidak ada
    $image_file = $_FILES['image_path'] ?? null;

    // Validasi
    if (empty($name)) $errors['name'] = "Nama item tidak boleh kosong.";
    if (empty($category)) $errors['category'] = "Kategori tidak boleh kosong.";
    if ($price === '' || !is_numeric($price) || $price < 0) { // Periksa apakah price kosong juga
        $errors['price'] = "Harga tidak valid atau tidak boleh kosong.";
    }
    if (empty($description)) $errors['description'] = "Deskripsi tidak boleh kosong.";
    if (!in_array($is_available, [0, 1])) { // Pastikan nilai is_available valid
        $errors['is_available'] = "Status ketersediaan tidak valid.";
    }


    $target_dir = "../assets/menu_images/";
    $image_db_path = null;

    if ($image_file && $image_file['error'] == UPLOAD_ERR_OK) {
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0775, true) && !is_dir($target_dir)) {
                $errors['image_path'] = "Gagal membuat direktori upload. Pastikan folder 'admin' memiliki izin tulis untuk membuat subfolder 'assets/menu_images/' di level atasnya, atau buat folder secara manual.";
            }
        }
        if (empty($errors['image_path'])) { // Hanya lanjutkan jika tidak ada error pembuatan direktori
            $image_name = uniqid('menu_') . "_" . preg_replace("/[^a-zA-Z0-9\-\._]/", "_", basename($image_file["name"]));
            $target_file = $target_dir . $image_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if ($image_file["size"] > 30000000) { // 30MB
                $errors['image_path'] = "Ukuran file terlalu besar (maks 30MB).";
            }
            if (!in_array($imageFileType, $allowed_types)) {
                $errors['image_path'] = "Hanya file JPG, JPEG, PNG, GIF, WEBP yang diizinkan.";
            }

            if (empty($errors['image_path'])) { // Cek lagi error setelah validasi file
                if (move_uploaded_file($image_file["tmp_name"], $target_file)) {
                    $image_db_path = "assets/menu_images/" . $image_name; // Path relatif dari root folder proyek
                } else {
                    $errors['image_path'] = "Gagal mengupload gambar. Cek izin tulis pada folder: " . realpath($target_dir);
                }
            }
        }
    } elseif ($image_file && $image_file['error'] != UPLOAD_ERR_NO_FILE) {
        $errors['image_path'] = "Terjadi kesalahan saat mengupload gambar (Error code: " . $image_file['error'] . ").";
    }


    if (empty($errors)) {
        if ($conn) {
            // Menambahkan is_available ke query INSERT
            $stmt = $conn->prepare("INSERT INTO menu (name, category, price, description, image_path, is_available) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                // Tipe data untuk is_available adalah integer (i)
                $stmt->bind_param("ssdssi", $name, $category, $price, $description, $image_db_path, $is_available);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Item menu berhasil ditambahkan!";
                    header("Location: menu.php");
                    exit;
                } else {
                    $errors['db'] = "Gagal menyimpan ke database: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors['db'] = "Gagal menyiapkan statement: " . $conn->error;
            }
        } else {
            $errors['db'] = "Koneksi database gagal.";
        }
    }
}

require_once 'admin_header.php';
?>

<main class="admin-main-content" id="mainContent">
    <div class="content-header">
        <div class="title-area">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <div class="breadcrumb-area">
                <i class="fas fa-home"></i> <a href="dashboard.php">Home</a>
                <i class="fas fa-angle-right"></i> <a href="menu.php">Kelola Menu</a>
                <i class="fas fa-angle-right"></i> <span><?php echo htmlspecialchars($page_title); ?></span>
            </div>
        </div>
    </div>

    <?php if (!empty($errors['db'])): ?>
        <div class="alert alert-danger mx-3"><?php echo htmlspecialchars($errors['db']); ?></div>
    <?php endif; ?>

    <form action="add_menu_item.php" method="POST" enctype="multipart/form-data">
        <div class="content-panel panel-primary">
            <div class="panel-header">
                <h3 class="panel-title">Form Tambah Item Menu</h3>
            </div>
            <div class="panel-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Item <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?php echo $errors['name']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select <?php echo isset($errors['category']) ? 'is-invalid' : ''; ?>" id="category" name="category" required>
                        <option value="">Pilih Kategori</option>
                        <option value="sushi" <?php echo ($category == 'sushi') ? 'selected' : ''; ?>>Sushi</option>
                        <option value="ramen" <?php echo ($category == 'ramen') ? 'selected' : ''; ?>>Ramen</option>
                        <option value="donburi" <?php echo ($category == 'donburi') ? 'selected' : ''; ?>>Donburi</option> <!-- Tambahkan Donburi jika belum ada -->
                        <option value="minuman" <?php echo ($category == 'minuman') ? 'selected' : ''; ?>>Minuman</option>
                        <option value="dessert" <?php echo ($category == 'dessert') ? 'selected' : ''; ?>>Dessert</option>
                        <option value="lainnya" <?php echo ($category == 'lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                    </select>
                    <?php if (isset($errors['category'])): ?><div class="invalid-feedback"><?php echo $errors['category']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" required min="0" step="1">
                    <?php if (isset($errors['price'])): ?><div class="invalid-feedback"><?php echo $errors['price']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                    <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" id="description" name="description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
                    <?php if (isset($errors['description'])): ?><div class="invalid-feedback"><?php echo $errors['description']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="is_available" class="form-label">Ketersediaan <span class="text-danger">*</span></label>
                    <select class="form-select <?php echo isset($errors['is_available']) ? 'is-invalid' : ''; ?>" id="is_available" name="is_available" required>
                        <option value="1" <?php echo ($is_available == 1) ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="0" <?php echo ($is_available == 0) ? 'selected' : ''; ?>>Tidak Tersedia</option>
                    </select>
                    <?php if (isset($errors['is_available'])): ?><div class="invalid-feedback"><?php echo $errors['is_available']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="image_path" class="form-label">Gambar Item (Opsional)</label>
                    <input type="file" class="form-control <?php echo isset($errors['image_path']) ? 'is-invalid' : ''; ?>" id="image_path" name="image_path" accept="image/png, image/jpeg, image/gif, image/webp">
                    <div id="imagePathHelp" class="form-text">Format yang didukung: JPG, JPEG, PNG, GIF, WEBP. Maks 30MB.</div>
                    <?php if (isset($errors['image_path'])): ?><div class="invalid-feedback d-block"><?php echo $errors['image_path']; ?></div><?php endif; ?>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-primary">Simpan Item</button>
                <a href="menu.php" class="btn btn-secondary">Batal</a>
            </div>
        </div>
    </form>
</main>

<?php
if (isset($conn) && is_object($conn) && method_exists($conn, 'close')) {
    $conn->close();
}
require_once 'admin_footer.php';
?>