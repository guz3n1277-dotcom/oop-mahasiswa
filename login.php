
<?php

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/SecurityLog.php';
require_once __DIR__ . '/models/LoginRateLimit.php';

// Jika sudah login, langsung ke halaman utama cd /c/xampp/htdocs/LatihanPHP/oop-mahasiswa
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Buat token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $token)
    ) {
        $pesan = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!is_string($username) || !is_string($password)) {
            $pesan = 'Format input tidak valid.';
        } else {
            $username = trim($username);

            if ($username === '' || $password === '') {
                $pesan = 'Username dan password wajib diisi.';
            } elseif (strlen($username) > 50) {
                $pesan = 'Username tidak valid.';
            } else {
                
                $koneksi = null;
                $stmt = null;

                try {
                    $database = new Database();
                    $koneksi = $database->connect();

                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $rateLimit = new LoginRateLimit($koneksi);

                    // Periksa pembatasan sebelum memeriksa password.
                    if ($rateLimit->diblokir($username, $ip)) {
                        $pesan = 'Terlalu banyak percobaan login. Coba lagi setelah 15 menit.';
                    } else {
                        $sql = 'SELECT id, username, password, role
                                FROM users
                                WHERE username = ?
                                LIMIT 1';

                        $stmt = mysqli_prepare($koneksi, $sql);

                        mysqli_stmt_bind_param($stmt, 's', $username);
                        mysqli_stmt_execute($stmt);

                        $hasil = mysqli_stmt_get_result($stmt);
                        $user = mysqli_fetch_assoc($hasil);

                        mysqli_stmt_close($stmt);
                        $stmt = null;

                        if ($user && password_verify($password, $user['password'])) {
                            // Reset hitungan kegagalan sebelum membuat sesi login.
                            $rateLimit->reset($username, $ip);

                            session_regenerate_id(true);

                            $_SESSION['user_id'] = (int) $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['role'] = $user['role'];
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                            // Kegagalan pencatatan log ditangani oleh SecurityLog.
                            $securityLog = new SecurityLog($koneksi);
                            $securityLog->catat(
                                $user['username'],
                                'login_berhasil'
                            );

                            mysqli_close($koneksi);
                            $koneksi = null;

                            header('Location: index.php');
                            exit;
                        }

                        // Login gagal: simpan percobaan dan catat aktivitas.
                        $rateLimit->gagal($username, $ip);

                        $securityLog = new SecurityLog($koneksi);
                        $securityLog->catat($username, 'login_gagal');

                        $pesan = 'Username atau password salah.';
                    }
                } catch (Throwable $e) {
                    // Simpan detail teknis hanya di log server.
                    error_log(
                        'Proses login gagal: ' .
                        get_class($e) . ': ' .
                        $e->getMessage()
                    );

                    $pesan = 'Layanan sedang mengalami gangguan. Silakan coba lagi.';
                } finally {
                    if ($stmt instanceof mysqli_stmt) {
                        try {
                            mysqli_stmt_close($stmt);
                        } catch (Throwable $e) {
                            error_log('Gagal menutup statement login.');
                        }
                    }

                    if ($koneksi instanceof mysqli) {
                        try {
                            mysqli_close($koneksi);
                        } catch (Throwable $e) {
                            error_log('Gagal menutup koneksi database login.');
                        }
                    }
                }
            }
        }
    }

}

$csrfToken = htmlspecialchars(
    $_SESSION['csrf_token'],
    ENT_QUOTES,
    'UTF-8'
);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Informasi Mahasiswa</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at top left, #dbeafe 0, transparent 35%),
                linear-gradient(135deg, #f1f5f9, #e8efff);
            color: #1e293b;
        }

        .login-wrapper {
            width: 100%;
            max-width: 430px;
        }

        .brand {
            margin-bottom: 24px;
            text-align: center;
        }

        .brand-icon {
            width: 62px;
            height: 62px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border-radius: 18px;
            background: #2563eb;
            color: #ffffff;
            font-size: 25px;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2);
        }

        .brand h1 {
            margin: 0 0 8px;
            font-size: 25px;
            letter-spacing: -0.5px;
        }

        .brand p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }

        .login-card {
            padding: 32px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 15px 45px rgba(15, 23, 42, 0.08);
        }

        .card-heading {
            margin-bottom: 26px;
        }

        .card-heading h2 {
            margin: 0 0 8px;
            font-size: 21px;
        }

        .card-heading p {
            margin: 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
        }

        .form-group input {
            display: block;
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            background: #ffffff;
            color: #0f172a;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input::placeholder {
            color: #94a3b8;
        }

        .form-group input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px #dbeafe;
        }

        .error-message {
            margin-bottom: 20px;
            padding: 12px 14px;
            border: 1px solid #fecaca;
            border-radius: 9px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 13px;
            line-height: 1.6;
        }

        .login-button {
            width: 100%;
            padding: 13px 16px;
            border: none;
            border-radius: 9px;
            background: #2563eb;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .login-button:hover {
            background: #1d4ed8;
        }

        .login-button:active {
            transform: scale(0.99);
        }

        .security-note {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-top: 22px;
            padding: 13px;
            border-radius: 9px;
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
        }

        .security-note strong {
            color: #475569;
        }

        .footer {
            margin-top: 22px;
            color: #94a3b8;
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 480px) {
            body {
                padding: 18px;
            }

            .login-card {
                padding: 24px 20px;
            }

            .brand h1 {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

    <main class="login-wrapper">

        <header class="brand">
            <div class="brand-icon" aria-hidden="true">A</div>
            <h1>Akademik</h1>
            <p>Informasi Mahasiswa</p>
        </header>

        <section class="login-card">

            <div class="card-heading">
                <h2>Selamat Datang</h2>
                <p>Silakan masuk menggunakan akun Anda untuk mengakses dashboard.</p>
            </div>

            <?php if ($pesan !== ''): ?>
                <div class="error-message" role="alert">
                    <?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo $csrfToken; ?>"
                >

                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        maxlength="50"
                        placeholder="Masukkan username"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button class="login-button" type="submit">
                    Masuk ke Dashboard
                </button>

            </form>

            <div class="security-note">
                <span aria-hidden="true">🔒</span>
                <div>
                    <strong>Akses terlindungi</strong><br>
                    Percobaan login dibatasi untuk membantu melindungi akun.
                </div>
            </div>

        </section>

        <footer class="footer">
            &copy; <?php echo date('Y'); ?> Informasi Mahasiswa
        </footer>

    </main>

</body>
</html>
