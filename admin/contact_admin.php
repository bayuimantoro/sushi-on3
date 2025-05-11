<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//pengecekan login & role ada di admin_header.php

require_once __DIR__ . '/../db.php';
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = "Pesan Kontak Masuk";

//mengambil dan menghapus pesan dari session (untuk notifikasi hapus pesan)
$success_message = $_SESSION['message'] ?? ($_SESSION['success_message'] ?? null);
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
            <h3 class="panel-title">Daftar Pesan Kontak</h3>
        </div>
        <div class="panel-body no-padding">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Pesan</th>
                            <th>Dikirim Pada</th>
                            <th>User ID</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($conn) {
                            $sql_contact = "SELECT id, name, email, message, submitted_at, user_id FROM contact_messages ORDER BY submitted_at DESC";
                            $result_contact = $conn->query($sql_contact);
                            if ($result_contact && $result_contact->num_rows > 0) {
                                while ($row = $result_contact->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    $short_message = strlen($row['message']) > 70 ? substr(htmlspecialchars($row['message']), 0, 70) . "..." : htmlspecialchars($row['message']);
                                    echo "<td>" . $short_message . "</td>";
                                    echo "<td>" . htmlspecialchars(date('d M Y, H:i', strtotime($row['submitted_at']))) . "</td>";
                                    echo "<td>" . ($row['user_id'] ? htmlspecialchars($row['user_id']) : "<i>Guest</i>") . "</td>";
                                    echo "<td>
                                            <button class='btn btn-xs btn-info' title='Lihat Detail' data-bs-toggle='modal' data-bs-target='#messageModal' 
                                                    data-name='" . htmlspecialchars($row['name'], ENT_QUOTES) . "' 
                                                    data-email='" . htmlspecialchars($row['email'], ENT_QUOTES) . "'
                                                    data-message='" . htmlspecialchars($row['message'], ENT_QUOTES) . "'
                                                    data-submitted='" . htmlspecialchars(date('d M Y, H:i', strtotime($row['submitted_at']))) . "'>
                                                <i class='fas fa-eye'></i>
                                            </button>
                                            <a href='delete_contact_message.php?id=" . $row['id'] . "' class='btn btn-xs btn-danger' title='Hapus' onclick='return confirm(\"Apakah Anda yakin ingin menghapus pesan ini?\");'><i class='fas fa-trash'></i></a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Tidak ada pesan kontak.</td></tr>";
                            }
                        } else {
                             echo "<tr><td colspan='7' class='text-center'>Koneksi database gagal.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php

if (isset($conn) && is_object($conn) && method_exists($conn, 'close')) {
    $conn->close();
}
require_once 'admin_footer.php';
?>
<script>
    var messageModalElement = document.getElementById('messageModal');
    if (messageModalElement) {
        messageModalElement.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var name = button.getAttribute('data-name');
            var email = button.getAttribute('data-email');
            var message = button.getAttribute('data-message');
            var submitted = button.getAttribute('data-submitted');

            var modalTitle = messageModalElement.querySelector('.modal-title');
            var modalName = messageModalElement.querySelector('#modalName');
            var modalEmail = messageModalElement.querySelector('#modalEmail');
            var modalSubmitted = messageModalElement.querySelector('#modalSubmitted');
            var modalMessage = messageModalElement.querySelector('#modalMessage');

            modalTitle.textContent = 'Detail Pesan dari ' + name;
            modalName.textContent = name;
            modalEmail.textContent = email;
            modalSubmitted.textContent = submitted;
            modalMessage.textContent = message;
        });
    }
</script>