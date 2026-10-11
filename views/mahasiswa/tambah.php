
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
        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
        >
            <!-- Input NIM, nama, alamat, dan fakultas tetap seperti semula -->

        <label>NIM:</label><br>
        <input
            type="text"
            name="nim"
            pattern="[0-9]{7}"
            minlength="7"
            maxlength="7"
            title="NIM harus terdiri dari tepat 7 digit angka"
            value="<?php echo htmlspecialchars($_POST['nim'] ?? ''); ?>"
            required
        >
        <br><br>

        <label>Nama:</label><br>
        <input
            type="text"
            name="nama"
            minlength="3"
            required
            value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>"
            required
        >
        <br><br>

        <label>Alamat:</label><br>
        <textarea name="alamat"
                minlength="3"
                required
        required><?php
            echo htmlspecialchars($_POST['alamat'] ?? '');
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
                $terpilih = (($_POST['fakultas'] ?? '') === $fakultasPilihan)
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

        <button type="submit" name="simpan">Simpan</button>
        <a href="index.php">Kembali</a>
    </form>
</body>
</html>
