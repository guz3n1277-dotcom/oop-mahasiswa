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

            mysqli_stmt_bind_param(
                $stmt,
                "ssssi",
                $nim,
                $nama,
                $alamat,
                $fakultas,
                $id
            );

        return mysqli_stmt_execute($stmt);

        }catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function ambilById($id)
    {
        $query = "SELECT * FROM mahasiswa WHERE id = ?";

        $stmt = mysqli_prepare($this->koneksi, $query);

        mysqli_stmt_bind_param($stmt, "i", $id);

        mysqli_stmt_execute($stmt);

        $hasil = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($hasil);
    }    

    public function hapus($id)
    {
        $query = "DELETE FROM mahasiswa WHERE id = ?";

        $stmt = mysqli_prepare($this->koneksi, $query);

        mysqli_stmt_bind_param($stmt, "i", $id);

        return mysqli_stmt_execute($stmt);
    }  
    

    public function nimAda($nim, $kecualiId = null)
    {
        if ($kecualiId !== null) {
            $query = "SELECT id FROM mahasiswa WHERE nim = ? AND id != ?";
            $stmt = mysqli_prepare($this->koneksi, $query);

            mysqli_stmt_bind_param($stmt, "si", $nim, $kecualiId);
        } else {
            $query = "SELECT id FROM mahasiswa WHERE nim = ?";
            $stmt = mysqli_prepare($this->koneksi, $query);

            mysqli_stmt_bind_param($stmt, "s", $nim);
        }

        mysqli_stmt_execute($stmt);

        $hasil = mysqli_stmt_get_result($stmt);

        return mysqli_num_rows($hasil) > 0;
    }
    public function cari($kataKunci)
    {
        $query = "SELECT * FROM mahasiswa
                WHERE nim LIKE ? OR nama LIKE ?";

        $stmt = mysqli_prepare($this->koneksi, $query);

        $kataKunci = "%" . $kataKunci . "%";

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $kataKunci,
            $kataKunci
        );

        mysqli_stmt_execute($stmt);

        return mysqli_stmt_get_result($stmt);
    }

    public function tampilkanHalaman($kataKunci, $limit, $offset)
    {
        if ($kataKunci !== '') {
            $query = "SELECT * FROM mahasiswa
                    WHERE nim LIKE ? OR nama LIKE ?
                    LIMIT ? OFFSET ?";

            $stmt = mysqli_prepare($this->koneksi, $query);

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

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $limit,
                $offset
            );
        }

        mysqli_stmt_execute($stmt);

        return mysqli_stmt_get_result($stmt);
    }

    
    public function hitungSemua($kataKunci)
    {
        if ($kataKunci !== '') {
            $query = "SELECT COUNT(*) AS total
                    FROM mahasiswa
                    WHERE nim LIKE ? OR nama LIKE ?";

            $stmt = mysqli_prepare($this->koneksi, $query);

            $pola = "%" . $kataKunci . "%";

            mysqli_stmt_bind_param($stmt, "ss", $pola, $pola);
        } else {
            $query = "SELECT COUNT(*) AS total FROM mahasiswa";

            $stmt = mysqli_prepare($this->koneksi, $query);
        }

        mysqli_stmt_execute($stmt);

        $hasil = mysqli_stmt_get_result($stmt);
        $baris = mysqli_fetch_assoc($hasil);

        return (int) $baris['total'];
    }


}

