<?php
session_start();

include "config/Database.php";
include "models/Mahasiswa.php";
include "controllers/MahasiswaController.php";

$database = new Database();
$koneksi = $database->connect();

$mahasiswa = new Mahasiswa($koneksi);
$controller = new MahasiswaController($mahasiswa);

$controller->hapus();