<?php
// ===============================================
// File: views/landing/home.php
// Deskripsi: Halaman utama landing portal peminjaman alat
// ===============================================

// Ambil 12 alat terbaru berdasarkan tanggal pembelian
$query = "
  SELECT a.*, k.namakategori
  FROM alat a
  LEFT JOIN kategori k ON a.idkategori = k.idkategori
  ORDER BY a.tanggalpembelian DESC
  LIMIT 12
";
$alat = $koneksi->query($query);

// Include Hero Section (hanya sekali)
$heroFile = PAGES_PATH . 'landing/hero.php';
if (file_exists($heroFile)) {
    include_once $heroFile; // include_once untuk menghindari duplikasi
}
?>

<div class="container-fluid my-4 px-4">
  <div class="row">

    <!-- ================================
         KOLOM KONTEN (4 kolom card)
    ================================= -->
    <div class="col-lg-9">
      <h3 class="mb-4 border-bottom pb-2">Daftar Alat Terbaru</h3>

      <div class="row">

      <?php if ($alat && $alat->num_rows > 0): ?>
        <?php while ($a = $alat->fetch_assoc()): ?>

          <?php
          // ========== FOTO ==========
          $fotoPath = __DIR__ . '/../../uploads/alat/' . ($a['foto'] ?? '');
          $fotoURL = (!empty($a['foto']) && file_exists($fotoPath))
              ? $base_url . 'uploads/alat/' . $a['foto']
              : $base_url . 'assets/dist/img/placeholder.png';

          // ========== ADMIN & STATUS ==========
          $qdet = "
            SELECT d.*, p.idadmin, u.namauser
            FROM detilpeminjaman d
            LEFT JOIN peminjaman p ON d.idpeminjaman = p.idpeminjaman
            LEFT JOIN user u ON p.idadmin = u.iduser
            WHERE d.idalat = {$a['idalat']}
            ORDER BY d.iddetilpeminjaman DESC
            LIMIT 1
          ";
          $dinfo = $koneksi->query($qdet)->fetch_assoc();

          $admin  = $dinfo['namauser'] ?? '-';
          $status = !empty($dinfo['keterangan']) ? $dinfo['keterangan'] : 'tersedia';
          ?>

          <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm">

              <!-- FOTO -->
              <img src="<?= $fotoURL ?>"
                   class="card-img-top"
                   style="height:150px; object-fit:cover;"
                   alt="<?= htmlspecialchars($a['namaalat']); ?>">

              <div class="card-body">
                <!-- Nama Alat -->
                <h6 class="card-title mb-2">
                  <?= htmlspecialchars($a['namaalat']); ?>
                </h6>

                <!-- Badge kategori-admin-status -->
                <div class="small text-muted mb-2">
                  <span class="badge bg-primary">
                    <?= htmlspecialchars($a['namakategori'] ?? '-'); ?>
                  </span>

                  <span class="badge bg-success">
                    Admin: <?= htmlspecialchars($admin); ?>
                  </span>

                  <span class="badge bg-warning text-dark">
                    Status: <?= htmlspecialchars($status); ?>
                  </span>
                </div>

                <!-- Deskripsi singkat -->
                <p class="card-text small">
                  <?= substr(strip_tags($a['deskripsi'] ?? ''), 0, 80); ?>...
                </p>
              </div>

              <div class="card-footer bg-white">
                <a href="<?= $base_url . '?hal=detilalat&id=' . $a['idalat']; ?>"
                   class="btn btn-sm btn-primary w-100">
                    Lihat Detail
                </a>
              </div>

            </div>
          </div>

        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted">Belum ada alat yang tersedia.</p>
      <?php endif; ?>

      </div> <!-- row -->
    </div> <!-- col-lg-9 -->


    <!-- ================================
         SIDEBAR KANAN
    ================================= -->
    <div class="col-lg-3">

      <?php 
      $sidebarFile = PAGES_PATH . 'landing/sidebar-right.php';
      if (file_exists($sidebarFile)) {
          include_once $sidebarFile;
      } else {
          echo '<div class="text-muted small">Sidebar kanan belum tersedia.</div>';
      }
      ?>

    </div>

  </div> <!-- row -->
</div> <!-- container-fluid -->
