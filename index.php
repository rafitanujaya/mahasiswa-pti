
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

// Dashboard statistics
$totalMahasiswa = (int) $pdo
    ->query("SELECT COUNT(*) FROM mahasiswa")
    ->fetchColumn();

$totalJurusan = (int) $pdo
    ->query("SELECT COUNT(DISTINCT jurusan) FROM mahasiswa")
    ->fetchColumn();

$angkatanTerbaru = $pdo
    ->query("SELECT MAX(angkatan) FROM mahasiswa")
    ->fetchColumn();

$totalAngkatanTerbaru = 0;

if ($angkatanTerbaru !== null) {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM mahasiswa WHERE angkatan = :angkatan"
    );

    $stmt->execute([
        'angkatan' => $angkatanTerbaru
    ]);

    $totalAngkatanTerbaru = (int) $stmt->fetchColumn();
}

// Latest student records
$mahasiswaTerbaru = $pdo
    ->query("
        SELECT id, nim, nama, email, jurusan, angkatan
        FROM mahasiswa
        ORDER BY created_at DESC, id DESC
        LIMIT 5
    ")
    ->fetchAll();

$pageTitle = 'Dashboard';
$activePage = 'dashboard';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Heading -->
<section class="mb-10 flex items-end justify-between">

    <div>
        <p class="mb-2 text-sm font-medium text-blue-600">
            Student Management
        </p>

        <h1 class="text-[34px] font-semibold tracking-[-0.055em] text-slate-950">
            Welcome back, <?= e($username) ?>.
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Berikut ringkasan data mahasiswa yang kamu kelola.
        </p>
    </div>

    <a
        href="/mahasiswa/create.php"
        class="inline-flex h-11 items-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-700">

        <svg width="17" height="17" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 5v14M5 12h14" />
        </svg>

        Tambah Mahasiswa
    </a>

</section>

<!-- Statistics -->
<section class="mb-12 grid grid-cols-3 gap-5">

    <!-- Total Students -->
    <div class="rounded-2xl border border-slate-200 bg-white p-7">

        <div class="mb-8 flex items-center justify-between">
            <span class="text-sm font-medium text-slate-500">
                Total Mahasiswa
            </span>

            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <svg width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                </svg>
            </span>
        </div>

        <p class="text-[42px] font-semibold leading-none tracking-[-0.06em]">
            <?= number_format($totalMahasiswa, 0, ',', '.') ?>
        </p>

        <p class="mt-3 text-xs text-slate-400">
            Mahasiswa terdaftar
        </p>
    </div>

    <!-- Total Majors -->
    <div class="rounded-2xl border border-slate-200 bg-white p-7">

        <div class="mb-8 flex items-center justify-between">
            <span class="text-sm font-medium text-slate-500">
                Total Jurusan
            </span>

            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <svg width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m2 10 10-5 10 5-10 5-10-5Z" />
                    <path d="M6 12v5c3 3 9 3 12 0v-5" />
                    <path d="M22 10v6" />
                </svg>
            </span>
        </div>

        <p class="text-[42px] font-semibold leading-none tracking-[-0.06em]">
            <?= number_format($totalJurusan, 0, ',', '.') ?>
        </p>

        <p class="mt-3 text-xs text-slate-400">
            Jurusan terdaftar
        </p>
    </div>

    <!-- Latest Cohort -->
    <div class="rounded-2xl border border-slate-200 bg-white p-7">

        <div class="mb-8 flex items-center justify-between">
            <span class="text-sm font-medium text-slate-500">
                Angkatan Terbaru
            </span>

            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                <svg width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="16" rx="2" />
                    <path d="M16 3v4M8 3v4M3 11h18" />
                </svg>
            </span>
        </div>

        <p class="text-[42px] font-semibold leading-none tracking-[-0.06em]">
            <?= $angkatanTerbaru !== null ? e($angkatanTerbaru) : '—' ?>
        </p>

        <p class="mt-3 text-xs text-slate-400">
            <?= number_format($totalAngkatanTerbaru, 0, ',', '.') ?>
            mahasiswa pada angkatan ini
        </p>
    </div>

</section>

<!-- Latest Students -->
<section>

    <div class="mb-6 flex items-center justify-between">

        <div>
            <h2 class="text-xl font-semibold tracking-[-0.035em]">
                Mahasiswa Terbaru
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Daftar mahasiswa yang terakhir ditambahkan.
            </p>
        </div>

        <a
            href="/mahasiswa/index.php"
            class="text-sm font-semibold text-blue-600 transition hover:text-blue-800">
            Lihat semua →
        </a>

    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">

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

                <?php if (empty($mahasiswaTerbaru)): ?>

                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <p class="text-sm font-medium text-slate-700">
                                Belum ada data mahasiswa
                            </p>

                            <p class="mt-2 text-sm text-slate-400">
                                Tambahkan mahasiswa pertama untuk memulai.
                            </p>

                            <a
                                href="/mahasiswa/create.php"
                                class="mt-5 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-800">
                                + Tambah Mahasiswa
                            </a>
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($mahasiswaTerbaru as $mhs): ?>

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

                            <td class="px-6 py-4 text-sm text-slate-600">
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

                            <td class="px-6 py-4 text-right">
                                <a
                                    href="/mahasiswa/edit.php?id=<?= (int) $mhs['id'] ?>"
                                    class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                    Edit
                                </a>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>
        </table>

    </div>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
