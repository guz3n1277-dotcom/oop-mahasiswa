
<?php

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/SecurityLog.php';

// Logout hanya boleh melalui POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Validasi token CSRF
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    !hash_equals($_SESSION['csrf_token'] ?? '', $token)
) {
    http_response_code(403);
    exit('Permintaan tidak valid.');
}

// Simpan username sebelum session dikosongkan
$username = (string) (
    $_SESSION['username'] ?? 'tidak_diketahui'
);

// Catat aktivitas logout
$database = new Database();
$koneksi = $database->connect();

$securityLog = new SecurityLog($koneksi);
$securityLog->catat($username, 'logout');

mysqli_close($koneksi);

// Hapus data session
$_SESSION = [];

// Hapus cookie session
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax'
    ]);
}

// Hancurkan session
session_destroy();

header('Location: login.php');
exit;
