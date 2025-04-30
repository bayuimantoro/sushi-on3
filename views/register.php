<?php
session_start();
include '../db.php';

$register_error = '';
$register_success = '';

if (isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $email = $_POST['reg_email'];
    $password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        $register_error = "Username sudah digunakan.";
    } else {
        $insert = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'customer')");
        $insert->bind_param("sss", $username, $password, $email);
        if ($insert->execute()) {
            $register_success = "Pendaftaran berhasil. Silakan login.";
        } else {
            $register_error = "Terjadi kesalahan saat mendaftar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SushiOn3</title>
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
            height: 100vh;
        }

        .container {
            background-color: #1e1e1e;
            border-radius: 12px;
            padding: 40px;
            width: 400px;
            max-width: 95%;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.6);
            color: #fff;
        }

        .container h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        label {
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
            color: #ccc;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: none;
            border-radius: 8px;
            background: #333;
            color: #fff;
        }

        input:focus {
            background-color: #444;
            outline: none;
        }

        button {
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

        button:hover {
            background-color: #b20710;
        }

        .error-message,
        .success-message {
            background-color: #ff4d4d;
            color: white;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 20px;
        }

        .success-message {
            background-color: #4caf50;
        }

        p {
            text-align: center;
            font-size: 14px;
            color: #aaa;
            margin-top: 10px;
        }

        a {
            color: #e50914;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Register</h2>
        <?php if (!empty($register_error)) : ?>
            <div class="error-message"><?= htmlspecialchars($register_error) ?></div>
        <?php endif; ?>
        <?php if (!empty($register_success)) : ?>
            <div class="success-message"><?= htmlspecialchars($register_success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="reg_username">Username</label>
            <input type="text" name="reg_username" required>

            <label for="reg_email">Email</label>
            <input type="email" name="reg_email" required>

            <label for="reg_password">Password</label>
            <input type="password" name="reg_password" required>

            <button type="submit" name="register">Daftar</button>

            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </form>
    </div>
</body>

</html>