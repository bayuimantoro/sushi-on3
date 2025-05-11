<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../db.php';
$current_page = 'menu.php'; //agar link "Kelola Menu" di sidebar tetap aktif
$page_title = "Edit Item Menu";

$item_id = $_GET['id'] ?? null;
if (!$item_id || !is_numeric($item_id)) {
    $_SESSION['error_message'] = "ID item tidak valid.";
    header("Location: menu.php");
    exit;
}

$errors = [];
$name = '';
$category = '';
$price = '';
$description = '';
$is_available_db = 1;
$current_image_path = '';

if ($conn) {
    //mengambil SEMUA kolom yang relevan, termasuk is_available
    $stmt_select = $conn->prepare("SELECT name, category, price, description, image_path, is_available FROM menu WHERE id = ?");
    if ($stmt_select) {
        $stmt_select->bind_param("i", $item_id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        if ($item = $result->fetch_assoc()) {
            $name = $item['name'];
            $category = $item['category'];
            $price = $item['price'];
            $description = $item['description'];
            $current_image_path = $item['image_path'];
            $is_available_db = (int)$item['is_available'];
            $page_title = "Edit Item: " . htmlspecialchars($name); //update judul dengan nama item
        } else {
            $_SESSION['error_message'] = "Item menu tidak ditemukan.";
            header("Location: menu.php");
            exit;
        }
        $stmt_select->close();
    } else {
        $_SESSION['error_message'] = "Gagal mengambil data item: " . $conn->error;
        header("Location: menu.php");
        exit;
    }
} else {
    $_SESSION['error_message'] = "Koneksi database gagal.";
    header("Location: menu.php");
    exit;
}

//inisialisasi variabel form dengan data dari DB
$is_available_form = $is_available_db;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? $name);
    $category = trim($_POST['category'] ?? $category);
    $price = trim($_POST['price'] ?? $price);
    $description = trim($_POST['description'] ?? $description);
    $is_available_form = isset($_POST['is_available']) ? (int)$_POST['is_available'] : 0; //di ambil dari POST
    $image_file = $_FILES['image_path'] ?? null;
    $remove_current_image = isset($_POST['remove_current_image']);

    // Validasi
    if (empty($name)) $errors['name'] = "Nama item tidak boleh kosong.";
    if (empty($category)) $errors['category'] = "Kategori tidak boleh kosong.";
    if ($price === '' || !is_numeric($price) || $price < 0) $errors['price'] = "Harga tidak valid atau kosong.";
    if (empty($description)) $errors['description'] = "Deskripsi tidak boleh kosong.";
    if (!in_array($is_available_form, [0, 1])) $errors['is_available'] = "Status ketersediaan tidak valid.";


    $target_dir = "../assets/menu_images/";
    $new_image_db_path = $current_image_path;

    if ($remove_current_image && $current_image_path) {
        $image_to_delete_path = "../" . $current_image_path;
        if (file_exists($image_to_delete_path)) {
            @unlink($image_to_delete_path);
        }
        $new_image_db_path = null;
        $current_image_path = null;
    }

    if ($image_file && $image_file['error'] == UPLOAD_ERR_OK) {
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0775, true) && !is_dir($target_dir)) {
                $errors['image_path'] = "Gagal membuat direktori upload.";
            }
        }
        if (empty($errors['image_path'])) {
            $image_name = uniqid('menu_') . "_" . preg_replace("/[^a-zA-Z0-9\-\._]/", "_", basename($image_file["name"]));
            $target_file = $target_dir . $image_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if ($image_file["size"] > 30000000) $errors['image_path'] = "Ukuran file terlalu besar (maks 30MB).";
            if (!in_array($imageFileType, $allowed_types)) $errors['image_path'] = "Hanya file JPG, JPEG, PNG, GIF, WEBP yang diizinkan.";

            if (empty($errors['image_path'])) {
                if ($current_image_path && file_exists("../" . $current_image_path) && !$remove_current_image) {
                    @unlink("../" . $current_image_path);
                }
                if (move_uploaded_file($image_file["tmp_name"], $target_file)) {
                    $new_image_db_path = "assets/menu_images/" . $image_name;
                } else {
                    $errors['image_path'] = "Gagal mengupload gambar baru. Cek izin folder.";
                }
            }
        }
    } elseif ($image_file && $image_file['error'] != UPLOAD_ERR_NO_FILE) {
        $errors['image_path'] = "Terjadi kesalahan saat mengupload gambar (Error code: " . $image_file['error'] . ").";
    }

    if (empty($errors)) {
        if ($conn) {
            $stmt = $conn->prepare("UPDATE menu SET name = ?, category = ?, price = ?, description = ?, image_path = ?, is_available = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ssdssii", $name, $category, $price, $description, $new_image_db_path, $is_available_form, $item_id);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Item menu berhasil diperbarui!";
                    header("Location: menu.php");
                    exit;
                } else {
                    $errors['db'] = "Gagal memperbarui database: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors['db'] = "Gagal menyiapkan statement update: " . $conn->error;
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
                <i class="fas fa-angle-right"></i> <span>Edit Item</span>
            </div>
        </div>
    </div>

    <?php if (!empty($errors['db'])): ?>
        <div class="alert alert-danger mx-3"><?php echo htmlspecialchars($errors['db']); ?></div>
    <?php endif; ?>

    <form action="edit_menu_item.php?id=<?php echo $item_id; ?>" method="POST" enctype="multipart/form-data">
        <div class="content-panel panel-primary">
            <div class="panel-header">
                <h3 class="panel-title">Form Edit Item Menu</h3>
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
                        <option value="minuman" <?php echo ($category == 'minuman') ? 'selected' : ''; ?>>Minuman</option>
                        <option value="dessert" <?php echo ($category == 'dessert') ? 'selected' : ''; ?>>Dessert</option>
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
                        <option value="1" <?php echo ($is_available_form == 1) ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="0" <?php echo ($is_available_form == 0 && isset($is_available_form)) ? 'selected' : ''; ?>>Tidak Tersedia</option>
                    </select>
                    <?php if (isset($errors['is_available'])): ?><div class="invalid-feedback"><?php echo $errors['is_available']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="image_path" class="form-label">Ganti Gambar Item (Opsional)</label>
                    <input type="file" class="form-control <?php echo isset($errors['image_path']) ? 'is-invalid' : ''; ?>" id="image_path" name="image_path" accept="image/png, image/jpeg, image/gif, image/webp">
                    <div class="form-text">Kosongkan jika tidak ingin mengganti gambar. Format: JPG, PNG, GIF, WEBP. Maks 30MB.</div>
                    <?php if (isset($errors['image_path'])): ?><div class="invalid-feedback d-block"><?php echo $errors['image_path']; ?></div><?php endif; ?>

                    <?php if ($current_image_path): ?>
                        <div class="mt-2">
                            <p class="mb-1">Gambar Saat Ini:</p>
                            <img src="../<?php echo htmlspecialchars($current_image_path); ?>" alt="Gambar Item <?php echo htmlspecialchars($name); ?>" style="max-width: 200px; max-height: 150px; border-radius: 5px; border: 1px solid #ddd; object-fit: cover;">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_current_image" id="remove_current_image" value="1">
                                <label class="form-check-label" for="remove_current_image">
                                    Hapus gambar saat ini (dan tidak mengganti dengan yang baru)
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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