<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//pengecekan login & role ada di admin_header.php

require_once __DIR__ . '/../db.php';
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = "Kelola Pesanan";

//mengambil dan menghapus pesan dari session
$success_message = $_SESSION['message'] ?? ($_SESSION['success_message'] ?? null); //mengecek kedua variabel session
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['message']);
unset($_SESSION['message_type']);
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

require_once 'admin_header.php';
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
        <div class="header-page-actions">
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
            <h3 class="panel-title">Daftar Pesanan Masuk</h3>
        </div>
        <div class="panel-body no-padding">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Nama Pelanggan</th>
                            <th>Email</th>
                            <th>Tanggal</th>
                            <th>Total (Rp)</th>
                            <th>Metode Kirim</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($conn) {
                            $sql_orders = "SELECT o.id, 
                                                  o.created_at AS order_date, 
                                                  o.total,
                                                  o.delivery_method,
                                                  o.status,
                                                  u.username AS registered_customer_username,
                                                  o.name AS order_customer_name,
                                                  o.email AS order_customer_email
                                           FROM orders o
                                           LEFT JOIN users u ON o.user_id = u.id
                                           ORDER BY o.created_at DESC";
                            $result_orders = $conn->query($sql_orders);

                            if ($result_orders && $result_orders->num_rows > 0) {
                                while ($row = $result_orders->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>#" . htmlspecialchars($row['id']) . "</td>";

                                    $customerDisplayName = "";
                                    if (!empty($row['registered_customer_username'])) {
                                        $customerDisplayName = htmlspecialchars($row['registered_customer_username']) . " <small class='text-muted'>(User)</small>";
                                    } elseif (!empty($row['order_customer_name'])) {
                                        $customerDisplayName = htmlspecialchars($row['order_customer_name']) . " <small class='text-muted'>(Tamu)</small>";
                                    } else {
                                        $customerDisplayName = "<i>Tidak ada nama</i>";
                                    }
                                    echo "<td>" . $customerDisplayName . "</td>";
                                    echo "<td>" . ($row['order_customer_email'] ? htmlspecialchars($row['order_customer_email']) : '<em>N/A</em>') . "</td>";
                                    echo "<td>" . htmlspecialchars(date('d M Y, H:i', strtotime($row['order_date']))) . "</td>";
                                    echo "<td>" . number_format($row['total'], 0, ',', '.') . "</td>";
                                    
                                    $delivery_method_display = htmlspecialchars(ucfirst($row['delivery_method']));
                                    echo "<td>" . $delivery_method_display . "</td>";
                                    
                                    $status_display = htmlspecialchars(ucfirst($row['status'] ?? 'N/A'));
                                    $status_class = 'status-' . strtolower(htmlspecialchars($row['status'] ?? 'unknown'));
                                    echo "<td><span class='badge " . $status_class . "'>" . $status_display . "</span></td>";

                                    echo "<td>
                                            <a href='view_order_details.php?id=" . $row['id'] . "' class='btn btn-xs btn-info' title='Lihat Detail'><i class='fas fa-eye'></i></a>
                                            <a href='edit_order_status.php?id=" . $row['id'] . "' class='btn btn-xs btn-warning' title='Ubah Status/Metode'><i class='fas fa-pencil-alt'></i></a>
                                            <!-- <a href='delete_order.php?id=" . $row['id'] . "' class='btn btn-xs btn-danger' title='Hapus' onclick='return confirm(\"Yakin?\");'><i class='fas fa-trash'></i></a> -->
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>Tidak ada pesanan.</td></tr>";
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
        </div>
    </div>
</main>

<?php
if (isset($conn) && is_object($conn) && method_exists($conn, 'close')) {
    $conn->close();
}
require_once 'admin_footer.php';
?>