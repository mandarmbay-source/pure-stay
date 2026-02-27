<?php
session_start();
require 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header('Location: index.php'); 
    exit; 
}

// Ambil Nomor Kamar dari URL
if (!isset($_GET['no'])) {
    header('Location: admin_kos.php');
    exit;
}

$no_kamar = mysqli_real_escape_string($koneksi, $_GET['no']);
$query = mysqli_query($koneksi, "SELECT * FROM kamar_kos WHERE nomor_kamar = '$no_kamar'");
$d = mysqli_fetch_assoc($query);

if (!$d) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='admin_kos.php';</script>";
    exit;
}

// PROSES UPDATE
$sukses = false; // Inisialisasi awal
if (isset($_POST['update'])) {
    $tipe  = mysqli_real_escape_string($koneksi, $_POST['tipe_kamar']);
    $harga = (int)$_POST['harga_bulanan']; 
    
    $update = mysqli_query($koneksi, "UPDATE kamar_kos SET tipe_kamar='$tipe', harga_bulanan=$harga WHERE nomor_kamar='$no_kamar'");
    
    if($update) {
        $sukses = true;
        $pesan_harga = number_format($harga, 0, ',', '.');
    } else {
        $error_msg = mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Unit Kamar - PureStay</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #4f8cf0;
            --primary-dark: #3b76d6;
            --bg: #f8fbff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        body { 
            background: var(--bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--text-dark); 
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .form-container { 
            width: 100%;
            max-width: 600px;
            background: white; 
            padding: 40px; 
            border-radius: 24px; 
            box-shadow: 0 20px 40px rgba(79, 140, 240, 0.1); 
            border: 1px solid #f1f5f9; 
            box-sizing: border-box;
        }

        .header-section {
            text-align: center;
            margin-bottom: 35px;
        }

        h2 { 
            margin: 0;
            font-size: 1.8rem;
            font-weight: 800; 
            color: var(--primary); 
        }

        .form-group { margin-bottom: 25px; }

        label { 
            display: block; 
            margin-bottom: 10px; 
            font-size: 0.9rem; 
            font-weight: 700; 
            color: var(--text-dark); 
        }

        input { 
            width: 100%; 
            padding: 14px 18px; 
            border: 2px solid #f1f5f9; 
            border-radius: 14px; 
            box-sizing: border-box; 
            font-size: 1rem; 
            transition: all 0.3s ease; 
            background: #fcfdfe;
        }

        input:focus { 
            border-color: var(--primary); 
            outline: none; 
            box-shadow: 0 0 0 4px rgba(79, 140, 240, 0.1);
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 40px;
        }

        .btn-save { 
            background: var(--primary); 
            color: white; 
            border: none; 
            padding: 16px; 
            border-radius: 14px; 
            font-size: 1rem; 
            font-weight: 700; 
            cursor: pointer; 
            transition: all 0.3s ease;
        }

        .btn-save:hover { 
            background: var(--primary-dark); 
            transform: translateY(-2px); 
        }

        .btn-cancel { 
            text-align: center; 
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 0.95rem; 
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="header-section">
            <h2>Edit Unit Kamar</h2>
            <p style="color:var(--text-muted)">Nomor Kamar: <strong><?php echo htmlspecialchars($no_kamar); ?></strong></p>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Tipe Kamar</label>
                <input type="text" name="tipe_kamar" value="<?php echo htmlspecialchars($d['tipe_kamar']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Harga Bulanan</label>
                <input type="number" name="harga_bulanan" value="<?php echo (int)$d['harga_bulanan']; ?>" required>
            </div>
            
            <div class="action-buttons">
                <button type="submit" name="update" class="btn-save">Simpan Perubahan</button>
                <a href="admin_kos.php" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>

    <script>
    <?php if($sukses): ?>
        Swal.fire({
            title: 'Berhasil!',
            text: 'Data kamar diperbarui menjadi Rp <?php echo $pesan_harga; ?>',
            icon: 'success',
            confirmButtonColor: '#4f8cf0'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'admin_kos.php';
            }
        });
    <?php endif; ?>

    <?php if(isset($error_msg)): ?>
        Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan: <?php echo $error_msg; ?>',
            icon: 'error'
        });
    <?php endif; ?>
    </script>
</body>
</html>