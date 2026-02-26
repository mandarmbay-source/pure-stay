<?php
session_start();
require 'koneksi.php'; 

// Cek apakah yang login adalah admin
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

// Query dengan JOIN untuk mendapatkan nama pelanggan
// Query: Mengambil data yang statusnya 'Selesai' (Arsip)
$sql = "SELECT t.*, u.nama_lengkap 
        FROM transaksi_air t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.status_pesanan = 'Selesai' 
        ORDER BY t.tanggal DESC";

$query = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengantaran Air | PureStay</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* --- CSS VARIABLES & RESET --- */
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --bg: #f8fafc;
            --white: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --danger: #ef4444;
            --shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            padding: 40px 20px;
            margin: 0;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* --- HEADER SECTION --- */
        .header {
            margin-bottom: 30px;
            text-align: center;
        }

        .header h2 {
            font-weight: 800;
            margin-bottom: 10px;
        }

        .header p {
            color: var(--text-muted);
        }

        /* --- CARD & TABLE STYLE --- */
        .card {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px;
            background: #f1f5f9;
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
            vertical-align: middle;
        }

        .row-name {
            font-weight: 700;
            color: var(--text-main);
        }

        .row-address-empty {
            color: var(--danger);
            font-style: italic;
        }

        /* --- BADGES & BUTTONS --- */
        .badge-antar {
            background: #e0e7ff;
            color: #4338ca;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 800;
            font-size: 0.7rem;
            display: inline-block;
        }

        .btn-maps {
            background: var(--primary);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            transition: 0.2s;
            display: inline-block;
        }

        .btn-maps:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .no-gps {
            color: #cbd5e1;
            font-size: 0.8rem;
        }

        /* --- NAVIGATION BUTTONS --- */
        .nav-footer {
            margin-top: 30px;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn-exit {
            background: var(--white);
            color: var(--danger);
            border: 1px solid #fee2e2;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .btn-exit:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>🚚 Daftar Pengantaran Rumah</h2>
        <p>Data pesanan khusus metode pengantaran langsung ke pelanggan</p>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Pelanggan</th>
                    <th>Alamat Lengkap</th>
                    <th>Status</th>
                    <th>Aksi Lokasi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($query) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                            
                           <td class="row-name">
    <?php 
        $nama_fix = isset($row['nama_lengkap']) ? $row['nama_lengkap'] : 'Tidak ada nama';
        echo htmlspecialchars((string)$nama_fix); 
    ?>
</td>

<td>
    <?php 
        $alamat_fix = isset($row['alamat']) ? $row['alamat'] : ''; 
        if ($alamat_fix == '') {
            echo '<span class="row-address-empty">Alamat tidak tersedia</span>';
        } else {
            echo htmlspecialchars((string)$alamat_fix);
        }
    ?>
</td>

                            <td><span class="badge-antar">DIANTAR</span></td>

                            <td>
                                                            <?php 
                                    // Menggunakan isset untuk mengecek data (kompatibel semua versi PHP)
                                    $koor = isset($row['koordinat']) ? $row['koordinat'] : '';
                                    
                                    if (!empty($koor) && $koor !== '-'): 
                                ?>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode((string)$koor); ?>" 
                                    target="_blank" class="btn-maps">
                                        📍 Buka Maps
                                    </a>
                                <?php else: ?>
                                    <span class="no-gps">Tidak ada koordinat</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state">Belum ada pesanan aktif untuk diantar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="nav-footer">
        <a href="admin_air.php" class="btn-exit">
            Keluar ke Dashboard
        </a>
    </div>
</div>

</body>
</html>