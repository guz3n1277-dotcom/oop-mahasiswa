PHP OOP CRUD Mahasiswa
Aplikasi pengelolaan data mahasiswa berbasis PHP OOP dan MySQL/MariaDB. Proyek ini dibuat untuk latihan pengembangan aplikasi web, pengelolaan database, autentikasi, dan penerapan keamanan dasar.
Fitur
- Menampilkan, mencari, menambahkan, mengubah, dan menghapus data mahasiswa.
- Validasi NIM dan pencegahan NIM duplikat.
- Login dan logout pengguna.
- Pembatasan hak akses berdasarkan peran pengguna.
- Proteksi CSRF pada formulir yang mengubah data.
- Prepared statements untuk query yang menerima input.
- Output escaping untuk membantu mencegah XSS.
- Pembatasan percobaan login.
- Pencatatan aktivitas login, logout, dan perubahan data pada log keamanan.
- Antarmuka responsif untuk halaman dashboard, formulir, login, dan log keamanan.
Teknologi
- PHP 8.2 atau versi yang kompatibel dengan kode proyek.
- MySQL/MariaDB.
- MySQLi.
- Apache dan phpMyAdmin melalui XAMPP.
- Git dan GitHub.
Struktur database
Aplikasi menggunakan database akademik dengan tabel:
- mahasiswa — data mahasiswa.
- users — akun dan peran pengguna.
- security_logs — riwayat aktivitas keamanan.
- login_attempts — pencatatan percobaan login dan status pembatasan.
File database.sql berisi struktur tabel tanpa data pribadi. Gunakan file tersebut hanya untuk membuat database baru atau database pengujian yang kosong.
Cara menjalankan secara lokal
1. Pasang dan jalankan XAMPP.
2. Aktifkan Apache dan MySQL.
3. Letakkan folder proyek di C:\xampp\htdocs\LatihanPHP\oop-mahasiswa.
4. Buka phpMyAdmin dan buat database kosong bernama akademik.
5. Pilih database akademik, buka tab Import, lalu impor file database.sql.
6. Periksa pengaturan koneksi database di config/Database.php agar sesuai dengan lingkungan lokal.
7. Siapkan akun admin menggunakan mekanisme pembuatan akun yang menyimpan password dengan password_hash(); jangan menyimpan password dalam teks biasa.
8. Buka http://localhost/LatihanPHP/oop-mahasiswa/ di browser.
Nama file, pengaturan koneksi, dan mekanisme pembuatan akun harus disesuaikan dengan versi kode yang ada di repositori. Jangan menimpa database aktif yang berisi data penting saat mencoba instalasi.

Pengujian keamanan yang telah dilakukan
Pengujian manual pada lingkungan lokal dilaporkan lulus untuk skenario berikut:
- Pembatasan akses halaman admin.
- Proteksi CSRF.
- Pengujian input SQL injection.
- Pengujian XSS pada input yang diuji.
- Pembatasan percobaan login.
- Validasi NIM dan pencegahan NIM duplikat.
Hasil tersebut berlaku untuk skenario yang diuji, bukan jaminan bahwa semua kemungkinan kerentanan telah ditemukan atau ditutup.
Catatan keamanan
- Jangan unggah password, file konfigurasi rahasia, data mahasiswa asli, atau dump database berisi data pengguna.
- Gunakan password hash dari password_hash() dan verifikasi dengan password_verify().
- Pastikan semua halaman yang membutuhkan autentikasi melakukan pemeriksaan akses di sisi server.
- Gunakan HTTPS saat aplikasi ditempatkan di server yang dapat diakses melalui jaringan.
- Buat cadangan database sebelum melakukan perubahan struktur atau data.
- Untuk penggunaan produksi, lakukan audit kode dan pengujian keamanan tambahan.
Tujuan pembelajaran
Proyek ini dapat digunakan sebagai portofolio untuk menunjukkan pemahaman dasar PHP OOP, CRUD, MySQLi, validasi input, autentikasi, otorisasi, session, CSRF, prepared statements, output escaping, serta pencatatan aktivitas.