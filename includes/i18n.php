
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!in_array($_SESSION['lang'] ?? null, ['id', 'en'], true)) {
    $_SESSION['lang'] = 'id';
}

$lang = $_SESSION['lang'];

$translations = require __DIR__ . '/../lang/' . $lang . '.php';

function t(string $key): string
{
    global $translations;

    return $translations[$key] ?? $key;
}
