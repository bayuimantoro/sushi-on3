<?php
session_start();

// Ambil pesan sukses dan hapus dari session
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Berhasil</title>
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 80px 20px;
        }

        .message-box {
            background-color: #1e1e1e;
            border: 2px solid #e50914;
            border-radius: 12px;
            padding: 40px;
            display: inline-block;
        }

        .message-box h1 {
            color: #e50914;
            margin-bottom: 20px;
        }

        .message-box p {
            font-size: 1.2em;
        }

        a {
            color: #e50914;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="message-box">
    <h1>🎉 Pesanan Berhasil!</h1>
    <p><?= htmlspecialchars($success_message ?? 'Terima kasih, pesanan Anda telah diterima.') ?></p>
    <p><a href="order.php">Pesan Lagi</a></p>
</div>

</body>
</html>
