<?php
session_start();

require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

$error = '';
$username = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    } elseif ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, username, password FROM users WHERE username = :username LIMIT 1'
        );

        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            header('Location: /index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIMAWA</title>
    <link rel="stylesheet" href="/assets/css/output.css">
</head>
<body class="min-w-[1280px] bg-white text-slate-900 antialiased">

    <main class="grid min-h-screen grid-cols-[1.15fr_0.85fr]">

        <!-- LEFT -->
        <section class="relative overflow-hidden bg-white px-20 py-16">
            <div class="relative z-10 flex h-full flex-col justify-between">

                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-sm font-semibold text-white">
                        S
                    </div>
                    <div>
                        <p class="text-lg font-semibold tracking-[-0.04em] text-slate-950">SIMAWA</p>
                        <p class="text-sm text-slate-500">Student Management System</p>
                    </div>
                </div>

                <div class="max-w-2xl">
                    <p class="mb-5 text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">
                        Academic Management
                    </p>

                    <h1 class="max-w-xl text-[64px] font-semibold leading-[1.02] tracking-[-0.07em] text-slate-950">
                        Manage student data
                        in a calmer, cleaner way.
                    </h1>

                    <p class="mt-7 max-w-lg text-[17px] leading-8 text-slate-500">
                        Sistem sederhana untuk mengelola data mahasiswa dengan alur yang rapi,
                        cepat, dan mudah digunakan.
                    </p>
                </div>

                <div class="max-w-md">

                </div>
            </div>

            <!-- abstract -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -left-24 top-24 h-72 w-72 rounded-full bg-slate-100 blur-3xl"></div>
                <div class="absolute bottom-[-60px] left-28 h-64 w-64 rounded-full bg-blue-100/70 blur-3xl"></div>
                <div class="absolute right-12 top-20 h-[320px] w-[320px] rounded-full border border-slate-200"></div>
                <div class="absolute right-24 top-36 h-[220px] w-[220px] rounded-full border border-slate-100"></div>
                <div class="absolute right-16 bottom-14 h-40 w-40 rounded-[40px] bg-slate-50 rotate-12"></div>
            </div>
        </section>

        <!-- RIGHT -->
        <section class="flex items-center justify-center bg-[linear-gradient(180deg,#f5f9ff_0%,#edf4ff_100%)] px-16 py-16">
            <div class="w-full max-w-[380px]">

                <div class="mb-10">
                    <p class="text-sm font-medium text-blue-600">
                        Welcome back
                    </p>
                    <h2 class="mt-2 text-[38px] font-semibold tracking-[-0.06em] text-slate-950">
                        Sign in
                    </h2>
                    <p class="mt-3 text-sm leading-7 text-slate-500">
                        Masuk ke dashboard untuk melanjutkan pengelolaan data mahasiswa.
                    </p>
                </div>

                <?php if ($error): ?>
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/login.php" class="space-y-5">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
                    >

                    <div>
                        <label for="username" class="mb-2 block text-sm font-medium text-slate-700">
                            Username
                        </label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Enter your username"
                            autocomplete="username"
                            maxlength="50"
                            required
                            class="h-13 w-full rounded-2xl border border-blue-100 bg-white/90 px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-slate-700">
                            Password
                        </label>

                        <div class="relative">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                                class="h-13 w-full rounded-2xl border border-blue-100 bg-white/90 px-4 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                aria-label="Show password"
                                aria-pressed="false"
                                class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 transition hover:text-slate-700"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     width="18" height="18"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8"
                                     stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="mt-2 flex h-13 w-full items-center justify-center rounded-2xl bg-blue-600 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        Sign in
                    </button>
                </form>
            </div>
        </section>

    </main>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';

            togglePassword.setAttribute(
                'aria-label',
                isHidden ? 'Hide password' : 'Show password'
            );
            togglePassword.setAttribute('aria-pressed', String(isHidden));
        });
    </script>
</body>
</html>