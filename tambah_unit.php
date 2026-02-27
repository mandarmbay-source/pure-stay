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

    $cek = mysqli_query($koneksi, "SELECT * FROM kamar_kos WHERE nomor_kamar = '$nomor'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "error_exist";
    } else {
        $insert = mysqli_query($koneksi, "INSERT INTO kamar_kos (nomor_kamar, tipe_kamar, harga_bulanan) VALUES ('$nomor', '$tipe', '$harga')");
        if ($insert) { $pesan = "success_add"; }
    }
}

// PROSES HAPUS UNIT
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM kamar_kos WHERE nomor_kamar = '$id_hapus'");
    header('Location: tambah_unit.php?pesan=success_delete');
    exit;
}

$semua_kamar = mysqli_query($koneksi, "SELECT * FROM kamar_kos ORDER BY nomor_kamar ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Unit Kamar - PureStay</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #4f8cf0;
            --primary-hover: #3b76d6;
            --bg: #f0f5ff;
            --text-dark: #1e293b;
            --danger: #ef4444;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg); 
            margin: 0; 
            padding: 20px;
            color: var(--text-dark);
        }

        .container { max-width: 1100px; margin: auto; }

        .header-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .btn-back {
            text-decoration: none;
            color: var(--primary);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .btn-back:hover { transform: translateX(-5px); }

        /* Grid Layout */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 25px;
        }

        .card { 
            background: white; 
            padding: 25px; 
            border-radius: 24px; 
            box-shadow: 0 10px 40px rgba(79, 140, 240, 0.08); 
            border: 1px solid rgba(255,255,255,0.7);
        }

        h2 { margin-bottom: 20px; font-weight: 800; font-size: 1.4rem; letter-spacing: -0.5px; }

        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 0.85rem; color: #64748b; }
        
        input { 
            width: 100%; 
            padding: 14px; 
            border: 2px solid #f1f5f9; 
            border-radius: 12px; 
            box-sizing: border-box; 
            font-family: inherit;
            transition: 0.3s;
        }

        input:focus { outline: none; border-color: var(--primary); background: #f8fbff; }

        .btn-add { 
            background: var(--primary); 
            color: white; 
            border: none; 
            padding: 15px; 
            border-radius: 12px; 
            font-weight: 700; 
            cursor: pointer; 
            width: 100%; 
            font-size: 1rem;
            transition: 0.3s;
        }

        .btn-add:hover { background: var(--primary-hover); box-shadow: 0 10px 20px rgba(79, 140, 240, 0.2); }

        /* Table Styling */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f8fafc; padding: 15px; font-size: 0.75rem; text-transform: uppercase; color: #64748b; letter-spacing: 1px; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; }

        .kamar-badge {
            background: #eef2ff;
            color: var(--primary);
            padding: 5px 12px;
            border-radius: 8px;
            font-weight: 800;
        }

        .btn-delete { 
            color: var(--danger); 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 0.85rem; 
            padding: 8px 12px;
            border-radius: 8px;
            transition: 0.2s;
        }

        .btn-delete:hover { background: #fff1f2; }

        /* Responsive Mobile */
        @media (max-width: 900px) {
            .main-grid { grid-template-columns: 1fr; }
            body { padding: 15px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-nav">
        <a href="admin_kos.php" class="btn-back">← Kembali ke Dashboard</a>
    </div>
    
    <div class="main-grid">
        <div class="card">
            <h2>Tambah Unit</h2>
            <form method="POST">
                <div class="form-group">
                    <label>NOMOR KAMAR</label>
                    <input type="text" name="nomor_kamar" placeholder="Contoh: A01" required>
                </div>
                <div class="form-group">
                    <label>TIPE FASILITAS</label>
                    <input type="text" name="tipe_kamar" placeholder="Contoh: AC, TV, KM Dalam" required>
                </div>
                <div class="form-group">
                    <label>HARGA BULANAN (RP)</label>
                    <input type="number" name="harga_bulanan" placeholder="850000" required>
                </div>
                <button type="submit" name="submit_unit" class="btn-add">Simpan Unit Baru</button>
            </form>
        </div>

        <div class="card">
            <h2>Daftar Kamar</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tipe</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($semua_kamar)): ?>
                        <tr>
                            <td><span class="kamar-badge"><?php echo $row['nomor_kamar']; ?></span></td>
                            <td><small style="color:#64748b; font-weight:600;"><?php echo $row['tipe_kamar']; ?></small></td>
                            <td style="font-weight:700;">Rp <?php echo number_format($row['harga_bulanan'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="javascript:void(0)" onclick="confirmDelete('<?php echo $row['nomor_kamar']; ?>')" class="btn-delete">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($semua_kamar) == 0): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color:#94a3b8; padding:30px;">Belum ada data unit.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // SweetAlert untuk notifikasi PHP
    <?php if(isset($pesan) && $pesan == "success_add"): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Unit kamar baru telah ditambahkan.', timer: 2000, showConfirmButton: false });
    <?php elseif(isset($pesan) && $pesan == "error_exist"): ?>
        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Nomor kamar tersebut sudah terdaftar!' });
    <?php endif; ?>

    // Cek pesan dari URL (untuk delete)
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('pesan') === 'success_delete') {
        Swal.fire({ icon: 'success', title: 'Terhapus', text: 'Unit berhasil dihapus dari sistem.', timer: 2000, showConfirmButton: false });
    }

    // Fungsi konfirmasi hapus custom
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Data unit " + id + " akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?hapus=' + id;
            }
        })
    }
</script>

</body>
</html>