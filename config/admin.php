
<?php
require_once __DIR__ . '/auth.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Akses Ditolak</title>
    </head>
    <body>
        <script>
            alert('Akses ditolak. Halaman ini khusus admin.');
            history.back();
        </script>
        <noscript>
            Akses ditolak. Halaman ini khusus admin.
        </noscript>
    </body>
    </html>
    <?php
    exit;
}
