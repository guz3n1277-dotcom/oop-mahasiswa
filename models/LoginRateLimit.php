
<?php

class LoginRateLimit
{
    private mysqli $koneksi;

    private int $maksPercobaan = 5;
    private int $periodeDetik = 900;
    private int $blokirDetik = 900;

    public function __construct(mysqli $koneksi)
    {
        $this->koneksi = $koneksi;
    }

    private function ambilData(
        string $username,
        string $ip
    ): ?array {
        $sql = "SELECT attempts, first_attempt_at, locked_until
                FROM login_attempts
                WHERE username = ? AND ip_address = ?
                LIMIT 1";

        $stmt = mysqli_prepare($this->koneksi, $sql);

        if (!$stmt) {
            throw new RuntimeException(
                'Gagal memeriksa pembatasan login.'
            );
        }

        mysqli_stmt_bind_param($stmt, 'ss', $username, $ip);
        mysqli_stmt_execute($stmt);

        $hasil = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($hasil) ?: null;

        mysqli_stmt_close($stmt);

        return $data;
    }

    public function diblokir(
        string $username,
        string $ip
    ): bool {
        $data = $this->ambilData($username, $ip);

        if (!$data) {
            return false;
        }

        $sekarang = time();
        $batas = strtotime($data['locked_until'] ?? '');

        if ($batas !== false && $batas > $sekarang) {
            return true;
        }

        $awal = strtotime($data['first_attempt_at']);

        if (
            $awal === false ||
            $sekarang - $awal >= $this->periodeDetik
        ) {
            $this->reset($username, $ip);
        }

        return false;
    }

    public function gagal(
        string $username,
        string $ip
    ): void {
        $data = $this->ambilData($username, $ip);
        $sekarang = time();

        if (!$data) {
            $sql = "INSERT INTO login_attempts
                    (username, ip_address, attempts, first_attempt_at)
                    VALUES (?, ?, 1, NOW())";

            $stmt = mysqli_prepare($this->koneksi, $sql);

            if (!$stmt) {
                throw new RuntimeException(
                    'Gagal mencatat percobaan login.'
                );
            }

            mysqli_stmt_bind_param($stmt, 'ss', $username, $ip);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            return;
        }

        $awal = strtotime($data['first_attempt_at']);

        if (
            $awal === false ||
            $sekarang - $awal >= $this->periodeDetik
        ) {
            $attempts = 1;
            $lockedUntil = null;
            $sql = "UPDATE login_attempts
                    SET attempts = ?, first_attempt_at = NOW(),
                        locked_until = NULL
                    WHERE username = ? AND ip_address = ?";
        } else {
            $attempts = (int) $data['attempts'] + 1;

            $lockedUntil = $attempts >= $this->maksPercobaan
                ? date('Y-m-d H:i:s', $sekarang + $this->blokirDetik)
                : null;

            $sql = "UPDATE login_attempts
                    SET attempts = ?, locked_until = ?
                    WHERE username = ? AND ip_address = ?";
        }

        $stmt = mysqli_prepare($this->koneksi, $sql);

        if (!$stmt) {
            throw new RuntimeException(
                'Gagal memperbarui percobaan login.'
            );
        }

        if ($awal === false || $sekarang - $awal >= $this->periodeDetik) {
            mysqli_stmt_bind_param(
                $stmt,
                'iss',
                $attempts,
                $username,
                $ip
            );
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                'isss',
                $attempts,
                $lockedUntil,
                $username,
                $ip
            );
        }

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function reset(
        string $username,
        string $ip
    ): void {
        $sql = "DELETE FROM login_attempts
                WHERE username = ? AND ip_address = ?";

        $stmt = mysqli_prepare($this->koneksi, $sql);

        if (!$stmt) {
            throw new RuntimeException(
                'Gagal mereset percobaan login.'
            );
        }

        mysqli_stmt_bind_param($stmt, 'ss', $username, $ip);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
