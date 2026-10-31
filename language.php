
<?php

require_once __DIR__ . '/includes/i18n.php';

// Hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

// Validasi CSRF
$csrf = $_POST['csrf_token'] ?? '';

if (
    !is_string($csrf) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrf)
) {
    http_response_code(403);
    exit('Invalid request.');
}

// Simpan bahasa ke Session
$selectedLanguage = $_POST['lang'] ?? '';

if (
    is_string($selectedLanguage) &&
    in_array($selectedLanguage, ['id', 'en'], true)
) {
    $_SESSION['lang'] = $selectedLanguage;
}

// Kembali ke halaman sebelumnya
$redirect = $_POST['redirect'] ?? '/index.php';

if (
    !is_string($redirect) ||
    !str_starts_with($redirect, '/') ||
    str_starts_with($redirect, '//') ||
    str_contains($redirect, '\\') ||
    preg_match('/[\r\n\x00-\x1F]/', $redirect)
) {
    $redirect = '/index.php';
}

header('Location: ' . $redirect, true, 303);
exit;
