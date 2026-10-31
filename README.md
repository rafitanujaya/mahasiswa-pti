# SIMAWA — Sistem Informasi Manajemen Mahasiswa

SIMAWA adalah aplikasi berbasis web untuk mengelola data mahasiswa secara sederhana dan terstruktur. Aplikasi ini dikembangkan menggunakan **PHP Native, MySQL, dan Tailwind CSS** sebagai implementasi sistem informasi manajemen data dengan fitur autentikasi, CRUD, validasi input, dan dukungan dua bahasa.

Project ini dibuat untuk memenuhi **Tugas 2 — Sistem Informasi Manajemen Data Sederhana**.

## Fitur Utama

- **Autentikasi Admin** — Login dan logout menggunakan PHP Session.
- **Dashboard** — Ringkasan total mahasiswa, jumlah jurusan, angkatan terbaru, dan data mahasiswa terbaru.
- **Manajemen Mahasiswa** — Menambahkan, menampilkan, mengubah, dan menghapus data mahasiswa.
- **Pencarian & Pagination** — Pencarian mahasiswa berdasarkan NIM, nama, atau jurusan dengan pembagian halaman.
- **Validasi Form** — Memeriksa kelengkapan dan format data sebelum disimpan.
- **Dual Language** — Mendukung Bahasa Indonesia dan Bahasa Inggris menggunakan PHP Session.
- **Keamanan** — Menggunakan PDO Prepared Statements, CSRF Token, password hashing, dan HTML escaping.

## Teknologi

| Teknologi | Penggunaan |
|---|---|
| PHP Native | Backend dan logika aplikasi |
| MySQL / MariaDB | Database |
| Tailwind CSS v4 | Styling antarmuka |
| JavaScript | Interaksi antarmuka |
| Laravel Herd | Local development server |
| Composer | Tidak diperlukan |
| NPM | Pengelolaan Tailwind CSS |

## Struktur Project

```text
mahasiswa-pti/
├── assets/
│   ├── css/
│   │   ├── input.css
│   │   └── output.css
│   └── js/
│       └── script.js
│
├── config/
│   └── database.php
│
├── database/
│   └── simawa.sql
│
├── includes/
│   ├── auth.php
│   ├── header.php
│   ├── footer.php
│   └── i18n.php
│
├── lang/
│   ├── id.php
│   └── en.php
│
├── mahasiswa/
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   └── delete.php
│
├── index.php
├── login.php
├── logout.php
├── language.php
├── package.json
├── package-lock.json
├── .gitignore
└── README.md
```

## Instalasi dan Konfigurasi

### 1. Clone Repository

```bash
git clone https://github.com/USERNAME/mahasiswa-pti.git
cd mahasiswa-pti
```

Ganti `USERNAME` dengan username GitHub pemilik repository.

### 2. Persyaratan

Pastikan sudah tersedia:

- PHP 8.2 atau lebih baru
- MySQL atau MariaDB
- Node.js dan NPM
- Web server lokal seperti Laravel Herd

Pastikan ekstensi PHP `pdo_mysql` dan `mbstring` aktif.

### 3. Konfigurasi Database

Buat database dengan mengimpor file SQL:

```bash
mysql -u root -p < database/simawa.sql
```

Kemudian sesuaikan konfigurasi `config/database.php`:

```php
<?php

$host = '127.0.0.1';
$port = '3306';
$dbname = 'simawa';
$username = 'root';
$password = 'YOUR_DATABASE_PASSWORD';

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);
```

Gunakan kredensial database lokal masing-masing. Jangan mengunggah password database asli ke repository publik.

### 4. Menyiapkan Akun Administrator

Aplikasi tidak menyediakan registrasi publik.

Untuk membuat akun administrator pertama, jalankan skrip PHP lokal yang menggunakan `password_hash()` dan menyimpan akun ke tabel `users`.

Contoh pembuatan hash:

```php
<?php
echo password_hash('GANTI_DENGAN_PASSWORD_KUAT', PASSWORD_DEFAULT);
```

Simpan hasil hash ke kolom `password`, bukan password dalam bentuk plaintext.

Contoh query:

```sql
INSERT INTO users (username, password)
VALUES ('admin', 'HASH_HASIL_PASSWORD_HASH');
```

Ganti `HASH_HASIL_PASSWORD_HASH` dengan hasil fungsi PHP tersebut.

### 5. Instalasi Tailwind CSS

```bash
npm install
```

Untuk menjalankan Tailwind dalam mode development:

```bash
npm run dev
```

Untuk menghasilkan file CSS final:

```bash
npm run build
```

File hasil build disimpan pada `assets/css/output.css`.

### 6. Menjalankan Aplikasi

Masuk ke direktori project:

```bash
cd mahasiswa-pti
```

Jika menggunakan Laravel Herd, hubungkan direktori:

```bash
herd link mahasiswa-pti
```

Kemudian akses:

```text
http://mahasiswa-pti.test
```

Alternatif menggunakan PHP built-in server:

```bash
php -S 127.0.0.1:8000
```

Akses melalui browser:

```text
http://127.0.0.1:8000
```

> Catatan: Untuk penggunaan di luar lingkungan development lokal, pastikan folder `config/`, `database/`, dan file internal lain tidak dapat diakses secara publik melalui web server.

## Alur Aplikasi

1. Pengguna mengakses halaman utama SIMAWA.
2. Sistem memeriksa status autentikasi melalui PHP Session.
3. Pengguna yang belum login diarahkan ke halaman login.
4. Setelah login berhasil, pengguna diarahkan ke dashboard.
5. Administrator dapat melakukan operasi CRUD pada data mahasiswa.
6. Data diproses melalui validasi PHP dan disimpan menggunakan PDO.
7. Pengguna dapat mengganti bahasa antarmuka melalui pilihan Indonesia atau English.
8. Ketika logout, session autentikasi dihapus dan pengguna diarahkan ke halaman login.

## Struktur Database

Aplikasi menggunakan database `simawa` dengan dua tabel utama.

### Tabel `users`

| Kolom | Keterangan |
|---|---|
| id | Primary key |
| username | Username administrator |
| password | Hash password |
| created_at | Waktu pembuatan akun |

### Tabel `mahasiswa`

| Kolom | Keterangan |
|---|---|
| id | Primary key |
| nim | Nomor induk mahasiswa (unik) |
| nama | Nama lengkap mahasiswa |
| email | Email mahasiswa |
| jurusan | Jurusan atau program studi |
| angkatan | Tahun masuk mahasiswa |
| created_at | Waktu penambahan data |

## Keamanan

Beberapa mekanisme keamanan yang diterapkan:

- Password disimpan menggunakan `password_hash()`.
- Proses login menggunakan `password_verify()`.
- Proteksi halaman menggunakan PHP Session.
- Regenerasi session ID setelah login berhasil.
- Operasi database menggunakan PDO Prepared Statements.
- Output HTML menggunakan `htmlspecialchars()`.
- Form POST dilindungi CSRF Token.
- Operasi penghapusan hanya menerima request POST.

## Pengujian

| Skenario | Hasil yang Diharapkan |
|---|---|
| Login dengan akun valid | Masuk ke dashboard |
| Login dengan akun tidak valid | Menampilkan pesan error |
| Mengakses dashboard tanpa login | Dialihkan ke login |
| Menambahkan mahasiswa | Data tersimpan |
| Menambahkan NIM duplikat | Validasi ditampilkan |
| Mengubah data mahasiswa | Data diperbarui |
| Menghapus data mahasiswa | Data terhapus |
| Mencari mahasiswa | Menampilkan data sesuai pencarian |
| Mengganti bahasa | Bahasa antarmuka berubah |
| Logout | Session dihapus dan kembali ke login |

## Pengembang

Project ini dikembangkan sebagai bagian dari tugas akademik pemrograman web menggunakan PHP Native.

## Lisensi

Project ini dibuat untuk keperluan pembelajaran dan akademik.