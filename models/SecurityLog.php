
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

        $berhasil = mysqli_stmt_execute($stmt);

        if (!$berhasil) {
            error_log('Gagal menyimpan security log.');
        }

        mysqli_stmt_close($stmt);

        return $berhasil;
    }
}
