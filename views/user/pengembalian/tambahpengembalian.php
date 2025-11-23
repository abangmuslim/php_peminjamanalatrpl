<?php
// ============================================================
// File: views/user/pengembalian/tambahpengembalian.php
// FINAL — lengkap + foto pengembalian
// ============================================================
require_once __DIR__ . '/../../../includes/path.php';
require_once INCLUDES_PATH . 'koneksi.php';
require_once INCLUDES_PATH . 'ceksession.php';

date_default_timezone_set('Asia/Jakarta');

// Validasi idpeminjaman
$idpeminjaman = intval($_GET['idpeminjaman'] ?? 0);
if ($idpeminjaman <= 0) {
    echo "<script>alert('ID peminjaman tidak ditemukan!'); window.location='" . BASE_URL . "dashboard.php?hal=pengembalian/daftarpengembalian';</script>";
    exit;
}

// Ambil data peminjaman + peminjam
$sql = "
    SELECT p.*, pm.namapeminjam
    FROM peminjaman p
    JOIN peminjam pm ON p.idpeminjam = pm.idpeminjam
    WHERE p.idpeminjaman = $idpeminjaman
    LIMIT 1
";
$res = $koneksi->query($sql);
if (!$res || $res->num_rows === 0) {
    echo "<script>alert('Data peminjaman tidak ditemukan!'); window.location='" . BASE_URL . "dashboard.php?hal=pengembalian/daftarpengembalian';</script>";
    exit;
}
$peminjaman = $res->fetch_assoc();

// Ambil detail peminjaman (alat)
$qDetil = $koneksi->query("
    SELECT d.*, a.namaalat
    FROM detilpeminjaman d
    LEFT JOIN alat a ON d.idalat = a.idalat
    WHERE d.idpeminjaman = $idpeminjaman
    ORDER BY d.iddetilpeminjaman ASC
");

// Tarif denda
$tarifDenda = 1000;

include PAGES_PATH . 'user/header.php';
include PAGES_PATH . 'user/navbar.php';
include PAGES_PATH . 'user/sidebar.php';
?>

<style>
.badge-small { padding: .35rem .5rem; font-size: .85rem; }
.summary-card { padding: 18px; border-radius: 8px; color: #fff; }
.summary-title { font-size: .9rem; opacity: .9; }
.summary-value { font-weight: 700; font-size: 1.4rem; margin-top:6px; }
.table-fixed td, .table-fixed th { vertical-align: middle; }
</style>

<div class="content">
  <div class="card mb-3">
    <div class="card-header bg-primary text-white">
      <h4 class="mb-0">Proses Pengembalian — <?= htmlspecialchars($peminjaman['namapeminjam']) ?></h4>
    </div>

    <div class="card-body">
      <form method="POST" enctype="multipart/form-data"
            action="<?= BASE_URL ?>dashboard.php?hal=pengembalian/prosespengembalian">

        <input type="hidden" name="idpeminjaman" value="<?= $idpeminjaman ?>">
        <input type="hidden" name="tanggalbayar" id="tanggalbayar" value="<?= date('Y-m-d') ?>">

        <div class="table-responsive">
          <table class="table table-bordered table-striped table-fixed w-100">
            <thead class="bg-light text-center">
              <tr>
                <th>No</th>
                <th>Nama Alat</th>
                <th>Tanggal Pinjam</th>
                <th>Durasi</th>
                <th>Harus Kembali</th>
                <th>Tanggal Dikembalikan</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th>Terlambat</th>
                <th>Denda</th>
                <th style="width:160px">Foto Pengembalian</th>
              </tr>
            </thead>
            <tbody>

<?php
$no = 1;
$rows_exist = false;

while ($d = $qDetil->fetch_assoc()):
    $rows_exist = true;
    $iddetil = (int)$d['iddetilpeminjaman'];
    $namaalat = $d['namaalat'] ?? '-';
    $tglToday = date('Y-m-d');
?>
              <tr data-iddetil="<?= $iddetil ?>">
                <td class="text-center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($namaalat) ?></td>
                <td class="text-center"><?= $d['tanggalpinjam'] ?></td>
                <td class="text-center"><?= $d['durasipeminjaman'] ?> hari</td>
                <td class="text-center tgl-kembali-asli"><?= $d['tanggalkembali'] ?></td>

                <td class="text-center">
                  <?php if ($d['keterangan'] === 'sudahkembali'): ?>
                    <input type="date" class="form-control" value="<?= $d['tanggaldikembalikan'] ?>" readonly>
                  <?php else: ?>
                    <input type="date" name="tgl_kembali[<?= $iddetil ?>]"
                           class="form-control tgl-kembali" value="<?= $tglToday ?>">
                  <?php endif; ?>
                </td>

                <td class="text-center status">
                  <span class="badge bg-secondary badge-small">Belum dihitung</span>
                </td>

                <td class="text-center">
                  <?php if ($d['keterangan'] === 'sudahkembali'): ?>
                    <span class="badge bg-primary badge-small">Sudah Kembali</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark badge-small">Belum Kembali</span>
                  <?php endif; ?>
                </td>

                <td class="text-center hari-terlambat">0</td>
                <td class="text-center denda">Rp0</td>

                <!-- hidden untuk prosespengembalian -->
                <input type="hidden" name="detail[<?= $iddetil ?>][jumlahharitelat]" class="input-terlambat">
                <input type="hidden" name="detail[<?= $iddetil ?>][denda]" class="input-denda">
                <input type="hidden" name="detail[<?= $iddetil ?>][status]" class="input-status">

                <!-- FOTO PENGEMBALIAN -->
                <td class="text-center">
                  <?php if ($d['keterangan'] === 'sudahkembali'): ?>
                      <span class="badge bg-success">Sudah ada</span>
                  <?php else: ?>
                      <input type="file" class="form-control"
                             name="fotopengembalian[<?= $iddetil ?>]"
                             accept="image/*" required>
                  <?php endif; ?>
                </td>
              </tr>

<?php endwhile; ?>

            </tbody>
          </table>
        </div>

        <?php if (!$rows_exist): ?>
          <div class="alert alert-info">Tidak ada detail peminjaman untuk ID ini.</div>
        <?php endif; ?>

        <!-- RINGKASAN -->
        <div class="row mt-4 align-items-center text-center">
          <div class="col-md-3 mb-3">
            <div class="summary-card bg-info">
              <div class="summary-title">Total Denda</div>
              <div id="total-denda" class="summary-value">Rp0</div>
              <input type="hidden" name="totaldenda" id="input-total-denda" value="0">
            </div>
          </div>

          <div class="col-md-3 mb-3">
            <label class="fw-bold d-block">Dibayar</label>
            <input type="number" name="dibayar" id="uang-bayar"
                   class="form-control text-center form-control-lg" value="0" min="0">
            <div class="mt-2"><span id="badge-bayar" class="badge bg-warning text-dark">Rp0</span></div>
          </div>

          <div class="col-md-3 mb-3">
            <div class="summary-card bg-danger">
              <div class="summary-title">Tunggakan</div>
              <div id="tunggakan" class="summary-value">Rp0</div>
              <input type="hidden" id="input-tunggakan" name="tunggakan" value="0">
            </div>
          </div>

          <div class="col-md-3 mb-3">
            <div class="summary-card bg-success">
              <div class="summary-title">Kembalian</div>
              <div id="kembalian" class="summary-value">Rp0</div>
            </div>
          </div>
        </div>

        <div class="mt-4 text-end">
          <a href="<?= BASE_URL ?>dashboard.php?hal=pengembalian/daftarpengembalian" class="btn btn-secondary">Kembali</a>
          <button type="reset" class="btn btn-warning">Reset</button>
          <button type="submit" class="btn btn-danger">Simpan Pengembalian</button>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const rows = [...document.querySelectorAll('tbody tr[data-iddetil]')];
    const tarif = <?= intval($tarifDenda) ?>;

    const totalDendaEl = document.getElementById('total-denda');
    const inputTotalDenda = document.getElementById('input-total-denda');
    const inputTunggakan = document.getElementById('input-tunggakan');
    const uangBayarEl = document.getElementById('uang-bayar');
    const kembalianEl = document.getElementById('kembalian');
    const tunggakanEl = document.getElementById('tunggakan');
    const badgeBayar = document.getElementById('badge-bayar');

    const formatRupiah = num =>
        'Rp' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    const toDateYMD = s => new Date(s + 'T00:00:00');

    function hitungDenda() {
        let totalDenda = 0;

        rows.forEach(row => {
            const asli = row.querySelector('.tgl-kembali-asli').textContent.trim();
            const inputTgl = row.querySelector('.tgl-kembali');
            if (!inputTgl) return;

            const asal = toDateYMD(asli);
            const dikembali = toDateYMD(inputTgl.value);

            const selisih = Math.max(0,
                Math.floor((dikembali - asal) / (1000*60*60*24))
            );

            const denda = selisih * tarif;

            row.querySelector('.hari-terlambat').textContent = selisih;
            row.querySelector('.denda').textContent = formatRupiah(denda);

            row.querySelector('.input-terlambat').value = selisih;
            row.querySelector('.input-denda').value = denda;
            row.querySelector('.input-status').value = selisih > 0 ? 'terlambat' : 'tidakterlambat';

            row.querySelector('.status').innerHTML =
                selisih > 0
                ? '<span class="badge bg-danger badge-small">Terlambat</span>'
                : '<span class="badge bg-success badge-small">Tepat Waktu</span>';

            totalDenda += denda;
        });

        const bayar = parseInt(uangBayarEl.value) || 0;
        const tungg = Math.max(totalDenda - bayar, 0);
        const kembali = Math.max(bayar - totalDenda, 0);

        totalDendaEl.textContent = formatRupiah(totalDenda);
        inputTotalDenda.value = totalDenda;

        tunggakanEl.textContent = formatRupiah(tungg);
        inputTunggakan.value = tungg;

        kembalianEl.textContent = formatRupiah(kembali);
        badgeBayar.textContent = formatRupiah(bayar);
    }

    rows.forEach(r => {
        const input = r.querySelector('.tgl-kembali');
        if (input) input.addEventListener('change', hitungDenda);
    });

    uangBayarEl.addEventListener('input', hitungDenda);

    hitungDenda();
});
</script>

<?php include PAGES_PATH . 'user/footer.php'; ?>
