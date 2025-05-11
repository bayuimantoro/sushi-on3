<?php
// 1. MULAI SESSION (HARUS DI BARIS PALING ATAS)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. AKTIFKAN PELAPORAN ERROR UNTUK DEBUGGING (HAPUS ATAU KOMENTARI DI LINGKUNGAN PRODUKSI)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. --- KONFIGURASI DATABASE ---
// Pastikan path ke db.php sesuai dengan struktur direktori Anda.
// Jika kontak.php berada di direktori yang sama dengan login.php (misal /views/),
// dan db.php berada satu level di atasnya (/db.php), maka path ini benar.
require_once __DIR__ . '/../db.php'; // Menggunakan koneksi $conn dari db.php

// Variabel untuk menyimpan pesan feedback
$message_status = '';
$message_text = '';

// 4. Ambil detail user dari session jika ada
$loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
// Gunakan $_SESSION['name'] atau $_SESSION['username'] sesuai yang di-set di login.php
$loggedInUserName = isset($_SESSION['name']) ? $_SESSION['name'] : (isset($_SESSION['username']) ? $_SESSION['username'] : '');
$loggedInUserEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// 5. --- PROSES FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message_content = trim($_POST['message']);
    $name_for_db = '';
    $email_for_db = '';

    if ($loggedInUserId) {
        // Jika user login, nama dan email diambil dari session
        $name_for_db = $loggedInUserName;
        $email_for_db = $loggedInUserEmail;
    } else {
        // Jika user tidak login, nama dan email diambil dari input form
        $name_for_db = trim($_POST['name']);
        $email_for_db = trim($_POST['email']);
    }

    // Validasi
    if (empty($name_for_db) || empty($email_for_db) || empty($message_content)) {
        $message_status = 'error';
        $message_text = 'Semua kolom (Nama, Email, Pesan) wajib diisi.';
    } elseif (!filter_var($email_for_db, FILTER_VALIDATE_EMAIL)) {
        $message_status = 'error';
        $message_text = 'Format email tidak valid.';
    } else {
        // Cek apakah koneksi $conn dari db.php berhasil
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO contact_messages (user_id, name, email, message) VALUES (?, ?, ?, ?)");
            
            if ($stmt) {
                $stmt->bind_param("isss", $loggedInUserId, $name_for_db, $email_for_db, $message_content);

                if ($stmt->execute()) {
                    $message_status = 'success';
                    $message_text = 'Pesan Anda berhasil dikirim!';
                    // Pertimbangkan untuk mengosongkan $message_content jika sukses
                    // $message_content = ''; // Jika ingin textarea pesan dikosongkan setelah submit
                } else {
                    $message_status = 'error';
                    $message_text = 'Gagal mengirim pesan. Silakan coba lagi nanti.';
                    error_log("Contact form - Execute statement gagal: " . $stmt->error); // Catat error detail
                }
                $stmt->close();
            } else {
                $message_status = 'error';
                $message_text = 'Gagal menyiapkan permintaan. Silakan coba lagi nanti.';
                error_log("Contact form - Prepare statement gagal: " . $conn->error); // Catat error detail
            }
            // Tidak perlu $conn->close() jika koneksi di-manage oleh db.php dan digunakan di tempat lain.
            // Jika db.php membuat koneksi baru setiap kali di-include, maka $conn->close() aman.
            // Umumnya, koneksi yang di-include dibiarkan terbuka hingga akhir skrip.
        } else {
            $message_status = 'error';
            $message_text = 'Koneksi database tidak tersedia. Hubungi administrator.';
            error_log("Contact form - Variabel koneksi \$conn tidak valid atau null.");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami - SushiOn3</title>
    <!-- Pastikan Bootstrap CSS Anda sudah ter-link dengan benar -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: white; min-height: 100vh; font-family: 'Poppins', sans-serif; /* Asumsi dari login.php */ }
        .form-control.bg-dark { background-color: #343a40 !important; color: white !important; border: 1px solid #495057; }
        .form-control.bg-dark::placeholder { color: #adb5bd; }
        .form-control.bg-dark:focus { border-color: #c62828; box-shadow: 0 0 0 0.2rem rgba(198, 40, 40, 0.25); }
        .form-control[readonly] { background-color: #2a2a2a !important; /* Warna sedikit beda untuk readonly */ }
        .btn-danger { background-color: #c62828; border-color: #c62828; }
        .btn-danger:hover { background-color: #e50914; border-color: #e50914; /* Samakan dengan hover login */ }
        .alert-success { background-color: #28a745; color: white; border-color: #1e7e34; }
        .alert-danger { background-color: #dc3545; color: white; border-color: #bd2130; }
        /* Style tambahan dari login.php yang mungkin relevan */
        .container h1.text-center { color: #c62828; font-weight: bold; }
        .container p a { color: #e50914; text-decoration: none; }
        .container p a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<section class="py-5">
    <div class="container">
        <h1 class="text-center mb-5">Hubungi Kami</h1>

        <?php if (!empty($message_text)): ?>
            <div class="alert <?php echo ($message_status == 'success') ? 'alert-success' : 'alert-danger'; ?> text-center mb-4" role="alert">
                <?php echo htmlspecialchars($message_text); ?>
            </div>
        <?php endif; ?>

        <!-- Arahkan action ke file ini sendiri -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><?php echo isset($_GET['page']) ? '?page=' . htmlspecialchars($_GET['page']) : ''; ?>" method="POST" class="row g-4">
            <!--kolom nama-->
            <div class="col-md-6">
                <label for="name" class="form-label">Nama:</label>
                <input type="text" id="name" name="name" required 
                       class="form-control bg-dark text-white" 
                       value="<?php echo htmlspecialchars($loggedInUserName); ?>"
                       <?php if ($loggedInUserId) echo 'readonly'; // Jika login, buat readonly ?> >
            </div>

            <!--kolom email-->
            <div class="col-md-6">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" required 
                       class="form-control bg-dark text-white"
                       value="<?php echo htmlspecialchars($loggedInUserEmail); ?>"
                       <?php if ($loggedInUserId) echo 'readonly'; // Jika login, buat readonly ?> >
            </div>

            <!--kolom pesan-->
            <div class="col-12">
                <label for="message" class="form-label">Pesan:</label>
                <textarea id="message" name="message" rows="5" required class="form-control bg-dark text-white"><?php echo isset($message_content) ? htmlspecialchars($message_content) : ''; ?></textarea>
            </div>

            <!--tombol kirim-->
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-danger px-5 py-2 rounded-pill">Kirim</button>
            </div>
        </form>

        <?php
        // Asumsi file login.php dan logout.php berada di direktori yang sama dengan kontak.php
        // Sesuaikan path jika berbeda
        $loginPage = 'login.php'; 
        $logoutPage = 'logout.php';
        // Jika ada parameter 'page' di URL, coba pertahankan
        $pageQuery = isset($_GET['page']) ? '?page=' . htmlspecialchars($_GET['page']) : '';
        $redirectQueryLogin = isset($_GET['page']) ? '?redirect=' . htmlspecialchars($_GET['page']) : ''; // Untuk login, agar kembali ke halaman ini
        ?>

        <?php if (!$loggedInUserId): ?>
        <p class="text-center mt-4">
            Sudah punya akun? <a href="<?php echo $loginPage . $redirectQueryLogin; ?>">Login di sini</a> untuk mengisi data otomatis.
        </p>
        <?php else: ?>
        <p class="text-center mt-4">
            Anda login sebagai <?php echo htmlspecialchars($loggedInUserName); ?>. <a href="<?php echo $logoutPage; ?>">Logout</a>.
        </p>
        <?php endif; ?>

    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>