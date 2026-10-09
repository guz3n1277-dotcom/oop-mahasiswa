<?php

class Database
{
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $database = "akademik";

    public function connect()
    {
        $koneksi = mysqli_connect(
            $this->host,
            $this->user,
            $this->password,
            $this->database
        );

        if (!$koneksi) {
            die("Koneksi database gagal");
        }

        return $koneksi;
    }
}