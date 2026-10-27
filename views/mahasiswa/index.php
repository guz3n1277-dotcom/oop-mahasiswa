<?php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = htmlspecialchars(
    $_SESSION['csrf_token'],
    ENT_QUOTES,
    'UTF-8'
);

$username = htmlspecialchars(
    $_SESSION['username'] ?? 'Pengguna',
    ENT_QUOTES,
    'UTF-8'
);

$role = $_SESSION['role'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa | Admin Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f4f7fb;
            color: #1e293b;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input {
            font: inherit;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 245px;
            flex-shrink: 0;
            background: #172554;
            color: #dbeafe;
            padding: 28px 18px;
        }

        .brand {
            padding: 0 12px 30px;
            border-bottom: 1px solid #334477;
        }

        .brand h2 {
            margin: 0;
            color: #ffffff;
            font-size: 21px;
        }

        .brand p {
            margin: 7px 0 0;
            color: #a5b4fc;
            font-size: 12px;
        }

        .menu-title {
            margin: 28px 12px 12px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .menu a {
            display: block;
            padding: 13px 14px;
            margin-bottom: 7px;
            border-radius: 9px;
            color: #dbeafe;
            font-size: 14px;
            transition: background 0.2s;
        }

        .menu a:hover,
        .menu a.active {
            background: #29458a;
            color: #ffffff;
        }

        .sidebar-note {
            margin: 35px 10px 0;
            color: #a5b4fc;
            font-size: 12px;
            line-height: 1.7;
        }

        /* Konten utama */
        .main {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            min-height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 32px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }

        .topbar h1 {
            margin: 0;
            font-size: 20px;
        }

        .account {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            display: flex;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #dbeafe;
            color: #1d4ed8;
            font-weight: 700;
        }

        .account-info {
            font-size: 13px;
        }

        .account-info strong {
            display: block;
            margin-bottom: 4px;
        }

        .account-info span {
            color: #64748b;
            text-transform: capitalize;
        }

        .logout-form {
            margin-left: 8px;
        }

        .logout-button {
            padding: 9px 13px;
            border: 1px solid #fecaca;
            border-radius: 8px;
            background: #fff;
            color: #dc2626;
            cursor: pointer;
        }

        .logout-button:hover {
            background: #fef2f2;
        }

        .content {
            padding: 30px 32px;
        }

        .page-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 25px;
        }

        .page-heading h2 {
            margin: 0 0 8px;
            font-size: 26px;
        }

        .page-heading p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }

        /* Ringkasan */
        .summary-card {
            padding: 22px 24px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 3px 12px rgba(15, 23, 42, 0.03);
        }

        .summary-label {
            color: #64748b;
            font-size: 13px;
        }

        .summary-number {
            display: block;
            margin-top: 9px;
            color: #1d4ed8;
            font-size: 30px;
            font-weight: 700;
        }

        /* Tombol */
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 16px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .button-primary {
            background: #2563eb;
            color: #ffffff;
        }

        .button-primary:hover {
            background: #1d4ed8;
        }

        .button-edit {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .button-edit:hover {
            background: #dbeafe;
        }

        .button-delete {
            background: #fef2f2;
            color: #dc2626;
        }

        .button-delete:hover {
            background: #fee2e2;
        }

        /* Pencarian dan tabel */
        .table-card {
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 3px 12px rgba(15, 23, 42, 0.03);
        }

        .table-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-toolbar h3 {
            margin: 0;
            font-size: 17px;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .search-input {
            width: 260px;
            max-width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            outline: none;
        }

        .search-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px #dbeafe;
        }

        .button-search {
            background: #2563eb;
            color: #ffffff;
        }

        .button-reset {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
        }

        .table-scroll {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 15px 18px;
            color: #64748b;
            font-size: 11px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            padding: 16px 18px;
            border-top: 1px solid #edf2f7;
            color: #334155;
            font-size: 13px;
            vertical-align: middle;
        }
        
        /* Kolom fakultas */
        table th:nth-child(6),
        table td:nth-child(6) {
            min-width: 220px;
        }

        table td:nth-child(6) {
            white-space: normal;
            overflow-wrap: break-word;
        }

        tbody tr:hover {
            background: #f8fbff;
        }

        .student-name {
            color: #0f172a;
            font-weight: 600;
        }

        .student-id {
            color: #64748b;
        }

        /* Foto mahasiswa */
        .student-photo {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            display: block;
        }

        .photo-placeholder {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
            color: #64748b;
            font-size: 10px;
            text-align: center;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .action-buttons form {
            margin: 0;
        }

        .action-buttons .button {
            padding: 8px 11px;
        }

        .empty-state {
            padding: 45px 20px;
            color: #64748b;
            text-align: center;
        }

        .empty-state strong {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 16px;
        }

        /* Pagination */
        .table-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            padding: 18px 24px;
            border-top: 1px solid #e2e8f0;
        }

        .pagination-info {
            color: #64748b;
            font-size: 13px;
        }

        .pagination {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .page-link {
            display: inline-flex;
            min-width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            padding: 0 10px;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            background: #ffffff;
            color: #475569;
            font-size: 13px;
        }

        .page-link:hover {
            border-color: #93c5fd;
            background: #eff6ff;
        }

        .page-link.current {
            border-color: #2563eb;
            background: #2563eb;
            color: #ffffff;
            font-weight: 700;
        }

        .page-link.disabled {
            color: #cbd5e1;
            pointer-events: none;
        }

        .footer {
            padding: 24px 32px;
            color: #94a3b8;
            font-size: 12px;
            text-align: center;
        }

        /* Tampilan ponsel/tablet */
        @media (max-width: 900px) {
            .sidebar {
                width: 200px;
            }

            .content {
                padding: 24px 20px;
            }

            .topbar {
                padding: 16px 20px;
            }

            .table-toolbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .search-form {
                width: 100%;
            }

            .search-input {
                flex: 1;
                width: auto;
            }
        }

        @media (max-width: 600px) {
            .layout {
                display: block;
            }

            .sidebar {
                width: 100%;
                padding: 18px;
            }

            .brand {
                padding-bottom: 15px;
            }

            .menu-title {
                margin-top: 17px;
            }

            .menu a {
                display: inline-block;
                margin-bottom: 0;
                padding: 10px;
            }

            .sidebar-note {
                display: none;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .account {
                width: 100%;
                flex-wrap: wrap;
            }

            .content {
                padding: 22px 14px;
            }

            .page-heading {
                flex-direction: column;
            }

            .page-heading h2 {
                font-size: 23px;
            }

            .table-toolbar,
            .table-footer {
                padding: 17px;
            }

            .search-form {
                display: grid;
                grid-template-columns: 1fr auto;
            }

            .search-input {
                width: 100%;
                grid-column: 1 / -1;
            }

            .table-footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .footer {
                padding: 20px 12px;
            }
        }

    </style>
</head>

<body>

<div class="layout">

    <aside class="sidebar">
        <div class="brand">
            <h2>Akademik</h2>
            <p>Sistem Informasi Mahasiswa</p>
        </div>

        <div class="menu-title">Menu Utama</div>

        <nav class="menu">
            <a href="index.php" class="active">Data Mahasiswa</a>

            <a href="tambah.php">+ Tambah Mahasiswa</a>

            <?php if ($role === 'admin'): ?>
                <a href="logs.php">Log Keamanan</a>
            <?php endif; ?>
        </nav>

        <?php if ($role === 'admin'): ?>
            <a href="export.php" class="button button-primary">
                Ekspor CSV
            </a>
        <?php endif; ?>

        <div class="sidebar-note">
            Dashboard pengelolaan data mahasiswa.
        </div>
    </aside>

    <main class="main">

        <header class="topbar">
            <h1>Dashboard</h1>

            <div class="account">
                <div class="avatar">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </div>

                <div class="account-info">
                    <strong><?php echo $username; ?></strong>
                    <span><?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <form class="logout-form" method="POST" action="logout.php">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo $csrfToken; ?>"
                    >
                    <button class="logout-button" type="submit">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <section class="content">

            <div class="page-heading">
                <div>
                    <h2>Data Mahasiswa</h2>
                    <p>Kelola dan pantau data mahasiswa yang tersimpan.</p>
                </div>

            </div>

            <div class="summary-card">
                <span class="summary-label">Total Data Mahasiswa</span>
                <span class="summary-number">
                    <?php echo (int) $totalData; ?>
                </span>
            </div>

            <section class="table-card">

                <div class="table-toolbar">
                    <h3>Daftar Mahasiswa</h3>

                    <form class="search-form" method="GET" action="index.php">
                        <input
                            class="search-input"
                            type="text"
                            name="cari"
                            placeholder="Cari NIM, nama, atau fakultas..."
                            value="<?php echo htmlspecialchars($kataKunci, ENT_QUOTES, 'UTF-8'); ?>"
                        >

                        <button class="button button-search" type="submit">
                            Cari
                        </button>

                        <a href="index.php" class="button button-reset">
                            Reset
                        </a>
                    </form>
                </div>

                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Foto</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Alamat</th>
                                <th>Fakultas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php
                        $adaData = false;

                        while ($row = mysqli_fetch_assoc($data)):
                            $adaData = true;
                            $id = (int) $row['id'];
                        ?>
                            <tr>
                                <td class="student-id">
                                    <?php echo $id; ?>
                                </td>
                                
                                <td>
                                    <?php
                                    $foto = $row['foto'] ?? null;

                                    $fotoValid = is_string($foto)
                                        && preg_match('/\A[a-f0-9]{32}\.(jpg|png)\z/', $foto);

                                    $pathFoto = __DIR__ . '/../../uploads/' . ($fotoValid ? $foto : '');
                                    ?>

                                    <?php if ($fotoValid && is_file($pathFoto)): ?>
                                        <img
                                            class="student-photo"
                                            src="<?php echo 'uploads/' . rawurlencode($foto); ?>"
                                            alt="Foto mahasiswa"
                                            loading="lazy"
                                        >
                                    <?php else: ?>
                                        <div class="photo-placeholder">Tanpa foto</div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['nim'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td class="student-name">
                                    <?php echo htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['alamat'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['fakultas'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <a
                                            class="button button-edit"
                                            href="edit.php?id=<?php echo $id; ?>"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="hapus.php"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus data mahasiswa ini?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo $id; ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?php echo $csrfToken; ?>"
                                            >

                                            <button
                                                class="button button-delete"
                                                type="submit"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (!$adaData): ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <strong>Data tidak ditemukan</strong>
                                    Coba kata kunci lain atau tambahkan data mahasiswa baru.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="pagination-info">
                        <?php
                        $halamanTampil = $totalHalaman > 0 ? (int) $halaman : 0;
                        $urlCari = urlencode($kataKunci);
                        $jumlahPerHalaman = 5;

                        $awalData = $totalData > 0
                            ? (($halamanTampil - 1) * $jumlahPerHalaman) + 1
                            : 0;

                        $akhirData = $totalData > 0
                            ? min($halamanTampil * $jumlahPerHalaman, $totalData)
                            : 0;
                        ?>

                        <?php if ($totalData > 0): ?>
                            Menampilkan <?php echo $awalData; ?>&ndash;<?php echo $akhirData; ?>
                            dari <?php echo (int) $totalData; ?>
                            <?php echo $kataKunci !== '' ? 'hasil pencarian' : 'mahasiswa'; ?>
                        <?php else: ?>
                            <?php if ($kataKunci !== ''): ?>
                                Tidak ada data yang cocok dengan pencarian.
                            <?php else: ?>
                                Belum ada data mahasiswa.
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($totalHalaman > 0): ?>
                            &middot;
                            Halaman <?php echo $halamanTampil; ?>
                            dari <?php echo (int) $totalHalaman; ?>
                        <?php endif; ?>
                    </div>

                    <nav class="pagination" aria-label="Navigasi halaman">
                        <?php if ($halamanTampil > 1): ?>
                            <a
                                class="page-link"
                                href="?cari=<?php echo $urlCari; ?>&amp;halaman=<?php echo $halamanTampil - 1; ?>"
                            >
                                Sebelumnya
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                            <a
                                class="page-link <?php echo $i === $halamanTampil ? 'current' : ''; ?>"
                                href="?cari=<?php echo $urlCari; ?>&amp;halaman=<?php echo $i; ?>"
                                <?php echo $i === $halamanTampil ? 'aria-current="page"' : ''; ?>
                            >
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($halamanTampil > 0 && $halamanTampil < $totalHalaman): ?>
                            <a
                                class="page-link"
                                href="?cari=<?php echo $urlCari; ?>&amp;halaman=<?php echo $halamanTampil + 1; ?>"
                            >
                                Berikutnya
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>

            </section>

        </section>

        <footer class="footer">
            MARI MENUJU INDONESIA EMAS &copy; <?php echo date('Y'); ?>
        </footer>

    </main>

</div>

</body>
</html>