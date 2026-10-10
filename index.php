<?php

include "config/Database.php";
include "models/Mahasiswa.php";

$database = new Database();

$koneksi = $database->connect();

$mahasiswa = new Mahasiswa($koneksi);

$kataKunci = trim($_GET['cari'] ?? '');

if ($kataKunci !== '') {
    $data = $mahasiswa->cari($kataKunci);
} else {
    $data = $mahasiswa->tampilkanSemua();
}

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

</body>
</html>