
<?php

require_once __DIR__ . '/config/admin.php';
require_once __DIR__ . '/config/Database.php';

$database = new Database();
$koneksi = $database->connect();

$username = $_GET['username'] ?? '';
$activity = $_GET['activity'] ?? '';

if (!is_string($username)) {
    $username = '';
}
if (!is_string($activity)) {
    $activity = '';
}

$username = trim($username);

$aktivitasValid = [
    '',
    'login_berhasil',
    'login_gagal',
    'logout',
    'MAHASISWA_TAMBAH',
    'MAHASISWA_EDIT',
    'MAHASISWA_HAPUS',
    'MAHASISWA_TAMBAH_GAGAL',
    'MAHASISWA_EDIT_GAGAL',
    'MAHASISWA_HAPUS_GAGAL'
];

if (!in_array($activity, $aktivitasValid, true)) {
    $activity = '';
}

$sql = "SELECT username, activity, ip_address, created_at
        FROM security_logs
        WHERE username LIKE ?
        AND activity LIKE ?
        ORDER BY id DESC
        LIMIT 100";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    mysqli_close($koneksi);
    exit('Gagal menyiapkan query log.');
}

$cariUsername = '%' . $username . '%';

if ($activity === '') {
    $cariAktivitas = '%';
} elseif (strpos($activity, 'MAHASISWA_') === 0) {
    $cariAktivitas = $activity . '%';
} else {
    $cariAktivitas = $activity;
}

mysqli_stmt_bind_param(
    $stmt,
    'ss',
    $cariUsername,
    $cariAktivitas
);

mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

function aman($nilai): string
{
    return htmlspecialchars(
        (string) ($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Keamanan</title>
</head>
<body>
    <h1>Riwayat Aktivitas Keamanan</h1>

    <form method="GET" action="logs.php">
        <label for="username">Cari username:</label>
        <input
            type="text"
            id="username"
            name="username"
            maxlength="50"
            value="<?= aman($username) ?>"
        >

        <label for="activity">Jenis aktivitas:</label>
        <select id="activity" name="activity">
            <option value="" <?= $activity === '' ? 'selected' : '' ?>>
                Semua aktivitas
            </option>
            <option value="login_berhasil" <?= $activity === 'login_berhasil' ? 'selected' : '' ?>>
                Login berhasil
            </option>
            <option value="login_gagal" <?= $activity === 'login_gagal' ? 'selected' : '' ?>>
                Login gagal
            </option>
            <option value="logout" <?= $activity === 'logout' ? 'selected' : '' ?>>
                Logout
            </option>
        </select>

        <button type="submit">Cari</button>
        <a href="logs.php">Reset</a>
    </form>

    <p>Menampilkan maksimal 100 catatan terbaru.</p>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Username</th>
                <th>Aktivitas</th>
                <th>Alamat IP</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($hasil) === 0) { ?>
                <tr>
                    <td colspan="4">Tidak ada data yang cocok.</td>
                </tr>
            <?php } ?>

            <?php while ($log = mysqli_fetch_assoc($hasil)) { ?>
                <tr>
                    <td><?= aman($log['username']) ?></td>
                    <td><?= aman($log['activity']) ?></td>
                    <td><?= aman($log['ip_address'] ?? '-') ?></td>
                    <td><?= aman($log['created_at']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <p><a href="index.php">Kembali ke halaman utama</a></p>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
mysqli_close($koneksi);
?>
