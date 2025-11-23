<?php
// =======================================
// File: dashboard.php - FINAL 100% FIXED
// Routing Backend PEMINJAMANALATRPL
// =======================================

require_once __DIR__ . '/includes/path.php';
require_once INCLUDES_PATH . 'konfig.php';
require_once INCLUDES_PATH . 'koneksi.php';
require_once INCLUDES_PATH . 'fungsivalidasi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =======================================
// 0️⃣ Allow Logout Without Blocking
// =======================================
if (isset($_GET['hal'])) {
    if ($_GET['hal'] === 'logoutuser') {
        include VIEWS_PATH . 'otentikasiuser/logoutuser.php';
        exit;
    }

    if ($_GET['hal'] === 'logoutpeminjam') {
        include VIEWS_PATH . 'otentikasipeminjam/logoutpeminjam.php';
        exit;
    }
}

// =======================================
// 1️⃣ Login Check
// =======================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'peminjam') {
        header("Location: " . BASE_URL . "?hal=loginpeminjam");
        exit;
    }

    header("Location: " . BASE_URL . "?hal=loginuser");
    exit;
}

// =======================================
// 2️⃣ Detect Role
// =======================================
$role = $_SESSION['role'] ?? '';

switch ($role) {
    case 'petugas':
        $viewFolder = 'views/user';
        $defaultPage = 'dashboardpetugas';
        break;

    case 'peminjam':
        $viewFolder = 'views/peminjam';
        $defaultPage = 'dashboardpeminjam';
        break;

    default: // admin
        $viewFolder = 'views/user';
        $defaultPage = 'dashboardadmin';
        break;
}

// =======================================
// 3️⃣ Read Requested Page
// =======================================
$hal = $_GET['hal'] ?? $defaultPage;
$halParts = explode('/', $hal);

// =======================================
// 4️⃣ Role Protection: PETUGAS Restrictions
// =======================================
$petugasBlocked = [
    'user',
    'jabatan',
    'merk',
    'kategori',
    'alat',
    'peminjam',
    'peminjaman',
    'pengembalian',
    'laporan',
    'komentar',
    'asal'
];

if ($role === 'petugas') {
    $reqModule = $halParts[0] ?? '';
    if (in_array($reqModule, $petugasBlocked)) {
        include BASE_PATH . '/views/notfound.php';
        exit;
    }
}

// =======================================
// 5️⃣ Build File Path
// =======================================

// 📌 FIX KHUSUS PEMINJAM (1 baris solusi)
// Peminjam: semua file langsung di /views/peminjam/*.php (tanpa subfolder)
if ($role === 'peminjam') {
    $page = $halParts[1] ?? $halParts[0];

    $candidate = BASE_PATH . "/{$viewFolder}/{$page}.php";

    if (file_exists($candidate)) {
        $file = $candidate;
    } else {
        $file = BASE_PATH . "/views/notfound.php";
    }

    include $file;
    exit;
}

// =============================
// MODE ADMIN & PETUGAS (normal)
// =============================

// --- Case: Two-level module → ex: alat/daftaralat ---
if (count($halParts) === 2) {
    $module = $halParts[0];
    $page   = $halParts[1];

    $candidate = BASE_PATH . "/{$viewFolder}/{$module}/{$page}.php";

    if (file_exists($candidate)) {
        $file = $candidate;
    } else {
        // fallback ke daftar modul
        $fallbackIndex = [
            'user'         => 'user/daftaruser',
            'jabatan'      => 'jabatan/daftarjabatan',
            'kategori'     => 'kategori/daftarkategori',
            'merk'         => 'merk/daftarmerk',
            'alat'         => 'alat/daftaralat',
            'peminjam'     => 'peminjam/daftarpeminjam',
            'peminjaman'   => 'peminjaman/daftarpeminjaman',
            'pengembalian' => 'pengembalian/daftarpengembalian',
            'komentar'     => 'komentar/daftarkomentar',
            'asal'         => 'asal/daftarasal',
            'laporan'      => 'laporan/daftarlaporan'
        ];

        if (isset($fallbackIndex[$module])) {
            $file = BASE_PATH . "/{$viewFolder}/" . $fallbackIndex[$module] . ".php";
        } else {
            $file = BASE_PATH . "/views/notfound.php";
        }
    }
}

// --- Case: Single page → ex: dashboardadmin ---
else {

    $candidate = BASE_PATH . "/{$viewFolder}/{$hal}.php";
    $file = file_exists($candidate)
        ? $candidate
        : BASE_PATH . "/views/notfound.php";
}

// =======================================
// 6️⃣ Load the Page
// =======================================
include $file;
