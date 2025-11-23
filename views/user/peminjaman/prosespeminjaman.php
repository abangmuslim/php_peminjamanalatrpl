<?php
require_once __DIR__ . '/../../../includes/path.php';
require_once INCLUDES_PATH . 'koneksi.php';
require_once INCLUDES_PATH . 'ceksession.php';
require_once __DIR__ . '/../../../includes/fungsiupload.php';

$db = $koneksi;
$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';


//
// ================= TAMBAH PEMINJAMAN =================
if ($aksi === 'tambah') {

    $idadmin     = $_SESSION['iduser']; 
    $idpeminjam  = $_POST['idpeminjam'];

    // INSERT peminjaman
    $stmt = $db->prepare("INSERT INTO peminjaman (idadmin, idpeminjam) VALUES (?, ?)");
    $stmt->bind_param("ii", $idadmin, $idpeminjam);
    $stmt->execute();

    $idpeminjaman = $stmt->insert_id;

    //
    // ================= DETIL PEMINJAMAN (DENGAN FOTO PER ALAT) =================
    //
    if (!empty($_POST['idalat'])) {

        foreach ($_POST['idalat'] as $i => $idalat) {

            $tanggalpinjam   = $_POST['tanggalpinjam'][$i]  ?? date('Y-m-d');
            $tanggalkembali  = $_POST['tanggalkembali'][$i] ?? date('Y-m-d', strtotime('+1 day'));

            // ===== Upload foto per alat =====
            $fotoPinjam = null;
            if (isset($_FILES['fotopeminjaman']['name'][$i]) && $_FILES['fotopeminjaman']['error'][$i] !== 4) {

                // Build ulang structure file untuk fungsi upload
                $file = [
                    'name'     => $_FILES['fotopeminjaman']['name'][$i],
                    'type'     => $_FILES['fotopeminjaman']['type'][$i],
                    'tmp_name' => $_FILES['fotopeminjaman']['tmp_name'][$i],
                    'error'    => $_FILES['fotopeminjaman']['error'][$i],
                    'size'     => $_FILES['fotopeminjaman']['size'][$i],
                ];

                $upload = upload_gambar($file, 'peminjaman');
                if ($upload['status'] === 'success') {
                    $fotoPinjam = $upload['filename'];
                }
            }

            // Insert detil
            $stmtDetil = $db->prepare("
                INSERT INTO detilpeminjaman 
                (idpeminjaman, idalat, tanggalpinjam, tanggalkembali, fotopeminjaman, 
                 keterangan, status, denda)
                VALUES (?, ?, ?, ?, ?, 'belumkembali', 'tidakterlambat', 0)
            ");
            $stmtDetil->bind_param("iisss", 
                $idpeminjaman, 
                $idalat, 
                $tanggalpinjam, 
                $tanggalkembali, 
                $fotoPinjam
            );
            $stmtDetil->execute();
        }
    }

    header("Location: ../../../dashboard.php?hal=peminjaman/daftarpeminjaman&msg=sukses_tambah");
    exit;
}


//
// ================= EDIT PEMINJAMAN =================
if ($aksi === 'edit') {

    $idpeminjaman = $_POST['idpeminjaman'];
    $idpeminjam   = $_POST['idpeminjam'];

    // UPDATE header peminjaman
    $stmt = $db->prepare("UPDATE peminjaman SET idpeminjam=? WHERE idpeminjaman=?");
    $stmt->bind_param("ii", $idpeminjam, $idpeminjaman);
    $stmt->execute();

    // ================= UPDATE / INSERT DETIL (DENGAN FOTO PER ALAT) =================
    $idalatArr         = $_POST['idalat'] ?? [];
    $tanggalpinjamArr  = $_POST['tanggalpinjam'] ?? [];
    $tanggalkembaliArr = $_POST['tanggalkembali'] ?? [];
    $iddetilArr        = $_POST['iddetilpeminjaman'] ?? [];
    $fotoLamaArr       = $_POST['fotolama'] ?? [];

    foreach ($idalatArr as $i => $idalat) {

        $tanggalpinjam   = $tanggalpinjamArr[$i]  ?? date('Y-m-d');
        $tanggalkembali  = $tanggalkembaliArr[$i] ?? date('Y-m-d', strtotime('+1 day'));
        $iddetil         = $iddetilArr[$i]        ?? null;

        // ==== Foto baru? ====
        $fotoBaru = null;
        if (isset($_FILES['fotopeminjaman']['name'][$i]) && $_FILES['fotopeminjaman']['error'][$i] !== 4) {

            $file = [
                'name'     => $_FILES['fotopeminjaman']['name'][$i],
                'type'     => $_FILES['fotopeminjaman']['type'][$i],
                'tmp_name' => $_FILES['fotopeminjaman']['tmp_name'][$i],
                'error'    => $_FILES['fotopeminjaman']['error'][$i],
                'size'     => $_FILES['fotopeminjaman']['size'][$i],
            ];

            $upload = upload_gambar($file, 'peminjaman');
            if ($upload['status'] === 'success') {
                $fotoBaru = $upload['filename'];

                // Delete foto lama
                if (!empty($fotoLamaArr[$i])) {
                    $path = __DIR__ . "/../../../uploads/peminjaman/" . $fotoLamaArr[$i];
                    if (file_exists($path)) unlink($path);
                }
            }
        }

        $fotoFinal = $fotoBaru ?: ($fotoLamaArr[$i] ?? null);

        if ($iddetil) {

            // UPDATE detil lama
            $stmtDetil = $db->prepare("
                UPDATE detilpeminjaman 
                SET idalat=?, tanggalpinjam=?, tanggalkembali=?, fotopeminjaman=?
                WHERE iddetilpeminjaman=?
            ");
            $stmtDetil->bind_param("isssi", 
                $idalat, 
                $tanggalpinjam, 
                $tanggalkembali,
                $fotoFinal,
                $iddetil
            );
            $stmtDetil->execute();

        } else {

            // INSERT detil baru
            $stmtDetil = $db->prepare("
                INSERT INTO detilpeminjaman 
                (idpeminjaman, idalat, tanggalpinjam, tanggalkembali, fotopeminjaman,
                 keterangan, status, denda)
                VALUES (?, ?, ?, ?, ?, 'belumkembali', 'tidakterlambat', 0)
            ");
            $stmtDetil->bind_param("iisss", 
                $idpeminjaman, 
                $idalat, 
                $tanggalpinjam, 
                $tanggalkembali, 
                $fotoFinal
            );
            $stmtDetil->execute();
        }
    }

    header("Location: ../../../dashboard.php?hal=peminjaman/daftarpeminjaman&msg=sukses_edit");
    exit;
}


//
// ================= HAPUS PEMINJAMAN =================
if ($aksi === 'hapus') {
    $id = $_GET['id'];

    // Ambil semua foto detil → hapus file
    $q = $db->query("SELECT fotopeminjaman FROM detilpeminjaman WHERE idpeminjaman=$id");
    while ($row = $q->fetch_assoc()) {
        if ($row['fotopeminjaman']) {
            $path = __DIR__ . "/../../../uploads/peminjaman/" . $row['fotopeminjaman'];
            if (file_exists($path)) unlink($path);
        }
    }

    // Hapus detil
    $db->query("DELETE FROM detilpeminjaman WHERE idpeminjaman=$id");

    // Hapus header peminjaman
    $db->query("DELETE FROM peminjaman WHERE idpeminjaman=$id");

    header("Location: ../../../dashboard.php?hal=peminjaman/daftarpeminjaman&msg=sukses_hapus");
    exit;
}
?>
