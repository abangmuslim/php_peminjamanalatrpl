<?php
require_once __DIR__ . '/../../includes/path.php';
require_once INCLUDES_PATH . 'koneksi.php';
require_once INCLUDES_PATH . 'fungsivalidasi.php';

// Pastikan peminjam login
$idpeminjam = $_SESSION['idpeminjam'] ?? null;
if (!$idpeminjam) {
    header("Location: " . BASE_URL . "?hal=loginpeminjam");
    exit;
}

// Ambil daftar alat
$alatData = [];
$alatQuery = $koneksi->query("SELECT * FROM alat ORDER BY namaalat ASC");
while ($row = $alatQuery->fetch_assoc()) {
    $alatData[] = $row;
}

// Ambil admin & petugas
$admins = $koneksi->query("SELECT iduser, namauser FROM user WHERE role IN ('admin','petugas')");

// Layout peminjam
include PAGES_PATH . 'peminjam/header.php';
include PAGES_PATH . 'peminjam/navbar.php';
?>

<div class="content p-3">
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h4 class="m-0">Tambah Peminjaman</h4>
        </div>

        <div class="card-body">
            <form action="<?= BASE_URL ?>dashboard.php?hal=peminjam/prosespeminjaman"
                  method="POST" enctype="multipart/form-data">

                <input type="hidden" name="aksi" value="tambah">
                <input type="hidden" name="idpeminjam" value="<?= htmlspecialchars($idpeminjam) ?>">

                <!-- PILIH ADMIN -->
                <div class="mb-3">
                    <label class="form-label">Pilih Admin / Petugas</label>
                    <select name="idadmin" class="form-control" required>
                        <option value="">-- Pilih Admin / Petugas --</option>
                        <?php while ($adm = $admins->fetch_assoc()): ?>
                            <option value="<?= $adm['iduser'] ?>">
                                <?= htmlspecialchars($adm['namauser']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <hr>
                <h5>Alat yang Dipinjam</h5>

                <div id="alat-container">

                    <!-- ITEM DEFAULT -->
                    <div class="row mb-3 alat-item border p-2 rounded">

                        <div class="col-md-3">
                            <label>Alat</label>
                            <select name="idalat[]" class="form-control" required>
                                <option value="">-- Pilih Alat --</option>
                                <?php foreach ($alatData as $a): ?>
                                    <option value="<?= $a['idalat'] ?>">
                                        <?= htmlspecialchars($a['namaalat']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>Tanggal Pinjam</label>
                            <input type="date" name="tanggalpinjam[]" class="form-control"
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-2">
                            <label>Tanggal Kembali</label>
                            <input type="date" name="tanggalkembali[]" class="form-control"
                                   value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label>Foto Peminjaman</label>
                            <input type="file" name="fotopeminjaman[]" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-alat">Hapus</button>
                        </div>

                    </div>
                </div>

                <button type="button" id="add-alat" class="btn btn-secondary mb-3">
                    Tambah Alat
                </button>

                <br>
                <button type="submit" class="btn btn-primary">Simpan Peminjaman</button>

            </form>
        </div>
    </div>
</div>

<?php include PAGES_PATH . 'peminjam/footer.php'; ?>

<script>
const alatData = <?= json_encode($alatData) ?>;

// Tambah item alat
document.getElementById('add-alat').addEventListener('click', function () {
    const container = document.getElementById('alat-container');
    const newItem = document.createElement('div');
    newItem.className = 'row mb-3 alat-item border p-2 rounded';

    let options = '<option value="">-- Pilih Alat --</option>';
    alatData.forEach(a => {
        options += `<option value="${a.idalat}">${a.namaalat}</option>`;
    });

    newItem.innerHTML = `
        <div class="col-md-3">
            <label>Alat</label>
            <select name="idalat[]" class="form-control" required>${options}</select>
        </div>

        <div class="col-md-2">
            <label>Tanggal Pinjam</label>
            <input type="date" name="tanggalpinjam[]" class="form-control"
                   value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="col-md-2">
            <label>Tanggal Kembali</label>
            <input type="date" name="tanggalkembali[]" class="form-control"
                   value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
        </div>

        <div class="col-md-3">
            <label>Foto Peminjaman</label>
            <input type="file" name="fotopeminjaman[]" class="form-control" accept="image/*">
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-danger remove-alat">Hapus</button>
        </div>
    `;

    container.appendChild(newItem);
});

// Hapus item alat
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-alat')) {
        const item = e.target.closest('.alat-item');
        if (document.querySelectorAll('.alat-item').length > 1) {
            item.remove();
        }
    }
});
</script>
