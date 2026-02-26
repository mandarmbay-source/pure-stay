<?php
session_start();
require 'koneksi.php';

// 1. Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

// --- LOGIKA HITUNG ---
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kamar_kos");
$res_total = mysqli_fetch_assoc($q_total);
$total_kamar = (int)$res_total['total'];

$q_terisi = mysqli_query($koneksi, "SELECT COUNT(k.nomor_kamar) as total 
                                    FROM kamar_kos k 
                                    INNER JOIN users u ON k.nomor_kamar = u.no_kamar");
$res_terisi = mysqli_fetch_assoc($q_terisi);
$terisi = (int)$res_terisi['total'];

$kosong = $total_kamar - $terisi;

// --- QUERY UTAMA GRID ---
$query_kamar = mysqli_query($koneksi, "
    SELECT 
        k.nomor_kamar, 
        k.tipe_kamar, 
        k.harga_bulanan,
        u.nama_lengkap,
        u.id as user_id,
        (SELECT status FROM data_kos WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as status_pembayaran,
        (SELECT tanggal_bayar FROM data_kos WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as tgl_bayar
    FROM kamar_kos k
    LEFT JOIN users u ON k.nomor_kamar = u.no_kamar
    ORDER BY LENGTH(k.nomor_kamar) ASC, k.nomor_kamar ASC
");

function formatTglIndo($tgl) {
    if (!$tgl) return "-";
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $ts = strtotime($tgl);
    return date('d', $ts) . ' ' . $bulan[(int)date('m', $ts)] . ' ' . date('Y', $ts);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Hunian - Admin PureStay</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f8cf0; --success: #2ecc71; --warning: #f1c40f; --danger: #e74c3c;
            --text-dark: #1e293b; --text-light: #64748b;
        }
        body { background: #f8fbff; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; color: var(--text-dark); }
        .dashboard-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .stats-mini-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px; }
        .stat-mini-card { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); text-align: center; }
        
        .room-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        .room-card { background: white; border-radius: 20px; padding: 24px; position: relative; border: 1px solid #eee; transition: 0.3s; }
        .room-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(79, 140, 240, 0.1); }
        
        .badge-status { position: absolute; top: 15px; right: 15px; padding: 6px 12px; border-radius: 8px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; }
        .status-tersedia { background: #ecfdf5; color: var(--success); }
        .status-terisi { background: #eff6ff; color: var(--primary); }

        .pay-status { display: inline-flex; align-items: center; margin: 10px 0; font-size: 0.65rem; font-weight: 800; padding: 4px 10px; border-radius: 6px; border: 1px solid; }
        .pay-lunas { color: var(--success); background: #f0fff4; border-color: #bbf7d0; }
        .pay-proses { color: var(--warning); background: #fffaf0; border-color: #fef08a; }
        .pay-pending { color: var(--danger); background: #fff5f5; border-color: #fecaca; }

        .timeline-info { background: #f8fafc; padding: 12px; border-radius: 12px; margin-top: 15px; font-size: 0.8rem; }
        .timeline-item { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .t-label { color: var(--text-light); }
        .t-val { font-weight: 700; color: var(--text-dark); }

        .room-info h3 { margin: 0 0 5px 0; font-size: 1.3rem; font-weight: 800; }
        .price { color: var(--primary); font-weight: 800; font-size: 1.1rem; display: block; margin-top: 10px; }

        .action-area { margin-top: 20px; display: flex; gap: 10px; border-top: 1px solid #f1f5f9; padding-top: 15px; }
        .btn-small { flex: 1; padding: 10px; border-radius: 10px; text-decoration: none; text-align: center; font-size: 0.8rem; font-weight: 700; transition: 0.2s; }
        .btn-edit { background: #f1f5f9; color: #475569; }
        .btn-manage { background: var(--primary); color: white; }
        .btn-history { background: #1e293b; color: white; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div style="margin: 20px 0; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="admin_dashboard.php" style="text-decoration: none; color: var(--primary); font-weight: 700;">← Dashboard</a>
            <h2 style="margin: 5px 0 0 0; font-weight: 800;">🏠 Kelola Hunian</h2>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="riwayat_pembayaran_kos.php" style="text-decoration: none; background: #1e293b; color: white; padding: 10px 20px; border-radius: 10px; font-weight: 700; font-size: 0.9rem;">📊 Monitoring Bayar</a>
            <a href="tambah_unit.php" style="text-decoration: none; background: var(--success); color: white; padding: 10px 20px; border-radius: 10px; font-weight: 700; font-size: 0.9rem;">+ Unit Baru</a>
        </div>
    </div>

    <div class="stats-mini-grid">
        <div class="stat-mini-card">
            <h4>Total Unit</h4>
            <p style="font-size: 1.8rem; font-weight: 800; margin: 5px 0;"><?php echo $total_kamar; ?></p>
        </div>
        <div class="stat-mini-card">
            <h4 style="color: var(--primary);">Terisi</h4>
            <p style="font-size: 1.8rem; font-weight: 800; margin: 5px 0; color: var(--primary);"><?php echo $terisi; ?></p>
        </div>
        <div class="stat-mini-card">
            <h4 style="color: var(--success);">Kosong</h4>
            <p style="font-size: 1.8rem; font-weight: 800; margin: 5px 0; color: var(--success);"><?php echo $kosong; ?></p>
        </div>
    </div>

    <div class="room-grid">
        <?php if(mysqli_num_rows($query_kamar) > 0): ?>
            <?php while($room = mysqli_fetch_assoc($query_kamar)): 
                $is_terisi = !empty($room['nama_lengkap']);
                $status_p = isset($room['status_pembayaran']) ? strtolower($room['status_pembayaran']) : '';
                
                $tgl_bayar = $room['tgl_bayar'];
                $jt_indo = "-";
                if($tgl_bayar) {
                    $jt_raw = strtotime($tgl_bayar . " +30 days");
                    $jt_indo = formatTglIndo(date('Y-m-d', $jt_raw));
                    if(time() > $jt_raw && $status_p == 'lunas') { $status_p = 'expired'; }
                }
            ?>
                <div class="room-card">
                    <span class="badge-status <?php echo $is_terisi ? 'status-terisi' : 'status-tersedia'; ?>">
                        <?php echo $is_terisi ? 'Terisi' : 'Tersedia'; ?>
                    </span>
                    
                    <div class="room-info">
                        <h3>Kamar <?php echo htmlspecialchars($room['nomor_kamar']); ?></h3>
                        <p style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 10px;">
                            Tipe: <b><?php echo htmlspecialchars($room['tipe_kamar']); ?></b>
                        </p>
                        
                        <p style="margin: 0;">Penghuni:</p>
                        <b style="font-size: 1rem;"><?php echo $is_terisi ? htmlspecialchars($room['nama_lengkap']) : '<span style="color:#cbd5e1">Kosong</span>'; ?></b>
                        
                        <?php if($is_terisi): ?>
                            <br>
                            <?php 
                                if($status_p == 'lunas') echo '<span class="pay-status pay-lunas">✓ TERBAYAR</span>';
                                elseif($status_p == 'proses') echo '<span class="pay-status pay-proses">⏳ BUTUH VERIFIKASI</span>';
                                elseif($status_p == 'expired') echo '<span class="pay-status pay-pending">🚨 JATUH TEMPO</span>';
                                else echo '<span class="pay-status pay-pending">✗ TUNGGAKAN</span>';
                            ?>

                            <div class="timeline-info">
                                <div class="timeline-item">
                                    <span class="t-label">Waktu Bayar:</span>
                                    <span class="t-val"><?php echo formatTglIndo($tgl_bayar); ?></span>
                                </div>
                                <div class="timeline-item">
                                    <span class="t-label">Batas Sewa:</span>
                                    <span class="t-val" style="color: <?php echo ($status_p == 'expired') ? 'var(--danger)' : 'var(--success)'; ?>">
                                        <?php echo $jt_indo; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <span class="price">
                            Rp <?php echo number_format((float)$room['harga_bulanan'], 0, ',', '.'); ?> 
                            <small style="font-size: 0.7rem; color: #94a3b8; font-weight: 400;">/bln</small>
                        </span>              
                    </div>

                    <div class="action-area">
                        <a href="edit_unit.php?no=<?php echo $room['nomor_kamar']; ?>" class="btn-small btn-edit">Edit</a>
                        
                        <?php if($is_terisi): ?>
                            <a href="hapus_penghuni.php?id=<?php echo $room['user_id']; ?>" 
                               class="btn-small" 
                               style="background: #fee2e2; color: #ef4444;"
                               onclick="return confirm('Apakah Anda yakin penghuni ini keluar?')">Keluar</a>
                        <?php endif; ?>

                        <a href="riwayat_pembayaran_kos.php" class="btn-small btn-manage">
                            <?php echo ($status_p == 'proses') ? 'Verifikasi' : 'Riwayat'; ?>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center; color: var(--text-light);">Belum ada data kamar.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>