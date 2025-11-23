<?php
// Pastikan sesi aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/path.php';

// Hapus seluruh sesi
$_SESSION = [];

// Hapus cookie session dengan format yang benar
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    // Menggunakan mode array untuk mendukung SameSite
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => 'Lax'
    ]);
}

// Hancurkan session server
session_destroy();

// Redirect
header("Location: " . BASE_URL . "?hal=loginuser");
exit;
