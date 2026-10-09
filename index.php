<?php

include "config/Database.php";
include "models/Mahasiswa.php";

$database = new Database();

$koneksi = $database->connect();

$mahasiswa = new Mahasiswa($koneksi);

$data = $mahasiswa->tampilkanSemua();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Mahasiswa</title>
</head>

<body>

<h1>Data Mahasiswa</h1>

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