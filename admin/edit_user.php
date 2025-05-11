<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//pengecekan login & role ada di admin_header.php

require_once __DIR__ . '/../db.php';
$current_page = 'users.php'; //supaya link "Kelola Pengguna" di sidebar tetap aktif
$page_title = "Edit Pengguna";

$user_id_to_edit = $_GET['id'] ?? null;
if (!$user_id_to_edit || !is_numeric($user_id_to_edit)) {
    $_SESSION['error_message'] = "ID pengguna tidak valid.";
    header("Location: users.php");
    exit;
}

if (isset($_SESSION['user_id']) && $user_id_to_edit == $_SESSION['user_id']) {
     $_SESSION['error_message'] = "Anda tidak dapat mengedit akun Anda sendiri melalui formulir ini. Gunakan halaman profil jika tersedia.";
     header("Location: users.php");
     exit;
}

$errors = [];
$username_db = '';
$email_db = '';
$role_db = '';

if ($conn) {
    $stmt_select = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
    if ($stmt_select) {
        $stmt_select->bind_param("i", $user_id_to_edit);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        if ($user = $result->fetch_assoc()) {
            $username_db = $user['username'];
            $email_db = $user['email'];
            $role_db = $user['role'];
            $page_title = "Edit Pengguna: " . htmlspecialchars($username_db); //update judul halaman
        } else {
            $_SESSION['error_message'] = "Pengguna tidak ditemukan.";
            header("Location: users.php");
            exit;
        }
        $stmt_select->close();
    } else {
        $_SESSION['error_message'] = "Gagal mengambil data pengguna: " . $conn->error;
        header("Location: users.php");
        exit;
    }
} else {
    $_SESSION['error_message'] = "Koneksi database gagal.";
    header("Location: users.php");
    exit;
}

$username_form = $username_db;
$email_form = $email_db;
$role_form = $role_db;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_form = trim($_POST['username'] ?? $username_db);
    $email_form = trim($_POST['email'] ?? $email_db);
    $role_form = $_POST['role'] ?? $role_db;
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    if (empty($username_form)) $errors['username'] = "Username tidak boleh kosong.";
    else if ($conn) {
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        if($stmt_check){
            $stmt_check->bind_param("si", $username_form, $user_id_to_edit);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $errors['username'] = "Username sudah digunakan oleh pengguna lain.";
            }
            $stmt_check->close();
        } else {
             $errors['db'] = "Gagal memeriksa username: " . $conn->error;
        }
    }

    if (empty($email_form)) $errors['email'] = "Email tidak boleh kosong.";
    elseif (!filter_var($email_form, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Format email tidak valid.";
    else if ($conn) {
        $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        if($stmt_check_email){
            $stmt_check_email->bind_param("si", $email_form, $user_id_to_edit);
            $stmt_check_email->execute();
            if ($stmt_check_email->get_result()->num_rows > 0) {
                $errors['email'] = "Email sudah terdaftar oleh pengguna lain.";
            }
            $stmt_check_email->close();
        } else {
            $errors['db'] = "Gagal memeriksa email: " . $conn->error;
        }
    }
    
    if (!in_array($role_form, ['admin', 'customer'])) {
         $errors['role'] = "Role tidak valid.";
    }

    $hashed_password_to_update = null;
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) $errors['new_password'] = "Password baru minimal 6 karakter.";
        if ($new_password !== $confirm_new_password) $errors['confirm_new_password'] = "Konfirmasi password baru tidak cocok.";
        if (empty($errors['new_password']) && empty($errors['confirm_new_password'])) {
            $hashed_password_to_update = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }

    if (empty($errors)) {
        if ($conn) {
            if ($hashed_password_to_update) {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username_form, $email_form, $role_form, $hashed_password_to_update, $user_id_to_edit);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username_form, $email_form, $role_form, $user_id_to_edit);
            }

            if ($stmt) {
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Data pengguna berhasil diperbarui!";
                    header("Location: users.php");
                    exit;
                } else {
                    $errors['db'] = "Gagal memperbarui pengguna: " . $stmt->error;
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
                <i class="fas fa-angle-right"></i> <a href="users.php">Kelola Pengguna</a>
                <i class="fas fa-angle-right"></i> <span>Edit Pengguna</span>
            </div>
        </div>
    </div>

    <?php if (!empty($errors['db'])): ?>
        <div class="alert alert-danger mx-3"><?php echo htmlspecialchars($errors['db']); ?></div>
    <?php endif; ?>

    <form action="edit_user.php?id=<?php echo $user_id_to_edit; ?>" method="POST">
        <div class="content-panel panel-primary">
            <div class="panel-header">
                <h3 class="panel-title">Form Edit Pengguna</h3>
            </div>
            <div class="panel-body">
                <div class="mb-3">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" id="username" name="username" value="<?php echo htmlspecialchars($username_form); ?>" required>
                    <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?php echo $errors['username']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email_form); ?>" required>
                    <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>" id="role" name="role" required>
                        <option value="customer" <?php echo ($role_form == 'customer') ? 'selected' : ''; ?>>Customer</option>
                        <option value="admin" <?php echo ($role_form == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                     <?php if (isset($errors['role'])): ?><div class="invalid-feedback"><?php echo $errors['role']; ?></div><?php endif; ?>
                </div>

                <hr>
                <p class="text-muted">Kosongkan field password jika tidak ingin mengubah password pengguna ini.</p>

                <div class="mb-3">
                    <label for="new_password" class="form-label">Password Baru</label>
                    <input type="password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>" id="new_password" name="new_password">
                    <?php if (isset($errors['new_password'])): ?><div class="invalid-feedback"><?php echo $errors['new_password']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="confirm_new_password" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control <?php echo isset($errors['confirm_new_password']) ? 'is-invalid' : ''; ?>" id="confirm_new_password" name="confirm_new_password">
                    <?php if (isset($errors['confirm_new_password'])): ?><div class="invalid-feedback"><?php echo $errors['confirm_new_password']; ?></div><?php endif; ?>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="users.php" class="btn btn-secondary">Batal</a>
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