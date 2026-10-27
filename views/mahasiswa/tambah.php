
<?php
$csrfToken = htmlspecialchars(
    $_SESSION['csrf_token'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$nimInput = htmlspecialchars(
    $_POST['nim'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$namaInput = htmlspecialchars(
    $_POST['nama'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$alamatInput = htmlspecialchars(
    $_POST['alamat'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$fakultasSaatIni = $_POST['fakultas'] ?? '';

$daftarFakultas = [
    'Teknik',
    'Ekonomi dan Bisnis',
    'Filkom',
    'Psikologi',
    'Keguruan dan Ilmu Pendidikan'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mahasiswa | Akademik</title>

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
            text-decoration: none;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 245px;
            flex-shrink: 0;
            padding: 28px 18px;
            background: #172554;
            color: #dbeafe;
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
            margin-bottom: 7px;
            padding: 13px 14px;
            border-radius: 9px;
            color: #dbeafe;
            font-size: 14px;
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
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
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
            width: 40px;
            height: 40px;
            display: flex;
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
            background: #ffffff;
            color: #dc2626;
            cursor: pointer;
        }

        .logout-button:hover {
            background: #fef2f2;
        }

        .content {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 32px;
        }

        .breadcrumb {
            margin-bottom: 22px;
            color: #64748b;
            font-size: 13px;
        }

        .breadcrumb a {
            color: #2563eb;
        }

        .page-heading {
            margin-bottom: 24px;
        }

        .page-heading h2 {
            margin: 0 0 8px;
            font-size: 26px;
        }

        .page-heading p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        .form-card {
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 3px 12px rgba(15, 23, 42, 0.03);
        }

        .form-card-heading {
            padding: 22px 26px;
            border-bottom: 1px solid #e2e8f0;
        }

        .form-card-heading h3 {
            margin: 0 0 6px;
            font-size: 17px;
        }

        .form-card-heading p {
            margin: 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        .form-body {
            padding: 26px;
        }

        .error-message {
            margin-bottom: 22px;
            padding: 13px 15px;
            border: 1px solid #fecaca;
            border-radius: 9px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 13px;
            line-height: 1.6;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .form-group {
            min-width: 0;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
        }

        .required {
            color: #dc2626;
        }

        .form-control {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            color: #0f172a;
            font: inherit;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px #dbeafe;
        }

        textarea.form-control {
            min-height: 110px;
            resize: vertical;
            line-height: 1.6;
        }

        .help-text {
            display: block;
            margin-top: 7px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid #e2e8f0;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 17px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .button-primary {
            background: #2563eb;
            color: #ffffff;
        }

        .button-primary:hover {
            background: #1d4ed8;
        }

        .button-secondary {
            border-color: #cbd5e1;
            background: #ffffff;
            color: #475569;
        }

        .button-secondary:hover {
            background: #f8fafc;
        }

        .footer {
            padding: 24px 32px;
            color: #94a3b8;
            font-size: 12px;
            text-align: center;
        }

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

            .page-heading h2 {
                font-size: 23px;
            }

            .form-card-heading,
            .form-body {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .form-group.full-width {
                grid-column: auto;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .form-actions .button {
                width: 100%;
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
            <a href="index.php">Data Mahasiswa</a>
            <a href="tambah.php" class="active">+ Tambah Mahasiswa</a>

            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="logs.php">Log Keamanan</a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-note">
            Dashboard pengelolaan data mahasiswa.
        </div>
    </aside>

    <main class="main">

        <header class="topbar">
            <h1>Dashboard</h1>

            <div class="account">
                <div class="avatar">
                    <?php
                    echo htmlspecialchars(
                        strtoupper(substr($_SESSION['username'] ?? 'P', 0, 1)),
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </div>

                <div class="account-info">
                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION['username'] ?? 'Pengguna',
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </strong>
                    <span>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION['role'] ?? 'user',
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </span>
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

            <div class="breadcrumb">
                <a href="index.php">Data Mahasiswa</a>
                &nbsp;/&nbsp; Tambah Mahasiswa
            </div>

            <div class="page-heading">
                <h2>Tambah Mahasiswa</h2>
                <p>Masukkan informasi mahasiswa pada formulir di bawah ini.</p>
            </div>

            <section class="form-card">

                <div class="form-card-heading">
                    <h3>Informasi Mahasiswa</h3>
                    <p>Pastikan semua data yang dimasukkan benar sebelum disimpan.</p>
                </div>

                <div class="form-body">

                    <?php if ($pesan !== ''): ?>
                        <div class="error-message" role="alert">
                            <?php
                            echo htmlspecialchars(
                                $pesan,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="tambah.php" enctype="multipart/form-data">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo $csrfToken; ?>"
                        >

                        <div class="form-grid">

                            <div class="form-group">
                                <label for="nim">
                                    NIM <span class="required">*</span>
                                </label>

                                <input
                                    class="form-control"
                                    type="text"
                                    id="nim"
                                    name="nim"
                                    pattern="[0-9]{7}"
                                    minlength="7"
                                    maxlength="7"
                                    inputmode="numeric"
                                    title="NIM harus terdiri dari tepat 7 digit angka"
                                    placeholder="Contoh: 2210282"
                                    value="<?php echo $nimInput; ?>"
                                    required
                                >

                                <small class="help-text">
                                    NIM harus terdiri dari tepat 7 digit angka.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="nama">
                                    Nama Lengkap <span class="required">*</span>
                                </label>

                                <input
                                    class="form-control"
                                    type="text"
                                    id="nama"
                                    name="nama"
                                    minlength="3"
                                    maxlength="100"
                                    placeholder="Masukkan nama lengkap"
                                    value="<?php echo $namaInput; ?>"
                                    required
                                >
                            </div>

                            <div class="form-group full-width">
                                <label for="alamat">
                                    Alamat <span class="required">*</span>
                                </label>

                                <textarea
                                    class="form-control"
                                    id="alamat"
                                    name="alamat"
                                    minlength="3"
                                    placeholder="Masukkan alamat lengkap"
                                    required
                                ><?php echo $alamatInput; ?></textarea>
                            </div>

                            <div class="form-group full-width">
                                <label for="fakultas">
                                    Fakultas <span class="required">*</span>
                                </label>

                                <select
                                    class="form-control"
                                    id="fakultas"
                                    name="fakultas"
                                    required
                                >
                                    <option value="">-- Pilih Fakultas --</option>

                                    <?php foreach ($daftarFakultas as $fakultasPilihan): ?>
                                        <option
                                            value="<?php echo htmlspecialchars($fakultasPilihan, ENT_QUOTES, 'UTF-8'); ?>"
                                            <?php echo $fakultasSaatIni === $fakultasPilihan ? 'selected' : ''; ?>
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $fakultasPilihan,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="foto">Foto Mahasiswa (Opsional)</label>

                                <input
                                    class="form-control"
                                    type="file"
                                    id="foto"
                                    name="foto"
                                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                >

                                <small class="help-text">
                                    Format yang diperbolehkan: JPG, JPEG, atau PNG.
                                    Ukuran maksimal 2 MB. Foto boleh dikosongkan.
                                </small>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="index.php" class="button button-secondary">
                                Batal
                            </a>

                            <button
                                type="submit"
                                name="simpan"
                                class="button button-primary"
                            >
                                Simpan Mahasiswa
                            </button>
                        </div>

                    </form>
                </div>
            </section>

        </section>

        <footer class="footer">
            &copy; <?php echo date('Y'); ?> Sistem Informasi Mahasiswa
        </footer>

    </main>

</div>

</body>
</html>
