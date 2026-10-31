
<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/i18n.php';

$pageTitle = $pageTitle ?? t('dashboard');
$activePage = $activePage ?? 'dashboard';
$username = $_SESSION['username'] ?? 'Admin';

if (!function_exists('e')) {
    function e($value): string {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$initial = strtoupper(substr($username, 0, 1));
$currentUrl = $_SERVER['REQUEST_URI'] ?? '/index.php';
?>

<!DOCTYPE html>
<html lang="<?= e($_SESSION['lang']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — SIMAWA</title>
    <link rel="stylesheet" href="/assets/css/output.css">
</head>

<body class="min-w-[1100px] bg-[#F8FAFD] font-sans text-slate-900 antialiased">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="sticky top-0 flex h-screen w-[250px] shrink-0 flex-col border-r border-slate-200 bg-white px-5 py-7">

        <a href="/index.php" class="mb-14 flex items-center gap-3 px-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-lg font-bold text-white">
                S
            </div>

            <div>
                <h1 class="text-lg font-bold tracking-[-0.05em]">simawa.</h1>
                <p class="text-[11px] text-slate-400">Student Management</p>
            </div>
        </a>

        <p class="mb-4 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
            <?= e(t('workspace')) ?>
        </p>

        <nav class="space-y-1">

            <a href="/index.php"
               <?= $activePage === 'dashboard' ? 'aria-current="page"' : '' ?>
               class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition <?= $activePage === 'dashboard'
                   ? 'bg-blue-50 font-semibold text-blue-700'
                   : 'font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-900' ?>">

                <svg width="19" height="19" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>

                <?= e(t('dashboard')) ?>
            </a>

            <a href="/mahasiswa/index.php"
               <?= $activePage === 'mahasiswa' ? 'aria-current="page"' : '' ?>
               class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition <?= $activePage === 'mahasiswa'
                   ? 'bg-blue-50 font-semibold text-blue-700'
                   : 'font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-900' ?>">

                <svg width="19" height="19" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>

                <?= e(t('students')) ?>
            </a>
        </nav>

        <!-- Account -->
        <div class="mt-auto border-t border-slate-100 pt-5">

            <div class="mb-4 flex items-center gap-3 px-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700">
                    <?= e($initial) ?>
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900">
                        <?= e($username) ?>
                    </p>
                    <p class="text-xs text-slate-400">
                        <?= e(t('administrator')) ?>
                    </p>
                </div>
            </div>


<!-- Logout -->
<form action="/logout.php" method="POST">

    <input
        type="hidden"
        name="csrf_token"
        value="<?= e($_SESSION['csrf_token']) ?>"
    >

    <button
        type="submit"
        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-medium text-slate-500 transition hover:bg-red-50 hover:text-red-600"
    >
        <svg width="19" height="19"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="1.8"
             stroke-linecap="round"
             stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <path d="m16 17 5-5-5-5"/>
            <path d="M21 12H9"/>
        </svg>

        <?= e(t('logout')) ?>
    </button>

</form>

        </div>
    </aside>

    <!-- Main -->
    <div class="min-w-0 flex-1">

        <header class="flex h-[84px] items-center justify-between border-b border-slate-200 bg-white px-10">

            <div>
                <p class="text-xs text-slate-400">
                    <?= e(t('workspace')) ?> / <?= e($pageTitle) ?>
                </p>
                <h2 class="mt-1 text-sm font-semibold text-slate-800">
                    <?= e($pageTitle) ?>
                </h2>
            </div>

            <div class="flex items-center gap-6">

                <!-- Language Switch -->
                <form action="/language.php" method="POST">
                    <input type="hidden"
                           name="csrf_token"
                           value="<?= e($_SESSION['csrf_token']) ?>">

                    <input type="hidden"
                           name="redirect"
                           value="<?= e($currentUrl) ?>">

                    <label for="language-select" class="sr-only">
                        <?= e(t('language')) ?>
                    </label>

                    <select id="language-select"
                            name="lang"
                            onchange="this.form.submit()"
                            class="cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 outline-none transition focus:border-blue-500">

                        <option value="id" <?= $_SESSION['lang'] === 'id' ? 'selected' : '' ?>>
                            Indonesia
                        </option>

                        <option value="en" <?= $_SESSION['lang'] === 'en' ? 'selected' : '' ?>>
                            English
                        </option>

                    </select>
                </form>

                <span class="h-6 w-px bg-slate-200"></span>

                <div class="flex items-center gap-2 text-sm font-medium text-slate-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <?= e(t('administrator')) ?>
                </div>

            </div>
        </header>

        <main class="mx-auto max-w-[1400px] px-10 py-10">
