<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//pengecekan login & role ada di admin_header.php

require_once __DIR__ . '/../db.php';
$current_page = 'orders_admin.php'; 
$page_title = "Ubah Status Pesanan";

$order_id = null;
if (isset($_GET['id'])) {
    $order_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
}

if (!$order_id) {
    $_SESSION['message'] = "ID Pesanan tidak valid atau tidak ditemukan.";
    $_SESSION['message_type'] = "danger";
    header("Location: orders_admin.php");
    exit;
}

if (!$conn) {
    //tidak akan terjadi kalau admin_header.php sudah melakukan cek
    die("Koneksi database gagal.");
}

$order_data = null;
$sql_get_order = "SELECT o.id, o.total, o.delivery_method, o.status, o.name, o.email 
                  FROM orders o 
                  WHERE o.id = ?";
$stmt_get = $conn->prepare($sql_get_order);
if ($stmt_get) {
    $stmt_get->bind_param("i", $order_id);
    $stmt_get->execute();
    $result_get = $stmt_get->get_result();
    if ($result_get->num_rows > 0) {
        $order_data = $result_get->fetch_assoc();
        $page_title = "Ubah Status Pesanan #" . htmlspecialchars($order_data['id']); //update page title dengan ID
    } else {
        $_SESSION['message'] = "Pesanan dengan ID #{$order_id} tidak ditemukan.";
        $_SESSION['message_type'] = "warning";
        header("Location: orders_admin.php");
        exit;
    }
    $stmt_get->close();
} else {
    $_SESSION['message'] = "Gagal mengambil data pesanan: " . $conn->error;
    $_SESSION['message_type'] = "danger";
    header("Location: orders_admin.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'] ?? $order_data['status'];
    $new_delivery_method = $_POST['delivery_method'] ?? $order_data['delivery_method'];

    //validasi sederhana
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'];
    $allowed_delivery = ['pickup', 'delivery'];

    if (!in_array($new_status, $allowed_statuses)) {
        $_SESSION['message'] = "Status pesanan tidak valid.";
        $_SESSION['message_type'] = "danger";
        header("Location: edit_order_status.php?id=" . $order_id); //kembali ke form edit
        exit;
    }
    if (!in_array($new_delivery_method, $allowed_delivery)) {
        $_SESSION['message'] = "Metode pengiriman tidak valid.";
        $_SESSION['message_type'] = "danger";
        header("Location: edit_order_status.php?id=" . $order_id);
        exit;
    }


    $sql_update = "UPDATE orders SET status = ?, delivery_method = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update) {
        $stmt_update->bind_param("ssi", $new_status, $new_delivery_method, $order_id);
        if ($stmt_update->execute()) {
            $_SESSION['message'] = "Status pesanan #{$order_id} berhasil diperbarui.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal memperbarui status pesanan: " . $stmt_update->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt_update->close();
    } else {
        $_SESSION['message'] = "Gagal mempersiapkan statement update: " . $conn->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: orders_admin.php");
    exit;
}

require_once 'admin_header.php';
?>

<main class="admin-main-content" id="mainContent">
    <div class="content-header">
        <div class="title-area">
            <h1><?php echo $page_title; ?></h1>
            <div class="breadcrumb-area">
                <i class="fas fa-home"></i> <a href="dashboard.php">Home</a>
                <i class="fas fa-angle-right"></i> <a href="orders_admin.php">Kelola Pesanan</a>
                <i class="fas fa-angle-right"></i> <span>Ubah Status</span>
            </div>
        </div>
    </div>

    <?php
    if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
        echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message_type']) . ' alert-dismissible fade show mx-3" role="alert">';
        echo htmlspecialchars($_SESSION['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>

    <form method="POST" action="edit_order_status.php?id=<?php echo htmlspecialchars($order_data['id']); ?>">
        <div class="content-panel panel-primary">
            <div class="panel-header">
                <h3 class="panel-title">Form Ubah Status & Metode Pengiriman</h3>
            </div>
            <div class="panel-body">
                <div class="mb-3">
                    <label class="form-label">ID Pesanan</label>
                    <input type="text" class="form-control" value="#<?php echo htmlspecialchars($order_data['id']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Pelanggan</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($order_data['name']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Pelanggan</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($order_data['email']); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="delivery_method" class="form-label">Metode Pengiriman</label>
                    <select class="form-select" id="delivery_method" name="delivery_method">
                        <option value="pickup" <?php echo (strtolower($order_data['delivery_method']) == 'pickup') ? 'selected' : ''; ?>>Pickup</option>
                        <option value="delivery" <?php echo (strtolower($order_data['delivery_method']) == 'delivery') ? 'selected' : ''; ?>>Delivery</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status Pesanan</label>
                    <select class="form-select" id="status" name="status">
                        <option value="pending" <?php echo (strtolower($order_data['status'] ?? '') == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo (strtolower($order_data['status'] ?? '') == 'processing') ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo (strtolower($order_data['status'] ?? '') == 'shipped') ? 'selected' : ''; ?>>Shipped / Ready for Pickup</option>
                        <option value="delivered" <?php echo (strtolower($order_data['status'] ?? '') == 'delivered') ? 'selected' : ''; ?>>Delivered / Picked Up</option>
                        <option value="completed" <?php echo (strtolower($order_data['status'] ?? '') == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo (strtolower($order_data['status'] ?? '') == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="orders_admin.php" class="btn btn-secondary">Batal</a>
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