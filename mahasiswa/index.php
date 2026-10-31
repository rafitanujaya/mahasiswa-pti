
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Search
$search = trim($_GET['search'] ?? '');

// Pagination
$perPage = 10;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = ($page && $page > 0) ? $page : 1;

$where = '';
$params = [];

if ($search !== '') {
    $where = "WHERE nim LIKE :nim OR nama LIKE :nama OR jurusan LIKE :jurusan";
    $keyword = '%' . $search . '%';

    $params = [
        'nim' => $keyword,
        'nama' => $keyword,
        'jurusan' => $keyword,
    ];
}

// Total results
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM mahasiswa $where");
$countStmt->execute($params);

$totalData = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalData / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// Fetch students
$stmt = $pdo->prepare("
    SELECT id, nim, nama, email, jurusan, angkatan
    FROM mahasiswa
    $where
    ORDER BY created_at DESC, id DESC
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$mahasiswa = $stmt->fetchAll();

// CSRF token for delete forms
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Data Mahasiswa';
$activePage = 'mahasiswa';

require_once __DIR__ . '/../includes/header.php';


function pageUrl(int $page, string $search): string
{
    return '/mahasiswa/index.php?' . http_build_query([
        'page' => $page,
        'search' => $search,
    ]);
}
?>

<?php
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<?php if ($flashSuccess): ?>
    <div role="status" class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-700">
        <?= e($flashSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($flashError): ?>
    <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-700">
        <?= e($flashError) ?>
    </div>
<?php endif; ?>

<!-- Heading -->
<section class="mb-9 flex items-end justify-between">
    <div>
        <p class="mb-2 text-sm font-medium text-blue-600">
            Student Directory
        </p>

        <h1 class="text-[34px] font-semibold tracking-[-0.055em] text-slate-950">
            Data Mahasiswa
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Kelola seluruh data mahasiswa dalam satu tempat.
        </p>
    </div>

    <a
        href="/mahasiswa/create.php"
        class="inline-flex h-11 items-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-700">

        <svg width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round">
            <path d="M12 5v14M5 12h14"/>
        </svg>

        Tambah Mahasiswa
    </a>
</section>

<!-- Toolbar -->
<section class="mb-5 flex items-center justify-between gap-6">

    <div>
        <p class="text-sm font-semibold text-slate-800">
            Daftar Mahasiswa
            <span class="ml-2 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                <?= number_format($totalData, 0, ',', '.') ?>
            </span>
        </p>

        <p class="mt-1 text-xs text-slate-400">
            <?= $search !== '' ? 'Hasil pencarian mahasiswa' : 'Seluruh data mahasiswa terdaftar' ?>
        </p>
    </div>

    <form action="/mahasiswa/index.php" method="GET" class="flex items-center gap-2">

        <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"
                 width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>

            <input
                type="search"
                name="search"
                value="<?= e($search) ?>"
                placeholder="Cari nama, NIM, jurusan..."
                class="h-11 w-[300px] rounded-xl border border-slate-200 bg-white pl-11 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
            >
        </div>

        <button
            type="submit"
            class="h-11 rounded-xl border border-slate-200 bg-white px-5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
            Cari
        </button>

        <?php if ($search !== ''): ?>
            <a
                href="/mahasiswa/index.php"
                class="text-sm font-medium text-blue-600 hover:text-blue-800">
                Reset
            </a>
        <?php endif; ?>
    </form>

</section>

<!-- Table -->
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">

    <table class="w-full text-left">

        <thead class="border-b border-slate-100 bg-slate-50/70">
            <tr class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                <th class="px-6 py-4">Mahasiswa</th>
                <th class="px-6 py-4">NIM</th>
                <th class="px-6 py-4">Jurusan</th>
                <th class="px-6 py-4">Angkatan</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">

            <?php if (empty($mahasiswa)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-20 text-center">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                            <svg width="23" height="23" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.5"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                        </div>

                        <h3 class="text-sm font-semibold text-slate-800">
                            <?= $search !== '' ? 'Mahasiswa tidak ditemukan' : 'Belum ada mahasiswa' ?>
                        </h3>

                        <p class="mt-2 text-sm text-slate-400">
                            <?= $search !== ''
                                ? 'Coba gunakan kata kunci pencarian lainnya.'
                                : 'Tambahkan mahasiswa pertama untuk memulai.' ?>
                        </p>

                        <?php if ($search === ''): ?>
                            <a href="/mahasiswa/create.php"
                               class="mt-5 inline-block text-sm font-semibold text-blue-600 hover:text-blue-800">
                                + Tambah Mahasiswa
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($mahasiswa as $mhs): ?>
                    <tr class="transition hover:bg-slate-50/70">

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-semibold text-blue-700">
                                    <?= e(strtoupper(substr($mhs['nama'], 0, 1))) ?>
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800">
                                        <?= e($mhs['nama']) ?>
                                    </p>

                                    <p class="truncate text-xs text-slate-400">
                                        <?= e($mhs['email']) ?>
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-sm font-medium text-slate-600">
                            <?= e($mhs['nim']) ?>
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600">
                            <?= e($mhs['jurusan']) ?>
                        </td>

                        <td class="px-6 py-4">
                            <span class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                <?= e($mhs['angkatan']) ?>
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-4">

                                <a
                                    href="/mahasiswa/edit.php?id=<?= (int) $mhs['id'] ?>"
                                    class="text-sm font-medium text-blue-600 transition hover:text-blue-800">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="/mahasiswa/delete.php"
                                      onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini?');">

                                    <input type="hidden"
                                           name="id"
                                           value="<?= (int) $mhs['id'] ?>">

                                    <input type="hidden"
                                           name="csrf_token"
                                           value="<?= e($_SESSION['csrf_token']) ?>">

                                    <button type="submit"
                                            class="text-sm font-medium text-red-500 transition hover:text-red-700">
                                        Hapus
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>
                <?php endforeach; ?>

            <?php endif; ?>

        </tbody>
    </table>

    <!-- Pagination -->
    <div class="flex items-center justify-between border-t border-slate-100 px-6 py-5">

        <p class="text-xs text-slate-500">
            <?php if ($totalData > 0): ?>
                Menampilkan
                <span class="font-semibold text-slate-700">
                    <?= $offset + 1 ?>–<?= min($offset + $perPage, $totalData) ?>
                </span>
                dari
                <span class="font-semibold text-slate-700">
                    <?= number_format($totalData, 0, ',', '.') ?>
                </span>
                mahasiswa
            <?php else: ?>
                Tidak ada data ditampilkan
            <?php endif; ?>
        </p>

        <div class="flex items-center gap-2">

            <?php if ($page > 1): ?>
                <a href="<?= e(pageUrl($page - 1, $search)) ?>"
                   class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">
                    Sebelumnya
                </a>
            <?php endif; ?>

            <span class="px-2 text-xs font-medium text-slate-500">
                <?= $page ?> / <?= $totalPages ?>
            </span>

            <?php if ($page < $totalPages): ?>
                <a href="<?= e(pageUrl($page + 1, $search)) ?>"
                   class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">
                    Selanjutnya
                </a>
            <?php endif; ?>

        </div>
    </div>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
