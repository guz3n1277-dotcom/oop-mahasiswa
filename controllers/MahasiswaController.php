
<?php

class MahasiswaController
{
    private $mahasiswa;

    public function __construct($mahasiswa)
    {
        $this->mahasiswa = $mahasiswa;
    }

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
            'kataKunci' => $kataKunci,
            'halaman' => $halaman,
            'totalData' => $totalData,
            'totalHalaman' => $totalHalaman,
            'data' => $data
        ];
    }

    public function tambah()
    {
        $pesan = '';

        if (isset($_POST['simpan'])) {
            $nim = trim($_POST['nim'] ?? '');
            $nama = trim($_POST['nama'] ?? '');
            $alamat = trim($_POST['alamat'] ?? '');
            $fakultas = trim($_POST['fakultas'] ?? '');

            if (
                $nim === '' ||
                $nama === '' ||
                $alamat === '' ||
                $fakultas === ''
            ) {
                $pesan = "Semua kolom harus diisi.";
            } elseif ($this->mahasiswa->nimAda($nim)) {
                $pesan = "NIM sudah digunakan mahasiswa lain.";
            } else {
                $berhasil = $this->mahasiswa->tambah(
                    $nim,
                    $nama,
                    $alamat,
                    $fakultas
                );

                if ($berhasil) {
                    header("Location: index.php");
                    exit();
                } else {
                    $pesan = "Data gagal ditambahkan.";
                }
            }
        }

        return [
            'pesan' => $pesan
        ];
    }

    public function edit()
    {
        $pesan = '';
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            header("Location: index.php");
            exit();
        }

        $dataMahasiswa = $this->mahasiswa->ambilById($id);

        if (!$dataMahasiswa) {
            header("Location: index.php");
            exit();
        }

        if (isset($_POST['ubah'])) {
            $nim = trim($_POST['nim'] ?? '');
            $nama = trim($_POST['nama'] ?? '');
            $alamat = trim($_POST['alamat'] ?? '');
            $fakultas = trim($_POST['fakultas'] ?? '');

            if (
                $nim === '' ||
                $nama === '' ||
                $alamat === '' ||
                $fakultas === ''
            ) {
                $pesan = "Semua kolom harus diisi.";
            } elseif ($this->mahasiswa->nimAda($nim, $id)) {
                $pesan = "NIM sudah digunakan mahasiswa lain.";
            } else {
                $berhasil = $this->mahasiswa->ubah(
                    $id,
                    $nim,
                    $nama,
                    $alamat,
                    $fakultas
                );

                if ($berhasil) {
                    header("Location: index.php");
                    exit();
                } else {
                    $pesan = "Data gagal diperbarui.";
                }
            }

            // Pertahankan nilai yang dimasukkan jika validasi gagal.
            $dataMahasiswa = array_merge($dataMahasiswa, [
                'nim' => $nim,
                'nama' => $nama,
                'alamat' => $alamat,
                'fakultas' => $fakultas
            ]);
        }

        return [
            'pesan' => $pesan,
            'dataMahasiswa' => $dataMahasiswa
        ];
    }


    public function hapus()
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php");
            exit();
        }
        
        if (
            !isset($_SESSION['csrf_token'], $_POST['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            http_response_code(403);
            exit('Permintaan tidak valid. Silakan muat ulang halaman.');
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            header("Location: index.php");
            exit();
        }

        $dataMahasiswa = $this->mahasiswa->ambilById($id);

        if (!$dataMahasiswa) {
            header("Location: index.php");
            exit();
        }

        if ($this->mahasiswa->hapus($id)) {
            header("Location: index.php");
            exit();
        }

        die("Data mahasiswa gagal dihapus.");
    }



}
