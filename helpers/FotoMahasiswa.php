
<?php

class FotoMahasiswa
{
    private const MAKS_UKURAN = 2 * 1024 * 1024;
    private const MAKS_DIMENSI = 6000;
    private const MAKS_PIXEL = 10000000;

    public function simpan(?array $file): array
    {
        if ($file === null || !isset($file['error'])) {
            return ['nama' => null, 'error' => null];
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['nama' => null, 'error' => null];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'nama' => null,
                'error' => 'Unggah foto gagal. Silakan coba lagi.'
            ];
        }

        if (
            !isset($file['tmp_name'], $file['size']) ||
            !is_string($file['tmp_name']) ||
            !is_numeric($file['size'])
        ) {
            return [
                'nama' => null,
                'error' => 'Data foto tidak valid.'
            ];
        }

        $ukuran = (int) $file['size'];

        if ($ukuran <= 0 || $ukuran > self::MAKS_UKURAN) {
            return [
                'nama' => null,
                'error' => 'Ukuran foto maksimal 2 MB.'
            ];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return [
                'nama' => null,
                'error' => 'File unggahan tidak valid.'
            ];
        }

        if (!extension_loaded('gd')) {
            error_log('Ekstensi GD tidak aktif.');

            return [
                'nama' => null,
                'error' => 'Pemrosesan foto tidak tersedia.'
            ];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $tipeValid = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ];

        if (!isset($tipeValid[$mime])) {
            return [
                'nama' => null,
                'error' => 'Foto harus berformat JPG, JPEG, atau PNG.'
            ];
        }

        $info = @getimagesize($file['tmp_name']);

        if (
            $info === false ||
            ($info['mime'] ?? '') !== $mime ||
            !isset($info[0], $info[1])
        ) {
            return [
                'nama' => null,
                'error' => 'Isi file bukan gambar yang valid.'
            ];
        }

        $lebar = (int) $info[0];
        $tinggi = (int) $info[1];

        if (
            $lebar <= 0 ||
            $tinggi <= 0 ||
            $lebar > self::MAKS_DIMENSI ||
            $tinggi > self::MAKS_DIMENSI ||
            $lebar > intdiv(self::MAKS_PIXEL, $tinggi)
        ) {
            return [
                'nama' => null,
                'error' => 'Dimensi foto terlalu besar.'
            ];
        }

        $folder = dirname(__DIR__)
            . DIRECTORY_SEPARATOR . 'uploads';

        if (
            !is_dir($folder) &&
            !mkdir($folder, 0750, true) &&
            !is_dir($folder)
        ) {
            error_log('Folder unggahan foto tidak dapat dibuat.');

            return [
                'nama' => null,
                'error' => 'Penyimpanan foto sedang mengalami gangguan.'
            ];
        }

        $gambar = false;
        $hasil = false;
        $nama = null;
        $tujuan = null;

        try {
            if ($mime === 'image/jpeg') {
                $gambar = @imagecreatefromjpeg($file['tmp_name']);
            } elseif ($mime === 'image/png') {
                $gambar = @imagecreatefrompng($file['tmp_name']);
            }

            if ($gambar === false) {
                return [
                    'nama' => null,
                    'error' => 'Foto tidak dapat diproses.'
                ];
            }

            // Buat nama acak; nama asli tidak digunakan.
            $nama = bin2hex(random_bytes(16))
                . '.' . $tipeValid[$mime];

            $tujuan = $folder . DIRECTORY_SEPARATOR . $nama;

            // Simpan ulang gambar, bukan salin file asli.
            if ($mime === 'image/jpeg') {
                $hasil = @imagejpeg($gambar, $tujuan, 90);
            } else {
                // Pertahankan transparansi PNG.
                imagealphablending($gambar, false);
                imagesavealpha($gambar, true);
                $hasil = @imagepng($gambar, $tujuan, 6);
            }

            if (!$hasil || !is_file($tujuan) || filesize($tujuan) === 0) {
                if (is_file($tujuan) && !is_link($tujuan)) {
                    @unlink($tujuan);
                }

                error_log('Gagal menyimpan hasil pemrosesan foto.');

                return [
                    'nama' => null,
                    'error' => 'Foto gagal disimpan. Silakan coba lagi.'
                ];
            }

            return ['nama' => $nama, 'error' => null];
        } catch (Throwable $e) {
            if (
                $tujuan !== null &&
                is_file($tujuan) &&
                !is_link($tujuan)
            ) {
                @unlink($tujuan);
            }

            error_log('Terjadi kesalahan saat memproses foto.');

            return [
                'nama' => null,
                'error' => 'Foto gagal diproses. Silakan coba lagi.'
            ];
        } finally {
            if ($gambar !== false) {
                imagedestroy($gambar);
            }
        }
    }

    public function hapus(?string $nama): void
    {
        if (
            $nama === null ||
            !preg_match('/\A[a-f0-9]{32}\.(jpg|png)\z/', $nama)
        ) {
            return;
        }

        $folder = dirname(__DIR__)
            . DIRECTORY_SEPARATOR . 'uploads';

        $path = $folder . DIRECTORY_SEPARATOR . $nama;

        if (is_file($path) && !is_link($path)) {
            if (!unlink($path)) {
                error_log('Gagal menghapus foto mahasiswa: ' . $nama);
            }
        }
    }
}
