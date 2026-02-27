<?php
session_start(); 
require 'koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header('Location: index.php'); 
    exit; 
}

// 1. Ambil rekap pendapatan harian (Hanya yang sudah Lunas atau Gratis)
// Transaksi 'Proses' TIDAK akan masuk hitungan ini sampai Anda mengonfirmasinya.
$query_rekap = mysqli_query($koneksi, "SELECT DATE(tanggal) as tgl, SUM(harga) as total 
                                       FROM transaksi_air 
                                       WHERE status_pembayaran = 'Lunas' OR status_gratis = 1
                                       GROUP BY DATE(tanggal)");
$rekap_pendapatan = [];
while($r = mysqli_fetch_assoc($query_rekap)) {
    $rekap_pendapatan[$r['tgl']] = $r['total'];
}

// 2. Query Detail Transaksi
$query = mysqli_query($koneksi, "SELECT transaksi_air.*, users.nama_lengkap 
                                 FROM transaksi_air 
                                 JOIN users ON transaksi_air.user_id = users.id 
                                 ORDER BY transaksi_air.tanggal DESC");

function tglIndo($tanggal) {
    return date('d/m/Y', strtotime($tanggal));
}

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - PureStay</title>
    <link rel="stylesheet" href="riwayat_air.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        /* Desain Banner Keuntungan */
        .profit-card {
            background: linear-gradient(135deg, #1e293b, #334155);
            color: white;
            padding: 25px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .profit-amount {
            font-size: 2rem;
            font-weight: 800;
            color: #4ade80; /* Hijau terang */
            margin-top: 5px;
        }

        .daily-divider {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            background: #f1f5f9;
            border-radius: 12px;
            margin: 20px 0 10px 0;
            border-left: 6px solid #4f8cf0;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 800;
        }

        .btn-action {
            border: none;
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
        }

        .lunas-animasi {
            color: #16a34a;
            background: #dcfce7;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <a href="admin_dashboard.php" style="text-decoration:none; color:#64748b; font-weight:700;">← Kembali</a>
    </div>

    <?php 
        $today = date('Y-m-d');
        $total_today = isset($rekap_pendapatan[$today]) ? $rekap_pendapatan[$today] : 0;
    ?>
    <div class="profit-card">
        <div>
            <span style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; opacity: 0.8;">Profit Masuk (Hari Ini)</span>
            <div class="profit-amount"><?php echo formatRupiah($total_today); ?></div>
        </div>
        <div style="text-align: right;">
            <div style="font-weight: 700;"><?php echo tglIndo($today); ?></div>
            <div style="font-size: 0.8rem; opacity: 0.7;">Status: Terupdate Otomatis</div>
        </div>
    </div>

    <?php 
    $current_date = "";
    if(mysqli_num_rows($query) > 0):
        while($row = mysqli_fetch_assoc($query)): 
            $row_date = date('Y-m-d', strtotime($row['tanggal']));

            // Pemisah Tanggal & Sub-total
            if ($row_date != $current_date):
                if ($current_date != "") echo "</tbody></table></div>";
                $current_date = $row_date;
                $daily_profit = isset($rekap_pendapatan[$current_date]) ? $rekap_pendapatan[$current_date] : 0;
    ?>
        <div class="daily-divider">
            <span style="font-weight: 800; color: #1e293b;">📅 <?php echo tglIndo($current_date); ?></span>
            <span style="font-weight: 800; color: #166534;">Total: <?php echo formatRupiah($daily_profit); ?></span>
        </div>
        <div class="glass-card table-responsive">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; font-size: 0.85rem; color: #64748b; border-bottom: 1px solid #eee;">
                        <th style="padding: 15px;">User</th>
                        <th>Metode</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
    <?php endif; ?>
                <tr style="border-bottom: 1px solid #f8fafc;">
                    <td style="padding: 15px;"><strong><?php echo $row['nama_lengkap']; ?></strong></td>
                    <td><?php echo ($row['metode_ambil'] == 'antar' ? '🚚 Antar' : '🏠 Ambil'); ?></td>
                    <td style="font-weight: 700; color: #1e293b;"><?php echo formatRupiah($row['harga']); ?></td>
                    <td>
                        <?php if($row['status_gratis']): ?>
                            <span class="status-badge lunas-animasi">✨ GRATIS</span>
                        <?php elseif($row['status_pembayaran'] == 'Lunas'): ?>
                            <span class="status-badge lunas-animasi">✅ LUNAS</span>
                        <?php else: ?>
                            <span class="status-badge" style="background: #fef9c3; color: #854d0e;">⏳ PROSES</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <?php if($row['status_pembayaran'] == 'Proses' && !$row['status_gratis']): ?>
                                <button onclick="ubahKeLunas(<?php echo $row['id']; ?>)" class="btn-action" style="background: #dcfce7; color: #16a34a;" title="Tandai Lunas">✔️</button>
                            <?php endif; ?>
                            <button onclick="hapusData(<?php echo $row['id']; ?>)" class="btn-action" style="background: #fee2e2; color: #ef4444;">🗑️</button>
                        </div>
                    </td>
                </tr>
        <?php endwhile; ?>
        </tbody></table></div>
    <?php else: ?>
        <div class="glass-card" style="text-align:center; padding:50px; color:#64748b;">Belum ada transaksi.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function ubahKeLunas(id) {
    Swal.fire({
        title: 'Konfirmasi Pembayaran',
        text: "Setelah ini, total keuntungan harian akan langsung bertambah.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        confirmButtonText: 'Ya, Sudah Bayar!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mengarahkan ke file pengolah konfirmasi
            window.location.href = 'konfirmasi_air.php?id=' + id;
        }
    })
}

function hapusData(id) {
    Swal.fire({
        title: 'Hapus Riwayat?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'hapus_air.php?id=' + id;
        }
    })
}
</script>

</body>
</html>