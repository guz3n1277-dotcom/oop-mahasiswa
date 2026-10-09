
<?php

include "config/Database.php";
include "models/Mahasiswa.php";

$database = new Database();
$koneksi = $database->connect();

$mahasiswa = new Mahasiswa($koneksi);

if (isset($_GET['id']) && is_numeric($_GET['id'])) {

    $id = (int) $_GET['id'];

    $berhasil = $mahasiswa->hapus($id);

    if ($berhasil) {
        header("Location: index.php");
        exit();
    } else {
        echo "Data gagal dihapus.";
    }

} else {
    echo "ID mahasiswa tidak valid.";
}