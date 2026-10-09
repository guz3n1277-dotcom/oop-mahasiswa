<?php

include "config/Database.php";
include "models/Mahasiswa.php";

$database = new Database();

$koneksi = $database->connect();

$mahasiswa = new Mahasiswa($koneksi);


if (isset($_POST['simpan'])) {

    $nim = trim($_POST['nim']);
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $fakultas = $_POST['fakultas'];

    if ($nim === '' || $nama === '' || $alamat === '') {
        $pesan = "Semua kolom harus diisi.";
    } elseif ($mahasiswa->nimAda($nim)) {
        $pesan = "NIM sudah digunakan mahasiswa lain.";
    } else {
        $berhasil = $mahasiswa->tambah(
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

?>

<!DOCTYPE html>
<html>
<head>

    <title>Tambah Mahasiswa</title>

</head>

<body>

<h1>Tambah Mahasiswa</h1>

<?php
if (isset($pesan)) {
    echo htmlspecialchars($pesan);
}
?>

<form method="POST">

    <label>NIM</label><br>
    <input type="text" name="nim">

    <br><br>

    <label>Nama</label><br>
    <input type="text" name="nama">

    <br><br>

    <label>Alamat</label><br>
    <input type="text" name="alamat">

    <br><br>

    <label>Fakultas</label><br>

    <select name="fakultas">

        <option value="Saintek">Saintek</option>
        <option value="Filkom">Filkom</option>
        <option value="Teknik">Teknik</option>

    </select>

    <br><br>

    <button type="submit" name="simpan">
        Simpan
    </button>

</form>

<br>

<a href="index.php">Kembali</a>

</body>
</html>