<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: ../views/login.php?redirect=" . $redirect_url);
    exit;
}

//cek apakah user memiliki role 'admin'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<div style='text-align:center; padding: 50px; font-family: Poppins, sans-serif;'>";
    echo "<h1>Akses Ditolak</h1>";
    echo "<p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>";
    echo "<p><a href='../index.php' style='color: #e50914; text-decoration:none;'>Kembali ke Beranda</a></p>";
    echo "</div>";
    exit;
}

//include koneksi database
require_once __DIR__ . '/../db.php';

$user_count = 0;
$menu_item_count = 0;
$contact_message_count = 0;
$pending_orders_count = 0;
$total_revenue_this_month = 0;

if ($conn) {
    $result_users = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result_users) $user_count = $result_users->fetch_assoc()['count'];

    $result_menu_items = $conn->query("SELECT COUNT(*) as count FROM menu_items");
    if ($result_menu_items) $menu_item_count = $result_menu_items->fetch_assoc()['count'];

    $result_contact = $conn->query("SELECT COUNT(*) as count FROM contact_messages");
    if ($result_contact) $contact_message_count = $result_contact->fetch_assoc()['count'];

    $result_pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    if ($result_pending_orders) $pending_orders_count = $result_pending_orders->fetch_assoc()['count'];

    $current_month = date('Y-m');
    $stmt_revenue = $conn->prepare("SELECT SUM(total) as total_revenue FROM orders WHERE status = 'completed' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
    if ($stmt_revenue) {
        $stmt_revenue->bind_param("s", $current_month);
        $stmt_revenue->execute();
        $result_revenue = $stmt_revenue->get_result();
        if ($result_revenue) {
            $revenue_data = $result_revenue->fetch_assoc();
            $total_revenue_this_month = $revenue_data['total_revenue'] ?? 0;
        }
        $stmt_revenue->close();
    }
}

$recent_orders_data = [];
if ($conn) {
    $sql_recent_orders = "SELECT id, name, created_at, total, status FROM orders ORDER BY created_at DESC LIMIT 5";
    $result_recent = $conn->query($sql_recent_orders);
    if ($result_recent && $result_recent->num_rows > 0) {
        while ($row = $result_recent->fetch_assoc()) {
            $recent_orders_data[] = $row;
        }
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
$admin_username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SushiOn3</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* === ISI DARI admin_style.css === */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            background-color: #ecf0f5;
            color: #444;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
            color: #3c8dbc;
        }

        a:hover {
            text-decoration: none;
            color: #337ab7;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 500;
            color: #333;
        }

        .me-1 {
            margin-right: 0.25rem !important;
        }

        .ms-1 {
            margin-left: 0.25rem !important;
        }

        .text-success {
            color: #00a65a !important;
        }

        .text-center {
            text-align: center !important;
        }

        /* === top header === */
        .admin-top-header {
            background-color: #3c8dbc;
            /* Warna header (biru) */
            color: #fff;
            padding: 0 15px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        }

        .admin-top-header .logo-area {
            display: flex;
            align-items: center;
        }

        .admin-top-header .logo-text {
            font-size: 20px;
            font-weight: bold;
            color: #fff;
            margin-left: 10px;
        }

        .sidebar-toggle-btn,
        .sidebar-toggle-btn-mobile {
            background: none;
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            padding: 0 10px;
            height: 50px;
            line-height: 50px;
        }

        .sidebar-toggle-btn-mobile {
            display: none;
        }

        /*hanya tampil di mobile*/

        .admin-top-header .header-actions {
            display: flex;
            align-items: center;
        }

        .admin-top-header .header-action-item {
            color: #fff;
            margin-left: 15px;
            font-size: 16px;
            position: relative;
            padding: 10px 5px;
            opacity: 0.9;
            display: flex;
            align-items: center;
        }

        .admin-top-header .header-action-item:hover {
            opacity: 1;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .admin-top-header .header-action-item .badge {
            position: absolute;
            top: 8px;
            right: 0px;
            font-size: 9px;
            background-color: #dd4b39;
            padding: 2px 4px;
            border-radius: 3px;
            line-height: 1;
        }

        .user-avatar-sm {
            width: 25px;
            height: 25px;
            border-radius: 50%;
        }

        .dropdown-menu {
            font-size: 14px;
            border-radius: 3px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            border: 1px solid #ddd;
        }

        .dropdown-item {
            padding: 8px 15px;
            color: #333;
        }

        .dropdown-item:hover {
            background-color: #f4f4f4;
        }

        .dropdown-item:active {
            background-color: #3c8dbc;
            color: #fff;
        }

        .dropdown-divider {
            border-top-color: #eee;
        }


        /* === admin body  === */
        .admin-body-container {
            display: flex;
            padding-top: 50px;
            flex-grow: 1;
            /*agar mengisi sisa ruang vertikal*/
        }

        /* === SIDEBAR ADMIN === */
        .admin-sidebar {
            width: 230px;
            background-color: #222d32;
            /*warna sidebar gelap AdminLTE*/
            color: #b8c7ce;
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0;
            top: 50px;
            bottom: 0;
            z-index: 1020;
            transition: margin-left 0.3s ease-in-out;
            overflow-y: auto;
            /*scroll jika konten sidebar panjang*/
        }

        .admin-sidebar.collapsed {
            margin-left: -230px;
        }

        .sidebar-user-profile {
            padding: 15px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #1a2226;
            background-color: #1e282c;
        }

        .profile-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid #444;
        }

        .profile-info .profile-name {
            display: block;
            font-weight: 500;
            color: #fff;
            font-size: 15px;
        }

        .profile-info .profile-status {
            font-size: 12px;
            color: #b8c7ce;
        }

        .profile-info .profile-status .fa-circle {
            font-size: 9px;
            margin-right: 4px;
        }

        .sidebar-search {
            padding: 10px;
            display: flex;
        }

        .sidebar-search input {
            flex-grow: 1;
            padding: 8px 10px;
            border: 1px solid #374850;
            background-color: #374850;
            color: #eee;
            border-radius: 3px 0 0 3px;
            font-size: 13px;
        }

        .sidebar-search input::placeholder {
            color: #8aa4af;
        }

        .sidebar-search button {
            padding: 8px 10px;
            background-color: #374850;
            border: 1px solid #374850;
            border-left: none;
            color: #eee;
            border-radius: 0 3px 3px 0;
            cursor: pointer;
        }

        .main-navigation {
            padding-top: 10px;
            flex-grow: 1;
        }

        .nav-section-title {
            padding: 10px 15px 8px;
            font-size: 11px;
            color: #4b646f;
            text-transform: uppercase;
            font-weight: 600;
        }

        .admin-sidebar .nav-link {
            color: #b8c7ce;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            font-size: 14px;
            transition: background-color 0.2s, color 0.2s, border-left-color 0.2s;
            border-left: 3px solid transparent;
            position: relative;
        }

        .admin-sidebar .nav-link i:first-child {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 1em;
        }

        .admin-sidebar .nav-link span {
            flex-grow: 1;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #1e282c;
            color: #fff;
            border-left-color: #3c8dbc;
        }

        .admin-sidebar .nav-separator {
            height: 1px;
            background-color: #1a2226;
            margin: 10px 15px;
        }


        .nav-link.has-submenu .submenu-arrow {
            margin-left: auto;
            font-size: 0.8em;
            transition: transform 0.2s ease;
        }

        .nav-link.has-submenu.open .submenu-arrow {
            transform: rotate(90deg);
        }

        .submenu {
            list-style: none;
            padding-left: 0;
            background-color: #2c3b41;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .submenu.open {
            max-height: 500px;
        }

        .submenu .nav-link {
            padding-left: 38px;
            font-size: 13px;
            border-left: 3px solid transparent !important;
        }

        .submenu .nav-link:hover,
        .submenu .nav-link.active {
            background-color: #374850;
            color: #fff;
            border-left-color: #3c8dbc !important;
        }

        .submenu .nav-link i:first-child {
            font-size: 0.8em;
            opacity: 0.7;
            margin-right: 8px;
        }


        .sidebar-footer-links {
            border-top: 1px solid #1a2226;
            padding: 5px 0;
            margin-top: auto;
        }

        .admin-sidebar .logout-link:hover {
            background-color: #dd4b39;
            color: #fff;
            border-left-color: #d73925;
        }

        /* === KONTEN UTAMA ADMIN === */
        .admin-main-content {
            flex-grow: 1;
            padding: 15px;
            margin-left: 230px;
            transition: margin-left 0.3s ease-in-out;
        }

        .admin-main-content.sidebar-collapsed {
            margin-left: 0;
        }

        .content-header {
            margin: -15px -15px 15px -15px;
            padding: 15px;
            background-color: #fff;
            border-bottom: 1px solid #d2d6de;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-header .title-area h1 {
            font-size: 20px;
            color: #333;
            margin: 0;
            font-weight: 400;
        }

        .content-header .title-area .breadcrumb-area {
            font-size: 13px;
            color: #777;
            margin-top: 3px;
        }

        .content-header .title-area .breadcrumb-area i {
            margin: 0 3px;
            font-size: 11px;
        }

        .content-header .title-area .breadcrumb-area a {
            color: #3c8dbc;
        }

        .content-header .header-page-actions .btn {
            font-size: 13px;
            padding: 5px 10px;
        }

        .btn-light {
            background-color: #f4f4f4;
            border-color: #ddd;
            color: #444;
        }

        .btn-light:hover {
            background-color: #e7e7e7;
        }


        /* === statistik kartu  === */
        .stat-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            border-radius: 3px;
            color: #fff;
            display: flex;
            padding: 15px;
            position: relative;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            min-height: 110px;
        }

        .stat-card .stat-icon {
            font-size: 45px;
            opacity: 0.9;
            width: 70px;
            text-align: center;
            padding-right: 15px;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-card .stat-details {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-card .stat-value {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 0px;
            display: block;
            line-height: 1;
        }

        .stat-card .stat-title {
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }

        .stat-card .stat-card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            padding: 3px 0;
            font-size: 12px;
            background: rgba(0, 0, 0, 0.1);
            color: rgba(255, 255, 255, 0.8);
            transition: background 0.2s;
        }

        .stat-card .stat-card-footer:hover {
            background: rgba(0, 0, 0, 0.15);
            color: #fff;
        }

        .stat-card.bg-blue {
            background-color: #00c0ef !important;
        }

        .stat-card.bg-green {
            background-color: #00a65a !important;
        }

        .stat-card.bg-yellow {
            background-color: #f39c12 !important;
            color: #fff !important;
        }

        .stat-card.bg-yellow .stat-card-footer {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .stat-card.bg-yellow .stat-card-footer:hover {
            color: #fff !important;
        }

        .stat-card.bg-red {
            background-color: #dd4b39 !important;
        }

        /* === PANEL KONTEN === */
        .content-panel {
            background: #fff;
            border-radius: 3px;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            border-top: 3px solid #d2d6de;
        }

        .content-panel.panel-primary {
            border-top-color: #3c8dbc;
        }

        .content-panel.panel-success {
            border-top-color: #00a65a;
        }

        .content-panel.panel-warning {
            border-top-color: #f39c12;
        }

        .content-panel.panel-danger {
            border-top-color: #dd4b39;
        }

        .panel-header {
            padding: 10px 15px;
            border-bottom: 1px solid #f4f4f4;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-header .panel-title {
            font-size: 16px;
            margin: 0;
            font-weight: 500;
        }

        .panel-actions .btn {
            font-size: 12px;
            padding: 4px 8px;
        }

        .btn-outline-primary {
            color: #3c8dbc;
            border-color: #3c8dbc;
        }

        .btn-outline-primary:hover {
            background-color: #3c8dbc;
            color: #fff;
        }

        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }

        .panel-body {
            padding: 15px;
        }

        .panel-body.no-padding {
            padding: 0;
        }

        .panel-footer {
            padding: 10px 15px;
            background-color: #f5f5f5;
            border-top: 1px solid #ddd;
            border-bottom-left-radius: 3px;
            border-bottom-right-radius: 3px;
        }

        /* style tabel */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            font-weight: 500;
            font-size: 13px;
            text-transform: uppercase;
            background-color: #f8f9fa;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, .03);
        }

        .table-hover tbody tr:hover {
            color: #212529;
            background-color: rgba(0, 0, 0, .055);
        }

        .table .btn-xs {
            padding: .15rem .3rem;
            font-size: .75rem;
            border-radius: .2rem;
            margin: 0 2px;
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: #fff;
        }

        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }

        .badge {
            display: inline-block;
            padding: .3em .5em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
        }

        .badge.status-completed {
            background-color: #00a65a;
            color: white;
        }

        .badge.status-processing {
            background-color: #00c0ef;
            color: white;
        }

        .badge.status-pending {
            background-color: #f39c12;
            color: white;
        }

        .badge.status-cancelled {
            background-color: #dd4b39;
            color: white;
        }


        /* === footer === */
        .admin-app-footer {
            background-color: #fff;
            padding: 10px 15px;
            border-top: 1px solid #d2d6de;
            font-size: 13px;
            color: #444;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            transition: margin-left 0.3s ease-in-out;
            margin-left: 230px;
        }

        .admin-app-footer.sidebar-collapsed {
            margin-left: 0;
        }


        /* === penyesuaian responsif === */
        @media (max-width: 767.98px) {
            .admin-sidebar {
                margin-left: -230px;
            }

            .admin-sidebar.collapsed {
                margin-left: 0;
            }

            .admin-main-content {
                margin-left: 0 !important;
            }

            .sidebar-toggle-btn {
                display: none;
            }

            .sidebar-toggle-btn-mobile {
                display: inline-block;
                margin-left: 5px;
            }

            .admin-top-header .logo-text {
                display: none;
            }

            .admin-top-header .header-actions .header-action-item:not(:last-child):not(.dropdown-toggle) {
                display: none;
            }

            .admin-top-header .header-actions .dropdown-toggle {
                display: flex !important;
            }


            .content-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .content-header .title-area h1 {
                font-size: 18px;
            }

            .content-header .header-page-actions {
                margin-top: 10px;
                width: 100%;
                text-align: left;
            }

            .stat-cards-grid {
                grid-template-columns: 1fr;
            }

            /* Stack cards */
            .admin-app-footer {
                flex-direction: column;
                text-align: center;
                margin-left: 0 !important;
            }

            .admin-app-footer .footer-right {
                margin-top: 5px;
            }
        }
    </style>
</head>

<body>
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
                    <img src="assets/images/admin-avatar.png" alt="User Avatar" class="user-avatar-sm">
                </a>
            </div>
            <button class="sidebar-toggle-btn-mobile" id="sidebarToggleMobile"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <div class="admin-body-container">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-user-profile">
                <img src="assets/images/admin-avatar.png" alt="Admin Avatar" class="profile-avatar">
                <div class="profile-info">
                    <span class="profile-name"><?php echo htmlspecialchars($admin_username); ?></span>
                    <span class="profile-status"><i class="fas fa-circle text-success"></i> Online</span>
                </div>
            </div>
            <div class="sidebar-search">
                <input type="text" placeholder="Cari menu...">
                <button><i class="fas fa-search"></i></button>
            </div>
            <nav class="main-navigation">
                <span class="nav-section-title">MAIN NAVIGATION</span>
                <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
                <a href="users.php" class="nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> <span>Kelola Pengguna</span>
                </a>
                <a href="menu.php" class="nav-link has-submenu <?php echo (in_array($current_page, ['menu.php', 'add_menu_item.php', 'edit_menu_item.php'])) ? 'active open' : ''; ?>">
                    <i class="fas fa-utensils"></i> <span>Kelola Menu</span> <i class="fas fa-chevron-right submenu-arrow"></i>
                </a>
                <ul class="submenu <?php echo (in_array($current_page, ['menu.php', 'add_menu_item.php', 'edit_menu_item.php'])) ? 'open' : ''; ?>">
                    <li><a href="menu.php" class="nav-link <?php echo ($current_page == 'menu.php') ? 'active' : ''; ?>">Daftar Menu</a></li>
                    <li><a href="add_menu_item.php" class="nav-link <?php echo ($current_page == 'add_menu_item.php') ? 'active' : ''; ?>">Tambah Menu</a></li>
                </ul>
                <a href="orders_admin.php" class="nav-link <?php echo ($current_page == 'orders_admin.php' || $current_page == 'edit_order_status.php') ? 'active' : ''; ?>">
                    <i class="fas fa-receipt"></i> <span>Kelola Pesanan</span>
                </a>
                <a href="contact_admin.php" class="nav-link <?php echo ($current_page == 'contact_admin.php') ? 'active' : ''; ?>">
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

        <main class="admin-main-content" id="mainContent">
            <div class="content-header">
                <div class="title-area">
                    <h1>Dashboard</h1>
                    <div class="breadcrumb-area">
                        <i class="fas fa-home"></i> <a href="dashboard.php">Home</a> <i class="fas fa-angle-right"></i> <span>Dashboard</span>
                    </div>
                </div>
                <div class="header-page-actions">
                    <button class="btn btn-sm btn-light"><i class="fas fa-calendar-alt me-1"></i> <?php echo date("d M Y"); ?></button>
                </div>
            </div>

            <div class="stat-cards-grid">
                <div class="stat-card bg-blue">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-details">
                        <span class="stat-title">TOTAL PENGGUNA</span>
                        <span class="stat-value"><?php echo $user_count; ?></span>
                    </div>
                    <a href="users.php" class="stat-card-footer">Info lebih lanjut <i class="fas fa-arrow-circle-right"></i></a>
                </div>
                <div class="stat-card bg-green">
                    <div class="stat-icon"><i class="fas fa-utensils"></i></div>
                    <div class="stat-details">
                        <span class="stat-title">ITEM MENU</span>
                        <span class="stat-value"><?php echo $menu_item_count; ?></span>
                    </div>
                    <a href="menu.php" class="stat-card-footer">Info lebih lanjut <i class="fas fa-arrow-circle-right"></i></a>
                </div>
                <div class="stat-card bg-yellow">
                    <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-details">
                        <span class="stat-title">PESANAN PENDING</span>
                        <span class="stat-value"><?php echo $pending_orders_count; ?></span>
                    </div>
                    <a href="orders_admin.php" class="stat-card-footer">Info lebih lanjut <i class="fas fa-arrow-circle-right"></i></a>
                </div>
                <div class="stat-card bg-red">
                    <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                    <div class="stat-details">
                        <span class="stat-title">PESAN KONTAK</span>
                        <span class="stat-value"><?php echo $contact_message_count; ?></span>
                    </div>
                    <a href="contact_admin.php" class="stat-card-footer">Info lebih lanjut <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="content-panel panel-primary">
                <div class="panel-header">
                    <h3 class="panel-title">Pesanan Terbaru</h3>
                </div>
                <div class="panel-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Pelanggan</th>
                                    <th>Tanggal</th>
                                    <th>Total (Rp)</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders_data as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['name']); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($order['created_at']))); ?></td>
                                        <td><?php echo number_format($order['total'], 0, ',', '.'); ?></td>
                                        <td><span class="badge status-<?php echo strtolower(htmlspecialchars($order['status'])); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                                        <td>
                                            <a href="edit_order_status.php?id=<?php echo $order['id']; ?>" class="btn btn-xs btn-info" title="Lihat/Ubah Status"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recent_orders_data)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada pesanan terbaru untuk ditampilkan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel-footer text-center">
                    <a href="orders_admin.php" class="btn btn-sm btn-outline-secondary">Lihat Semua Pesanan</a>
                </div>
            </div>
        </main>
    </div>

    <footer class="admin-app-footer" id="appFooter">
        <div class="footer-left">
            <strong>Copyright © <?php echo date("Y"); ?> <a href="../index.php">SushiOn3</a>.</strong> All rights reserved.
        </div>
        <div class="footer-right">
            <b>Version</b> 1.0.0
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('adminSidebar');
            const mainContent = document.getElementById('mainContent');
            const appFooter = document.getElementById('appFooter'); //id untuk footer
            const sidebarToggleDesktop = document.getElementById('sidebarToggleDesktop');
            const sidebarToggleMobile = document.getElementById('sidebarToggleMobile');

            function applySidebarState() {
                const isCollapsed = sidebar.classList.contains('collapsed');
                if (mainContent) {
                    if (isCollapsed) {
                        mainContent.classList.add('sidebar-collapsed');
                    } else {
                        mainContent.classList.remove('sidebar-collapsed');
                    }
                }
                if (appFooter) {
                    if (isCollapsed) {
                        appFooter.classList.add('sidebar-collapsed');
                    } else {
                        appFooter.classList.remove('sidebar-collapsed');
                    }
                }
            }

            function toggleSidebar() {
                if (!sidebar) return;
                sidebar.classList.toggle('collapsed');
                applySidebarState();
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            }

            if (sidebarToggleDesktop) {
                sidebarToggleDesktop.addEventListener('click', toggleSidebar);
            }
            if (sidebarToggleMobile) {
                sidebarToggleMobile.addEventListener('click', toggleSidebar);
            }

            //cek status sidebar dari localStorage saat halaman dimuat
            if (sidebar && localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
            }
            if (sidebar) applySidebarState();

            //submenu Toggle
            const submenuToggles = document.querySelectorAll('.admin-sidebar .nav-link.has-submenu');
            submenuToggles.forEach(toggle => {
                toggle.addEventListener('click', function(event) {
                    if (this.getAttribute('href') === '#') {
                        event.preventDefault();
                    }

                    const submenu = this.nextElementSibling;

                    if (submenu && submenu.classList.contains('submenu')) {
                        if (!submenu.classList.contains('open')) {
                            document.querySelectorAll('.admin-sidebar .submenu.open').forEach(otherSubmenu => {
                                if (otherSubmenu !== submenu) {
                                    otherSubmenu.classList.remove('open');
                                    if (otherSubmenu.previousElementSibling) {
                                        otherSubmenu.previousElementSibling.classList.remove('open');
                                    }
                                }
                            });
                        }
                        submenu.classList.toggle('open');
                        this.classList.toggle('open');
                    }
                });
            });
        });
    </script>
</body>

</html>