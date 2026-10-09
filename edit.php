<?php

include "config/Database.php";
include "models/Mahasiswa.php";

$database = new Database();

$koneksi = $database->connect();

$mahasiswa = new Mahasiswa($koneksi);

$id = $_GET["id"];

$data = $mahasiswa->ambilById($id);


$pesan = "";

if (isset($_POST['edit'])) {

    $nim = trim($_POST['nim']);
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $fakultas = $_POST['fakultas'];

    if ($nim === "" || $nama === "" || $alamat === "") {
        $pesan = "Semua kolom harus diisi.";

    } elseif ($mahasiswa->nimAda($nim, $id)) {
        $pesan = "NIM sudah digunakan mahasiswa lain.";

    } else {
        $berhasil = $mahasiswa->ubah(
            $id,
            $nim,
            $nama,
            $alamat,
            $fakultas
        );

        if ($berhasil) {
            header("Location: index.php");
            exit();
        } else {
            $pesan = "Data gagal diubah.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Edit Mahasiswa</title>

</head>

<body>

<h1>Edit Mahasiswa</h1>

<?php if ($pesan !== ""): ?>
    <p><?php echo htmlspecialchars($pesan); ?></p>
<?php endif; ?>

<form method="POST">

    <label>NIM</label><br>
    <input type="text" name="nim"
        value="<?php echo htmlspecialchars($data['nim']) ?>">

    <br><br>

    <label>Nama</label><br>
    <input type="text" name="nama"
        value="<?php echo htmlspecialchars($data['nama']) ?>">

    <br><br>

    <label>Alamat</label><br>
    <input type="text" name="alamat"
        value="<?php echo htmlspecialchars($data['alamat']) ?>">

    <br><br>

    <label>Fakultas</label><br>

    <select name="fakultas">

        <option value="Saintek"<?php if ($data['fakultas'] == 'Saintek') echo 'selected'; ?>>
            Saintek
        </option>

        <option value="Filkom"<?php if ($data['fakultas'] == 'Filkom') echo 'selected'; ?>>
            Filkom
        </option>
        
        <option value="Teknik"<?php if ($data['fakultas'] == 'Teknik') echo 'selected'; ?>>
            Teknik
        </option>

    </select>

    <br><br>

    <button type="submit" name="edit">
        Simpan
    </button>

</form>

<br>

<a href="index.php">Kembali</a>

</body>
</html>