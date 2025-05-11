<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//cek login dan role TERPUSAT di sini
if (!isset($_SESSION['user_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: ../views/login.php?redirect=" . $redirect_url);
    exit;
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    //menampilkan pesan error sederhana jika akses ditolak
    echo "<div style='text-align:center; padding: 50px; font-family: sans-serif; background-color: #f8f9fa; color: #333; min-height:100vh; display:flex; flex-direction:column; justify-content:center; align-items:center;'>";
    echo "<h1 style='color:#dc3545; margin-bottom:20px;'>Akses Ditolak</h1>";
    echo "<p style='font-size:1.1em; margin-bottom:10px;'>Anda tidak memiliki izin untuk mengakses halaman ini.</p>";
    echo "<p><a href='../index.php' style='color: #007bff; text-decoration:none; font-weight:bold; padding:10px 20px; background-color:#f0f0f0; border-radius:5px;'>Kembali ke Beranda</a></p>";
    echo "</div>";
    exit;
}

$admin_username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';
$page_title = isset($page_title) ? htmlspecialchars($page_title) : 'Admin Area';
$current_page_for_header = isset($current_page) ? $current_page : basename($_SERVER['PHP_SELF']);

//path ke gambar avatar
$avatar_path = "assets/images/admin-avatar.png";
$default_avatar_path = "assets/images/default-avatar.png";

if (!file_exists($avatar_path))

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title . ' - SushiOn3 Admin'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_style.css">
</head>

<body class="admin-body">
    <header class="admin-top-header">
        <div class="logo-area">
            <button class="sidebar-toggle-btn" id="sidebarToggleDesktop"><i class="fas fa-bars"></i></button>
            <span class="logo-text">SushiOn3</span>
        </div>
        <div class="header-actions">
            <a href="#" class="header-action-item" title="Notifikasi">
                <i class="fas fa-bell"></i>
                <span class="badge">3</span>
            </a>
            <div class="dropdown">
                <a href="#" class="header-action-item dropdown-toggle" title="Pengguna" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $avatar_path; ?>" alt="User Avatar" class="user-avatar-sm">
                </a>
                
            </div>
            <button class="sidebar-toggle-btn-mobile" id="sidebarToggleMobile"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <div class="admin-body-container">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-user-profile">
                <img src="<?php echo $avatar_path; ?>" alt="Admin Avatar" class="profile-avatar">
                <div class="profile-info">
                    <span class="profile-name"><?php echo $admin_username; ?></span>
                    <span class="profile-status"><i class="fas fa-circle text-success"></i> Online</span>
                </div>
            </div>
            <div class="sidebar-search">
                <input type="text" placeholder="Cari menu...">
                <button><i class="fas fa-search"></i></button>
            </div>
            <nav class="main-navigation">
                <span class="nav-section-title">MAIN NAVIGATION</span>
                <a href="dashboard.php" class="nav-link <?php echo ($current_page_for_header == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
                <a href="users.php" class="nav-link <?php echo (in_array($current_page_for_header, ['users.php', 'add_user.php', 'edit_user.php'])) ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> <span>Kelola Pengguna</span>
                </a>
                <a href="menu.php" class="nav-link has-submenu <?php echo (in_array($current_page_for_header, ['menu.php', 'add_menu_item.php', 'edit_menu_item.php'])) ? 'active open' : ''; ?>">
                    <i class="fas fa-utensils"></i> <span>Kelola Menu</span> <i class="fas fa-chevron-right submenu-arrow"></i>
                </a>
                <ul class="submenu <?php echo (in_array($current_page_for_header, ['menu.php', 'add_menu_item.php', 'edit_menu_item.php'])) ? 'open' : ''; ?>">
                    <li><a href="menu.php" class="nav-link <?php echo ($current_page_for_header == 'menu.php') ? 'active' : ''; ?>">Daftar Menu</a></li>
                    <li><a href="add_menu_item.php" class="nav-link <?php echo ($current_page_for_header == 'add_menu_item.php') ? 'active' : ''; ?>">Tambah Menu</a></li>
                </ul>
                <a href="orders_admin.php" class="nav-link <?php echo (in_array($current_page_for_header, ['orders_admin.php', 'edit_order_status.php', 'view_order_details.php'])) ? 'active' : ''; ?>">
                    <i class="fas fa-receipt"></i> <span>Kelola Pesanan</span>
                </a>
                <a href="contact_admin.php" class="nav-link <?php echo ($current_page_for_header == 'contact_admin.php') ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i> <span>Pesan Kontak</span>
                </a>
                <div class="nav-separator"></div>
                <a href="../index.php" target="_blank" class="nav-link">
                    <i class="fas fa-globe"></i> <span>Lihat Situs</span>
                </a>
            </nav>
            <div class="sidebar-footer-links">
                <a href="../views/logout.php" class="nav-link logout-link">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </div>
        </aside>