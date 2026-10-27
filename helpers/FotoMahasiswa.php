
<?php

class FotoMahasiswa
{
    private const MAKS_UKURAN = 2 * 1024 * 1024;

    public function simpan(?array $file): array
    {
        // Foto bersifat opsional.
        if ($file === null || !isset($file['error'])) {
            return [
                'nama' => null,
                'error' => null
            ];
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return [
                'nama' => null,
                'error' => null
            ];
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
            !is_int($file['size']) && !is_numeric($file['size'])
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
                'error' => 'Ukuran foto harus lebih dari 0 dan maksimal 2 MB.'
            ];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return [
                'nama' => null,
                'error' => 'File unggahan tidak valid.'
            ];
        }

        // Periksa tipe berdasarkan isi file, bukan nama file.
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

        // Pastikan file dapat dikenali sebagai gambar.
        $infoGambar = @getimagesize($file['tmp_name']);

        if (
            $infoGambar === false ||
            ($infoGambar['mime'] ?? '') !== $mime
        ) {
            return [
                'nama' => null,
                'error' => 'Isi file bukan gambar yang valid.'
            ];
        }

        $folder = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';

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

        // Nama acak agar nama asli dan ekstensi kiriman pengguna
        // tidak digunakan sebagai nama file penyimpanan.
        try {
            $nama = bin2hex(random_bytes(16))
                . '.'
                . $tipeValid[$mime];
        } catch (Throwable $e) {
            error_log('Gagal membuat nama file foto.');

            return [
                'nama' => null,
                'error' => 'Foto gagal diproses. Silakan coba lagi.'
            ];
        }

        $tujuan = $folder . DIRECTORY_SEPARATOR . $nama;

        if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
            error_log('Gagal memindahkan file foto ke folder unggahan.');

            return [
                'nama' => null,
                'error' => 'Foto gagal disimpan. Silakan coba lagi.'
            ];
        }

        return [
            'nama' => $nama,
            'error' => null
        ];
    }


    public function hapus(?string $nama): void
    {
        // Pastikan nama file sesuai format yang dibuat aplikasi.
        if (
            $nama === null ||
            !preg_match('/\A[a-f0-9]{32}\.(jpg|png)\z/', $nama)
        ) {
            return;
        }

        $folder = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
        $path = $folder . DIRECTORY_SEPARATOR . $nama;

        // Hapus hanya file foto yang dituju, bukan symbolic link.
        if (is_file($path) && !is_link($path)) {
            if (!unlink($path)) {
                error_log('Gagal menghapus file foto mahasiswa: ' . $nama);
            }
        }
    }

}
