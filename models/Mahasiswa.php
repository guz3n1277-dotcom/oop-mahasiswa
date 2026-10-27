<?php

class Mahasiswa
{
    private $koneksi;

    public function __construct($koneksi)
    {
        $this->koneksi = $koneksi;
    }

    public function tampilkanSemua()
    {
        try {
            $query = "SELECT * FROM mahasiswa";
            return mysqli_query($this->koneksi, $query);
        } catch (mysqli_sql_exception $e) {
            error_log('Gagal menampilkan seluruh mahasiswa.');
            return false;
        }
    }

   
    public function tambah($nim, $nama, $alamat, $fakultas, $foto = null)
    {
        $query = "INSERT INTO mahasiswa
                (nim, nama, alamat, fakultas, foto)
                VALUES (?, ?, ?, ?, ?)";

        try {
            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                return false;
            }

            mysqli_stmt_bind_param(
                $stmt,
                "sssss",
                $nim,
                $nama,
                $alamat,
                $fakultas,
                $foto
            );

            $berhasil = mysqli_stmt_execute($stmt);

            if ($berhasil) {
                $idBaru = mysqli_insert_id($this->koneksi);
                mysqli_stmt_close($stmt);

                return $idBaru;
            }

            mysqli_stmt_close($stmt);

            return false;

        } catch (mysqli_sql_exception $e) {
            error_log('Gagal menambahkan data mahasiswa.');

            return false;
        }
    }

    public function ubah($id, $nim, $nama, $alamat, $fakultas, $foto = null)
    {
        // Jika foto baru tidak diberikan, jangan ubah kolom foto.
        if ($foto === null) {
            $query = "UPDATE mahasiswa
                    SET nim = ?, nama = ?, alamat = ?, fakultas = ?
                    WHERE id = ?";

            $parameter = "ssssi";
            $nilai = [$nim, $nama, $alamat, $fakultas, $id];
        } else {
            $query = "UPDATE mahasiswa
                    SET nim = ?, nama = ?, alamat = ?, fakultas = ?, foto = ?
                    WHERE id = ?";

            $parameter = "sssssi";
            $nilai = [$nim, $nama, $alamat, $fakultas, $foto, $id];
        }

        try {
            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                error_log('Gagal menyiapkan query ubah mahasiswa.');
                return false;
            }

            mysqli_stmt_bind_param(
                $stmt,
                $parameter,
                ...$nilai
            );

            $berhasil = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            return $berhasil;

        } catch (mysqli_sql_exception $e) {
            error_log('Gagal mengubah data mahasiswa.');
            return false;
        }
    }


    public function ambilById($id)
    {
        $query = "SELECT * FROM mahasiswa WHERE id = ?";

        try {
            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                error_log('Gagal menyiapkan query ambil mahasiswa.');
                return null;
            }

            mysqli_stmt_bind_param($stmt, "i", $id);

            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                return null;
            }

            $hasil = mysqli_stmt_get_result($stmt);
            $data = $hasil ? mysqli_fetch_assoc($hasil) : null;

            mysqli_stmt_close($stmt);

            return $data ?: null;
        } catch (mysqli_sql_exception $e) {
            error_log('Gagal mengambil data mahasiswa.');
            return null;
        }
    }

    public function hapus($id)
    {
        $query = "DELETE FROM mahasiswa WHERE id = ?";

        try {
            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                error_log('Gagal menyiapkan query hapus mahasiswa.');
                return false;
            }

            mysqli_stmt_bind_param($stmt, "i", $id);

            $berhasil = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            return $berhasil;
        } catch (mysqli_sql_exception $e) {
            error_log('Gagal menghapus data mahasiswa.');
            return false;
        }
    }



    public function nimAda($nim, $kecualiId = null)
    {
        $stmt = null;

        try {
            if ($kecualiId !== null) {
                $query = "SELECT id FROM mahasiswa
                        WHERE nim = ? AND id != ?";
                $stmt = mysqli_prepare($this->koneksi, $query);

                if (!$stmt) {
                    error_log('Gagal menyiapkan pemeriksaan NIM.');
                    return null;
                }

                mysqli_stmt_bind_param($stmt, "si", $nim, $kecualiId);
            } else {
                $query = "SELECT id FROM mahasiswa WHERE nim = ?";
                $stmt = mysqli_prepare($this->koneksi, $query);

                if (!$stmt) {
                    error_log('Gagal menyiapkan pemeriksaan NIM.');
                    return null;
                }

                mysqli_stmt_bind_param($stmt, "s", $nim);
            }

            if (!mysqli_stmt_execute($stmt)) {
                error_log('Gagal menjalankan pemeriksaan NIM.');
                return null;
            }

            $hasil = mysqli_stmt_get_result($stmt);

            if ($hasil === false) {
                error_log('Gagal mengambil hasil pemeriksaan NIM.');
                return null;
            }

            return mysqli_num_rows($hasil) > 0;

        } catch (Throwable $e) {
            error_log('Terjadi kesalahan saat memeriksa NIM.');
            return null;

        } finally {
            if ($stmt !== null) {
                mysqli_stmt_close($stmt);
            }
        }
    }



public function cari($kataKunci)
{
    $query = "SELECT * FROM mahasiswa
              WHERE nim LIKE ?
                 OR nama LIKE ?
                 OR fakultas LIKE ?
              ORDER BY id ASC";

    try {
        $stmt = mysqli_prepare($this->koneksi, $query);

        if (!$stmt) {
            error_log('Gagal menyiapkan query pencarian mahasiswa.');
            return false;
        }

        $pola = "%" . $kataKunci . "%";

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $pola,
            $pola,
            $pola
        );

        if (!mysqli_stmt_execute($stmt)) {
            error_log('Gagal menjalankan pencarian mahasiswa.');
            mysqli_stmt_close($stmt);
            return false;
        }

        $hasil = mysqli_stmt_get_result($stmt);

        if ($hasil === false) {
            error_log('Gagal mengambil hasil pencarian mahasiswa.');
            mysqli_stmt_close($stmt);
            return false;
        }

        mysqli_stmt_close($stmt);

        return $hasil;
    } catch (mysqli_sql_exception $e) {
        error_log('Terjadi kesalahan saat mencari mahasiswa.');
        return false;
    }
}

public function tampilkanHalaman($kataKunci, $limit, $offset)
{
    $limit = max(1, (int) $limit);
    $offset = max(0, (int) $offset);

    try {
        if ($kataKunci !== '') {
            $query = "SELECT * FROM mahasiswa
                      WHERE nim LIKE ?
                         OR nama LIKE ?
                         OR fakultas LIKE ?
                      ORDER BY id ASC
                      LIMIT ? OFFSET ?";

            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                error_log('Gagal menyiapkan query halaman mahasiswa.');
                return false;
            }

            $pola = "%" . $kataKunci . "%";

            mysqli_stmt_bind_param(
                $stmt,
                "sssii",
                $pola,
                $pola,
                $pola,
                $limit,
                $offset
            );
        } else {
            $query = "SELECT * FROM mahasiswa
                      ORDER BY id ASC
                      LIMIT ? OFFSET ?";

            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                error_log('Gagal menyiapkan query halaman mahasiswa.');
                return false;
            }

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $limit,
                $offset
            );
        }

        if (!mysqli_stmt_execute($stmt)) {
            error_log('Gagal menjalankan query halaman mahasiswa.');
            mysqli_stmt_close($stmt);
            return false;
        }

        $hasil = mysqli_stmt_get_result($stmt);

        if ($hasil === false) {
            error_log('Gagal mengambil data halaman mahasiswa.');
            mysqli_stmt_close($stmt);
            return false;
        }

        mysqli_stmt_close($stmt);

        return $hasil;
    } catch (mysqli_sql_exception $e) {
        error_log('Terjadi kesalahan saat mengambil halaman mahasiswa.');
        return false;
    }
}

public function hitungSemua($kataKunci)
{
    try {
        if ($kataKunci !== '') {
            $query = "SELECT COUNT(*) AS total
                      FROM mahasiswa
                      WHERE nim LIKE ?
                         OR nama LIKE ?
                         OR fakultas LIKE ?";

            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                error_log('Gagal menyiapkan query hitung mahasiswa.');
                return false;
            }

            $pola = "%" . $kataKunci . "%";

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $pola,
                $pola,
                $pola
            );
        } else {
            $query = "SELECT COUNT(*) AS total FROM mahasiswa";

            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                error_log('Gagal menyiapkan query hitung mahasiswa.');
                return false;
            }
        }

        if (!mysqli_stmt_execute($stmt)) {
            error_log('Gagal menghitung data mahasiswa.');
            mysqli_stmt_close($stmt);
            return false;
        }

        $hasil = mysqli_stmt_get_result($stmt);

        if ($hasil === false) {
            error_log('Gagal mengambil jumlah data mahasiswa.');
            mysqli_stmt_close($stmt);
            return false;
        }

        $baris = mysqli_fetch_assoc($hasil);

        mysqli_stmt_close($stmt);

        return $baris ? (int) $baris['total'] : false;
    } catch (mysqli_sql_exception $e) {
        error_log('Terjadi kesalahan saat menghitung mahasiswa.');
        return false;
    }
}


public function ambilSemuaUntukEkspor()
{
    $query = "SELECT nim, nama, alamat, fakultas
              FROM mahasiswa
              ORDER BY id ASC";

    try {
        $hasil = mysqli_query($this->koneksi, $query);
        return $hasil;
    } catch (mysqli_sql_exception $e) {
        error_log('Gagal mengambil data untuk ekspor CSV.');
        return false;
    }
}


}

