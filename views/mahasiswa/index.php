<?php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Mahasiswa</title>
</head>

<body>

<h1>Data Mahasiswa</h1>

<p>
    <a href="tambah.php">Tambah Mahasiswa</a>
</p>

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
            <form action="hapus.php" method="POST"
                onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                <input type="hidden" name="id"
                    value="<?php echo (int) $row['id']; ?>">
                <button type="submit">Hapus</button>
            </form>

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