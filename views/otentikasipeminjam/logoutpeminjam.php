<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/path.php';

// Hapus semua data session
$_SESSION = [];

// Hapus session di server
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    // Hapus cookie session dengan format array (PHP 7.3+)
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => 'Lax'
    ]);
}

// Destroy session
session_destroy();

// Redirect ke halaman login peminjam
header("Location: " . BASE_URL . "dashboard.php?hal=loginpeminjam");
exit;
