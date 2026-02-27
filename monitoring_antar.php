<?php
session_start();
require 'koneksi.php'; 

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

// Query untuk mengambil data Selesai, termasuk harga
$sql = "SELECT t.*, u.nama_lengkap 
        FROM transaksi_air t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.status_pesanan = 'Selesai' 
        ORDER BY t.tanggal DESC";

$query = mysqli_query($koneksi, $sql);

// Mengelompokkan data berdasarkan Bulan dan Hari
$data_arsip = [];
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $bulan = date('F Y', strtotime($row['tanggal']));
        $hari  = date('Y-m-d', strtotime($row['tanggal']));
        $data_arsip[$bulan][$hari][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Penghasilan | PureStay</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg: #f8fafc;
            --white: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --accent: #f1f5f9;
            --success: #059669;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            padding: 20px;
            margin: 0;
        }

        .container { max-width: 1000px; margin: 0 auto; }

        /* --- TOP NAVIGATION BAR --- */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: var(--white);
            padding: 15px 25px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .btn-back { 
            text-decoration: none; 
            background: var(--primary); 
            padding: 10px 18px; 
            border-radius: 10px; 
            color: var(--white); 
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: var(--primary-hover);
            transform: translateX(-3px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .page-title h2 { margin: 0; font-size: 1.25rem; font-weight: 800; }

        /* --- MONTHLY SECTION --- */
        .month-section { margin-bottom: 40px; }
        .month-label { 
            background: #e0e7ff; 
            color: #4338ca; 
            padding: 8px 16px; 
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 20px;
            font-weight: 800;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .day-card {
            background: var(--white);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }

        .day-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--accent);
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .day-date { font-weight: 800; color: var(--text-main); font-size: 0.95rem; }
        .day-total { font-weight: 800; color: var(--success); background: #ecfdf5; padding: 6px 12px; border-radius: 8px; font-size: 0.85rem; }

        /* --- TABLE --- */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 0.7rem; color: var(--text-muted); padding: 10px; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 12px 10px; border-bottom: 1px solid #f8fafc; font-size: 0.85rem; }

        .price { font-weight: 700; color: var(--text-main); }
        .gratis { color: var(--primary); font-style: italic; font-weight: 700; }
        
        .btn-maps {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 6px;
            background: #eff6ff;
            transition: 0.2s;
        }

        .btn-maps:hover { background: #dbeafe; }

        .empty-state {
            text-align: center; 
            padding: 60px; 
            background: white; 
            border-radius: 20px; 
            border: 2px dashed #e2e8f0;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="top-nav">
        <div class="page-title">
            <h2>💰 Monitoring Laporan</h2>
        </div>
        <a href="admin_air.php" class="btn-back">
            <span>←</span> Kembali 
        </a>
    </div>

    <?php if (empty($data_arsip)): ?>
        <div class="empty-state">
            <div style="font-size: 3rem; margin-bottom: 10px;">📊</div>
            <p>Belum ada data transaksi yang selesai untuk ditampilkan.</p>
        </div>
    <?php else: ?>
        
        <?php foreach ($data_arsip as $bulan => $hari_list): ?>
            <div class="month-section">
                <div class="month-label">📍 <?php echo $bulan; ?></div>

                <?php foreach ($hari_list as $tanggal => $transaksi): ?>
                    <div class="day-card">
                        <div class="day-header">
                            <div class="day-date">📅 <?php echo date('d F Y', strtotime($tanggal)); ?></div>
                            <?php 
                                $total_hari = 0;
                                foreach($transaksi as $t) { 
                                    if($t['status_gratis'] != 1) {
                                        $total_hari += $t['harga']; 
                                    }
                                }
                            ?>
                            <div class="day-total">Total: Rp <?php echo number_format($total_hari, 0, ',', '.'); ?></div>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Metode</th>
                                    <th>Aksi</th>
                                    <th style="text-align: right;">Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transaksi as $row): ?>
                                    <tr>
                                        <td><?php echo date('H:i', strtotime($row['tanggal'])); ?></td>
                                        <td style="font-weight:700;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td>
                                            <span style="font-size: 10px; font-weight: 700; color: #6366f1;">
                                                <?php echo strtoupper($row['metode_ambil']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if(!empty($row['koordinat']) && $row['koordinat'] !== '-'): ?>
                                                <a href="https://www.google.com/maps?q=<?php echo urlencode($row['koordinat']); ?>" target="_blank" class="btn-maps">📍 Maps</a>
                                            <?php else: ?>
                                                <span style="color: #cbd5e1; font-size: 0.75rem;">Self Pickup</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;" class="price">
                                            <?php 
                                                if($row['status_gratis'] == 1) {
                                                    echo '<span class="gratis">GRATIS</span>';
                                                } else {
                                                    echo 'Rp ' . number_format($row['harga'], 0, ',', '.');
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

</body>
</html>