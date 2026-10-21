# PHP OOP CRUD Mahasiswa

Aplikasi web untuk mengelola data mahasiswa menggunakan PHP Object-Oriented Programming (OOP), MySQL, dan MySQLi. Proyek ini dikembangkan sebagai latihan penerapan CRUD, pemisahan kode berdasarkan tanggung jawab, validasi input, dan dasar-dasar keamanan aplikasi web.

## Fitur Utama

### Pengelolaan Data Mahasiswa
- Menampilkan daftar mahasiswa.
- Menambahkan data mahasiswa.
- Mengubah data mahasiswa.
- Menghapus data mahasiswa.
- Mencari data mahasiswa dan menggunakan pagination.
- Memvalidasi NIM agar tidak duplikat.

### Autentikasi dan Keamanan
- Login menggunakan verifikasi password.
- Pembatasan percobaan login berulang (*login rate limiting*).
- Pengelolaan session dan regenerasi session ID setelah login.
- Validasi token CSRF pada permintaan yang memerlukan perlindungan.
- Pembatasan akses berdasarkan role pengguna.
- Penggunaan prepared statements untuk query database.
- Escaping output HTML untuk membantu mencegah XSS.
- Pencatatan aktivitas login, logout, dan operasi CRUD melalui security log.

## Teknologi

- PHP
- MySQL
- MySQLi
- HTML
- XAMPP
- Git dan GitHub

## Struktur Proyek

```text
oop-mahasiswa/
├── config/
│   ├── Database.php
│   ├── session.php
│   ├── auth.php
│   └── admin.php
├── controllers/
│   └── MahasiswaController.php
├── models/
│   ├── Mahasiswa.php
│   ├── SecurityLog.php
│   └── LoginRateLimit.php
├── views/
│   └── mahasiswa/
├── index.php
├── tambah.php
├── edit.php
├── hapus.php
├── login.php
├── logout.php
└── logs.php
```

*Struktur di atas menggambarkan file utama proyek; sesuaikan jika struktur repository berubah.*

## Persyaratan

- XAMPP dengan Apache dan MySQL/MariaDB.
- PHP dengan ekstensi MySQLi aktif.
- Browser web.
- Git (opsional, untuk mengelola kode sumber).

## Cara Menjalankan

1. Clone repository ini atau salin folder proyek ke direktori `htdocs` XAMPP.
2. Jalankan Apache dan MySQL melalui XAMPP Control Panel.
3. Buat database bernama `akademik`.
4. Buat tabel dan indeks yang diperlukan oleh aplikasi.
5. Sesuaikan konfigurasi koneksi database pada `config/Database.php`.
6. Pastikan tabel `mahasiswa`, `users`, `security_logs`, dan `login_attempts` telah dibuat dengan struktur yang sesuai.
7. Buka aplikasi melalui browser:

   `http://localhost/LatihanPHP/oop-mahasiswa/`

8. Siapkan akun pengguna pada tabel `users` dengan password yang telah di-hash menggunakan `password_hash()` dan role yang sesuai.

## Catatan Keamanan

Proyek ini merupakan aplikasi pembelajaran dan portofolio. Penggunaan pada lingkungan produksi memerlukan konfigurasi tambahan, termasuk HTTPS, pengelolaan kredensial melalui environment configuration, pengamanan akun database, pencadangan, serta pengujian keamanan lebih lanjut.

Jangan mengunggah password, kredensial database, atau data pribadi pengguna ke repository publik.

## Tujuan Pembelajaran

Melalui proyek ini, saya mempelajari dan mempraktikkan:

- Penerapan konsep OOP dalam PHP.
- Pemisahan model, controller, dan view.
- Operasi CRUD menggunakan MySQLi.
- Validasi data dan penanganan input pengguna.
- Autentikasi, otorisasi, dan pengelolaan session.
- Penerapan dasar keamanan aplikasi web.
- Penggunaan Git dan GitHub untuk pengelolaan versi kode.
