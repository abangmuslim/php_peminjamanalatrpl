<?php
require_once __DIR__ . '/../../includes/path.php';
require_once INCLUDES_PATH . 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$foto_user = 'default.png';
$nama_user = $_SESSION['namauser'] ?? 'Pengguna';
$role_user = $_SESSION['role'] ?? 'Guest';
$iduser    = $_SESSION['userlogin'] ?? 0;

if ($iduser) {
    $q = mysqli_query($koneksi, "SELECT foto FROM user WHERE iduser='$iduser' LIMIT 1");
    $d = mysqli_fetch_assoc($q);
    if ($d && $d['foto'] && file_exists(UPLOAD_USER . $d['foto'])) {
        $foto_user = $d['foto'];
    }
}
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">

    <a href="<?= BASE_URL ?>?hal=dashboardpetugas" class="brand-link text-center">
        <span class="brand-text font-weight-bold">PeminjamanAlatRPL</span>
    </a>

    <div class="sidebar">

        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?= BASE_URL ?>uploads/user/<?= htmlspecialchars($foto_user) ?>"
                     class="img-circle elevation-2" style="width:35px;height:35px;object-fit:cover;">
            </div>
            <div class="info">
                <a href="#" class="d-block"><?= htmlspecialchars($nama_user) ?></a>
                <small class="text-muted"><?= ucfirst($role_user) ?></small>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column">

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>dashboard.php?hal=dashboardpetugas" class="nav-link">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- ❌ Menu ini hanya untuk Admin -->
                <?php if ($role_user === 'admin'): ?>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>dashboard.php?hal=user/daftaruser" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Kelola User</p>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>dashboard.php?hal=jabatan/daftarjabatan" class="nav-link">
                        <i class="nav-icon fas fa-briefcase"></i>
                        <p>Jabatan</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>dashboard.php?hal=merk/daftarmerk" class="nav-link">
                        <i class="nav-icon fas fa-tag"></i>
                        <p>Merk</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>dashboard.php?hal=asal/daftarasal" class="nav-link">
                        <i class="nav-icon fas fa-tag"></i>
                        <p>Asal</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>dashboard.php?hal=kategori/daftarkategori" class="nav-link">
                        <i class="nav-icon fas fa-folder"></i>
                        <p>Kategori</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>dashboard.php?hal=alat/daftaralat" class="nav-link">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Alat</p>
                    </a>
                </li>

                <!-- ✔ PETUGAS BOLEH AKSES PEMINJAM -->
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>dashboard.php?hal=peminjam/daftarpeminjam" class="nav-link">
                        <i class="nav-icon fas fa-user-check"></i>
                        <p>Peminjam</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>dashboard.php?hal=peminjaman/daftarpeminjaman" class="nav-link">
                        <i class="nav-icon fas fa-handshake"></i>
                        <p>Peminjaman</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>dashboard.php?hal=pengembalian/daftarpengembalian" class="nav-link">
                        <i class="nav-icon fas fa-undo"></i>
                        <p>Pengembalian</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>dashboard.php?hal=laporan/daftarlaporan" class="nav-link">
                        <i class="fas fa-chart-line nav-icon"></i>
                        <p>Laporan</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>
