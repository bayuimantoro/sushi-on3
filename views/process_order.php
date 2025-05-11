<?php
// Aktifkan pelaporan error untuk development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session sudah dimulai oleh index.php, jadi tidak perlu session_start() lagi di sini.
// Jika file ini diakses langsung (seharusnya tidak), maka session_start() diperlukan.
// Namun, dengan pola front controller, index.php yang menanganinya.

// Cek apakah form dikirim dengan metode POST (seharusnya sudah divalidasi oleh index.php)
// Namun, sebagai lapisan tambahan jika file ini bisa diakses langsung:
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika diakses langsung, arahkan ke halaman order via index.php
    header('Location: /SushiOn3/index.php?page=order');
    exit;
}

// Ambil data dari form
$user_id_val = $_POST['user_id'] ?? null; // Menggunakan nama variabel yang konsisten dengan views/order.php
$name_val = trim($_POST['name'] ?? '');
$email_val = trim($_POST['email'] ?? '');
$order_details_text = trim($_POST['order_details'] ?? ''); // Lebih deskriptif
$delivery_method_val = $_POST['delivery_method'] ?? '';
$total_amount = (int)($_POST['total'] ?? 0); // Lebih deskriptif dan memastikan integer

// Validasi dasar (Anda bisa menambahkan validasi yang lebih spesifik, misal format email)
if (empty($name_val) || empty($email_val) || !filter_var($email_val, FILTER_VALIDATE_EMAIL) || empty($order_details_text) || empty($delivery_method_val) || $total_amount <= 0) {
    $_SESSION['pesan_error'] = 'Semua field wajib diisi dengan benar dan email harus valid.';
    // Redirect kembali ke halaman order melalui index.php
    header('Location: /SushiOn3/index.php?page=order');
    exit;
}

// Koneksi database
try {
    // Pastikan nama database (dbname) sesuai dengan yang Anda gunakan (sushion3)
    $pdo = new PDO('mysql:host=localhost;dbname=sushion3', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8"); // Set encoding jika perlu

    // Query INSERT disesuaikan dengan nama kolom di tabel 'orders' Anda
    // Kolom 'created_at' akan diisi otomatis oleh MySQL jika di-set DEFAULT CURRENT_TIMESTAMP
    $sql = "INSERT INTO orders (user_id, name, email, order_details, delivery_method, total)
            VALUES (:user_id, :name, :email, :order_details, :delivery_method, :total)";

    $stmt = $pdo->prepare($sql);

    // Bind parameters ke statement
    // Menggunakan nama variabel yang sudah kita definisikan di atas
    $stmt->bindParam(':user_id', $user_id_val, $user_id_val === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindParam(':name', $name_val);
    $stmt->bindParam(':email', $email_val);
    $stmt->bindParam(':order_details', $order_details_text);
    $stmt->bindParam(':delivery_method', $delivery_method_val);
    $stmt->bindParam(':total', $total_amount, PDO::PARAM_INT);

    // Eksekusi statement
    if ($stmt->execute()) {
        // Pesan sukses yang lebih informatif
        $_SESSION['success_message'] = 'Pesanan atas nama ' . htmlspecialchars($name_val) . ' berhasil dikirim!';
        
        // Opsional: Simpan ID pesanan terakhir jika ingin ditampilkan di halaman sukses
        // $_SESSION['last_order_id'] = $pdo->lastInsertId();

        // Redirect ke halaman order_success melalui index.php
        // Ini mengasumsikan Anda akan membuat case 'order_success' di index.php
        // yang akan me-require file views/order_success.php
        header('Location: /SushiOn3/index.php?page=order_success');
        exit;
    } else {
        // Seharusnya tidak sampai sini jika ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION aktif dan query gagal
        $_SESSION['pesan_error'] = 'Gagal menyimpan pesanan karena kesalahan yang tidak diketahui.';
        header('Location: /SushiOn3/index.php?page=order');
        exit;
    }

} catch (PDOException $e) {
    // Catat error detail ke log server (lebih baik daripada menampilkannya langsung ke user di production)
    error_log("PDO Error: " . $e->getMessage() . " in " . __FILE__ . " on line " . __LINE__);
    
    $_SESSION['pesan_error'] = 'Gagal memproses pesanan karena terjadi masalah pada sistem. Silakan coba lagi nanti.';
    // $_SESSION['pesan_error'] = 'Gagal memproses pesanan: ' . $e->getMessage(); // Untuk debugging
    header('Location: /SushiOn3/index.php?page=order');
    exit;
}
?>