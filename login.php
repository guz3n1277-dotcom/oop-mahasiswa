
<?php

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/SecurityLog.php';

// Jika sudah login, langsung ke halaman utama cd /c/xampp/htdocs/LatihanPHP/oop-mahasiswa
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Buat token CSRF untuk formulir login
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

        if (is_string($username)) {
            $username = trim($username);
        }
        if (
            !is_string($username) ||
            !is_string($password) ||
            $username === '' ||
            $password === ''
        ) {
            $pesan = 'Username dan password wajib diisi.';
        } else {
            $database = new Database();
            $koneksi = $database->connect();

            $stmt = mysqli_prepare(
                $koneksi,
                'SELECT id, username, password, role
                 FROM users
                 WHERE username = ?
                 LIMIT 1'
            );

            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);

            $hasil = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($hasil);

            if (
                $user &&
                password_verify($password, $user['password'])
            ) {
                // Ganti ID session setelah login berhasil
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Token lama tidak digunakan lagi
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                //catat login berhasil
                $securityLog = new SecurityLog($koneksi);
                $securityLog->catat(
                    $user['username'],
                    'login_berhasil'
                );

                mysqli_stmt_close($stmt);
                mysqli_close($koneksi);

                header('Location: index.php');
                exit;
            }

            $pesan = 'Username atau password salah.';
            
            //catat llogin salah            
            $securityLog = new SecurityLog($koneksi);

            $securityLog->catat(
                is_string($username) ? $username : 'input_tidak_valid',
                'login_gagal'
            );
            mysqli_stmt_close($stmt);
            mysqli_close($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
</head>
<body>
    <h1>Login</h1>

    <?php if ($pesan !== '') { ?>
        <p style="color: red;">
            <?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php } ?>

    <form method="POST" action="login.php">
        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
        >

        <label>Username:</label><br>
        <input
            type="text"
            name="username"
            maxlength="50"
            autocomplete="username"
            required
        >
        <br><br>

        <label>Password:</label><br>
        <input
            type="password"
            name="password"
            autocomplete="current-password"
            required
        >
        <br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
