
<?php

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/admin.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Mahasiswa.php';
require_once __DIR__ . '/models/SecurityLog.php';
require_once __DIR__ . '/controllers/MahasiswaController.php';
require_once __DIR__ . '/helpers/FotoMahasiswa.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$database = new Database();
$koneksi = $database->connect();

$mahasiswa = new Mahasiswa($koneksi);
$securityLog = new SecurityLog($koneksi);

$controller = new MahasiswaController(
    $mahasiswa,
    $securityLog
);
$hasil = $controller->tambah();
$pesan = $hasil['pesan'];

include "views/mahasiswa/tambah.php";
