<?php
require_once __DIR__ . '/../../includes/path.php';
require_once INCLUDES_PATH . 'koneksi.php';
require_once INCLUDES_PATH . 'fungsiupload.php';
require_once INCLUDES_PATH . 'fungsivalidasi.php';
require_once INCLUDES_PATH . 'ceksessionpeminjam.php';

$idpeminjam = $_SESSION['idpeminjam'] ?? null;
if (!$idpeminjam) {
    header("Location: " . BASE_URL . "?hal=loginpeminjam");
    exit;
}

$db   = $koneksi;
$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

/* ============================================================
   A. TAMBAH PEMINJAMAN
   ============================================================ */
if ($aksi === 'tambah') {

    $idadmin = (int)($_POST['idadmin'] ?? 0);
    if ($idadmin <= 0) {
        die("Admin tidak boleh kosong!");
    }

    // INSERT peminjaman
    $stmt = $db->prepare("INSERT INTO peminjaman (idadmin, idpeminjam) VALUES (?, ?)");
    $stmt->bind_param("ii", $idadmin, $idpeminjam);
    $stmt->execute();
    $idpeminjaman = $stmt->insert_id;
    $stmt->close();

    /* =======================================================
       Detil Peminjaman + Foto Array (uploads/fotopeminjaman)
       ======================================================= */
    $idalats        = $_POST['idalat'] ?? [];
    $tglPinjam      = $_POST['tanggalpinjam'] ?? [];
    $tglKembali     = $_POST['tanggalkembali'] ?? [];
    $fotopinjam_arr = $_FILES['fotopeminjaman'] ?? null;

    foreach ($idalats as $i => $idalat) {

        $idalat         = (int)$idalat;
        $tanggalpinjam  = bersihkan($tglPinjam[$i]);
        $tanggalkembali = bersihkan($tglKembali[$i]);

        /* --- Upload foto per item --- */
        $fotoPeminjaman = null;

        if (!empty($fotopinjam_arr['name'][$i])) {

            // bentuk struktur file agar upload_gambar bisa membaca
            $fileItem = [
                'name'     => $fotopinjam_arr['name'][$i],
                'type'     => $fotopinjam_arr['type'][$i],
                'tmp_name' => $fotopinjam_arr['tmp_name'][$i],
                'error'    => $fotopinjam_arr['error'][$i],
                'size'     => $fotopinjam_arr['size'][$i]
            ];

            // Upload ke folder FIXED: uploads/fotopeminjaman/
            $up = upload_gambar($fileItem, 'peminjaman');

            if ($up['status'] === 'success') {
                $fotoPeminjaman = $up['filename'];
            }
        }

        // Insert detil peminjaman
        $s = $db->prepare("
            INSERT INTO detilpeminjaman 
            (idpeminjaman, idalat, tanggalpinjam, tanggalkembali, fotopeminjaman, 
             keterangan, status, denda)
            VALUES (?, ?, ?, ?, ?, 'belumkembali', 'tidakterlambat', 0)
        ");
        $s->bind_param(
            "iisss",
            $idpeminjaman,
            $idalat,
            $tanggalpinjam,
            $tanggalkembali,
            $fotoPeminjaman
        );
        $s->execute();
        $s->close();
    }

    header("Location: " . BASE_URL . "dashboard.php?hal=peminjam/riwayatpeminjaman&msg=sukses_tambah");
    exit;
}

/* ============================================================
   B. EDIT PEMINJAMAN (versi final)
   ============================================================ */
if ($aksi === 'edit') {

    $idpeminjaman = (int)($_POST['idpeminjaman'] ?? 0);
    $idadmin      = (int)($_POST['idadmin'] ?? 0);

    if ($idadmin <= 0) die("Admin tidak boleh kosong!");

    // Cek kepemilikan
    $cek = $db->prepare("SELECT idpeminjam FROM peminjaman WHERE idpeminjaman=?");
    $cek->bind_param("i", $idpeminjaman);
    $cek->execute();
    $res = $cek->get_result();
    $data = $res->fetch_assoc();
    $cek->close();

    if (!$data || $data['idpeminjam'] != $idpeminjam) {
        die("Akses ditolak!");
    }

    // Update utama tabel peminjaman
    $u = $db->prepare("UPDATE peminjaman SET idadmin=? WHERE idpeminjaman=?");
    $u->bind_param("ii", $idadmin, $idpeminjaman);
    $u->execute();
    $u->close();

    /* ------ Update / Insert Detil ------ */
    $idalats   = $_POST['idalat'] ?? [];
    $tglPinjam = $_POST['tanggalpinjam'] ?? [];
    $tglKembali = $_POST['tanggalkembali'] ?? [];
    $iddetils  = $_POST['iddetilpeminjaman'] ?? [];

    $fotopinjam_arr = $_FILES['fotopeminjaman'] ?? null;

    foreach ($idalats as $i => $idalat) {

        $idalat         = (int)$idalat;
        $tanggalpinjam  = bersihkan($tglPinjam[$i]);
        $tanggalkembali = bersihkan($tglKembali[$i]);
        $iddetil        = (int)($iddetils[$i] ?? 0);

        $fotoBaru = null;

        if (!empty($fotopinjam_arr['name'][$i])) {

            $fileItem = [
                'name'     => $fotopinjam_arr['name'][$i],
                'type'     => $fotopinjam_arr['type'][$i],
                'tmp_name' => $fotopinjam_arr['tmp_name'][$i],
                'error'    => $fotopinjam_arr['error'][$i],
                'size'     => $fotopinjam_arr['size'][$i]
            ];

            $up = upload_gambar($fileItem, 'fotopeminjaman');

            if ($up['status'] === 'success') {
                $fotoBaru = $up['filename'];
            }
        }

        if ($iddetil > 0) {

            // Update detil
            if ($fotoBaru) {
                $q = "
                    UPDATE detilpeminjaman 
                    SET idalat=?, tanggalpinjam=?, tanggalkembali=?, fotopeminjaman=?
                    WHERE iddetilpeminjaman=?
                ";
                $s = $db->prepare($q);
                $s->bind_param("isssi",
                    $idalat, $tanggalpinjam, $tanggalkembali, $fotoBaru, $iddetil
                );
            } else {
                $q = "
                    UPDATE detilpeminjaman 
                    SET idalat=?, tanggalpinjam=?, tanggalkembali=?
                    WHERE iddetilpeminjaman=?
                ";
                $s = $db->prepare($q);
                $s->bind_param("issi",
                    $idalat, $tanggalpinjam, $tanggalkembali, $iddetil
                );
            }

            $s->execute();
            $s->close();

        } else {
            // Insert detil baru
            $s = $db->prepare("
                INSERT INTO detilpeminjaman
                (idpeminjaman, idalat, tanggalpinjam, tanggalkembali, fotopeminjaman,
                 keterangan, status, denda)
                VALUES (?, ?, ?, ?, ?, 'belumkembali', 'tidakterlambat', 0)
            ");
            $s->bind_param("iisss",
                $idpeminjaman,
                $idalat,
                $tanggalpinjam,
                $tanggalkembali,
                $fotoBaru
            );
            $s->execute();
            $s->close();
        }
    }

    header("Location: " . BASE_URL . "dashboard.php?hal=peminjam/riwayatpeminjaman&msg=sukses_edit");
    exit;
}

/* Default redirect */
header("Location: " . BASE_URL . "dashboard.php?hal=peminjam/riwayatpeminjaman");
exit;
