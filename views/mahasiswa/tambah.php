
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mahasiswa</title>
</head>
<body>
    <h1>Tambah Mahasiswa</h1>

    <?php if ($pesan !== '') { ?>
        <p style="color: red;">
            <?php echo htmlspecialchars($pesan); ?>
        </p>
    <?php } ?>

    <form method="POST" action="tambah.php">
        <label>NIM:</label><br>
        <input
            type="text"
            name="nim"
            value="<?php echo htmlspecialchars($_POST['nim'] ?? ''); ?>"
            required
        >
        <br><br>

        <label>Nama:</label><br>
        <input
            type="text"
            name="nama"
            value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>"
            required
        >
        <br><br>

        <label>Alamat:</label><br>
        <textarea name="alamat" required><?php
            echo htmlspecialchars($_POST['alamat'] ?? '');
        ?></textarea>
        <br><br>

        <label>Fakultas:</label><br>
        <input
            type="text"
            name="fakultas"
            value="<?php echo htmlspecialchars($_POST['fakultas'] ?? ''); ?>"
            required
        >
        <br><br>

        <button type="submit" name="simpan">Simpan</button>
        <a href="index.php">Kembali</a>
    </form>
</body>
</html>
