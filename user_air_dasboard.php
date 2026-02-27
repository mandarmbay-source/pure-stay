<?php
session_start();
require 'koneksi.php'; 

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['id'];

// Logika Loyalitas
$jumlah_lama = 0; 
if (isset($koneksi)) {
    $sql = "SELECT hitungan_loyalitas FROM users WHERE id = '$user_id'";
    $query_user = mysqli_query($koneksi, $sql);
    $data_user = mysqli_fetch_assoc($query_user);
    if ($data_user) {
        $jumlah_lama = (int)$data_user['hitungan_loyalitas'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PureStay | Pesan Air</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        /* CSS Gabungan & Responsif */
        :root {
            --primary: #0ea5e9;
            --dark: #0f172a;
            --glass: rgba(255, 255, 255, 0.85);
            --border: rgba(255, 255, 255, 0.3);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            background-attachment: fixed;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            min-height: 100vh;
        }

        .main-wrapper {
            max-width: 1100px;
            width: 100%;
        }

        .top-nav-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .brand { font-weight: 800; font-size: 1.4rem; color: var(--dark); }
        .brand span { color: var(--primary); }

        .btn-logout-modern {
            background: white;
            color: #ef4444;
            padding: 10px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.8rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: 0.3s;
        }

        .container {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 25px;
            align-items: start;
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border-radius: 28px;
            padding: 25px;
            border: 1px solid var(--border);
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }

        /* Loyalitas */
        .loyalitas-box {
            background: var(--dark);
            color: white;
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 25px;
        }

        .progress-bar {
            background: rgba(255,255,255,0.1);
            height: 8px;
            border-radius: 10px;
            margin: 10px 0;
            overflow: hidden;
        }

        .progress-fill {
            background: var(--primary);
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        /* Form Elements */
        label { display: block; font-weight: 700; font-size: 0.85rem; margin-bottom: 8px; color: var(--dark); }
        select, input {
            width: 100%;
            padding: 14px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            margin-bottom: 15px;
            font-family: inherit;
            background: white;
        }

        #map { height: 200px; border-radius: 14px; margin-bottom: 15px; border: 1px solid #ddd; }

        .btn-modern {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 16px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.2);
            transition: 0.3s;
        }
        .btn-modern:hover { transform: translateY(-2px); background: #0284c7; }

        /* Table Style */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 0.75rem; color: #64748b; padding: 12px 10px; text-transform: uppercase; border-bottom: 2px solid #f1f5f9; }
        td { padding: 15px 10px; border-bottom: 1px solid rgba(0,0,0,0.05); font-size: 0.9rem; font-weight: 600; }

        .badge-gratis { background: #10b981; color: white; padding: 4px 10px; border-radius: 8px; font-size: 0.7rem; font-weight: 800; }
        .status-badge { padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; }
        .status-lunas { background: #dcfce7; color: #166534; }
        .status-proses { background: #fef9c3; color: #854d0e; }

        /* Responsif */
        @media (max-width: 900px) {
            .container { grid-template-columns: 1fr; }
            body { padding: 10px; }
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="top-nav-wrapper">
        <div class="brand">PURE<span>STAY</span></div>
        <a href="javascript:void(0)" onclick="confirmLogout()" class="btn-logout-modern">Keluar Akun</a>
    </div>

    <div class="container">
        <div class="left-column">
            <div class="loyalitas-box">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.8rem; opacity: 0.8;">Program Loyalitas</span>
                    <span style="font-weight: 800;"><?php echo $jumlah_lama; ?>/5</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo ($jumlah_lama/5)*100; ?>%;"></div>
                </div>
                <p style="font-size: 0.75rem; margin: 0; opacity: 0.9;">
                    <?php echo ($jumlah_lama >= 5) ? "🎉 Selamat! Pesanan berikutnya GRATIS." : "Pesan ".(5 - $jumlah_lama)." kali lagi untuk dapat 1 gratis!"; ?>
                </p>
            </div>

            <div class="glass-card">
                <form action="beli_air_proses.php" method="POST">
                    <label>Metode Pengambilan</label>
                    <select name="metode" id="metode" onchange="toggleMaps()" required>
                        <option value="ambil_sendiri">🏠 Ambil Sendiri (Rp 10.000)</option>
                        <option value="antar">🚚 Antar Ke Rumah (Rp 50.000)</option>
                    </select>

                    <div id="section-maps" style="display:none;">
                        <label>Alamat Pengantaran</label>
                        <input type="text" name="alamat" placeholder="Contoh: Jl. Merdeka No. 12">
                        <div id="map"></div>
                        <input type="hidden" name="koordinat" id="koordinat">
                    </div>

                    <button type="submit" class="btn-modern">KONFIRMASI PESANAN</button>
                </form>
            </div>
        </div>

        <div class="right-column">
            <div class="glass-card" style="min-height: 100%;">
                <h3 style="margin-top: 0; font-size: 1.1rem; margin-bottom: 20px;">Riwayat Pesanan</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Metode</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $riwayat = mysqli_query($koneksi, "SELECT * FROM transaksi_air WHERE user_id = '$user_id' ORDER BY tanggal DESC LIMIT 10");
                            if (mysqli_num_rows($riwayat) > 0) {
                                while($row = mysqli_fetch_assoc($riwayat)) {
                                    $is_gratis = ($row['status_gratis'] == 1);
                                    ?>
                                    <tr>
                                        <td><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                                        <td style="color: #64748b; font-size: 0.8rem;"><?php echo ucfirst($row['metode_ambil']); ?></td>
                                        <td>
                                            <?php echo $is_gratis ? '<span class="badge-gratis">GRATIS</span>' : 'Rp '.number_format($row['harga'], 0, ',', '.'); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo ($row['status_pembayaran'] == 'Lunas' || $is_gratis) ? 'status-lunas' : 'status-proses'; ?>">
                                                <?php echo ($is_gratis) ? 'Selesai' : $row['status_pembayaran']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center; padding:30px; color:#94a3b8;'>Belum ada transaksi</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Keluar?',
            text: "Selesaikan pesananmu terlebih dahulu jika ada.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0ea5e9',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Keluar'
        }).then((result) => {
            if (result.isConfirmed) { window.location.href = 'logout.php'; }
        })
    }

    let map, marker;
    function initMap() {
        if (map) return;
        const pos = [-5.147, 119.432]; // Koordinat default (Makassar/Sesuaikan)
        map = L.map('map').setView(pos, 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        marker = L.marker(pos, {draggable: true}).addTo(map);
        
        document.getElementById('koordinat').value = pos[0] + ',' + pos[1];
        
        marker.on('dragend', function() {
            const latlng = marker.getLatLng();
            document.getElementById('koordinat').value = latlng.lat + ',' + latlng.lng;
        });
    }

    function toggleMaps() {
        const m = document.getElementById('metode').value;
        const s = document.getElementById('section-maps');
        if(m === 'antar') {
            s.style.display = 'block';
            setTimeout(() => { 
                initMap(); 
                map.invalidateSize(); 
            }, 200);
        } else {
            s.style.display = 'none';
        }
    }
</script>
</body>
</html>