<?php

class MahasiswaController
{
    private $mahasiswa;

    public function __construct($mahasiswa)
    {
        $this->mahasiswa = $mahasiswa;
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

            if (
                !isset($_SESSION['csrf_token'], $_POST['csrf_token']) ||
                !is_string($_POST['csrf_token']) ||
                !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
            ) {
                http_response_code(403);
                exit('Permintaan tidak valid. Silakan muat ulang halaman.');
            }

            $nim      = trim($_POST['nim'] ?? '');
            $nama     = trim($_POST['nama'] ?? '');
            $alamat   = trim($_POST['alamat'] ?? '');
            $fakultas = trim($_POST['fakultas'] ?? '');

            // Validasi kolom kosong
            if (
                $nim === '' ||
                $nama === '' ||
                $alamat === '' ||
                $fakultas === ''
            ) {
                $pesan = 'Semua kolom harus diisi.';
            }

            // Validasi NIM hanya angka
            elseif (!ctype_digit($nim)) {
                $pesan = 'NIM harus berupa angka.';
            }

            // Validasi NIM duplikat
            elseif ($this->mahasiswa->nimAda($nim)) {
                $pesan = 'NIM sudah digunakan mahasiswa lain.';
            }

            // Simpan data
            else {
                $berhasil = $this->mahasiswa->tambah(
                    $nim,
                    $nama,
                    $alamat,
                    $fakultas
                );

                if ($berhasil) {
                    header('Location: index.php');
                    exit();
                }

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

            $nim      = trim($_POST['nim'] ?? '');
            $nama     = trim($_POST['nama'] ?? '');
            $alamat   = trim($_POST['alamat'] ?? '');
            $fakultas = trim($_POST['fakultas'] ?? '');

            // Validasi kolom kosong
            if (
                $nim === '' ||
                $nama === '' ||
                $alamat === '' ||
                $fakultas === ''
            ) {
                $pesan = 'Semua kolom harus diisi.';
            }

            // Validasi NIM hanya angka
            elseif (!ctype_digit($nim)) {
                $pesan = 'NIM harus berupa angka.';
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
                    header('Location: index.php');
                    exit();
                }

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
            header('Location: index.php');
            exit();
        }

        http_response_code(500);
        exit('Data mahasiswa gagal dihapus.');
    }
}