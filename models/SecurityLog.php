<?php

class SecurityLog
{
    private mysqli $koneksi;

    public function __construct(mysqli $koneksi)
    {
        $this->koneksi = $koneksi;
    }

    public function catat(
        string $username,
        string $activity
    ): bool {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = null;

        try {
            $sql = "INSERT INTO security_logs
                    (username, activity, ip_address)
                    VALUES (?, ?, ?)";

            $stmt = mysqli_prepare($this->koneksi, $sql);

            if (!$stmt) {
                error_log('Gagal menyiapkan security log.');
                return false;
            }

            mysqli_stmt_bind_param(
                $stmt,
                'sss',
                $username,
                $activity,
                $ipAddress
            );

            return mysqli_stmt_execute($stmt);

        } catch (mysqli_sql_exception $e) {
            error_log('Gagal menyimpan security log: ' . $e->getMessage());
            return false;

        } finally {
            if ($stmt instanceof mysqli_stmt) {
                mysqli_stmt_close($stmt);
            }
        }
    }
}