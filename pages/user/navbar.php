<?php
// ==============================================
// File: pages/user/navbar.php (FINAL FIX LOGOUT)
// ==============================================

$namauser = $_SESSION['namauser'] ?? $_SESSION['namalengkap'] ?? 'Pengguna';
$role     = $_SESSION['role'] ?? 'User';
$foto     = $_SESSION['foto'] ?? 'default.png';

// FIX: Logout harus langsung ke file logout, bukan ke ?hal=
$logout_url = BASE_URL . 'views/' . 
              ($role === 'peminjam' 
                ? 'otentikasipeminjam/logoutpeminjam.php' 
                : 'otentikasiuser/logoutuser.php');

/**
 * =====================================================
 * Breadcrumb Otomatis
 * =====================================================
 */
if (!function_exists('buat_breadcrumb_otomatis')) {
    function buat_breadcrumb_otomatis()
    {
        $hal = $_GET['hal'] ?? 'dashboardadmin';

        if (in_array($hal, ['dashboardadmin','dashboardpetugas','dashboardpeminjam'])) {
            echo '<ol class="breadcrumb float-sm-right"><li class="breadcrumb-item active">Dashboard</li></ol>';
            return;
        }

        $parts = explode('/', $hal);
        $breadcrumb = [];

        $breadcrumb[] = '<li class="breadcrumb-item"><a href="?hal=dashboardadmin">Dashboard</a></li>';

        $fallbacks = [
            'user'         => 'user/daftaruser',
            'jabatan'      => 'jabatan/daftarjabatan',
            'merk'         => 'merk/daftarmerk',
            'kategori'     => 'kategori/daftarkategori',
            'alat'         => 'alat/daftaralat',
            'komentar'     => 'komentar/daftarkomentar',
            'asal'         => 'asal/daftarasal',
            'peminjam'     => 'peminjam/daftarpeminjam',
            'peminjaman'   => 'peminjaman/daftarpeminjaman',
            'pengembalian' => 'pengembalian/daftarpengembalian',
            'laporan'      => 'laporan/daftarlaporan'
        ];

        for ($i = 0; $i < count($parts); $i++) {
            $segment = htmlspecialchars(ucfirst(str_replace(['_', '-'], ' ', $parts[$i])));
            if ($i < count($parts) - 1) {
                $parent = $parts[$i];
                $suburl = '?hal=' . ($fallbacks[$parent] ?? implode('/', array_slice($parts, 0, $i + 1)));
                $breadcrumb[] = '<li class="breadcrumb-item"><a href="' . $suburl . '">' . $segment . '</a></li>';
            } else {
                $breadcrumb[] = '<li class="breadcrumb-item active">' . $segment . '</li>';
            }
        }

        echo '<ol class="breadcrumb float-sm-right">' . implode('', $breadcrumb) . '</ol>';
    }
}

/**
 * Judul Halaman Otomatis
 */
if (!function_exists('judul_halaman_otomatis')) {
    function judul_halaman_otomatis()
    {
        $hal = $_GET['hal'] ?? 'dashboardadmin';
        if (in_array($hal, ['dashboardadmin','dashboardpetugas','dashboardpeminjam'])) {
            return 'Dashboard';
        }
        $parts = explode('/', $hal);
        return ucfirst(str_replace(['_', '-'], ' ', end($parts)));
    }
}
?>

<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?= BASE_URL ?>?hal=dashboardadmin" class="nav-link">Beranda</a>
    </li>
  </ul>

  <ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown user-menu">
      <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        <img src="<?= BASE_URL ?>uploads/user/<?= $foto ?>"
             class="img-circle elevation-2"
             style="width:30px;height:30px;object-fit:cover;">
        <?= htmlspecialchars($namauser); ?> (<?= htmlspecialchars($role); ?>)
      </a>
      <ul class="dropdown-menu dropdown-menu-right">
        <li><a class="dropdown-item" href="#"><i class="fas fa-id-card mr-2"></i> Profil</a></li>

        <!-- FIX LOGOUT -->
        <li>
          <a class="dropdown-item text-danger" href="<?= $logout_url ?>">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </a>
        </li>
      </ul>
    </li>
  </ul>
</nav>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <h5 class="m-0"><?= judul_halaman_otomatis(); ?></h5>
      <?php buat_breadcrumb_otomatis(); ?>
    </div>
  </div>
