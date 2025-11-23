<?php
require_once __DIR__ . '/../../../includes/path.php';
require_once INCLUDES_PATH . 'koneksi.php';
require_once INCLUDES_PATH . 'ceksession.php';

// Ambil semua transaksi peminjaman
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
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Daftar Peminjaman yang Belum Dikembalikan</h4>
        </div>
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
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peminjamans as $i => $pm): ?>
                        <?php
                        // Ambil per-alat yang belum dikembalikan
                        $detil = $koneksi->query("
                            SELECT d.*, a.namaalat 
                            FROM detilpeminjaman d
                            LEFT JOIN alat a ON d.idalat = a.idalat
                            WHERE d.idpeminjaman = " . intval($pm['idpeminjaman']) . "
                              AND d.keterangan = 'belumkembali'
                        ")->fetch_all(MYSQLI_ASSOC);
                        if (!$detil) continue;
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($pm['namapeminjam']) ?></td>

                            <!-- Nama Alat -->
                            <td>
                                <ul class="mb-0">
                                    <?php foreach ($detil as $d): ?>
                                        <li><?= htmlspecialchars($d['namaalat']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>

                            <!-- Tanggal Pinjam -->
                            <td>
                                <ul class="mb-0">
                                    <?php foreach ($detil as $d): ?>
                                        <li><?= $d['tanggalpinjam'] ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>

                            <!-- Tanggal Kembali -->
                            <td>
                                <ul class="mb-0">
                                    <?php foreach ($detil as $d): ?>
                                        <li><?= $d['tanggalkembali'] ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>

                            <!-- FOTO PEMINJAMAN -->
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

                            <!-- STATUS -->
                            <td>
                                <ul class="mb-0">
                                    <?php foreach ($detil as $d): ?>
                                        <li>
                                            <span class="badge bg-warning text-dark">Belum kembali</span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>

                            <!-- Aksi -->
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>dashboard.php?hal=pengembalian/tambahpengembalian&idpeminjaman=<?= $pm['idpeminjaman'] ?>"
                                    class="btn btn-success btn-sm">
                                    <i class="fas fa-undo"></i> Kembalikan
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Preview Gambar -->
<div class="modal fade" id="fotoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-body text-center">
                <img id="fotoPreview" src="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        $('#datatable').DataTable();

        const fotoPreview = document.getElementById("fotoPreview");
        const fotoModal = document.getElementById("fotoModal");

        fotoModal.addEventListener("show.bs.modal", function(event) {
            let trigger = event.relatedTarget;
            fotoPreview.src = trigger.getAttribute("data-foto");
        });
    });
</script>

<?php include PAGES_PATH . 'user/footer.php'; ?>