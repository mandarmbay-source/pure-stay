<?php
session_start();
require 'koneksi.php';

// Proteksi Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header('Location: index.php'); 
    exit; 
}

// --- FITUR HAPUS SATUAN ---
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM transaksi_air WHERE id = '$id_hapus'");
    header("Location: riwayat_pembelian_air.php?pesan=terhapus");
    exit;
}

// --- FITUR HAPUS SEMUA ---
if (isset($_POST['hapus_semua'])) {
    mysqli_query($koneksi, "DELETE FROM transaksi_air");
    header("Location: riwayat_pembelian_air.php?pesan=kosong");
    exit;
}

// Ambil parameter pencarian dan filter bulan
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$bulan_filter = isset($_GET['bulan']) ? $_GET['bulan'] : '';

// Query SQL
$query_string = "SELECT transaksi_air.*, users.nama_lengkap 
                 FROM transaksi_air 
                 JOIN users ON transaksi_air.user_id = users.id 
                 WHERE 1=1";

if ($search) {
    $query_string .= " AND users.nama_lengkap LIKE '%$search%'";
}

if ($bulan_filter) {
    $query_string .= " AND MONTH(transaksi_air.tanggal) = '$bulan_filter'";
}

$query_string .= " ORDER BY transaksi_air.tanggal DESC";
$query = mysqli_query($koneksi, $query_string);

function formatTgl($tgl) {
    return date('d M Y | H:i', strtotime($tgl));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pembelian Air - PureStay</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary: #4f8cf0; --bg: #f8fafc; --dark: #1e293b; --danger: #ef4444; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: auto; }
        .glass-card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .search-box { display: flex; gap: 10px; }
        input, select { padding: 10px 15px; border-radius: 10px; border: 1px solid #e2e8f0; outline: none; }
        .btn { padding: 10px 20px; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-danger { background: var(--danger); color: white; font-size: 0.8rem; }
        .btn-outline-danger { border: 1.5px solid var(--danger); color: var(--danger); background: transparent; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; background: #f1f5f9; color: #64748b; font-size: 0.75rem; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
        .method-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; }
        .antar { background: #e0e7ff; color: #4338ca; }
        .ambil { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <a href="admin_dashboard.php" style="text-decoration: none; color: var(--primary); font-weight: 600;">← Kembali</a>
            <h2 style="margin: 10px 0 0 0; font-weight: 800;">💧 Monitoring Penjualan Air</h2>
        </div>
        <div style="display: flex; gap: 10px;">
            <form method="POST" onsubmit="return confirmHapusSemua(event)">
                <button type="submit" name="hapus_semua" class="btn btn-outline-danger">🗑️ Kosongkan Semua</button>
            </form>
            <a href="riwayat_air.php" class="btn" style="background: #1e293b; color: white;">⚙️ Kelola Transaksi</a>
        </div>
    </div>

    <div class="glass-card">
        <form class="toolbar" method="GET">
            <div class="search-box">
                <input type="text" name="search" placeholder="Cari nama pembeli..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="bulan">
                    <option value="">Semua Bulan</option>
                    <?php
                    $blns = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                    foreach($blns as $val => $name) {
                        $sel = ($bulan_filter == $val) ? 'selected' : '';
                        echo "<option value='$val' $sel>$name</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
            <a href="riwayat_pembelian_air.php" style="color: #64748b; font-size: 0.8rem;">Reset Filter</a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Nama Pembeli</th>
                    <th>Metode</th>
                    <th>Alamat Pengiriman</th>
                    <th>Total Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($query) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><b><?php echo formatTgl($row['tanggal']); ?></b></td>
                        <td><span style="font-weight: 700; color: var(--dark);"><?php echo $row['nama_lengkap']; ?></span></td>
                        <td>
                            <span class="method-badge <?php echo ($row['metode_ambil'] == 'antar') ? 'antar' : 'ambil'; ?>">
                                <?php echo strtoupper($row['metode_ambil']); ?>
                            </span>
                        </td>
                        <td>
                            <small style="color: #64748b;">
                                <?php 
                                if($row['metode_ambil'] == 'antar') {
                                    echo !empty($row['alamat']) ? $row['alamat'] : '<i style="color:red;">Alamat Kosong</i>';
                                } else {
                                    echo '<i>Ambil di Depot</i>';
                                }
                                ?>
                            </small>
                        </td>
                        <td style="font-weight: 800; color: #10b981;">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="javascript:void(0)" onclick="confirmHapus(<?php echo $row['id']; ?>)" class="btn btn-danger" style="padding: 5px 10px;">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 40px; color: #94a3b8;">Data pembelian tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Konfirmasi Hapus Satuan
    function confirmHapus(id) {
        Swal.fire({
            title: 'Hapus data ini?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'riwayat_pembelian_air.php?hapus=' + id;
            }
        })
    }

    // Konfirmasi Hapus Semua
    function confirmHapusSemua(e) {
        e.preventDefault();
        Swal.fire({
            title: 'KOSONGKAN SEMUA DATA?',
            text: "Tindakan ini akan menghapus SELURUH riwayat transaksi!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'YA, HAPUS SEMUA!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                e.target.submit();
            }
        })
    }

    // Notifikasi sukses (Jika ada parameter pesan di URL)
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('pesan')) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Data telah diperbarui',
            timer: 1500,
            showConfirmButton: false
        });
    }
</script>

</body>
</html>