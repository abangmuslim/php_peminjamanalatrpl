<?php
require_once __DIR__ . '/../../../includes/path.php';
require_once INCLUDES_PATH . 'koneksi.php';
require_once INCLUDES_PATH . 'ceksession.php';

// ==============================
// Ambil ID
// ==============================
$id = $_GET['id'] ?? 0;
$id = intval($id);

// ==============================
// Ambil Data Peminjaman
// ==============================
$sql = "
    SELECT pm.*, p.namapeminjam
    FROM peminjaman pm
    LEFT JOIN peminjam p ON pm.idpeminjam = p.idpeminjam
    WHERE pm.idpeminjaman = $id
";
$peminjaman = $koneksi->query($sql)->fetch_assoc();
if (!$peminjaman) {
    echo "<script>alert('Data tidak ditemukan!'); history.back();</script>";
    exit;
}

// ==============================
// Ambil semua peminjam
// ==============================
$peminjamList = $koneksi->query("SELECT * FROM peminjam ORDER BY namapeminjam ASC");

// ==============================
// Ambil detil alat + foto per alat
// ==============================
$detil = $koneksi->query("
    SELECT d.*, a.namaalat
    FROM detilpeminjaman d
    LEFT JOIN alat a ON d.idalat = a.idalat
    WHERE d.idpeminjaman = $id
")->fetch_all(MYSQLI_ASSOC);

// ==============================
// Ambil semua alat
// ==============================
$alatData = [];
$resA = $koneksi->query("SELECT * FROM alat ORDER BY namaalat ASC");
while ($r = $resA->fetch_assoc()) { $alatData[] = $r; }

include PAGES_PATH . 'user/header.php';
include PAGES_PATH . 'user/navbar.php';
include PAGES_PATH . 'user/sidebar.php';
?>

<div class="content">
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h4>Edit Peminjaman</h4>
        </div>

        <div class="card-body">
            <form action="<?= BASE_URL ?>views/user/peminjaman/prosespeminjaman.php" 
                  method="POST" enctype="multipart/form-data">

                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="idpeminjaman" value="<?= $id ?>">

                <!-- PILIH PEMINJAM -->
                <div class="mb-3">
                    <label class="form-label">Peminjam</label>
                    <select name="idpeminjam" class="form-control" required>
                        <option value="">-- Pilih Peminjam --</option>

                        <?php while ($p = $peminjamList->fetch_assoc()): ?>
                            <option 
                                value="<?= $p['idpeminjam'] ?>"
                                <?= $p['idpeminjam'] == $peminjaman['idpeminjam'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($p['namapeminjam']) ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <hr>
                <h5>Alat yang Dipinjam</h5>

                <div id="alat-container">
                    
                    <?php foreach ($detil as $d): ?>
                    <div class="row mb-3 alat-item">

                        <input type="hidden" name="iddetilpeminjaman[]" value="<?= $d['iddetilpeminjaman'] ?>">

                        <div class="col-md-3">
                            <label>Alat</label>
                            <select name="idalat[]" class="form-control" required>
                                <option value="">-- Pilih Alat --</option>
                                <?php foreach($alatData as $a): ?>
                                    <option 
                                        value="<?= $a['idalat'] ?>"
                                        <?= $a['idalat'] == $d['idalat'] ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($a['namaalat']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>Tanggal Pinjam</label>
                            <input type="date" name="tanggalpinjam[]" 
                                   value="<?= $d['tanggalpinjam'] ?>" class="form-control" required>
                        </div>

                        <div class="col-md-2">
                            <label>Tanggal Kembali</label>
                            <input type="date" name="tanggalkembali[]" 
                                   value="<?= $d['tanggalkembali'] ?>" class="form-control" required>
                        </div>

                        <!-- FOTO PER ALAT -->
                        <div class="col-md-3">
                            <label>Foto Lama</label><br>

                            <?php 
                                $foto = $d['fotopeminjaman'];
                                $file = BASE_PATH . "uploads/peminjaman/" . $foto;
                            ?>

                            <?php if (!empty($foto) && file_exists($file)): ?>
                                <img src="<?= BASE_URL . "uploads/peminjaman/" . $foto ?>" 
                                     style="width:70px; height:70px; object-fit:cover; border-radius:5px;">
                            <?php else: ?>
                                <span class="text-muted">Tidak ada foto</span>
                            <?php endif; ?>

                            <input type="hidden" name="fotolama[]" value="<?= $foto ?>">

                            <label class="mt-2">Ganti Foto (opsional)</label>
                            <input type="file" name="fotopeminjaman[]" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-alat">Hapus</button>
                        </div>

                    </div>
                    <?php endforeach; ?>

                </div>

                <button type="button" id="add-alat" class="btn btn-secondary mb-3">Tambah Alat</button>

                <br>
                <button type="submit" class="btn btn-primary">Update Peminjaman</button>

            </form>
        </div>
    </div>
</div>

<script>
const alatData = <?= json_encode($alatData) ?>;

document.getElementById('add-alat').addEventListener('click', function(){
    const container = document.getElementById('alat-container');
    const item = document.createElement('div');
    item.className = 'row mb-3 alat-item';

    let opts = '<option value="">-- Pilih Alat --</option>';
    alatData.forEach(a => {
        opts += `<option value="${a.idalat}">${a.namaalat}</option>`;
    });

    item.innerHTML = `
        <input type="hidden" name="iddetilpeminjaman[]" value="baru">

        <div class="col-md-3">
            <label>Alat</label>
            <select name="idalat[]" class="form-control" required>${opts}</select>
        </div>

        <div class="col-md-2">
            <label>Tanggal Pinjam</label>
            <input type="date" name="tanggalpinjam[]" 
                   value="<?= date('Y-m-d') ?>" class="form-control" required>
        </div>

        <div class="col-md-2">
            <label>Tanggal Kembali</label>
            <input type="date" name="tanggalkembali[]" 
                   value="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="form-control" required>
        </div>

        <div class="col-md-3">
            <label>Foto Peminjaman</label>
            <input type="file" name="fotopeminjaman[]" class="form-control" accept="image/*">

            <input type="hidden" name="fotolama[]" value="">
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-danger remove-alat">Hapus</button>
        </div>
    `;

    container.appendChild(item);
});

document.addEventListener('click', function(e){
    if (e.target.classList.contains('remove-alat')) {
        const row = e.target.closest('.alat-item');
        if (row) row.remove();
    }
});
</script>

<?php include PAGES_PATH . 'user/footer.php'; ?>
