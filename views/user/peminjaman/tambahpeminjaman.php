<?php
require_once __DIR__ . '/../../../includes/path.php';
require_once INCLUDES_PATH . 'koneksi.php';
require_once INCLUDES_PATH . 'ceksession.php';

// Ambil daftar peminjam
$peminjamQuery = $koneksi->query("SELECT * FROM peminjam ORDER BY namapeminjam ASC");

// Ambil daftar alat
$alatData = [];
$alatQuery = $koneksi->query("SELECT * FROM alat ORDER BY namaalat ASC");
while ($row = $alatQuery->fetch_assoc()) {
    $alatData[] = $row;
}

include PAGES_PATH . 'user/header.php';
include PAGES_PATH . 'user/navbar.php';
include PAGES_PATH . 'user/sidebar.php';
?>

<div class="content">
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h4>Tambah Peminjaman</h4>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>views/user/peminjaman/prosespeminjaman.php" 
                  method="POST" enctype="multipart/form-data">

                <input type="hidden" name="aksi" value="tambah">

                <!-- PEMINJAM -->
                <div class="mb-3">
                    <label for="idpeminjam" class="form-label">Peminjam</label>
                    <select name="idpeminjam" id="idpeminjam" class="form-control" required>
                        <option value="">-- Pilih Peminjam --</option>
                        <?php while($p = $peminjamQuery->fetch_assoc()): ?>
                            <option value="<?= $p['idpeminjam'] ?>">
                                <?= htmlspecialchars($p['namapeminjam']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <hr>
                <h5>Alat yang Dipinjam</h5>

                <div id="alat-container">

                    <!-- ITEM ALAT DEFAULT -->
                    <div class="row mb-3 alat-item">

                        <div class="col-md-3">
                            <label>Alat</label>
                            <select name="idalat[]" class="form-control" required>
                                <option value="">-- Pilih Alat --</option>
                                <?php foreach($alatData as $a): ?>
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

                        <!-- FOTO PER ALAT -->
                        <div class="col-md-3">
                            <label>Foto Peminjaman</label>
                            <input type="file" name="fotopeminjaman[]" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-alat">Hapus</button>
                        </div>

                    </div>
                </div>

                <button type="button" id="add-alat" class="btn btn-secondary mb-3">Tambah Alat</button>
                <br>

                <button type="submit" class="btn btn-primary">Simpan Peminjaman</button>
            </form>
        </div>
    </div>
</div>

<script>
// ======================================================================
//  Tambah item alat
// ======================================================================

const alatData = <?= json_encode($alatData) ?>;

document.getElementById('add-alat').addEventListener('click', function(){
    const container = document.getElementById('alat-container');
    const newItem = document.createElement('div');
    newItem.className = 'row mb-3 alat-item';

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
            <input type="date" name="tanggalpinjam[]" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="col-md-2">
            <label>Tanggal Kembali</label>
            <input type="date" name="tanggalkembali[]" class="form-control" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
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

// Hapus item
document.addEventListener('click', function(e){
    if (e.target.classList.contains('remove-alat')) {
        const item = e.target.closest('.alat-item');
        if (item) item.remove();
    }
});
</script>

<?php include PAGES_PATH . 'user/footer.php'; ?>
