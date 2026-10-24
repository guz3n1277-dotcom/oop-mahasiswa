
<?php

require_once __DIR__ . '/config/admin.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Mahasiswa.php';

// Hubungkan aplikasi ke database.
$database = new Database();
$koneksi = $database->connect();

$mahasiswa = new Mahasiswa($koneksi);
$hasil = $mahasiswa->ambilSemuaUntukEkspor();

if ($hasil === false) {
    http_response_code(500);
    exit('Data gagal diekspor. Silakan coba lagi.');
}

// Kirim header unduhan CSV.
$namaFile = 'data_mahasiswa_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header(
    'Content-Disposition: attachment; filename="' . $namaFile . '"'
);
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

// Buka aliran output, bukan membuat file permanen di server.
$output = fopen('php://output', 'w');

if ($output === false) {
    http_response_code(500);
    exit('File ekspor gagal dibuat.');
}

// BOM membantu Excel membaca UTF-8.
fwrite($output, "\xEF\xBB\xBF");

// Header kolom.
fputcsv($output, ['NIM', 'Nama', 'Alamat', 'Fakultas']);

// Lindungi data teks dari formula CSV berbahaya.
$amankanNilaiCsv = static function ($nilai): string {
    $nilai = (string) ($nilai ?? '');

    if (preg_match('/^[\t\r\n ]*[=+\-@]/', $nilai)) {
        return "'" . $nilai;
    }

    return $nilai;
};

// Tulis setiap baris data.
while ($baris = mysqli_fetch_assoc($hasil)) {
    fputcsv($output, [
        $amankanNilaiCsv($baris['nim']),
        $amankanNilaiCsv($baris['nama']),
        $amankanNilaiCsv($baris['alamat']),
        $amankanNilaiCsv($baris['fakultas'])
    ]);
}

mysqli_free_result($hasil);
fclose($output);
exit;
