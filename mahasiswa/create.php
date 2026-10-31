
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Inisialisasi form
$data = [
    'nim' => '',
    'nama' => '',
    'email' => '',
    'jurusan' => '',
    'angkatan' => ''
];

$errors = [];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $csrf = $_POST['csrf_token'] ?? '';

    if (!is_string($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        http_response_code(403);
        exit('Permintaan tidak valid.');
    }

    foreach ($data as $field => $value) {
        $input = $_POST[$field] ?? '';
        $data[$field] = is_string($input) ? trim($input) : '';
    }

    // Validasi NIM
    if ($data['nim'] === '') {
        $errors['nim'] = 'NIM wajib diisi.';
    } elseif (strlen($data['nim']) > 20) {
        $errors['nim'] = 'NIM maksimal 20 karakter.';
    } elseif (!preg_match('/^[0-9]+$/', $data['nim'])) {
        $errors['nim'] = 'NIM hanya boleh berisi angka.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM mahasiswa WHERE nim = :nim'
        );
        $stmt->execute(['nim' => $data['nim']]);

        if ((int) $stmt->fetchColumn() > 0) {
            $errors['nim'] = 'NIM sudah terdaftar.';
        }
    }

    // Validasi nama
    if ($data['nama'] === '') {
        $errors['nama'] = 'Nama lengkap wajib diisi.';
    } elseif (mb_strlen($data['nama']) > 100) {
        $errors['nama'] = 'Nama maksimal 100 karakter.';
    }

    // Validasi email
    if ($data['email'] === '') {
        $errors['email'] = 'Email wajib diisi.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    } elseif (strlen($data['email']) > 100) {
        $errors['email'] = 'Email maksimal 100 karakter.';
    }

    // Validasi jurusan
    if ($data['jurusan'] === '') {
        $errors['jurusan'] = 'Jurusan wajib dipilih.';
    } elseif (mb_strlen($data['jurusan']) > 100) {
        $errors['jurusan'] = 'Jurusan maksimal 100 karakter.';
    }

    // Validasi angkatan
    $currentYear = (int) date('Y');

    if ($data['angkatan'] === '') {
        $errors['angkatan'] = 'Angkatan wajib diisi.';
    } elseif (
        !ctype_digit($data['angkatan']) ||
        strlen($data['angkatan']) !== 4 ||
        (int) $data['angkatan'] < 2000 ||
        (int) $data['angkatan'] > $currentYear
    ) {
        $errors['angkatan'] = 'Angkatan harus antara 2000 dan ' . $currentYear . '.';
    }

    // Simpan jika valid
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO mahasiswa
                    (nim, nama, email, jurusan, angkatan)
                VALUES
                    (:nim, :nama, :email, :jurusan, :angkatan)
            ");

            $stmt->execute([
                'nim' => $data['nim'],
                'nama' => $data['nama'],
                'email' => $data['email'],
                'jurusan' => $data['jurusan'],
                'angkatan' => $data['angkatan']
            ]);

            $_SESSION['flash_success'] = 'Mahasiswa berhasil ditambahkan.';
            header('Location: /mahasiswa/index.php');
            exit;

        } catch (PDOException $e) {
            // 23000 = integrity constraint violation
            if ($e->getCode() === '23000') {
                $errors['nim'] = 'NIM sudah terdaftar.';
            } else {
                error_log($e->getMessage());
                $errors['general'] = 'Gagal menyimpan data. Silakan coba lagi.';
            }
        }
    }
}

$pageTitle = 'Tambah Mahasiswa';
$activePage = 'mahasiswa';

require_once __DIR__ . '/../includes/header.php';
?>
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div role="status" class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-700">
        <?= e($_SESSION['flash_success']) ?>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<!-- Breadcrumb -->
<nav class="mb-7 flex items-center gap-2 text-sm text-slate-400">
    <a href="/mahasiswa/index.php" class="transition hover:text-blue-600">
        Data Mahasiswa
    </a>

    <span>/</span>
    <span class="font-medium text-slate-700">Tambah Mahasiswa</span>
</nav>

<!-- Page Header -->
<div class="mb-9 flex items-start justify-between">
    <div>
        <h1 class="text-[34px] font-semibold tracking-[-0.055em] text-slate-950">
            Tambah Mahasiswa
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            Lengkapi informasi mahasiswa untuk menambahkan data baru.
        </p>
    </div>

    <a href="/mahasiswa/index.php"
       class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">

        <svg width="17" height="17" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="1.8"
             stroke-linecap="round" stroke-linejoin="round">
            <path d="m15 18-6-6 6-6"/>
        </svg>

        Kembali
    </a>
</div>

<!-- Form -->
<section class="max-w-[850px]">

    <?php if (isset($errors['general'])): ?>
        <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <?= e($errors['general']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/mahasiswa/create.php" novalidate>

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e($_SESSION['csrf_token']) ?>"
        >

        <!-- Basic Information -->
        <div class="border-b border-slate-200 pb-7">
            <h2 class="text-base font-semibold text-slate-900">
                Informasi Mahasiswa
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Informasi identitas dan akademik mahasiswa.
            </p>
        </div>

        <div class="space-y-7 py-8">

            <!-- NIM -->
            <div class="grid grid-cols-[210px_1fr] items-start gap-8">
                <div class="pt-3">
                    <label for="nim" class="block text-sm font-semibold text-slate-700">
                        Nomor Induk Mahasiswa
                        <span class="text-red-500">*</span>
                    </label>
                    <p class="mt-1 text-xs text-slate-400">
                        Identitas unik mahasiswa
                    </p>
                </div>

                <div>
                    <input
                        type="text"
                        id="nim"
                        name="nim"
                        value="<?= e($data['nim']) ?>"
                        placeholder="Contoh: 202410001"
                        maxlength="20"
                        inputmode="numeric"
                        autocomplete="off"
                        required
                        aria-invalid="<?= isset($errors['nim']) ? 'true' : 'false' ?>"
                        class="h-12 w-full rounded-xl border bg-white px-4 text-sm outline-none transition placeholder:text-slate-400 focus:ring-4 focus:ring-blue-500/10
                        <?= isset($errors['nim'])
                            ? 'border-red-400 focus:border-red-500'
                            : 'border-slate-200 focus:border-blue-500' ?>"
                    >

                    <?php if (isset($errors['nim'])): ?>
                        <p class="mt-2 text-xs text-red-600">
                            <?= e($errors['nim']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Nama -->
            <div class="grid grid-cols-[210px_1fr] items-start gap-8">
                <div class="pt-3">
                    <label for="nama" class="block text-sm font-semibold text-slate-700">
                        Nama Lengkap
                        <span class="text-red-500">*</span>
                    </label>
                    <p class="mt-1 text-xs text-slate-400">
                        Sesuai identitas mahasiswa
                    </p>
                </div>

                <div>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="<?= e($data['nama']) ?>"
                        placeholder="Masukkan nama lengkap"
                        maxlength="100"
                        autocomplete="name"
                        required
                        aria-invalid="<?= isset($errors['nama']) ? 'true' : 'false' ?>"
                        class="h-12 w-full rounded-xl border bg-white px-4 text-sm outline-none transition placeholder:text-slate-400 focus:ring-4 focus:ring-blue-500/10
                        <?= isset($errors['nama'])
                            ? 'border-red-400 focus:border-red-500'
                            : 'border-slate-200 focus:border-blue-500' ?>"
                    >

                    <?php if (isset($errors['nama'])): ?>
                        <p class="mt-2 text-xs text-red-600">
                            <?= e($errors['nama']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Email -->
            <div class="grid grid-cols-[210px_1fr] items-start gap-8">
                <div class="pt-3">
                    <label for="email" class="block text-sm font-semibold text-slate-700">
                        Email
                        <span class="text-red-500">*</span>
                    </label>
                    <p class="mt-1 text-xs text-slate-400">
                        Alamat email mahasiswa
                    </p>
                </div>

                <div>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= e($data['email']) ?>"
                        placeholder="nama@example.com"
                        maxlength="100"
                        autocomplete="email"
                        required
                        aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>"
                        class="h-12 w-full rounded-xl border bg-white px-4 text-sm outline-none transition placeholder:text-slate-400 focus:ring-4 focus:ring-blue-500/10
                        <?= isset($errors['email'])
                            ? 'border-red-400 focus:border-red-500'
                            : 'border-slate-200 focus:border-blue-500' ?>"
                    >

                    <?php if (isset($errors['email'])): ?>
                        <p class="mt-2 text-xs text-red-600">
                            <?= e($errors['email']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Jurusan -->
            <div class="grid grid-cols-[210px_1fr] items-start gap-8">
                <div class="pt-3">
                    <label for="jurusan" class="block text-sm font-semibold text-slate-700">
                        Jurusan
                        <span class="text-red-500">*</span>
                    </label>
                    <p class="mt-1 text-xs text-slate-400">
                        Program studi mahasiswa
                    </p>
                </div>

                <div>
                    <input
                        type="text"
                        id="jurusan"
                        name="jurusan"
                        value="<?= e($data['jurusan']) ?>"
                        placeholder="Contoh: Teknik Informatika"
                        maxlength="100"
                        required
                        aria-invalid="<?= isset($errors['jurusan']) ? 'true' : 'false' ?>"
                        class="h-12 w-full rounded-xl border bg-white px-4 text-sm outline-none transition placeholder:text-slate-400 focus:ring-4 focus:ring-blue-500/10
                        <?= isset($errors['jurusan'])
                            ? 'border-red-400 focus:border-red-500'
                            : 'border-slate-200 focus:border-blue-500' ?>"
                    >

                    <?php if (isset($errors['jurusan'])): ?>
                        <p class="mt-2 text-xs text-red-600">
                            <?= e($errors['jurusan']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Angkatan -->
            <div class="grid grid-cols-[210px_1fr] items-start gap-8">
                <div class="pt-3">
                    <label for="angkatan" class="block text-sm font-semibold text-slate-700">
                        Angkatan
                        <span class="text-red-500">*</span>
                    </label>
                    <p class="mt-1 text-xs text-slate-400">
                        Tahun masuk mahasiswa
                    </p>
                </div>

                <div>
                    <input
                        type="number"
                        id="angkatan"
                        name="angkatan"
                        value="<?= e($data['angkatan']) ?>"
                        placeholder="Contoh: 2024"
                        min="2000"
                        max="<?= date('Y') ?>"
                        step="1"
                        required
                        aria-invalid="<?= isset($errors['angkatan']) ? 'true' : 'false' ?>"
                        class="h-12 w-full rounded-xl border bg-white px-4 text-sm outline-none transition placeholder:text-slate-400 focus:ring-4 focus:ring-blue-500/10
                        <?= isset($errors['angkatan'])
                            ? 'border-red-400 focus:border-red-500'
                            : 'border-slate-200 focus:border-blue-500' ?>"
                    >

                    <?php if (isset($errors['angkatan'])): ?>
                        <p class="mt-2 text-xs text-red-600">
                            <?= e($errors['angkatan']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-7">

            <a href="/mahasiswa/index.php"
               class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-6 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                Batal
            </a>

            <button
                type="submit"
                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 text-sm font-semibold text-white transition hover:bg-blue-700">

                <svg width="17" height="17" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>

                Simpan Mahasiswa
            </button>
        </div>

    </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
