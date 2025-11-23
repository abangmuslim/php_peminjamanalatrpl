<?php
// ============================================================
// File: prosespengembalian.php (FINAL SESUAI STRUKTUR TERBARU)
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/path.php';
require_once INCLUDES_PATH . 'koneksi.php';

// Validasi request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Error: Akses tidak diperbolehkan.");
}

// Ambil data utama
$idpeminjaman = $_POST['idpeminjaman'] ?? '';
$detail       = $_POST['detail'] ?? [];
$tanggalbayar = $_POST['tanggalbayar'] ?? date('Y-m-d');

// Validasi dasar
if (empty($idpeminjaman) || empty($detail)) {
    die("Error: Data pengembalian tidak lengkap.");
}

/* ============================================================
   PERSIAPAN FOLDER UPLOAD
=============================================================== */
$folderUpload = BASE_PATH . "uploads/pengembalian/";

if (!is_dir($folderUpload)) {
    mkdir($folderUpload, 0777, true);
}

/* ============================================================
   PROSES SETIAP DETAIL PEMINJAMAN
=============================================================== */
foreach ($detail as $iddetail => $d) {

    // Tanggal kembali
    $tgl_kembali_input = $_POST['tgl_kembali'][$iddetail] ?? date('Y-m-d');

    $jumlahharitelat = intval($d['jumlahharitelat'] ?? 0);
    $denda           = floatval($d['denda'] ?? 0);
    $status          = $d['status'] ?? 'tidakterlambat';
    $keterangan      = 'sudahkembali';

    /* ============================================================
       UPLOAD FOTO PENGEMBALIAN — PER DETIL
    ===============================================================*/
    $fotoBaru = null;

    if (!empty($_FILES['fotopengembalian']['name'][$iddetail])) {

        $ext = pathinfo($_FILES['fotopengembalian']['name'][$iddetail], PATHINFO_EXTENSION);

        $namaFile = "kembali_" . $iddetail . "_" . time() . "_" . rand(100, 999) . "." . $ext;

        $pathTujuan = $folderUpload . $namaFile;

        if (move_uploaded_file($_FILES['fotopengembalian']['tmp_name'][$iddetail], $pathTujuan)) {
            $fotoBaru = $namaFile;
        }
    }

    /* ============================================================
       UPDATE detilpeminjaman
    ===============================================================*/
    $sql = "
        UPDATE detilpeminjaman 
        SET tanggaldikembalikan = ?, 
            jumlahharitelat = ?, 
            denda = ?, 
            status = ?, 
            keterangan = ?,
            fotopengembalian = COALESCE(?, fotopengembalian)
        WHERE iddetilpeminjaman = ?
    ";

    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param(
        "sidsssi",
        $tgl_kembali_input,
        $jumlahharitelat,
        $denda,
        $status,
        $keterangan,
        $fotoBaru,
        $iddetail
    );
    $stmt->execute();
    $stmt->close();
}

/* ============================================================
   SELESAI — REDIRECT
=============================================================== */
header("Location: " . BASE_URL . "dashboard.php?hal=pengembalian/daftarpengembalian&status=sukses");
exit;
