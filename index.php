<?php
// =======================================================
// File: index.php (root)
// Deskripsi: Routing utama tampilan publik peminjamanalatrpl
// =======================================================

// Load config, koneksi, path
require_once __DIR__ . '/includes/path.php';
require_once INCLUDES_PATH . 'konfig.php';
require_once INCLUDES_PATH . 'koneksi.php';

// Mulai session
session_start();

// =======================================================
// AUTO REDIRECT JIKA SUDAH LOGIN (FIX LOGOUT)
// =======================================================
// Perbaikan:
// - HANYA redirect jika login === true
// - Role saja tidak cukup, agar logout tidak kembali ke dashboard
// =======================================================
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {

    if (isset($_SESSION['role'])) {
        switch ($_SESSION['role']) {

            case 'admin':
                header("Location: " . BASE_URL . "dashboard.php?hal=dashboardadmin");
                exit;

            case 'petugas':
                header("Location: " . BASE_URL . "dashboard.php?hal=dashboardpetugas");
                exit;

            case 'peminjam':
                header("Location: " . BASE_URL . "dashboard.php?hal=dashboardpeminjam");
                exit;
        }
    }
}

// Ambil parameter ?hal (default = home)
$halaman = isset($_GET['hal']) ? trim($_GET['hal']) : 'home';

// Sanitasi agar tidak bisa traversal file
$halaman = basename(str_replace('.php', '', $halaman));


// =======================================================
//                  ROUTING LANDING
// =======================================================
switch ($halaman) {

    case '':
    case 'home':
        $file_view = VIEWS_PATH . 'landing/home.php';
        break;

    case 'kategori':
        $file_view = VIEWS_PATH . 'landing/kategori.php';
        break;

    case 'detilalat':
        $file_view = VIEWS_PATH . 'landing/detilalat.php';
        break;

    case 'tentang':
        $file_view = VIEWS_PATH . 'landing/tentang.php';
        break;

    case 'kontak':
        $file_view = VIEWS_PATH . 'landing/kontak.php';
        break;

    case 'hashbycrypt':
        $file_view = VIEWS_PATH . 'landing/hashbycrypt.php';
        break;

    // LOGIN USER
    case 'loginuser':
        $file_view = VIEWS_PATH . 'otentikasiuser/loginuser.php';
        break;

    case 'prosesloginuser':
        $file_view = VIEWS_PATH . 'otentikasiuser/prosesloginuser.php';
        break;

    case 'logoutuser':
        $file_view = VIEWS_PATH . 'otentikasiuser/logoutuser.php';
        break;

    // LOGIN PEMINJAM
    case 'loginpeminjam':
        $file_view = VIEWS_PATH . 'otentikasipeminjam/loginpeminjam.php';
        break;

    case 'registerpeminjam':
        $file_view = VIEWS_PATH . 'otentikasipeminjam/registerpeminjam.php';
        break;

    case 'prosesregisterpeminjam':
        $file_view = VIEWS_PATH . 'otentikasipeminjam/prosesregisterpeminjam.php';
        break;

    case 'prosesloginpeminjam':
        $file_view = VIEWS_PATH . 'otentikasipeminjam/prosesloginpeminjam.php';
        break;

    case 'logoutpeminjam':
        $file_view = VIEWS_PATH . 'otentikasipeminjam/logoutpeminjam.php';
        break;

    // Komentar publik
    case 'proseskomentar':
        $file_view = VIEWS_PATH . 'landing/proseskomentar.php';
        break;

    default:
        $file_view = VIEWS_PATH . 'landing/404.php';
        break;
}


// =======================================================
//        TEMPLATE LANDING (header → navbar → content)
// =======================================================
include PAGES_PATH . 'landing/header.php';
include PAGES_PATH . 'landing/navbar.php';

// Hero hanya muncul di home
if ($halaman === 'home') {
    include PAGES_PATH . 'landing/hero.php';
}

// Muat konten
if (file_exists($file_view)) {
    include $file_view;
} else {
    include VIEWS_PATH . 'landing/404.php';
}

include PAGES_PATH . 'landing/footer.php';
