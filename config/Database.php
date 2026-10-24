<?php

class Database
{
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $database = "akademik";

    public function connect()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $koneksi = mysqli_connect(
                $this->host,
                $this->user,
                $this->password,
                $this->database
            );

            mysqli_set_charset($koneksi, 'utf8mb4');

            return $koneksi;
        } catch (mysqli_sql_exception $e) {
            error_log('Koneksi database gagal: ' . $e->getMessage());

            http_response_code(500);
            exit('Layanan sedang mengalami gangguan. Silakan coba lagi.');
        }
    }
    
    public function connectOrNull(): ?mysqli
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $koneksi = mysqli_connect(
                $this->host,
                $this->user,
                $this->password,
                $this->database
            );

            mysqli_set_charset($koneksi, 'utf8mb4');

            return $koneksi;
        } catch (mysqli_sql_exception $e) {
            error_log('Koneksi database untuk logout gagal: ' . $e->getMessage());

            return null;
        }
    }

}