<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = "Kelola Pengguna";

//mengambil dan menghapus pesan dari session
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// ---include admin header---
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
            <a href="add_user.php" class="btn btn-sm btn-primary"><i class="fas fa-user-plus me-1"></i> Tambah Pengguna</a>
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
            <h3 class="panel-title">Daftar Pengguna</h3>
        </div>
        <div class="panel-body no-padding">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Terdaftar Sejak</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($conn) {
                            $sql_users = "SELECT id, username, email, role, created_at FROM users ORDER BY id ASC";
                            $result_all_users = $conn->query($sql_users);
                            if ($result_all_users && $result_all_users->num_rows > 0) {
                                while ($row = $result_all_users->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td>" . ($row['email'] ? htmlspecialchars($row['email']) : '<em>N/A</em>') . "</td>";
                                    echo "<td>" . ($row['role'] ? htmlspecialchars(ucfirst($row['role'])) : '<em>N/A</em>') . "</td>";
                                    echo "<td>" . htmlspecialchars(date('d M Y, H:i', strtotime($row['created_at']))) . "</td>";
                                    echo "<td>";
                                    if ($row['id'] != $_SESSION['user_id']) {
                                        echo "<a href='edit_user.php?id=" . $row['id'] . "' class='btn btn-xs btn-warning' title='Edit'><i class='fas fa-pencil-alt'></i></a> ";
                                        echo "<a href='delete_user.php?id=" . $row['id'] . "' class='btn btn-xs btn-danger' title='Hapus' onclick='return confirm(\"Apakah Anda yakin ingin menghapus pengguna ini?\");'><i class='fas fa-trash'></i></a>";
                                    } else {
                                        echo "<span class='text-muted'><i>(Akun Anda)</i></span>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>Tidak ada pengguna.</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>Koneksi database gagal.</td></tr>";
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
if (isset($conn)) {
    $conn->close();
}
require_once 'admin_footer.php';
?>