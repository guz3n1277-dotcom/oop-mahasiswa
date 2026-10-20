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
        $query = "SELECT * FROM mahasiswa";

        return mysqli_query($this->koneksi, $query);
    }

    public function tambah($nim, $nama, $alamat, $fakultas)
    {
        $query = "INSERT INTO mahasiswa (nim, nama, alamat, fakultas)
                VALUES (?, ?, ?, ?)";

        try {
            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                return false;
            }

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $nim,
                $nama,
                $alamat,
                $fakultas
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
            return false;
        }
    }

    public function ubah($id, $nim, $nama, $alamat, $fakultas)
    {
        $query = "UPDATE mahasiswa
                SET nim = ?, nama = ?, alamat = ?, fakultas = ?
                WHERE id = ?";

        try {
            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                error_log('Gagal menyiapkan query ubah mahasiswa.');
                return false;
            }

            mysqli_stmt_bind_param(
                $stmt,
                "ssssi",
                $nim,
                $nama,
                $alamat,
                $fakultas,
                $id
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
        try {
            if ($kecualiId !== null) {
                $query = "SELECT id FROM mahasiswa WHERE nim = ? AND id != ?";
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
                mysqli_stmt_close($stmt);
                return null;
            }

            $hasil = mysqli_stmt_get_result($stmt);

            if (!$hasil) {
                mysqli_stmt_close($stmt);
                return null;
            }

            $ada = mysqli_num_rows($hasil) > 0;
            mysqli_stmt_close($stmt);

            return $ada;
        } catch (mysqli_sql_exception $e) {
            error_log('Gagal memeriksa NIM mahasiswa.');
            return null;
        }
    }


public function cari($kataKunci)
{
    $query = "SELECT * FROM mahasiswa
              WHERE nim LIKE ? OR nama LIKE ?";

    try {
        $stmt = mysqli_prepare($this->koneksi, $query);

        if (!$stmt) {
            error_log('Gagal menyiapkan query pencarian mahasiswa.');
            return false;
        }

        $pola = "%" . $kataKunci . "%";

        mysqli_stmt_bind_param($stmt, "ss", $pola, $pola);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        $hasil = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        return $hasil;
    } catch (mysqli_sql_exception $e) {
        error_log('Gagal mencari mahasiswa.');
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
                      WHERE nim LIKE ? OR nama LIKE ?
                      LIMIT ? OFFSET ?";

            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                error_log('Gagal menyiapkan query halaman mahasiswa.');
                return false;
            }

            $pola = "%" . $kataKunci . "%";

            mysqli_stmt_bind_param(
                $stmt,
                "ssii",
                $pola,
                $pola,
                $limit,
                $offset
            );
        } else {
            $query = "SELECT * FROM mahasiswa
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
            mysqli_stmt_close($stmt);
            return false;
        }

        $hasil = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        return $hasil;
    } catch (mysqli_sql_exception $e) {
        error_log('Gagal menampilkan halaman mahasiswa.');
        return false;
    }
}

public function hitungSemua($kataKunci)
{
    try {
        if ($kataKunci !== '') {
            $query = "SELECT COUNT(*) AS total
                      FROM mahasiswa
                      WHERE nim LIKE ? OR nama LIKE ?";

            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                error_log('Gagal menyiapkan hitung mahasiswa.');
                return false;
            }

            $pola = "%" . $kataKunci . "%";
            mysqli_stmt_bind_param($stmt, "ss", $pola, $pola);
        } else {
            $query = "SELECT COUNT(*) AS total FROM mahasiswa";
            $stmt = mysqli_prepare($this->koneksi, $query);

            if (!$stmt) {
                error_log('Gagal menyiapkan hitung mahasiswa.');
                return false;
            }
        }

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        $hasil = mysqli_stmt_get_result($stmt);
        $baris = $hasil ? mysqli_fetch_assoc($hasil) : null;

        mysqli_stmt_close($stmt);

        return $baris ? (int) $baris['total'] : false;
    } catch (mysqli_sql_exception $e) {
        error_log('Gagal menghitung mahasiswa.');
        return false;
    }
}


}

