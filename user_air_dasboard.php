<?php
session_start();
require 'koneksi.php'; 

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['id'];

$jumlah_lama = 0; 
if (isset($koneksi)) {
    $sql = "SELECT hitungan_loyalitas FROM users WHERE id = '$user_id'";
    $query_user = mysqli_query($koneksi, $sql);
    $data_user = mysqli_fetch_assoc($query_user);

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
            display: flex;
            justify-content: center;
        }

        .wrapper {
            width: 100%;
            max-width: 600px;
            padding: 30px 20px;
        }

        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .brand { font-weight: 800; font-size: 1.3rem; letter-spacing: -1px; }
        .brand span { color: var(--primary); }

        .btn-exit {
            text-decoration: none;
            background: var(--white);
            color: #ef4444;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            border: 1px solid #fee2e2;
            cursor: pointer;
        }

        .card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

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

        .counter {
            background: rgba(255,255,255,0.2);
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: 800;
            backdrop-filter: blur(4px);
        }

        /* --- CSS PILIHAN METODE TANPA BOLA --- */
        .method-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 15px;
        }

        .method-card input[type="radio"] {
            display: none; /* Sembunyikan bola radio */
        }

        .method-card { cursor: pointer; }

        .method-content {
            background: var(--white);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px 10px;
            text-align: center;
            transition: 0.2s;
        }

        .method-content .title { display: block; font-weight: 700; font-size: 0.9rem; color: var(--text-main); }
        .method-content .price { display: block; font-size: 0.8rem; color: var(--text-muted); }

        .method-card input[type="radio"]:checked + .method-content {
            border-color: var(--primary);
            background-color: var(--primary-light);
        }

        .method-card input[type="radio"]:checked + .method-content .title { color: var(--primary); }

        /* --- MAPS & FORM --- */
        #map { height: 200px; border-radius: 10px; margin-bottom: 15px; border: 2px solid #e2e8f0; }
        
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        .btn-confirm {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        /* Tabel */
        .table-container { max-height: 250px; overflow-y: auto; border-radius: 8px; border: 1px solid #f1f5f9; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 0.75rem; padding: 12px 8px; color: var(--text-muted); text-transform: uppercase; border-bottom: 2px solid #f1f5f9; }
        td { padding: 12px 8px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; font-weight: 600; }
        .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; }
        .status-lunas { background: #dcfce7; color: #166534; }
        .status-proses { background: #fef9c3; color: #854d0e; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="top-nav">
        <div class="brand">PURE<span>STAY</span></div>
        <a href="javascript:void(0)" onclick="confirmLogout()" class="btn-exit">Keluar</a>
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
            <label style="display:block; margin-bottom:8px; font-weight:700; color:var(--text-muted);">Metode Pengambilan</label>
            <div class="method-grid">
                <label class="method-card">
                    <input type="radio" name="metode" value="ambil_sendiri" onchange="toggleMaps()" checked required>
                    <div class="method-content">
                        <span class="title">Ambil Sendiri</span>
                        <span class="price">Rp 10.000</span>
                    </div>
                </label>

                <label class="method-card">
                    <input type="radio" name="metode" value="antar" onchange="toggleMaps()" required>
                    <div class="method-content">
                        <span class="title">Antar ke Rumah</span>
                        <span class="price">Rp 50.000</span>
                    </div>
                </label>
            </div>

            <div id="section-maps" style="display:none;">
                <label style="display:block; margin-bottom:8px; font-weight:700; color:var(--text-muted);">Alamat Lengkap</label>
                <input type="text" name="alamat" placeholder="Jl. Nama Jalan No. 00">
                <div id="map"></div>
                <input type="hidden" name="koordinat" id="koordinat">
            </div>

            <button type="submit" class="btn-confirm">Pesan Sekarang</button>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-top:0; font-size: 1rem;">📜 Riwayat Pesanan</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>Tanggal</th><th>Metode</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php
                    $riwayat = mysqli_query($koneksi, "SELECT * FROM transaksi_air WHERE user_id = '$user_id' ORDER BY tanggal DESC");
                    while($row = mysqli_fetch_assoc($riwayat)) {
                        $is_gratis = ($row['status_gratis'] == 1);
                        echo "<tr>
                            <td>".date('d M', strtotime($row['tanggal']))."</td>
                            <td>".($row['metode_ambil'] == 'antar' ? 'Antar' : 'Ambil')."</td>
                            <td>".($is_gratis ? 'GRATIS' : 'Rp '.number_format($row['harga']))."</td>
                            <td><span class='status-badge ".($row['status_pembayaran']=='Lunas'?'status-lunas':'status-proses')."'>".$row['status_pembayaran']."</span></td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map, marker;

    function initMap() {
        if (map) return;
        const pos = [-5.147, 119.432]; // Koordinat Makassar/Default
        map = L.map('map').setView(pos, 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        marker = L.marker(pos, {draggable: true}).addTo(map);
        marker.on('dragend', () => {
            const latlng = marker.getLatLng();
            document.getElementById('koordinat').value = latlng.lat + ',' + latlng.lng;
        });
    }

    // FUNGSI TOGGLE YANG DIPERBAIKI (Hanya satu fungsi agar tidak bentrok)
    function toggleMaps() {
        const selected = document.querySelector('input[name="metode"]:checked').value;
        const section = document.getElementById('section-maps');

        if (selected === 'antar') {
            section.style.display = 'block';
            // Pastikan initMap dipanggil dan ukuran map di-refresh
            setTimeout(() => { 
                initMap(); 
                map.invalidateSize(); 
            }, 200);
        } else {
            section.style.display = 'none';
        }
    }

    function confirmLogout() {
        Swal.fire({
            title: 'Yakin mau keluar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Keluar'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = 'admin_dashboard.php';
        });
    }
</script>
</body>
</html>