
<?php
require_once __DIR__ . '/config/admin.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include "config/Database.php";
include "models/Mahasiswa.php";
include "controllers/MahasiswaController.php";

$database = new Database();
$koneksi = $database->connect();

$mahasiswa = new Mahasiswa($koneksi);
$controller = new MahasiswaController($mahasiswa);

$hasil = $controller->edit();

$pesan = $hasil['pesan'];
$dataMahasiswa = $hasil['dataMahasiswa'];

include "views/mahasiswa/edit.php";
