
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
            throw new RuntimeException('Gagal memeriksa pembatasan login.');
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
        $awal = strtotime($data['first_attempt_at']);
        $batas = $data['locked_until'] !== null
            ? strtotime($data['locked_until'])
            : false;

        if ($batas !== false && $batas > $sekarang) {
            return true;
        }

        if ($awal === false || ($sekarang - $awal) >= $this->periodeDetik) {
            $this->reset($username, $ip);
        }

        return false;
    }

    public function gagal(
        string $username,
        string $ip
    ): void {
        mysqli_begin_transaction($this->koneksi);

        try {
            // Pastikan baris tersedia. Indeks unik menangani
            // kemungkinan baris dibuat oleh permintaan lain.
            $sql = "INSERT INTO login_attempts
                    (username, ip_address, attempts, first_attempt_at)
                    VALUES (?, ?, 0, NOW())
                    ON DUPLICATE KEY UPDATE id = id";

            $stmt = mysqli_prepare($this->koneksi, $sql);

            if (!$stmt) {
                throw new RuntimeException('Gagal menyiapkan data login.');
            }

            mysqli_stmt_bind_param($stmt, 'ss', $username, $ip);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Kunci baris selama proses baca dan pembaruan.
            $sql = "SELECT attempts, first_attempt_at
                    FROM login_attempts
                    WHERE username = ? AND ip_address = ?
                    FOR UPDATE";

            $stmt = mysqli_prepare($this->koneksi, $sql);

            if (!$stmt) {
                throw new RuntimeException('Gagal membaca data login.');
            }

            mysqli_stmt_bind_param($stmt, 'ss', $username, $ip);
            mysqli_stmt_execute($stmt);

            $hasil = mysqli_stmt_get_result($stmt);
            $data = mysqli_fetch_assoc($hasil);

            mysqli_stmt_close($stmt);

            if (!$data) {
                throw new RuntimeException('Data percobaan login tidak ditemukan.');
            }

            $sekarang = time();
            $awal = strtotime($data['first_attempt_at']);

            if ($awal === false || ($sekarang - $awal) >= $this->periodeDetik) {
                $attempts = 1;
                $firstAttempt = date('Y-m-d H:i:s', $sekarang);
                $lockedUntil = null;
            } else {
                $attempts = (int) $data['attempts'] + 1;
                $firstAttempt = $data['first_attempt_at'];

                $lockedUntil = $attempts >= $this->maksPercobaan
                    ? date('Y-m-d H:i:s', $sekarang + $this->blokirDetik)
                    : null;
            }

            $sql = "UPDATE login_attempts
                    SET attempts = ?, first_attempt_at = ?, locked_until = ?
                    WHERE username = ? AND ip_address = ?";

            $stmt = mysqli_prepare($this->koneksi, $sql);

            if (!$stmt) {
                throw new RuntimeException('Gagal memperbarui data login.');
            }

            mysqli_stmt_bind_param(
                $stmt,
                'issss',
                $attempts,
                $firstAttempt,
                $lockedUntil,
                $username,
                $ip
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($this->koneksi);
        } catch (Throwable $e) {
            mysqli_rollback($this->koneksi);
            throw $e;
        }
    }

    public function reset(
        string $username,
        string $ip
    ): void {
        $sql = "DELETE FROM login_attempts
                WHERE username = ? AND ip_address = ?";

        $stmt = mysqli_prepare($this->koneksi, $sql);

        if (!$stmt) {
            throw new RuntimeException('Gagal mereset percobaan login.');
        }

        mysqli_stmt_bind_param($stmt, 'ss', $username, $ip);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
