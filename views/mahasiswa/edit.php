
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
            <?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php } ?>

    <form method="POST"
          action="edit.php?id=<?php echo (int) $dataMahasiswa['id']; ?>">

        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
        >

        <!-- Input NIM, nama, alamat, dan fakultas tetap seperti semula -->
        <label>NIM:</label><br>
        <input type="text" name="nim"
            value="<?php echo htmlspecialchars($dataMahasiswa['nim'], ENT_QUOTES, 'UTF-8'); ?>"
            required>
        <br><br>

        <label>Nama:</label><br>
        <input type="text" name="nama"
            value="<?php echo htmlspecialchars($dataMahasiswa['nama'], ENT_QUOTES, 'UTF-8'); ?>"
            required>
        <br><br>

        <label>Alamat:</label><br>
        <textarea name="alamat" required><?php
            echo htmlspecialchars($dataMahasiswa['alamat'], ENT_QUOTES, 'UTF-8');
        ?></textarea>
        <br><br>

        <label>Fakultas:</label><br>
        <select name="fakultas" required>
            <option value="">-- Pilih Fakultas --</option>

            <?php
            $daftarFakultas = [
                'Teknik',
                'Ekonomi dan Bisnis',
                'Filkom',
                'Psikologi',
                'Keguruan dan Ilmu Pendidikan'
            ];

            foreach ($daftarFakultas as $fakultasPilihan) {
                $fakultasSaatIni = $_POST['fakultas']
                    ?? $dataMahasiswa['fakultas'];

                $terpilih = ($fakultasSaatIni === $fakultasPilihan)
                    ? 'selected'
                    : '';

                echo '<option value="' .
                    htmlspecialchars($fakultasPilihan, ENT_QUOTES, 'UTF-8') .
                    '" ' . $terpilih . '>' .
                    htmlspecialchars($fakultasPilihan, ENT_QUOTES, 'UTF-8') .
                    '</option>';
            }
            ?>
        </select>
        <br><br>

        <button type="submit" name="ubah">Simpan Perubahan</button>
        <a href="index.php">Kembali</a>
    </form>
</body>
</html>
