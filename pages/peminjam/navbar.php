<?php
// ===============================================================
// File: pages/peminjam/navbar.php (FINAL FIXED)
// ===============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/path.php';

// Data session
$namapeminjam = $_SESSION['namapeminjam'] ?? 'Peminjam';
$foto         = $_SESSION['foto'] ?? 'default.png';

// URL Logout
$logout_url = BASE_URL . "dashboard.php?hal=logoutpeminjam";

/* ===============================================================
   Breadcrumb Otomatis
================================================================ */
if (!function_exists('breadcrumb_peminjam')) {
    function breadcrumb_peminjam()
    {
        $hal = $_GET['hal'] ?? 'dashboardpeminjam';

        if ($hal === 'dashboardpeminjam') {
            echo '<ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">Dashboard</li>
                  </ol>';
            return;
        }

        $parts = explode('/', $hal);
        $breadcrumb = [];

        // Dashboard selalu ada
        $breadcrumb[] = '<li class="breadcrumb-item">
                            <a href="' . BASE_URL . 'dashboard.php?hal=dashboardpeminjam">Dashboard</a>
                         </li>';

        // Segment lainnya
        for ($i = 0; $i < count($parts); $i++) {
            $segment = ucwords(str_replace(['_', '-'], ' ', $parts[$i]));

            if ($i < count($parts) - 1) {
                $sub = BASE_URL . 'dashboard.php?hal=' . implode('/', array_slice($parts, 0, $i + 1));
                $breadcrumb[] = '<li class="breadcrumb-item"><a href="' . $sub . '">' . $segment . '</a></li>';
            } else {
                $breadcrumb[] = '<li class="breadcrumb-item active">' . $segment . '</li>';
            }
        }

        echo '<ol class="breadcrumb float-sm-right">' . implode('', $breadcrumb) . '</ol>';
    }
}

/* ===============================================================
   Judul Halaman Otomatis
================================================================ */
if (!function_exists('judul_peminjam')) {
    function judul_peminjam()
    {
        $hal = $_GET['hal'] ?? 'dashboardpeminjam';
        if ($hal === 'dashboardpeminjam') return 'Dashboard';

        $parts = explode('/', $hal);
        return ucwords(str_replace(['_', '-'], ' ', end($parts)));
    }
}
?>

<!-- =============================================================== -->
<!-- NAVBAR -->
<!-- =============================================================== -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">

    <!-- Left Menu (Modern & Keren, Rapi) -->
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item d-none d-sm-inline-block mt-2">
            <a href="<?= BASE_URL ?>dashboard.php?hal=dashboardpeminjam"
                class="nav-link d-flex align-items-center px-3 py-2 rounded text-white"
                style="background: linear-gradient(90deg, #4e54c8, #8f94fb); transition: 0.3s;">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>

        <li class="nav-item d-none d-sm-inline-block mt-2">
            <a href="<?= BASE_URL ?>dashboard.php?hal=peminjam/riwayatpeminjaman"
                class="nav-link d-flex align-items-center px-3 py-2 rounded text-white"
                style="background: linear-gradient(90deg, #ff416c, #ff4b2b); transition: 0.3s;">
                <i class="fas fa-history me-2"></i> Riwayat Peminjaman
            </a>
        </li>

        <li class="nav-item d-none d-sm-inline-block mt-2">
            <a href="<?= BASE_URL ?>dashboard.php?hal=peminjam/tambahpeminjaman"
                class="nav-link d-flex align-items-center px-3 py-2 rounded text-white"
                style="background: linear-gradient(90deg, #00c6ff, #0072ff); transition: 0.3s;">
                <i class="fas fa-plus-circle me-2"></i> Tambah Peminjaman
            </a>
        </li>

    </ul>


    <!-- Right User Menu -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown user-menu">

            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <img src="<?= BASE_URL ?>uploads/peminjam/<?= htmlspecialchars($foto) ?>"
                    class="img-circle elevation-2"
                    style="width:30px;height:30px;object-fit:cover;">
                <?= htmlspecialchars($namapeminjam); ?> (Peminjam)
            </a>

            <ul class="dropdown-menu dropdown-menu-right">

                <!-- Profil (future feature) -->
                <li>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-id-card mr-2"></i> Profil
                    </a>
                </li>

                <!-- Logout -->
                <li>
                    <a class="dropdown-item text-danger" href="<?= $logout_url ?>">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </li>

            </ul>

        </li>
    </ul>

</nav>

<!-- =============================================================== -->
<!-- HEADER + BREADCRUMB -->
<!-- =============================================================== -->
<div class="content-header bg-warning">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h5 class="m-0"><?= judul_peminjam(); ?></h5>
        <?php breadcrumb_peminjam(); ?>
    </div>
</div>