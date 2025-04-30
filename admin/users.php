<?php
session_start();
include '../db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Delete user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: users.php");
    exit();
}

// Update user
if (isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $username, $email, $role, $id);
    $stmt->execute();

    header("Location: users.php");
    exit();
}

$edit_id = $_GET['edit'] ?? null;
$users = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola User</title>
</head>

<body>
    <h1>Daftar User</h1>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $users->fetch_assoc()) : ?>
            <?php if ($edit_id == $row['id']) : ?>
                <!-- Tampilkan form edit langsung di baris -->
                <form method="POST">
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>" required></td>
                        <td><input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>"></td>
                        <td>
                            <select name="role">
                                <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="customer" <?= $row['role'] == 'customer' ? 'selected' : '' ?>>Customer</option>
                            </select>
                        </td>
                        <td>
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" name="update_user">Simpan</button>
                            <a href="users.php">Batal</a>
                        </td>
                    </tr>
                </form>
            <?php else : ?>
                <!-- Baris normal -->
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= $row['role'] ?></td>
                    <td>
                        <a href="?edit=<?= $row['id'] ?>">Edit</a> |
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin?')">Hapus</a>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endwhile; ?>
    </table>
</body>

</html>