
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Hanya menerima request POST
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

// Validasi ID mahasiswa
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $_SESSION['flash_error'] = 'ID mahasiswa tidak valid.';

    header('Location: /mahasiswa/index.php');
    exit;
}

try {
    // Pastikan mahasiswa tersedia
    $stmt = $pdo->prepare(
        "SELECT id FROM mahasiswa WHERE id = :id LIMIT 1"
    );

    $stmt->execute(['id' => $id]);

    if (!$stmt->fetch()) {
        $_SESSION['flash_error'] = 'Mahasiswa tidak ditemukan.';
    } else {
        // Hapus mahasiswa berdasarkan ID
        $delete = $pdo->prepare(
            "DELETE FROM mahasiswa WHERE id = :id"
        );

        $delete->execute(['id' => $id]);

        if ($delete->rowCount() > 0) {
            $_SESSION['flash_success'] = 'Mahasiswa berhasil dihapus.';
        } else {
            $_SESSION['flash_error'] = 'Mahasiswa tidak ditemukan.';
        }
    }

} catch (PDOException $e) {
    error_log($e->getMessage());

    $_SESSION['flash_error'] = 'Gagal menghapus mahasiswa. Silakan coba lagi.';
}

// Redirect ke daftar mahasiswa
header('Location: /mahasiswa/index.php');
exit;
