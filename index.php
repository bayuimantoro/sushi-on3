<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'controllers/PageController.php';
// Anda mungkin juga perlu require OrderController di sini jika belum auto-loaded
require_once 'controllers/OrderController.php';


$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? null; // Tambahkan ini untuk menangani aksi

$controller = new PageController(); // PageController mungkin untuk halaman statis

// Tangani aksi spesifik SEBELUM routing halaman
if ($action === 'process_order' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Langsung panggil file process_order.php atau metode controller yang sesuai
    // Pastikan path ke process_order.php benar dari lokasi index.php
    require_once __DIR__ . '/views/process_order.php';
    exit; // Hentikan eksekusi index.php setelah process_order selesai
}


// Lanjutkan dengan routing halaman biasa
switch ($page) {
    case 'about':
        $controller->about();
        break;
    case 'contact':
        $controller->contact();
        break;
    case 'menu':
        $controller->menu();
        break;
    case 'order':
        // Pastikan OrderController sudah di-require jika belum
        if (!class_exists('OrderController')) {
            require_once 'controllers/OrderController.php';
        }
        $orderController = new OrderController();
        $orderController->index(); // Ini akan menampilkan form order
        break;
    case 'locations':
        $controller->locations();
        break;
    case 'information':
        $controller->information();
        break;
    case 'home':
    default:
        $controller->home();
        break;
}
?>