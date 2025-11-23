<?php
// ============================================================
// Dashboard Peminjam – FINAL versi sesuai desain
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
// Ambil data peminjam
// ===============================
$stmt = $koneksi->prepare("SELECT * FROM peminjam WHERE idpeminjam = ? LIMIT 1");
$stmt->bind_param("i", $idpeminjam);
$stmt->execute();
$peminjam = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ===============================
// Hitung statistik
// ===============================
$stmt1 = $koneksi->prepare("SELECT COUNT(*) AS total FROM peminjaman WHERE idpeminjam = ?");
$stmt1->bind_param("i", $idpeminjam);
$stmt1->execute();
$totalPinjam = $stmt1->get_result()->fetch_assoc()['total'] ?? 0;
$stmt1->close();

$stmt2 = $koneksi->prepare("
    SELECT COUNT(*) AS belumkembali
    FROM detilpeminjaman dp
    JOIN peminjaman p ON dp.idpeminjaman = p.idpeminjaman
    WHERE p.idpeminjam = ? AND dp.keterangan = 'belumkembali'
");
$stmt2->bind_param("i", $idpeminjam);
$stmt2->execute();
$belumKembali = $stmt2->get_result()->fetch_assoc()['belumkembali'] ?? 0;
$stmt2->close();

// ===============================
// Riwayat 5 terbaru
// ===============================
$stmt3 = $koneksi->prepare("
    SELECT a.namaalat, dp.tanggalpinjam, dp.keterangan
    FROM peminjaman p
    JOIN detilpeminjaman dp ON dp.idpeminjaman = p.idpeminjaman
    JOIN alat a ON a.idalat = dp.idalat
    WHERE p.idpeminjam = ?
    ORDER BY dp.tanggalpinjam DESC
    LIMIT 5
");
$stmt3->bind_param("i", $idpeminjam);
$stmt3->execute();
$riwayat = $stmt3->get_result();
$stmt3->close();

// ===============================
// Pinjaman yang belum kembali (5 terbaru)
// ===============================
$stmt4 = $koneksi->prepare("
    SELECT a.namaalat, dp.tanggalpinjam
    FROM peminjaman p
    JOIN detilpeminjaman dp ON dp.idpeminjaman = p.idpeminjaman
    JOIN alat a ON a.idalat = dp.idalat
    WHERE p.idpeminjam = ? AND dp.keterangan='belumkembali'
    ORDER BY dp.tanggalpinjam DESC
    LIMIT 5
");
$stmt4->bind_param("i", $idpeminjam);
$stmt4->execute();
$belumKembaliList = $stmt4->get_result();
$stmt4->close();

include PAGES_PATH . 'peminjam/header.php';
include PAGES_PATH . 'peminjam/navbar.php';
?>

<div class="container mt-4">

    <h2>Selamat Datang, <?= htmlspecialchars($peminjam['namapeminjam']) ?>!</h2>
    <p>Status akun: <strong><?= htmlspecialchars($peminjam['status']) ?></strong></p>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5>Total Peminjaman</h5>
                    <p class="display-4"><?= $totalPinjam ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5>Alat Belum Dikembalikan</h5>
                    <p class="display-4"><?= $belumKembali ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL -->
    <div class="row mt-5">

        <!-- Riwayat seluruh peminjaman -->
        <div class="col-md-6">
            <div class="p-2 mb-2" style="background:#2366CC; color:white; border-radius:4px;">
                <strong>Riwayat seluruh peminjaman yang pernah dilakukan</strong>
            </div>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Alat</th>
                        <th>Tanggal peminjaman</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($riwayat->num_rows > 0): $no=1; ?>
                        <?php while($r = $riwayat->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($r['namaalat']) ?></td>
                                <td><?= htmlspecialchars($r['tanggalpinjam']) ?></td>
                                <td><?= htmlspecialchars($r['keterangan']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Tidak ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Belum dikembalikan -->
        <div class="col-md-6">
            <div class="p-2 mb-2" style="background:#E00000; color:white; border-radius:4px;">
                <strong>Peminjaman yang belum dikembalikan</strong>
            </div>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Alat</th>
                        <th>Tanggal peminjaman</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($belumKembaliList->num_rows > 0): $no=1; ?>
                        <?php while($b = $belumKembaliList->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($b['namaalat']) ?></td>
                                <td><?= htmlspecialchars($b['tanggalpinjam']) ?></td>
                                <td>belumkembali</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Tidak ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<?php include PAGES_PATH . 'peminjam/footer.php'; ?>
