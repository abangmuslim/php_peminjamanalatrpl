<?php
require_once __DIR__ . '/../../../includes/path.php';
require_once INCLUDES_PATH . 'koneksi.php';
require_once INCLUDES_PATH . 'ceksession.php';

/* ============================
   AMBIL DATA PEMINJAMAN + PEMINJAM
=============================== */
$sql = "SELECT pm.*, p.namapeminjam 
        FROM peminjaman pm 
        LEFT JOIN peminjam p ON pm.idpeminjam = p.idpeminjam
        ORDER BY pm.idpeminjaman DESC";

$result = mysqli_query($koneksi, $sql);
$peminjamans = mysqli_fetch_all($result, MYSQLI_ASSOC);

include PAGES_PATH . 'user/header.php';
include PAGES_PATH . 'user/navbar.php';
include PAGES_PATH . 'user/sidebar.php';
?>

<div class="content">

    <!-- Header -->
    <div class="card mb-3 w-100">
        <div class="card-header" style="background-color:#1B03A3; color:white;">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Daftar Peminjaman</h4>
                <a href="<?= BASE_URL ?>dashboard.php?hal=peminjaman/tambahpeminjaman"
                    class="btn btn-light btn-sm">
                    <i class="fas fa-plus"></i> Tambah Peminjaman
                </a>
            </div>
        </div>
    </div>

    <section class="content">

        <div class="card shadow-sm">
            <div class="card-body table-responsive">

                <table class="table table-bordered table-striped" id="datatable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Alat</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Foto Peminjaman</th>
                            <th>Foto Pengembalian</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($peminjamans as $i => $pm): ?>
                            <?php
                            // Ambil detail alat
                            $detil = $koneksi->query(
                                "
                                SELECT d.*, a.namaalat 
                                FROM detilpeminjaman d 
                                LEFT JOIN alat a ON d.idalat = a.idalat
                                WHERE d.idpeminjaman = " . intval($pm['idpeminjaman'])
                            )->fetch_all(MYSQLI_ASSOC);
                            ?>

                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($pm['namapeminjam'] ?? '-') ?></td>

                                <!-- Alat -->
                                <td>
                                    <ul class="mb-0">
                                        <?php foreach ($detil as $d): ?>
                                            <li><?= htmlspecialchars($d['namaalat'] ?? '-') ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>

                                <!-- Tanggal Pinjam -->
                                <td>
                                    <ul class="mb-0">
                                        <?php foreach ($detil as $d): ?>
                                            <li><?= $d['tanggalpinjam'] ?? '-' ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>

                                <!-- Tanggal Kembali -->
                                <td>
                                    <ul class="mb-0">
                                        <?php foreach ($detil as $d): ?>
                                            <li><?= $d['tanggalkembali'] ?? '-' ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>

                                <!-- Foto Peminjaman (per alat) -->
                                <td style="text-align:center;">
                                    <ul class="mb-0" style="list-style:none; padding-left:0;">
                                        <?php foreach ($detil as $d): ?>
                                            <li class="mb-1">
                                                <?php
                                                $foto = $d['fotopeminjaman'] ?? null;
                                                $pathFile = BASE_PATH . "uploads/peminjaman/" . $foto;
                                                ?>

                                                <?php if (!empty($foto) && file_exists($pathFile)): ?>
                                                    <img src="<?= BASE_URL . 'uploads/peminjaman/' . $foto ?>"
                                                        style="width:100px; height:55px; object-fit:cover; border-radius:6px;"
                                                        alt="Foto Peminjaman">
                                                <?php else: ?>
                                                    <span class="text-muted small">Tidak ada foto</span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>

                                <!-- FOTO PENGEMBALIAN (per alat) -->
                                <td style="text-align:center;">
                                    <ul class="mb-0" style="list-style:none; padding-left:0;">
                                        <?php foreach ($detil as $d): ?>
                                            <li class="mb-1">
                                                <?php
                                                $foto = $d['fotopengembalian'] ?? null;
                                                $pathFile = BASE_PATH . "uploads/pengembalian/" . $foto;
                                                ?>

                                                <?php if (!empty($foto) && file_exists($pathFile)): ?>
                                                    <img src="<?= BASE_URL . 'uploads/pengembalian/' . $foto ?>"
                                                        style="width:100px; height:55px; object-fit:cover; border-radius:6px; cursor:pointer;"
                                                        alt="Foto Pengembalian"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#fotoModal"
                                                        data-foto="<?= BASE_URL . 'uploads/pengembalian/' . $foto ?>">
                                                <?php else: ?>
                                                    <span class="text-muted small">Tidak ada foto</span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>


                                <!-- Status -->
                                <td>
                                    <ul class="mb-0">
                                        <?php foreach ($detil as $d): ?>
                                            <?php
                                            $status = $d['keterangan'] ?? 'belumkembali';
                                            $badge = ($status === 'belumkembali') ? 'bg-warning' : 'bg-success';
                                            ?>
                                            <li>
                                                <span class="badge <?= $badge ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>

                                <!-- Aksi -->
                                <td class="text-center">

                                    <a href="<?= BASE_URL ?>dashboard.php?hal=peminjaman/editpeminjaman&id=<?= intval($pm['idpeminjaman']) ?>"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="<?= BASE_URL ?>views/user/peminjaman/prosespeminjaman.php?aksi=hapus&id=<?= intval($pm['idpeminjaman']) ?>"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin hapus peminjaman ini?')">

                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>

                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>

                </table>

            </div>
        </div>

    </section>
</div>

<?php include PAGES_PATH . 'user/footer.php'; ?>