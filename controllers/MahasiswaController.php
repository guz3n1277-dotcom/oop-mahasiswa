<?php

class MahasiswaController
{
    private $mahasiswa;
    private $securityLog;

    public function __construct($mahasiswa, ?SecurityLog $securityLog = null)
    {
        $this->mahasiswa = $mahasiswa;
        $this->securityLog = $securityLog;
    }

    // Mencatat aktivitas pengguna yang sedang login.
    private function catatAktivitas(string $aktivitas): void
    {
        if ($this->securityLog === null) {
            error_log('SecurityLog belum diinisialisasi.');
            return;
        }

        $username = $_SESSION['username'] ?? '';

        if (!is_string($username) || $username === '') {
            error_log('Username sesi tidak tersedia untuk security log.');
            return;
        }

        try {
            $this->securityLog->catat($username, $aktivitas);
        } catch (Throwable $e) {
            error_log('Pencatatan aktivitas keamanan gagal.');
        }
    }

    // Menampilkan data, pencarian, dan pagination
    public function index()
    {
        $kataKunci = $_GET['cari'] ?? '';

        if (!is_string($kataKunci)) {
            $kataKunci = '';
        }

        $kataKunci = trim($kataKunci);
        $kataKunci = mb_substr($kataKunci, 0, 100);

        $perHalaman = 5;
        $halaman = max(1, (int) ($_GET['halaman'] ?? 1));

        $totalData = $this->mahasiswa->hitungSemua($kataKunci);

        if ($totalData === false) {
            error_log('Gagal menghitung jumlah data mahasiswa.');
            http_response_code(500);
            exit('Gagal memuat data mahasiswa. Silakan coba lagi.');
        }

        $totalHalaman = (int) ceil($totalData / $perHalaman);

        if ($totalHalaman > 0 && $halaman > $totalHalaman) {
            $halaman = $totalHalaman;
        }

        $offset = ($halaman - 1) * $perHalaman;

        $data = $this->mahasiswa->tampilkanHalaman(
            $kataKunci,
            $perHalaman,
            $offset
        );

        if ($data === false) {
            error_log('Gagal mengambil data halaman mahasiswa.');
            http_response_code(500);
            exit('Gagal memuat data mahasiswa. Silakan coba lagi.');
        }

        return [
            'kataKunci'   => $kataKunci,
            'halaman'     => $halaman,
            'totalData'   => $totalData,
            'totalHalaman'=> $totalHalaman,
            'data'        => $data
        ];
    }

    // Menambah data mahasiswa

    public function tambah()
    {
        $pesan = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Validasi token CSRF
            if (
                !isset($_SESSION['csrf_token'], $_POST['csrf_token']) ||
                !is_string($_POST['csrf_token']) ||
                !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
            ) {
                http_response_code(403);
                exit('Permintaan tidak valid. Silakan muat ulang halaman.');
            }

            // Ambil input
            $nim      = $_POST['nim'] ?? '';
            $nama     = $_POST['nama'] ?? '';
            $alamat   = $_POST['alamat'] ?? '';
            $fakultas = $_POST['fakultas'] ?? '';

            // Pastikan seluruh input berupa string
            if (
                !is_string($nim) ||
                !is_string($nama) ||
                !is_string($alamat) ||
                !is_string($fakultas)
            ) {
                http_response_code(400);
                exit('Format input tidak valid.');
            }

            // Bersihkan spasi di awal dan akhir
            $nim      = trim($nim);
            $nama     = trim($nama);
            $alamat   = trim($alamat);
            $fakultas = trim($fakultas);

            // Validasi kolom kosong
            if (
                $nim === '' ||
                $nama === '' ||
                $alamat === '' ||
                $fakultas === ''
            ) {
                $pesan = 'Semua kolom harus diisi.';
            }

            // Validasi NIM harus tepat 7 digit
            elseif (!preg_match('/^[0-9]{7}$/', $nim)) {
                $pesan = 'NIM harus terdiri dari tepat 7 digit angka.';
            }

            // Validasi nama minimal 3 karakter
            elseif (mb_strlen($nama, 'UTF-8') < 3) {
                $pesan = 'Nama minimal 3 karakter.';
            }

            // Validasi alamat minimal 3 karakter
            elseif (mb_strlen($alamat, 'UTF-8') < 3) {
                $pesan = 'Alamat minimal 3 karakter.';
            }

            // Validasi panjang nama
            elseif (mb_strlen($nama, 'UTF-8') > 25) {
                $pesan = 'Nama maksimal 25 karakter.';
            }

            // Validasi panjang alamat
            elseif (mb_strlen($alamat, 'UTF-8') > 40) {
                $pesan = 'Alamat maksimal 40 karakter.';
            }

            // Validasi panjang fakultas
            elseif (mb_strlen($fakultas, 'UTF-8') > 100) {
                $pesan = 'Fakultas maksimal 100 karakter.';
            }

            // Validasi NIM duplikat
            elseif (($nimAda = $this->mahasiswa->nimAda($nim)) === null) {
                $pesan = 'Pemeriksaan NIM gagal. Silakan coba lagi.';
            }
            elseif ($nimAda) {
                $pesan = 'NIM sudah digunakan mahasiswa lain.';
            }

            // Simpan data setelah seluruh validasi lolos         
            else {
                // Proses foto setelah seluruh validasi data lolos.
                $fotoMahasiswa = new FotoMahasiswa();

                $hasilFoto = $fotoMahasiswa->simpan(
                    $_FILES['foto'] ?? null
                );

                if ($hasilFoto['error'] !== null) {
                    $pesan = $hasilFoto['error'];
                } else {
                    $namaFoto = $hasilFoto['nama'];

                    $idBaru = $this->mahasiswa->tambah(
                        $nim,
                        $nama,
                        $alamat,
                        $fakultas,
                        $namaFoto
                    );

                    if ($idBaru !== false) {
                        $this->catatAktivitas(
                            'MAHASISWA_TAMBAH ID=' . $idBaru
                        );

                        header('Location: index.php');
                        exit();
                    }

                    // Hapus foto jika penyimpanan data gagal.
                    $fotoMahasiswa->hapus($namaFoto);

                    $this->catatAktivitas('MAHASISWA_TAMBAH_GAGAL');

                    $pesan = 'Data gagal ditambahkan.';
                }
            }

        }

        return [
            'pesan' => $pesan
        ];
    }


    // Mengubah data mahasiswa
    public function edit()
    {
        $pesan = '';

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            header('Location: index.php');
            exit();
        }

        $dataMahasiswa = $this->mahasiswa->ambilById($id);

        if (!$dataMahasiswa) {
            header('Location: index.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (
            !isset($_SESSION['csrf_token'], $_POST['csrf_token']) ||
            !is_string($_POST['csrf_token']) ||
            !hash_equals(
                $_SESSION['csrf_token'],
                $_POST['csrf_token']
            )
        ) {
            http_response_code(403);
            exit('Permintaan tidak valid. Silakan muat ulang halaman.');
        }

            $nim      = $_POST['nim'] ?? '';
            $nama     = $_POST['nama'] ?? '';
            $alamat   = $_POST['alamat'] ?? '';
            $fakultas = $_POST['fakultas'] ?? '';

            if (
                !is_string($nim) ||
                !is_string($nama) ||
                !is_string($alamat) ||
                !is_string($fakultas)
            ) {
                http_response_code(400);
                exit('Format input tidak valid.');
            }

            $nim      = trim($nim);
            $nama     = trim($nama);
            $alamat   = trim($alamat);
            $fakultas = trim($fakultas);

            // Validasi kolom kosong
            if (
                $nim === '' ||
                $nama === '' ||
                $alamat === '' ||
                $fakultas === ''
            ) {
                $pesan = 'Semua kolom harus diisi.';
            }

            // Validasi NIM harus tepat 7 digit
            elseif (!preg_match('/^[0-9]{7}$/', $nim)) {
                $pesan = 'NIM harus terdiri dari tepat 7 digit angka.';
            }

            // Validasi nama minimal 3 karakter
            elseif (mb_strlen($nama) < 3) {
                $pesan = 'Nama minimal 3 karakter.';
            }

            // Validasi alamat minimal 3 karakter
            elseif (mb_strlen($alamat) < 3) {
                $pesan = 'Alamat minimal 3 karakter.';
            }

            elseif (mb_strlen($nama, 'UTF-8') > 25) {
                $pesan = 'Nama maksimal 25 karakter.';
            }

            elseif (mb_strlen($alamat, 'UTF-8') > 40) {
                $pesan = 'Alamat maksimal 40 karakter.';
            }

            elseif (mb_strlen($fakultas, 'UTF-8') > 100) {
                $pesan = 'Fakultas maksimal 100 karakter.';
            }


            // NIM boleh sama dengan milik data yang sedang diedit,
            // tetapi tidak boleh sama dengan milik mahasiswa lain.
            elseif (($nimAda = $this->mahasiswa->nimAda($nim, $id)) === null) {
                $pesan = 'Pemeriksaan NIM gagal. Silakan coba lagi.';
            }
            elseif ($nimAda) {
                $pesan = 'NIM sudah digunakan mahasiswa lain.';
            }

            // Simpan perubahan
            else {
                $fotoMahasiswa = new FotoMahasiswa();

                // Simpan foto baru jika pengguna memilihnya.
                $hasilFoto = $fotoMahasiswa->simpan(
                    $_FILES['foto'] ?? null
                );

                if ($hasilFoto['error'] !== null) {
                    $pesan = $hasilFoto['error'];
                } else {
                    $fotoBaru = $hasilFoto['nama'];
                    $fotoLama = $dataMahasiswa['foto'] ?? null;

                    // Jika tidak ada foto baru, parameter null akan
                    // mempertahankan foto lama di database.
                    $berhasil = $this->mahasiswa->ubah(
                        $id,
                        $nim,
                        $nama,
                        $alamat,
                        $fakultas,
                        $fotoBaru
                    );

                    if ($berhasil) {
                        // Hapus foto lama hanya setelah database berhasil
                        // diperbarui dan jika memang ada foto pengganti.
                        if (
                            $fotoBaru !== null &&
                            $fotoLama !== null &&
                            $fotoLama !== ''
                        ) {
                            $fotoMahasiswa->hapus($fotoLama);
                        }

                        $this->catatAktivitas(
                            'MAHASISWA_EDIT ID=' . $id
                        );

                        header('Location: index.php');
                        exit();
                    }

                    // Database gagal diperbarui: bersihkan foto baru.
                    if ($fotoBaru !== null) {
                        $fotoMahasiswa->hapus($fotoBaru);
                    }

                    $this->catatAktivitas(
                        'MAHASISWA_EDIT_GAGAL ID=' . $id
                    );

                    $pesan = 'Data gagal diperbarui.';
                }
            }


            // Pertahankan input ketika validasi gagal
            $dataMahasiswa = array_merge($dataMahasiswa, [
                'nim'      => $nim,
                'nama'     => $nama,
                'alamat'   => $alamat,
                'fakultas' => $fakultas
            ]);
        }

        return [
            'pesan'         => $pesan,
            'dataMahasiswa' => $dataMahasiswa
        ];
    }

    // Menghapus data mahasiswa dengan metode POST dan token CSRF

    public function hapus()
    {
        // Pastikan request menggunakan POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit();
        }

        // Validasi token CSRF
        if (
            !isset($_SESSION['csrf_token'], $_POST['csrf_token']) ||
            !is_string($_POST['csrf_token']) ||
            !hash_equals(
                $_SESSION['csrf_token'],
                $_POST['csrf_token']
            )
        ) {
            http_response_code(403);
            exit('Permintaan tidak valid. Silakan muat ulang halaman.');
        }

        // Validasi ID
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            header('Location: index.php');
            exit();
        }

        // Pastikan data tersedia
        $dataMahasiswa = $this->mahasiswa->ambilById($id);

        if (!$dataMahasiswa) {
            header('Location: index.php');
            exit();
        }

        // Simpan nama foto sebelum data database dihapus
        $fotoLama = $dataMahasiswa['foto'] ?? null;

        // Hapus data mahasiswa dari database
        if ($this->mahasiswa->hapus($id)) {

            // Hapus foto hanya jika data berhasil dihapus
            require_once __DIR__ . '/../helpers/FotoMahasiswa.php';

            $fotoMahasiswa = new FotoMahasiswa();
            $fotoMahasiswa->hapus($fotoLama);

            $this->catatAktivitas('MAHASISWA_HAPUS ID=' . $id);

            header('Location: index.php');
            exit();
        }

        // Catat jika penghapusan database gagal
        $this->catatAktivitas('MAHASISWA_HAPUS_GAGAL ID=' . $id);

        http_response_code(500);
        exit('Data mahasiswa gagal dihapus.');
    }

}