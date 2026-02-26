<?php
session_start(); 
require 'koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header('Location: index.php'); 
    exit; 
}

// Query JOIN untuk mengambil data transaksi dan nama user
$query = mysqli_query($koneksi, "SELECT transaksi_air.*, users.nama_lengkap 
                                 FROM transaksi_air 
                                 JOIN users ON transaksi_air.user_id = users.id 
                                 ORDER BY transaksi_air.tanggal DESC");

function bulanIndo($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $bulan[ (int)$split[1] ] . ' ' . $split[0];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi Air - PureStay</title>
    <link rel="stylesheet" href="riwayat_air.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .btn-delete { background: #fee2e2; color: #ef4444; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: 0.3s; font-size: 0.9rem; }
        .btn-delete:hover { background: #ef4444; color: white; }
        .btn-confirm { background: #dcfce7; color: #166534; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: 0.3s; font-size: 0.9rem; }
        .btn-confirm:hover { background: #22c55e; color: white; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap; }
        .btn-clear-all { background: #1e293b; color: white; border: none; padding: 12px 20px; border-radius: 12px; cursor: pointer; font-weight: 600; transition: 0.3s; }
        .btn-clear-all:hover { background: #0f172a; transform: translateY(-2px); }
        /* Badge Status Custom */
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block; }
        .status-yes { background: #dcfce7; color: #15803d; }
        .status-no { background: #fef9c3; color: #854d0e; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <a href="admin_dashboard.php" class="btn-back-link" style="margin:0;">← Kembali</a><br>
        <a href="riwayat_pembelian_air.php" style="background: #4f8cf0; color: white; padding: 10px 18px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(79, 140, 240, 0.3);">🔍 Cari & Filter Data</a>
    </div>
    
    <div class="header-flex">
        <div><br>
            <h2 style="margin:0;">📜 Riwayat Pembelian Air</h2><br>
            <p style="color: #64748b; margin: 5px 0 0 0;">Kelola dan konfirmasi transaksi air bersih.</p>
        </div>
        <button onclick="hapusSemuaAir()" class="btn-clear-all">🗑️ Kosongkan Riwayat</button>
    </div>
    
    <?php 
    $bulan_sekarang = ""; 
    $no = 1;

    if(mysqli_num_rows($query) > 0):
        while($row = mysqli_fetch_assoc($query)): 
            $bulan_data = bulanIndo($row['tanggal']);

            if ($bulan_data != $bulan_sekarang):
                if ($bulan_sekarang != "") echo "</tbody></table></div></div>"; 
                $bulan_sekarang = $bulan_data;
                $no = 1; 
    ?>
        <div class="month-group">
            <h3 class="month-title"><?php echo $bulan_sekarang; ?></h3>
            <div class="glass-card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th class="no-column">No</th>
                                <th>Nama Lengkap</th> 
                                <th>Metode</th>
                                <th>Harga</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php endif; ?>
                        <tr>
                            <td class="no-column"><?php echo $no++; ?></td>
                            <td><strong class="nama-user"><?php echo $row['nama_lengkap']; ?></strong></td>
                            <td><?php echo ($row['metode_ambil'] == 'antar' ? '🚚 Diantar' : '🏠 Ambil'); ?></td>
                            <td class="price-bold">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if($row['status_gratis']): ?>
                                    <span class="status-badge status-yes">✨ GRATIS</span>
                                <?php else: ?>
                                    <span class="status-badge <?php echo ($row['status_pembayaran'] == 'Lunas' ? 'status-yes' : 'status-no'); ?>">
                                        <?php echo ($row['status_pembayaran'] == 'Lunas' ? '✅ LUNAS' : '⏳ PROSES'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="date-column"><?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <?php if($row['status_pembayaran'] == 'Proses' && !$row['status_gratis']): ?>
                                        <button onclick="konfirmasiBayar(<?php echo $row['id']; ?>)" class="btn-confirm" title="Konfirmasi Lunas">✔️</button>
                                    <?php endif; ?>
                                    <button onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn-delete" title="Hapus">🗑️</button>
                                </div>
                            </td>
                        </tr>
        <?php endwhile; ?>
        </tbody></table></div></div> <?php else: ?>
        <div class="glass-card" style="text-align:center; padding:50px;">
            <p style="color: #64748b;">Belum ada riwayat transaksi air.</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Fungsi Konfirmasi Pembayaran Lunas
function konfirmasiBayar(id) {
    Swal.fire({
        title: 'Konfirmasi Lunas?',
        text: "Pesanan ini akan ditandai sebagai sudah dibayar.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Lunas!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'konfirmasi_air.php?id=' + id;
        }
    })
}

// Fungsi Hapus Satu Data
function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus data ini?',
        text: "Catatan transaksi ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'hapus_air.php?id=' + id;
        }
    })
}

// Fungsi Kosongkan Semua
function hapusSemuaAir() {
    Swal.fire({
        title: 'Kosongkan Semua Data?',
        text: "Semua riwayat transaksi air akan dihapus permanen!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#1e293b',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'hapus_semua_air.php';
        }
    })
}
</script>
</body>
</html>