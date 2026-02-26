<?php
session_start();
require 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header('Location: index.php'); 
    exit; 
}

// Ambil Nomor Kamar dari URL
$no_kamar = mysqli_real_escape_string($koneksi, $_GET['no']);
$query = mysqli_query($koneksi, "SELECT * FROM kamar_kos WHERE nomor_kamar = '$no_kamar'");
$d = mysqli_fetch_assoc($query);

// Cek jika data tidak ditemukan
if (!$d) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='admin_kos.php';</script>";
    exit;
}

if (isset($_POST['update'])) {
    $tipe  = mysqli_real_escape_string($koneksi, $_POST['tipe_kamar']);
    // Pastikan harga dibaca sebagai angka murni
    $harga = (int)$_POST['harga_bulanan']; 
    
    // Update Query: $harga tidak perlu pakai tanda kutip ('') jika tipenya INT di database
    $update = mysqli_query($koneksi, "UPDATE kamar_kos SET tipe_kamar='$tipe', harga_bulanan=$harga WHERE nomor_kamar='$no_kamar'");
    
    if($update) {
        echo "<script>alert('Data Kamar Berhasil Diubah ke Rp " . number_format($harga, 0, ',', '.') . "'); window.location.href='admin_kos.php';</script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Unit Kamar - PureStay</title>
    <style>
        body { background: #f8fbff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b; }
        .form-container { max-width: 450px; margin: 50px auto; background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(79, 140, 240, 0.08); border: 1px solid #f1f5f9; }
        h2 { margin-bottom: 25px; font-weight: 800; color: #4f8cf0; text-align: center; }
        label { display: block; margin-bottom: 8px; font-size: 0.9rem; font-weight: 600; color: #64748b; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; border: 2px solid #f1f5f9; border-radius: 12px; box-sizing: border-box; font-size: 1rem; transition: 0.3s; }
        input:focus { border-color: #4f8cf0; outline: none; background: #f0f7ff; }
        .btn-save { background: #4f8cf0; color: white; border: none; width: 100%; padding: 15px; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-save:hover { background: #3b76d6; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(79, 140, 240, 0.3); }
        .btn-cancel { display: block; text-align: center; margin-top: 20px; color: #94a3b8; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Kamar <?php echo htmlspecialchars($no_kamar); ?></h2>
        <form method="POST">
            <label>Tipe Kamar</label>
            <input type="text" name="tipe_kamar" value="<?php echo htmlspecialchars($d['tipe_kamar']); ?>" required placeholder="Contoh: Deluxe, Standar">
            
            <label>Harga Bulanan (Rp)</label>
            <input type="number" name="harga_bulanan" value="<?php echo (int)$d['harga_bulanan']; ?>" required placeholder="Contoh: 400000">
            
            <button type="submit" name="update" class="btn-save">Simpan Perubahan</button>
            <a href="admin_kos.php" class="btn-cancel">Batal dan Kembali</a>
        </form>
    </div>
</body>
</html>