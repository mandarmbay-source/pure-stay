<?php
session_start();
require 'koneksi.php'; 

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['id'];

// --- PERBAIKAN LOGIKA DATABASE (ANTI ERROR) ---
$jumlah_lama = 0; // Set default 0 di awal
if (isset($koneksi)) {
    $sql = "SELECT hitungan_loyalitas FROM users WHERE id = '$user_id'";
    $query_user = mysqli_query($koneksi, $sql);

   // 3. Cek apakah kolomnya ada dan tidak null
    if (isset($data_user['hitungan_loyalitas'])) {
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
    <link rel="stylesheet" href="air.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #eff6ff;
            --bg: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --white: #ffffff;
            --radius: 16px;
            --shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
        }

        .wrapper {
            width: 100%;
            max-width: 600px; /* Diperkecil sedikit agar lebih compact */
            padding: 30px 20px;
        }

        /* Top Navigation */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .brand {
            font-weight: 800;
            font-size: 1.3rem;
            letter-spacing: -1px;
        }

        .brand span { color: var(--primary); }

        .btn-exit {
            text-decoration: none;
            background: var(--white);
            color: #ef4444;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: all 0.2s;
            border: 1px solid #fee2e2;
            cursor: pointer;
        }

        .btn-exit:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
        }

        /* Card */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            border: 1px solid rgba(0,0,0,0.03);
        }

        /* Loyalty Section */
        .loyalty-banner {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: var(--radius);
            padding: 18px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .loyalty-info h4 { margin: 0; font-size: 0.75rem; opacity: 0.8; text-transform: uppercase; }
        .loyalty-info p { margin: 4px 0 0; font-weight: 700; font-size: 1rem; }

        .counter {
            background: rgba(255,255,255,0.2);
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 1.1rem;
            backdrop-filter: blur(4px);
        }

        label { display: block; font-weight: 700; font-size: 0.85rem; margin-bottom: 8px; color: var(--text-muted); }

        select, input {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            font-family: inherit;
            font-size: 0.95rem;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        select:focus, input:focus { border-color: var(--primary); outline: none; background: var(--primary-light); }

        .btn-confirm {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-confirm:hover { background: #1d4ed8; }

        #map { height: 200px; border-radius: 10px; margin-bottom: 15px; border: 2px solid #e2e8f0; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 0.75rem; color: var(--text-muted); padding: 12px 8px; border-bottom: 2px solid #f1f5f9; text-transform: uppercase; }
        td { padding: 12px 8px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; font-weight: 600; }

        .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; }
        .status-lunas { background: #dcfce7; color: #166534; }
        .status-proses { background: #fef9c3; color: #854d0e; }
        .status-free { background: var(--primary-light); color: var(--primary); border: 1px dashed var(--primary); }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="top-nav">
        <div class="brand">PURE<span>STAY</span></div>
        <a href="javascript:void(0)" onclick="confirmLogout()" class="btn-exit">Keluar Dashboard</a>
    </div>

    <div class="card">
        <div class="loyalty-banner">
            <div class="loyalty-info">
                <h4>Program Loyalitas</h4>
                <p><?php echo ($jumlah_lama >= 5) ? "🎉 Pesanan Gratis Tersedia!" : (5 - $jumlah_lama) . " pesanan lagi"; ?></p>
            </div>
            <div class="counter"><?php echo $jumlah_lama; ?>/5</div>
        </div>

        <form action="beli_air_proses.php" method="POST">
            <label>Metode Pengambilan</label>
            <select name="metode" id="metode" onchange="toggleMaps()" required>
                <option value="ambil_sendiri">🏠 Ambil Sendiri (Rp 10.000)</option>
                <option value="antar">🚚 Antar Ke Rumah (Rp 50.000)</option>
            </select>

            <div id="section-maps" style="display:none;">
                <label>Alamat Lengkap</label>
                <input type="text" name="alamat" placeholder="Jl. Nama Jalan No. 00">
                <div id="map"></div>
                <input type="hidden" name="koordinat" id="koordinat">
            </div>

            <button type="submit" class="btn-confirm">Pesan Sekarang</button>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-top:0; font-size: 1rem;">📜 Riwayat Pesanan</h3>
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
                $riwayat = mysqli_query($koneksi, "SELECT * FROM transaksi_air WHERE user_id = '$user_id' ORDER BY tanggal DESC LIMIT 5");
                if (mysqli_num_rows($riwayat) > 0) {
                    while($row = mysqli_fetch_assoc($riwayat)) {
                        $is_gratis = ($row['status_gratis'] == 1);
                        ?>
                        <tr>
                            <td><?php echo date('d M', strtotime($row['tanggal'])); ?></td>
                            <td style="color: var(--text-muted); font-size: 0.8rem;"><?php echo ($row['metode_ambil'] == 'antar' ? 'Antar' : 'Ambil sendiri'); ?></td>
                            <td><?php echo ($is_gratis ? '<span class="status-badge status-free">GRATIS</span>' : 'Rp '.number_format($row['harga'], 0, ',', '.')); ?></td>
                            <td>
                                <span class="status-badge <?php echo ($is_gratis || $row['status_pembayaran'] == 'Lunas' ? 'status-lunas' : 'status-proses'); ?>">
                                    <?php echo ($is_gratis ? 'Selesai' : $row['status_pembayaran']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; color:gray;'>Belum ada transaksi.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // JS Konfirmasi Keluar
    function confirmLogout() {
        Swal.fire({
            title: 'Yakin mau keluar?',
            text: "Pastikan pesanan kamu sudah disimpan ya!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'admin_dashboard.php';
            }
        })
    }

    let map, marker;

    function initMap() {
        if (map) return;
        const pos = [-5.147, 119.432];
        map = L.map('map').setView(pos, 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        marker = L.marker(pos, {draggable: true}).addTo(map);
        marker.on('dragend', () => {
            const latlng = marker.getLatLng();
            document.getElementById('koordinat').value = latlng.lat + ',' + latlng.lng;
        });
    }

    function toggleMaps() {
        const m = document.getElementById('metode').value;
        const s = document.getElementById('section-maps');
        if(m === 'antar') {
            s.style.display = 'block';
            setTimeout(() => { initMap(); map.invalidateSize(); }, 200);
        } else {
            s.style.display = 'none';
        }
    }
</script>
</body>
</html>