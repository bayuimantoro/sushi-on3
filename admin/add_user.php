<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Pengecekan login & role ada di admin_header.php

require_once __DIR__ . '/../db.php';
$current_page = 'users.php'; // Agar link "Kelola Pengguna" di sidebar tetap aktif
$page_title = "Tambah Pengguna Baru";

$errors = [];
$username = '';
$email = '';
$role = 'customer'; // Default role

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'customer';

    // Validasi
    if (empty($username)) $errors['username'] = "Username tidak boleh kosong.";
    else if ($conn) { // Hanya cek jika koneksi berhasil
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        if ($stmt_check) {
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $errors['username'] = "Username sudah digunakan.";
            }
            $stmt_check->close();
        } else {
            $errors['db'] = "Gagal memeriksa username: " . $conn->error;
        }
    }

    if (empty($email)) $errors['email'] = "Email tidak boleh kosong.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Format email tidak valid.";
    else if ($conn) { // Hanya cek jika koneksi berhasil
        $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt_check_email) {
            $stmt_check_email->bind_param("s", $email);
            $stmt_check_email->execute();
            if ($stmt_check_email->get_result()->num_rows > 0) {
                $errors['email'] = "Email sudah terdaftar.";
            }
            $stmt_check_email->close();
        } else {
            $errors['db'] = "Gagal memeriksa email: " . $conn->error;
        }
    }

    if (empty($password)) $errors['password'] = "Password tidak boleh kosong.";
    elseif (strlen($password) < 6) $errors['password'] = "Password minimal 6 karakter.";
    if ($password !== $confirm_password) $errors['confirm_password'] = "Konfirmasi password tidak cocok.";
    
    if (!in_array($role, ['admin', 'customer'])) {
        $errors['role'] = "Role tidak valid.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Pengguna baru berhasil ditambahkan!";
                    header("Location: users.php");
                    exit;
                } else {
                    $errors['db'] = "Gagal menyimpan pengguna: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors['db'] = "Gagal menyiapkan statement: " . $conn->error;
            }
        } else {
            // Error koneksi sudah ditangani di admin_header, tapi bisa juga di sini
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
                <i class="fas fa-angle-right"></i> <span><?php echo htmlspecialchars($page_title); ?></span>
            </div>
        </div>
    </div>

    <?php if (!empty($errors['db'])): ?>
        <div class="alert alert-danger mx-3"><?php echo htmlspecialchars($errors['db']); ?></div>
    <?php endif; ?>

    <form action="add_user.php" method="POST">
        <div class="content-panel panel-primary">
            <div class="panel-header">
                <h3 class="panel-title">Form Tambah Pengguna</h3>
            </div>
            <div class="panel-body">
                <div class="mb-3">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?php echo $errors['username']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                    <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
                    <?php if (isset($errors['confirm_password'])): ?><div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>" id="role" name="role" required>
                        <option value="customer" <?php echo ($role == 'customer') ? 'selected' : ''; ?>>Customer</option>
                        <option value="admin" <?php echo ($role == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                    <?php if (isset($errors['role'])): ?><div class="invalid-feedback"><?php echo $errors['role']; ?></div><?php endif; ?>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
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