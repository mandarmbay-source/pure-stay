<?php
session_start();
require 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

// PROSES INPUT UNIT BARU
if (isset($_POST['submit_unit'])) {
    $nomor = mysqli_real_escape_string($koneksi, $_POST['nomor_kamar']);
    $tipe = mysqli_real_escape_string($koneksi, $_POST['tipe_kamar']);
    $harga = mysqli_real_escape_string($koneksi, $_POST['harga_bulanan']);

    // Cek apakah nomor kamar sudah ada
    $cek = mysqli_query($koneksi, "SELECT * FROM kamar_kos WHERE nomor_kamar = '$nomor'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Nomor kamar sudah ada!');</script>";
    } else {
        $insert = mysqli_query($koneksi, "INSERT INTO kamar_kos (nomor_kamar, tipe_kamar, harga_bulanan) VALUES ('$nomor', '$tipe', '$harga')");
        if ($insert) {
            echo "<script>alert('Unit berhasil ditambahkan!'); window.location.href='tambah_unit.php';</script>";
        }
    }
}

// PROSES HAPUS UNIT
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM kamar_kos WHERE nomor_kamar = '$id_hapus'");
    header('Location: tambah_unit.php');
}

$semua_kamar = mysqli_query($koneksi, "SELECT * FROM kamar_kos ORDER BY nomor_kamar ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Unit Kamar - PureStay</title>
    <link rel="stylesheet" href="hover_unit.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fbff; margin: 0; padding: 40px; }
        .container { max-width: 900px; margin: auto; }
        .card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 30px; }
        h2 { margin-top: 0; color: #1e293b; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; }
        input { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; }
        .btn-add { background: #4f8cf0; color: white; border: none; padding: 12px 25px; border-radius: 10px; font-weight: 700; cursor: pointer; width: 100%; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; background: #f1f5f9; padding: 12px; font-size: 0.8rem; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
        .btn-delete { color: #e74c3c; text-decoration: none; font-weight: 700; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="container">
    <a href="admin_kos.php" style="text-decoration: none; color: #4f8cf0; font-weight: 700;">← Kembali</a>
    
    <div class="card" style="margin-top: 20px;">
        <h2>Tambah Unit Baru</h2>
        <form method="POST">
            <div class="form-group">
                <label>Nomor Kamar (Contoh: A1, B10)</label>
                <input type="text" name="nomor_kamar" placeholder="Masukkan nomor kamar" required>
            </div>
            <div class="form-group">
                <label>Tipe Kamar</label>
                <input type="text" name="tipe_kamar" placeholder="Contoh: AC + Kamar Mandi Dalam" required>
            </div>
            <div class="form-group">
                <label>Harga Per Bulan (Rp)</label>
                <input type="number" name="harga_bulanan" placeholder="400.000" required>
            </div>
            <button type="submit" name="submit_unit" class="btn-add">Simpan Unit</button>
        </form>
    </div>

    <div class="card">
        <h2>Daftar Seluruh Unit</h2>
        <table>
            <thead>
                <tr>
                    <th>No Kamar</th>
                    <th>Tipe</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($semua_kamar)): ?>
                <tr>
                    <td><b><?php echo $row['nomor_kamar']; ?></b></td>
                    <td><?php echo $row['tipe_kamar']; ?></td>
                    <td>Rp <?php echo number_format($row['harga_bulanan'], 0, ',', '.'); ?></td>
                    <td>
                        <a href="?hapus=<?php echo $row['nomor_kamar']; ?>" class="btn-delete" onclick="return confirm('Hapus unit ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>