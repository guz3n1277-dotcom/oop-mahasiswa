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
        $kataKunci = trim($_GET['cari'] ?? '');

        $perHalaman = 5;
        $halaman = max(1, (int) ($_GET['halaman'] ?? 1));

        $totalData = $this->mahasiswa->hitungSemua($kataKunci);
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
            elseif (mb_strlen($nama) < 3) {
                $pesan = 'Nama minimal 3 karakter.';
            }

            // Validasi alamat minimal 3 karakter
            elseif (mb_strlen($alamat) < 3) {
                $pesan = 'Alamat minimal 3 karakter.';
            }

            // Validasi NIM duplikat
            elseif ($this->mahasiswa->nimAda($nim)) {
                $pesan = 'NIM sudah digunakan mahasiswa lain.';
            }

            
            // Simpan data
            else {
                $idBaru = $this->mahasiswa->tambah(
                    $nim,
                    $nama,
                    $alamat,
                    $fakultas
                );

                if ($idBaru !== false) {
                    $this->catatAktivitas(
                        'MAHASISWA_TAMBAH ID=' . $idBaru
                    );

                    header('Location: index.php');
                    exit();
                }

                $this->catatAktivitas('MAHASISWA_TAMBAH_GAGAL');

                $pesan = 'Data gagal ditambahkan.';
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

            // NIM boleh sama dengan milik data yang sedang diedit,
            // tetapi tidak boleh sama dengan milik mahasiswa lain.
            elseif ($this->mahasiswa->nimAda($nim, $id)) {
                $pesan = 'NIM sudah digunakan mahasiswa lain.';
            }

            // Simpan perubahan
            else {
                $berhasil = $this->mahasiswa->ubah(
                    $id,
                    $nim,
                    $nama,
                    $alamat,
                    $fakultas
                );

                if ($berhasil) {
                    $this->catatAktivitas('MAHASISWA_EDIT ID=' . $id);

                    header('Location: index.php');
                    exit();
                }

                $this->catatAktivitas('MAHASISWA_EDIT_GAGAL ID=' . $id);

                $pesan = 'Data gagal diperbarui.';
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

        
        // Hapus data
        if ($this->mahasiswa->hapus($id)) {
            $this->catatAktivitas('MAHASISWA_HAPUS ID=' . $id);

            header('Location: index.php');
            exit();
        }

        $this->catatAktivitas('MAHASISWA_HAPUS_GAGAL ID=' . $id);

        http_response_code(500);
        exit('Data mahasiswa gagal dihapus.');
    }
}