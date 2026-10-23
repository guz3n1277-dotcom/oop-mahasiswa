
<?php

require_once __DIR__ . '/config/admin.php';
require_once __DIR__ . '/config/Database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function aman($nilai): string
{
    return htmlspecialchars(
        (string) ($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

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
$username = mb_substr($username, 0, 50);

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

$stmt = null;
$hasil = false;

try {
    $stmt = mysqli_prepare($koneksi, $sql);

    if (!$stmt) {
        throw new RuntimeException('Gagal menyiapkan query log.');
    }

    $cariUsername = '%' . $username . '%';

    if ($activity === '') {
        $cariAktivitas = '%';
    } elseif (strpos($activity, 'MAHASISWA_') === 0) {
        // Aktivitas CRUD dapat menyertakan ID setelah nama aktivitas.
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

    if ($hasil === false) {
        throw new RuntimeException('Gagal mengambil hasil query log.');
    }
} catch (Throwable $e) {
    error_log('Gagal memuat log keamanan: ' . $e->getMessage());

    if ($stmt instanceof mysqli_stmt) {
        mysqli_stmt_close($stmt);
    }

    mysqli_close($koneksi);

    http_response_code(500);
    exit('Log keamanan gagal dimuat. Silakan coba lagi.');
}

$totalLog = mysqli_num_rows($hasil);
$usernameLogin = aman($_SESSION['username'] ?? 'Admin');
$roleLogin = $_SESSION['role'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Keamanan | Akademik</title>

    <style>
        :root {
            --navy: #172554;
            --navy-light: #243765;
            --blue: #2563eb;
            --blue-light: #eff6ff;
            --background: #f4f7fb;
            --white: #ffffff;
            --text: #1e293b;
            --muted: #64748b;
            --border: #e2e8f0;
            --danger: #dc2626;
            --success: #15803d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--background);
            color: var(--text);
        }

        a {
            text-decoration: none;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 245px;
            flex-shrink: 0;
            background: var(--navy);
            color: var(--white);
            padding: 26px 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 8px 28px;
            border-bottom: 1px solid rgba(255, 255, 255, .15);
        }

        .brand-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--blue);
            font-size: 20px;
            font-weight: bold;
        }

        .brand-name {
            font-size: 16px;
            font-weight: bold;
        }

        .brand-subtitle {
            margin-top: 5px;
            color: #cbd5e1;
            font-size: 12px;
        }

        .nav-title {
            margin: 30px 10px 12px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .nav-link {
            display: block;
            padding: 13px 14px;
            margin-bottom: 6px;
            border-radius: 9px;
            color: #dbeafe;
            font-size: 14px;
            transition: background .2s;
        }

        .nav-link:hover {
            background: var(--navy-light);
        }

        .nav-link.active {
            background: var(--blue);
            color: var(--white);
            font-weight: bold;
        }

        .main {
            min-width: 0;
            flex: 1;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 82px;
            padding: 18px 32px;
            background: var(--white);
            border-bottom: 1px solid var(--border);
        }

        .topbar-title {
            margin: 0;
            font-size: 20px;
        }

        .topbar-subtitle {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .user-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-size: 13px;
            font-weight: bold;
        }

        .user-role {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        .logout-button {
            padding: 10px 13px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--white);
            color: var(--text);
            cursor: pointer;
            font-size: 13px;
        }

        .logout-button:hover {
            background: #f1f5f9;
        }

        .content {
            max-width: 1500px;
            margin: 0 auto;
            padding: 30px 32px;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 24px;
        }

        .page-heading h2 {
            margin: 0;
            font-size: 25px;
        }

        .page-heading p {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .breadcrumb {
            margin-bottom: 12px;
            color: var(--muted);
            font-size: 12px;
        }

        .breadcrumb a {
            color: var(--blue);
        }

        .summary-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 22px;
            margin-bottom: 22px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .03);
        }

        .summary-icon {
            display: grid;
            place-items: center;
            width: 50px;
            height: 50px;
            flex-shrink: 0;
            border-radius: 12px;
            background: var(--blue-light);
            color: var(--blue);
            font-size: 23px;
        }

        .summary-label {
            color: var(--muted);
            font-size: 13px;
        }

        .summary-value {
            margin-top: 6px;
            font-size: 24px;
            font-weight: bold;
        }

        .summary-note {
            margin-left: auto;
            color: var(--muted);
            font-size: 12px;
            text-align: right;
        }

        .card {
            margin-bottom: 22px;
            padding: 24px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .03);
        }

        .card-title {
            margin: 0 0 20px;
            font-size: 17px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: minmax(180px, 1fr) minmax(200px, 1fr) auto;
            align-items: end;
            gap: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            min-height: 43px;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: var(--white);
            color: var(--text);
            font: inherit;
            font-size: 13px;
        }

        .form-control:focus {
            outline: 2px solid #bfdbfe;
            border-color: var(--blue);
        }

        .filter-actions {
            display: flex;
            gap: 8px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 17px;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            white-space: nowrap;
        }

        .button-primary {
            background: var(--blue);
            color: var(--white);
        }

        .button-primary:hover {
            background: #1d4ed8;
        }

        .button-secondary {
            background: #f1f5f9;
            color: var(--text);
        }

        .button-secondary:hover {
            background: #e2e8f0;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .table-header h3 {
            margin: 0;
            font-size: 17px;
        }

        .result-count {
            color: var(--muted);
            font-size: 12px;
        }

        .table-wrapper {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 10px;
        }

        table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
            text-align: left;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 15px 16px;
            color: #475569;
            font-size: 11px;
            letter-spacing: .4px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            padding: 15px 16px;
            border-top: 1px solid var(--border);
            font-size: 13px;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .activity-badge {
            display: inline-block;
            max-width: 330px;
            padding: 6px 9px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #334155;
            font-size: 11px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .activity-login {
            background: #dcfce7;
            color: var(--success);
        }

        .activity-failed {
            background: #fee2e2;
            color: var(--danger);
        }

        .activity-logout {
            background: #e0e7ff;
            color: #3730a3;
        }

        .ip-address {
            font-family: Consolas, monospace;
            color: #475569;
            font-size: 12px;
        }

        .empty-state {
            padding: 38px 20px;
            color: var(--muted);
            text-align: center;
            line-height: 1.7;
        }

        .footer {
            padding: 0 32px 24px;
            color: var(--muted);
            font-size: 12px;
        }

        @media (max-width: 950px) {
            .sidebar {
                width: 205px;
            }

            .content {
                padding: 24px 20px;
            }

            .topbar {
                padding: 18px 20px;
            }

            .filter-grid {
                grid-template-columns: 1fr 1fr;
            }

            .filter-actions {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 650px) {
            .layout {
                display: block;
            }

            .sidebar {
                width: 100%;
                padding: 16px;
            }

            .brand {
                padding-bottom: 16px;
            }

            .nav-title {
                margin-top: 17px;
            }

            .nav-link {
                display: inline-block;
                margin: 0 3px 5px 0;
                padding: 10px 12px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .user-area {
                width: 100%;
                justify-content: space-between;
            }

            .user-info {
                text-align: left;
            }

            .content {
                padding: 22px 14px;
            }

            .page-heading h2 {
                font-size: 22px;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                grid-column: auto;
            }

            .filter-actions .button {
                flex: 1;
            }

            .card {
                padding: 17px;
            }

            .summary-note {
                display: none;
            }

            .footer {
                padding: 0 14px 20px;
            }
        }
    </style>
</head>

<body>
<div class="layout">

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">A</div>
            <div>
                <div class="brand-name">Akademik</div>
                <div class="brand-subtitle">Sistem Informasi</div>
            </div>
        </div>

        <div class="nav-title">Menu Utama</div>

        <a class="nav-link" href="index.php">
            ▦ &nbsp; Data Mahasiswa
        </a>

        <a class="nav-link active" href="logs.php">
            ◷ &nbsp; Log Keamanan
        </a>
    </aside>

    <main class="main">

        <header class="topbar">
            <div>
                <h1 class="topbar-title">Log Keamanan</h1>
                <p class="topbar-subtitle">
                    Pantau aktivitas pengguna dan perubahan data.
                </p>
            </div>

            <div class="user-area">
                <div class="user-info">
                    <div class="user-name"><?= $usernameLogin ?></div>
                    <div class="user-role"><?= aman($roleLogin) ?></div>
                </div>

                <form method="POST" action="logout.php">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= aman($_SESSION['csrf_token']) ?>"
                    >
                    <button class="logout-button" type="submit">
                        Keluar
                    </button>
                </form>
            </div>
        </header>

        <section class="content">

            <div class="page-heading">
                <div>
                    <div class="breadcrumb">
                        <a href="index.php">Data Mahasiswa</a>
                        &nbsp; / &nbsp; Log Keamanan
                    </div>

                    <h2>Riwayat Aktivitas</h2>
                    <p>
                        Lihat riwayat login, logout, dan aktivitas pengelolaan
                        data mahasiswa yang tercatat di sistem.
                    </p>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon">◷</div>
                <div>
                    <div class="summary-label">Catatan sesuai filter</div>
                    <div class="summary-value">
                        <?= number_format($totalLog, 0, ',', '.') ?>
                    </div>
                </div>
                <div class="summary-note">
                    Maksimal 100 catatan terbaru ditampilkan
                </div>
            </div>

            <section class="card">
                <h3 class="card-title">Filter Aktivitas</h3>

                <form method="GET" action="logs.php">
                    <div class="filter-grid">

                        <div class="form-group">
                            <label for="username">Cari Username</label>
                            <input
                                class="form-control"
                                type="text"
                                id="username"
                                name="username"
                                maxlength="50"
                                placeholder="Masukkan username..."
                                value="<?= aman($username) ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="activity">Jenis Aktivitas</label>
                            <select
                                class="form-control"
                                id="activity"
                                name="activity"
                            >
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
                                <option value="MAHASISWA_TAMBAH" <?= $activity === 'MAHASISWA_TAMBAH' ? 'selected' : '' ?>>
                                    Tambah mahasiswa
                                </option>
                                <option value="MAHASISWA_EDIT" <?= $activity === 'MAHASISWA_EDIT' ? 'selected' : '' ?>>
                                    Edit mahasiswa
                                </option>
                                <option value="MAHASISWA_HAPUS" <?= $activity === 'MAHASISWA_HAPUS' ? 'selected' : '' ?>>
                                    Hapus mahasiswa
                                </option>
                                <option value="MAHASISWA_TAMBAH_GAGAL" <?= $activity === 'MAHASISWA_TAMBAH_GAGAL' ? 'selected' : '' ?>>
                                    Gagal tambah mahasiswa
                                </option>
                                <option value="MAHASISWA_EDIT_GAGAL" <?= $activity === 'MAHASISWA_EDIT_GAGAL' ? 'selected' : '' ?>>
                                    Gagal edit mahasiswa
                                </option>
                                <option value="MAHASISWA_HAPUS_GAGAL" <?= $activity === 'MAHASISWA_HAPUS_GAGAL' ? 'selected' : '' ?>>
                                    Gagal hapus mahasiswa
                                </option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button class="button button-primary" type="submit">
                                Cari Log
                            </button>
                            <a class="button button-secondary" href="logs.php">
                                Reset
                            </a>
                        </div>

                    </div>
                </form>
            </section>

            <section class="card">
                <div class="table-header">
                    <h3>Daftar Log</h3>
                    <span class="result-count">
                        <?= number_format($totalLog, 0, ',', '.') ?> catatan ditemukan
                    </span>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Username</th>
                                <th>Aktivitas</th>
                                <th>Alamat IP</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if ($totalLog === 0) { ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <strong>Tidak ada catatan ditemukan.</strong><br>
                                        Coba ubah kata kunci atau pilih aktivitas lain.
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php
                        $nomor = 1;

                        while ($log = mysqli_fetch_assoc($hasil)) {
                            $aktivitasLog = (string) ($log['activity'] ?? '');

                            if (strpos($aktivitasLog, 'gagal') !== false) {
                                $kelasAktivitas = 'activity-failed';
                            } elseif ($aktivitasLog === 'login_berhasil') {
                                $kelasAktivitas = 'activity-login';
                            } elseif ($aktivitasLog === 'logout') {
                                $kelasAktivitas = 'activity-logout';
                            } else {
                                $kelasAktivitas = '';
                            }
                        ?>
                            <tr>
                                <td><?= $nomor++ ?></td>
                                <td><strong><?= aman($log['username']) ?></strong></td>
                                <td>
                                    <span class="activity-badge <?= $kelasAktivitas ?>">
                                        <?= aman($aktivitasLog) ?>
                                    </span>
                                </td>
                                <td class="ip-address">
                                    <?= aman($log['ip_address'] ?? '-') ?>
                                </td>
                                <td><?= aman($log['created_at']) ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </section>

        <footer class="footer">
            Sistem Informasi Akademik &copy; <?= date('Y') ?>
        </footer>

    </main>
</div>
</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($koneksi);
?>
