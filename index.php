<?php

include "config/Database.php";
include "models/Mahasiswa.php";

$database = new Database();

$koneksi = $database->connect();

$mahasiswa = new Mahasiswa($koneksi);


$kataKunci = trim($_GET['cari'] ?? '');

// Pengaturan pagination
$perHalaman = 5;
$halaman = (int) ($_GET['halaman'] ?? 1);

if ($halaman < 1) {
    $halaman = 1;
}

// Menghitung jumlah data dan halaman
$totalData = $mahasiswa->hitungSemua($kataKunci);
$totalHalaman = (int) ceil($totalData / $perHalaman);

// Mencegah halaman melebihi jumlah halaman tersedia
if ($totalHalaman > 0 && $halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}

// Menghitung posisi awal data
$offset = ($halaman - 1) * $perHalaman;

// Mengambil data untuk halaman aktif
$data = $mahasiswa->tampilkanHalaman(
    $kataKunci,
    $perHalaman,
    $offset
);


?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Mahasiswa</title>
</head>

<body>

<h1>Data Mahasiswa</h1>

<form method="GET" action="index.php">
    <input
        type="text"
        name="cari"
        placeholder="Cari NIM atau nama..."
        value="<?php echo htmlspecialchars($kataKunci); ?>"
    >

    <button type="submit">Cari</button>

    <a href="index.php">Reset</a>
</form>

<table border="1" cellpadding="10">

    <tr>
        <th>ID</th>
        <th>NIM</th>
        <th>Nama</th>
        <th>Alamat</th>
        <th>Fakultas</th>
        <th>Aksi</th>
    </tr>

    <?php while($row = mysqli_fetch_assoc($data)){ ?>

    <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo htmlspecialchars($row['nim']); ?></td>
        <td><?php echo htmlspecialchars($row['nama']); ?></td>
        <td><?php echo htmlspecialchars($row['alamat']); ?></td>
        <td><?php echo htmlspecialchars($row['fakultas']); ?></td>

        <td>
            <a href="edit.php?id=<?php echo $row['id']; ?>">
                Edit
            </a>
            |
            <a
                href="hapus.php?id=<?php echo $row['id']; ?>"
                onclick="return confirm('Yakin ingin menghapus data ini?');">
                Hapus
            </a>
        </td>
    </tr>

    <?php } ?>

</table>


<div>
    <p>
        Total data: <?php echo $totalData; ?> |
        Halaman <?php echo $totalHalaman > 0 ? $halaman : 0; ?>
        dari <?php echo $totalHalaman; ?>
    </p>

    <?php if ($halaman > 1) { ?>
        <a href="?cari=<?php echo urlencode($kataKunci); ?>&halaman=<?php echo $halaman - 1; ?>">
            Sebelumnya
        </a>
    <?php } ?>

    <?php for ($i = 1; $i <= $totalHalaman; $i++) { ?>
        <a
            href="?cari=<?php echo urlencode($kataKunci); ?>&halaman=<?php echo $i; ?>"
            style="<?php echo $i === $halaman ? 'font-weight: bold;' : ''; ?>"
        >
            <?php echo $i; ?>
        </a>
    <?php } ?>

    <?php if ($halaman < $totalHalaman) { ?>
        <a href="?cari=<?php echo urlencode($kataKunci); ?>&halaman=<?php echo $halaman + 1; ?>">
            Berikutnya
        </a>
    <?php } ?>
</div>


</body>
</html>