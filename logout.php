
<?php

require_once __DIR__ . '/includes/auth.php';

// Logout hanya boleh menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method tidak diizinkan.');
}

// Validasi CSRF token
$csrf = $_POST['csrf_token'] ?? '';

if (
    !is_string($csrf) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrf)
) {
    http_response_code(403);
    exit('Permintaan tidak valid.');
}

// Hapus data session
$_SESSION = [];

// Hapus cookie session
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}

// Hancurkan session
session_destroy();

// Kembali ke halaman login
header('Location: /login.php', true, 303);
exit;
