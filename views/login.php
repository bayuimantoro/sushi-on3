<?php
// 1. Pastikan session_start() dipanggil hanya jika sesi belum aktif
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Include koneksi database
require_once __DIR__ . '/../db.php'; // db.php berada satu level di atas direktori 'views'

$login_error = '';

// 3. Proses login jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $login_error = "Username dan Password harus diisi.";
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if ($conn) {
            // MODIFIKASI QUERY: Hanya ambil kolom yang ada di tabel Anda (id, username, password, role, email)
            $stmt = $conn->prepare("SELECT id, username, password, role, email FROM users WHERE username = ?");

            if ($stmt) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['password'])) {
                        session_regenerate_id(true);

                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];

                        // MODIFIKASI PENGISIAN SESSION:
                        // Gunakan $user['username'] untuk 'name'
                        // Gunakan $user['email'] untuk 'email' (sesuai nama kolom di tabel Anda)
                        $_SESSION['name'] = $user['username'];
                        $_SESSION['email'] = $user['email'] ?? ''; // Fallback ke string kosong jika email NULL

                        // Handle redirect (logika tetap sama)
                        if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                            $redirect_path_from_root = ltrim($_GET['redirect'], '/');
                            $target_location = '../' . $redirect_path_from_root;

                            if (file_exists(__DIR__ . '/../' . $redirect_path_from_root)) {
                                header("Location: " . $target_location);
                            } else {
                                error_log("Peringatan Login: Upaya redirect tidak valid ke '" . htmlspecialchars($_GET['redirect']) . "'. Mengarahkan ke index.");
                                header("Location: ../index.php");
                            }
                        } else {
                            header("Location: ../index.php");
                        }
                        exit();
                    } else {
                        $login_error = "Password salah.";
                    }
                } else {
                    $login_error = "Username tidak ditemukan.";
                }
                $stmt->close();
            } else {
                $login_error = "Terjadi kesalahan pada sistem (prepare failed). Silakan coba lagi nanti.";
                error_log("MySQLi prepare failed di login.php: " . $conn->error);
            }
        } else {
            $login_error = "Koneksi ke database gagal. Hubungi administrator.";
            error_log("Koneksi database ($conn) gagal di login.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SushiOn3</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #121212;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #fff;
        }

        .container {
            background-color: #1e1e1e;
            border-radius: 12px;
            padding: 40px;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.6);
        }

        .container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #fff;
        }

        label {
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
            color: #ccc;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #333;
            border-radius: 8px;
            background: #333;
            color: #fff;
            box-sizing: border-box;
        }

        input:focus {
            background-color: #444;
            outline: none;
            border-color: #e50914;
            box-shadow: 0 0 5px rgba(229, 9, 20, 0.5);
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #e50914;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            color: white;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #b20710;
        }

        .error-message {
            background-color: rgba(255, 77, 77, 0.9);
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 20px;
            border: 1px solid #cc0000;
        }

        .container p {
            text-align: center;
            font-size: 14px;
            color: #aaa;
            margin-top: 20px;
        }

        .container p a {
            color: #e50914;
            text-decoration: none;
        }

        .container p a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($login_error)) : ?>
            <div class="error-message"><?= htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php<?= isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : '' ?>">
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" name="login">Login</button>

            <p>Belum punya akun? <a href="register.php<?= isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : '' ?>">Daftar di sini</a></p>
        </form>
    </div>
</body>

</html>