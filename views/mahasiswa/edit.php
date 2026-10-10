
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa</title>
</head>
<body>
    <h1>Edit Mahasiswa</h1>

    <?php if ($pesan !== '') { ?>
        <p style="color: red;">
            <?php echo htmlspecialchars($pesan); ?>
        </p>
    <?php } ?>

    <form method="POST"
          action="edit.php?id=<?php echo (int) $dataMahasiswa['id']; ?>">

        <label>NIM:</label><br>
        <input type="text" name="nim"
            value="<?php echo htmlspecialchars($dataMahasiswa['nim']); ?>"
            required>
        <br><br>

        <label>Nama:</label><br>
        <input type="text" name="nama"
            value="<?php echo htmlspecialchars($dataMahasiswa['nama']); ?>"
            required>
        <br><br>

        <label>Alamat:</label><br>
        <textarea name="alamat" required><?php
            echo htmlspecialchars($dataMahasiswa['alamat']);
        ?></textarea>
        <br><br>

        <label>Fakultas:</label><br>
        <input type="text" name="fakultas"
            value="<?php echo htmlspecialchars($dataMahasiswa['fakultas']); ?>"
            required>
        <br><br>

        <button type="submit" name="ubah">Simpan Perubahan</button>
        <a href="index.php">Kembali</a>
    </form>
</body>
</html>
