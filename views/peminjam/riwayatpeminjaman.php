<?php
// ============================================================
// Riwayat Peminjaman - Peminjam
// ============================================================

require_once __DIR__ . '/../../includes/path.php';
require_once INCLUDES_PATH . 'konfig.php';
require_once INCLUDES_PATH . 'koneksi.php';

// ===============================
// Cek login peminjam
// ===============================
if (!isset($_SESSION['idpeminjam'])) {
    header("Location: " . BASE_URL . "?hal=otentikasipeminjam/loginpeminjam");
    exit;
}

$idpeminjam = $_SESSION['idpeminjam'];

// ===============================
// Ambil data riwayat peminjaman
// ===============================
$query = "
    SELECT 
        dp.iddetilpeminjaman,
        a.namaalat,
        dp.tanggalpinjam,
        dp.tanggalkembali,
        dp.tanggaldikembalikan,
        dp.keterangan,
        dp.fotopeminjaman,
        dp.fotopengembalian
    FROM detilpeminjaman dp
    JOIN peminjaman p ON p.idpeminjaman = dp.idpeminjaman
    JOIN alat a ON a.idalat = dp.idalat
    WHERE p.idpeminjam = ?
    ORDER BY dp.tanggalpinjam DESC
";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $idpeminjam);
$stmt->execute();
$riwayat = $stmt->get_result();
$stmt->close();

// ===============================
// Include layout
// ===============================
include PAGES_PATH . 'peminjam/header.php';
include PAGES_PATH . 'peminjam/navbar.php';
?>

<div class="container mt-4">
    <h3>Riwayat Peminjaman</h3>
    <p class="text-muted">Daftar alat yang pernah Anda pinjam</p>

    <div class="table-responsive mt-3">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama Alat</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali (Rencana)</th>
                    <th>Tanggal Dikembalikan</th>
                    <th>Foto Peminjaman</th>
                    <th>Foto Pengembalian</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($riwayat && $riwayat->num_rows > 0): ?>
                    <?php $no = 1; while ($row = $riwayat->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['namaalat']) ?></td>
                            <td><?= htmlspecialchars($row['tanggalpinjam']) ?></td>
                            <td><?= htmlspecialchars($row['tanggalkembali']) ?></td>
                            <td>
                                <?= $row['tanggaldikembalikan'] ? 
                                    htmlspecialchars($row['tanggaldikembalikan']) : '-' ?>
                            </td>

                            <!-- FOTO PEMINJAMAN -->
                            <td style="text-align:center;">
                                <?php
                                $foto = $row['fotopeminjaman'];
                                $path = BASE_PATH . "uploads/peminjaman/" . $foto;
                                ?>
                                <?php if (!empty($foto) && file_exists($path)): ?>
                                    <img src="<?= BASE_URL . 'uploads/peminjaman/' . $foto ?>"
                                        style="width:90px; height:55px; object-fit:cover; border-radius:6px; display:block; margin:0 auto;">
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- FOTO PENGEMBALIAN -->
                            <td style="text-align:center;">
                                <?php
                                $foto2 = $row['fotopengembalian'];
                                $path2 = BASE_PATH . "uploads/pengembalian/" . $foto2;
                                ?>
                                <?php if (!empty($foto2) && file_exists($path2)): ?>
                                    <img src="<?= BASE_URL . 'uploads/pengembalian/' . $foto2 ?>"
                                        style="width:90px; height:55px; object-fit:cover; border-radius:6px; display:block; margin:0 auto;">
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($row['keterangan'] == 'sudahkembali'): ?>
                                    <span class="badge bg-success">Sudah Kembali</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Belum Kembali</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">Belum ada riwayat peminjaman</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include PAGES_PATH . 'peminjam/footer.php'; ?>
