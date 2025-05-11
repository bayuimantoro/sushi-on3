<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Include koneksi database
require_once __DIR__ . '/../db.php'; // Pastikan path ini benar

// 2. Tentukan status login user
$is_logged_in = isset($_SESSION['user_id']);

// 3. Ambil data user jika login, atau set nilai default jika tidak
$user_id_val = '';
$nama_pengguna = '';
$email_pengguna = '';

if ($is_logged_in) {
    $user_id_val = $_SESSION['user_id'];
    $nama_pengguna = $_SESSION['name'] ?? 'Data Tidak Tersedia';
    $email_pengguna = $_SESSION['email'] ?? 'Data Tidak Tersedia';
}

// Tampilkan pesan error dari proses sebelumnya (jika ada)
if (isset($_SESSION['pesan_error'])) {
    echo '<div class="alert alert-danger" style="padding:15px;margin-bottom:20px;border-radius:4px;background:#f8d7da;color:#721c24;">'
        . htmlspecialchars($_SESSION['pesan_error']) .
        '</div>';
    unset($_SESSION['pesan_error']);
}
?>

<style>
    /* ... (CSS Anda yang lain untuk .order-section, form, tombol, dll. tetap sama) ... */
    .quantity-controls {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .quantity-controls button {
        background-color: #e50914;
        border: none;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        font-weight: bold;
        font-size: 16px;
        cursor: pointer;
    }

    .quantity-controls span {
        min-width: 20px;
        text-align: center;
        font-weight: bold;
        color: white;
        /* Pastikan warna teks kontras dengan latar belakang item */
    }

    .order-section {
        background-color: var(--secondary-color, #121212);
        color: var(--text-color, #ffffff);
        padding: 60px 20px;
        min-height: 100vh;
    }

    .order-section h1 {
        color: var(--primary-color, #e50914);
        font-weight: bold;
        margin-bottom: 40px;
    }

    .form-label {
        color: var(--accent-color, #cccccc);
        font-weight: 500;
    }

    .form-control {
        background-color: #1e1e1e;
        border: 1px solid var(--accent-color, #555555);
        color: var(--text-color, #ffffff);
    }

    .form-control:focus {
        background-color: #1e1e1e;
        color: var(--text-color, #ffffff);
        border-color: var(--primary-color, #e50914);
        box-shadow: none;
    }

    .form-control[readonly] {
        background-color: #2a2a2a;
        cursor: not-allowed;
    }

    .btn-danger {
        background-color: var(--primary-color, #e50914);
        border: none;
        padding: 10px 30px;
        font-weight: bold;
        border-radius: 30px;
    }

    .btn-danger:hover:not(:disabled) {
        background-color: #a91d1d;
    }

    .btn-danger:disabled {
        background-color: #555;
        color: #999;
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* Gaya untuk galeri menu */
    .menu-gallery {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        /* Atau 'flex-start' jika ingin rata kiri */
        gap: 25px;
        /* Jarak antar item sedikit lebih besar */
        margin-top: 30px;
        margin-bottom: 40px;
        /* Beri jarak sebelum form */
    }

    .menu-item {
        background-color: #1e1e1e;
        /* Latar belakang kartu item */
        border-radius: 12px;
        overflow: hidden;
        /* Penting agar gambar tidak keluar dari border-radius */
        text-align: center;
        width: 200px;
        /* <<< LEBAR KARTU ITEM DIPERBESAR >>> */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        /* Menggunakan flexbox untuk tata letak internal */
        flex-direction: column;
        /* Konten ditumpuk secara vertikal */
    }

    .menu-item:hover {
        transform: translateY(-5px);
        /* Efek hover sedikit terangkat */
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.7);
    }

    .menu-item img {
        width: 100%;
        /* Gambar mengisi lebar kartu menu item */
        height: 150px;
        /* <<< TINGGI GAMBAR DIPERBESAR >>> */
        object-fit: cover;
        /* Memastikan gambar menutupi area, kelebihan dipotong */
        display: block;
    }

    .menu-item .menu-info {
        /* Wrapper untuk teks agar bisa diberi padding */
        padding: 15px 10px;
        flex-grow: 1;
        /* Agar bagian info ini mengisi sisa ruang jika tinggi item bervariasi */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        /* Menyebarkan konten di dalam info */
    }

    .menu-item h3 {
        color: #f0f0f0;
        /* Warna teks nama menu */
        margin-top: 0;
        margin-bottom: 8px;
        font-size: 1.05rem;
        /* Sedikit lebih besar */
        font-weight: 600;
        min-height: 2.2em;
        /* Beri ruang untuk 2 baris nama menu */
        line-height: 1.1em;
    }

    .price-tag {
        color: #e50914;
        /* Harga dengan warna primer */
        font-weight: bold;
        font-size: 1rem;
        margin-bottom: 12px;
    }

    .menu-item.selected {
        border: 3px solid var(--primary-color, #e50914);
        box-shadow: 0 0 15px var(--primary-color, #e50914);
    }

    .alert-danger {
        /* ... style alert Anda ... */
    }

    .login-prompt {
        /* ... style login prompt Anda ... */
    }
</style>

<section class="order-section container">
    <h1 class="text-center">Pesan Sushi Favorit Anda!</h1>

    <?php if (!$is_logged_in): ?>
        <div class="login-prompt">
            Anda dapat melihat menu dan memilih item. Untuk menyelesaikan pesanan, silakan <strong>login</strong> atau <strong>daftar</strong> terlebih dahulu.
        </div>
    <?php endif; ?>

    <div class="menu-gallery">
        <?php
        if ($conn) {
            $sql_menu_items = "SELECT id, name, price, description, image_path, category FROM menu WHERE is_available = 1 ORDER BY category, name"; // Menambahkan filter is_available = 1
            $result_menu_items = $conn->query($sql_menu_items);

            if ($result_menu_items && $result_menu_items->num_rows > 0) {
                while ($item = $result_menu_items->fetch_assoc()) {
                    $image_url = !empty($item['image_path']) ? htmlspecialchars($item['image_path']) : 'assets/images/placeholder_menu.jpg';
        ?>
                    <div class="menu-item" data-item="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo intval($item['price']); ?>">
                        <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="menu-info">
                            <div> <!-- Div tambahan untuk mengelompokkan nama dan harga -->
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div class="price-tag">Rp<?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                            </div>
                            <div class="quantity-controls">
                                <button type="button" class="btn-minus">−</button>
                                <span class="item-qty">0</span>
                                <button type="button" class="btn-plus">+</button>
                            </div>
                        </div>
                    </div>
        <?php
                }
            } else {
                echo "<p class='text-center text-white'>Tidak ada item menu yang tersedia saat ini.</p>";
            }
        } else {
            echo "<p class='text-center text-white'>Gagal mengambil data menu. Silakan coba lagi nanti.</p>";
        }
        ?>
    </div>

    <!-- PERUBAHAN DI SINI: atribut action pada form -->
    <form action="/SushiOn3/index.php?action=process_order" method="POST" class="row g-4 mt-5" id="orderForm">
        <!-- Pastikan /SushiOn3/ adalah path yang benar ke root aplikasi Anda. 
             Jika aplikasi Anda ada di root domain (misal, http://localhost/), maka cukup:
             action="/index.php?action=process_order" 
        -->
        
        <?php if ($is_logged_in): ?>
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id_val) ?>">
        <?php endif; ?>

        <div class="col-md-6">
            <label for="name" class="form-label">Nama</label>
            <input type="text" class="form-control" id="name" name="name" required
                value="<?= htmlspecialchars($nama_pengguna) ?>" <?= $is_logged_in ? 'readonly' : '' ?>>
            <?php if (!$is_logged_in): ?>
                <small style="font-size:0.8em; color:#aaa;">Akan terisi otomatis setelah login.</small>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required
                value="<?= htmlspecialchars($email_pengguna) ?>" <?= $is_logged_in ? 'readonly' : '' ?>>
            <?php if (!$is_logged_in): ?>
                <small style="font-size:0.8em; color:#aaa;">Akan terisi otomatis setelah login.</small>
            <?php endif; ?>
        </div>
        <div class="col-12">
            <label for="order" class="form-label">Detail Pesanan</label>
            <textarea class="form-control" id="order" name="order_details" rows="5" required
                placeholder="Klik tombol + pada menu. Login untuk memesan."
                <?= !$is_logged_in ? 'readonly' : '' ?>></textarea>
        </div>
        <div class="col-md-6">
            <label for="delivery" class="form-label">Metode Pengiriman</label>
            <select class="form-control" id="delivery" name="delivery_method" required <?= !$is_logged_in ? 'disabled' : '' ?>>
                <option value="">Pilih metode pengiriman</option>
                <option value="pickup">Ambil di tempat</option>
                <option value="delivery">Delivery (Gratis Ongkir)</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="total" class="form-label">Total Harga</label>
            <input type="text" class="form-control" id="total" name="total_display" readonly value="Rp0">
            <input type="hidden" id="real_total" name="total" value="0">
        </div>
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-danger" id="submitOrderButton" <?= !$is_logged_in ? 'disabled' : '' ?>>
                <?= $is_logged_in ? 'Kirim Pesanan' : 'Login untuk Pesan' ?>
            </button>
        </div>
    </form>
</section>

<script>
    // ... (JavaScript Anda tetap sama seperti sebelumnya) ...
    document.addEventListener('DOMContentLoaded', function() {
        const menuItems = document.querySelectorAll('.menu-item');
        const orderTextarea = document.getElementById('order');
        const totalDisplay = document.getElementById('total');
        const realTotalInput = document.getElementById('real_total');
        const submitButton = document.getElementById('submitOrderButton');
        const deliverySelect = document.getElementById('delivery');
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');

        const isLoggedIn = <?= json_encode($is_logged_in) ?>;

        let selectedItems = [];

        menuItems.forEach(item => {
            const itemName = item.getAttribute('data-item');
            const itemPrice = parseInt(item.getAttribute('data-price'));
            const plusBtn = item.querySelector('.btn-plus');
            const minusBtn = item.querySelector('.btn-minus');
            const qtyDisplay = item.querySelector('.item-qty');

            if (plusBtn && minusBtn && qtyDisplay) {
                plusBtn.addEventListener('click', () => {
                    let existing = selectedItems.find(i => i.name === itemName);
                    if (!existing) {
                        existing = {
                            name: itemName,
                            price: itemPrice,
                            quantity: 0
                        };
                        selectedItems.push(existing);
                        item.classList.add('selected');
                    }
                    existing.quantity++;
                    qtyDisplay.textContent = existing.quantity;
                    updateOrderDetails();
                });

                minusBtn.addEventListener('click', () => {
                    const existingIndex = selectedItems.findIndex(i => i.name === itemName);
                    if (existingIndex > -1) {
                        selectedItems[existingIndex].quantity--;
                        if (selectedItems[existingIndex].quantity <= 0) {
                            selectedItems.splice(existingIndex, 1);
                            item.classList.remove('selected');
                            qtyDisplay.textContent = 0;
                        } else {
                            qtyDisplay.textContent = selectedItems[existingIndex].quantity;
                        }
                        updateOrderDetails();
                    }
                });
            }
        });

        function updateOrderDetails() {
            let orderText = '';
            let totalPrice = 0;

            selectedItems.forEach(item => {
                orderText += `${item.name} x${item.quantity} (Rp${(item.price * item.quantity).toLocaleString('id-ID')})\n`;
                totalPrice += item.price * item.quantity;
            });

            orderTextarea.value = orderText.trim();
            totalDisplay.value = `Rp${totalPrice.toLocaleString('id-ID')}`;
            realTotalInput.value = totalPrice;

            if (isLoggedIn) {
                const canSubmit = selectedItems.length > 0 && deliverySelect.value !== "";
                submitButton.disabled = !canSubmit;
            } else {
                submitButton.disabled = true;
                orderTextarea.readOnly = true;
                deliverySelect.disabled = true;

                if (selectedItems.length > 0) {
                    orderTextarea.placeholder = "Login untuk melanjutkan pesanan ini.";
                } else {
                    orderTextarea.placeholder = "Klik tombol + pada menu. Login untuk memesan.";
                }
            }
        }

        if (deliverySelect) {
            deliverySelect.addEventListener('change', updateOrderDetails);
        }

        updateOrderDetails();

        // Bagian ini untuk redirect ke login jika pengguna belum login dan menekan tombol pesan
        // Ini sudah ada dan seharusnya berfungsi dengan baik, tidak perlu diubah terkait action form
        if (!isLoggedIn && submitButton) {
            submitButton.addEventListener('click', function(event) {
                if (this.textContent.trim().toLowerCase().includes('login')) {
                    event.preventDefault(); // Mencegah submit form default
                    // Asumsi Anda memiliki halaman login.php di root atau views/
                    // Sesuaikan path ke login.php jika perlu
                    window.location.href = '/sushion3/index.php?page=login&redirect=views/order.php'; 
                }
            });
        }
    });
</script>