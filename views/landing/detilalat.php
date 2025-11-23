<?php
include __DIR__ . '/../../includes/fungsivalidasi.php';

$id = intval($_GET['id'] ?? 0);

// Ambil data alat
$query = $koneksi->query("
    SELECT a.*, k.namakategori, m.namamerk
    FROM alat a
    LEFT JOIN kategori k ON a.idkategori = k.idkategori
    LEFT JOIN merk m ON a.idmerk = m.idmerk
    WHERE a.idalat = $id
");
if ($query->num_rows == 0) { include __DIR__ . '/404.php'; exit; }
$alat = $query->fetch_assoc();

// Fungsi rekursif untuk render komentar
function renderKomentar($koneksi, $idalat, $idparent = null, $level = 0) {
    $stmt = $koneksi->prepare("
        SELECT * FROM komentar 
        WHERE idalat=? AND status='tampil' AND idparent " . ($idparent === null ? "IS NULL" : "= ?") . "
        ORDER BY tanggalbuat ASC
    ");
    if($idparent === null) $stmt->bind_param('i', $idalat);
    else $stmt->bind_param('ii', $idalat, $idparent);
    $stmt->execute();
    $res = $stmt->get_result();
    while($k = $res->fetch_assoc()):
?>
    <div class="card mb-2" style="margin-left: <?= $level*20 ?>px;">
        <div class="card-body">
            <h6 class="card-title"><?= htmlspecialchars($k['namakomentar'] ?? '') ?>
                <small class="text-muted"><?= $k['tanggalbuat'] ?></small>
                <button class="btn btn-sm btn-link balasBtn" data-id="<?= $k['idkomentar'] ?>">Balas</button>
            </h6>
            <p class="card-text"><?= nl2br(htmlspecialchars($k['isikomentar'] ?? '')) ?></p>

            <!-- Form balas komentar (disembunyikan default) -->
            <form method="post" class="formBalas mt-2" id="formBalas<?= $k['idkomentar'] ?>" style="display:none;">
                <input type="hidden" name="idparent" value="<?= $k['idkomentar'] ?>">
                <input type="hidden" name="idalat" value="<?= $idalat ?>">
                <div class="mb-2"><input type="text" name="namakomentar" class="form-control" placeholder="Nama" required></div>
                <div class="mb-2"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                <div class="mb-2"><textarea name="isikomentar" class="form-control" rows="2" placeholder="Komentar..." required></textarea></div>
                <button type="submit" class="btn btn-primary btn-sm submitBalas">Kirim</button>
            </form>

            <?php renderKomentar($koneksi, $idalat, $k['idkomentar'], $level+1); ?>
        </div>
    </div>
<?php
    endwhile;
    $stmt->close();
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-5">
            <img src="<?= $base_url ?>uploads/alat/<?= htmlspecialchars($alat['foto'] ?? '') ?>" 
                 class="img-fluid rounded shadow" alt="<?= htmlspecialchars($alat['namaalat'] ?? '') ?>">
        </div>
        <div class="col-md-7">
            <h3><?= htmlspecialchars($alat['namaalat'] ?? 'Tidak diketahui') ?></h3>
            <p class="text-muted">
                Kategori: <?= htmlspecialchars($alat['namakategori'] ?? 'Tidak ada') ?> |
                Merk: <?= htmlspecialchars($alat['namamerk'] ?? 'Tidak ada') ?>
            </p>
            <p><?= nl2br(htmlspecialchars($alat['deskripsi'] ?? 'Deskripsi belum tersedia')) ?></p>
            <hr>
            <a href="<?= BASE_URL ?>?hal=loginpeminjam" class="btn btn-success btn-lg">Login untuk Meminjam</a>
        </div>
    </div>

    <hr class="my-4">

    <!-- Form komentar utama -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h4>Tambah Komentar</h4>
            <form method="post" id="formKomentar">
                <input type="hidden" name="idalat" value="<?= $id ?>">
                <div class="mb-3"><input type="text" name="namakomentar" class="form-control" placeholder="Nama" required></div>
                <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                <div class="mb-3"><textarea name="isikomentar" class="form-control" rows="4" placeholder="Komentar..." required></textarea></div>
                <button type="submit" class="btn btn-primary">Kirim Komentar</button>
            </form>
        </div>
    </div>

    <h4>Komentar</h4>
    <div id="listKomentar">
        <?php renderKomentar($koneksi, $id); ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Tampilkan form balas saat tombol "Balas" diklik
$(document).on('click', '.balasBtn', function(){
    var id = $(this).data('id');
    $('#formBalas'+id).toggle();
});

// AJAX submit komentar utama & balas
function ajaxSubmit(form){
    $.ajax({
        url: '<?= $base_url ?>ajax/tambahkomentar.php',
        type: 'POST',
        data: $(form).serialize(),
        dataType: 'json',
        success: function(resp){
            if(resp.status === 'success'){
                alert(resp.message);
                location.reload(); // Bisa diganti append agar tanpa reload
            } else {
                alert(resp.message);
            }
        },
        error: function(){
            alert('Terjadi kesalahan AJAX.');
        }
    });
}

$('#formKomentar').on('submit', function(e){
    e.preventDefault();
    ajaxSubmit(this);
});
$(document).on('submit', '.formBalas', function(e){
    e.preventDefault();
    ajaxSubmit(this);
});
</script>
