<?php
session_start();
require 'koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

function hitungDataAktif($koneksi, $tabel) {
    if($tabel == 'transaksi_air') {
        $query = mysqli_query($koneksi, "SELECT id FROM transaksi_air WHERE status_pesanan != 'Selesai'");
    } else if($tabel == 'data_kos') { 
        $query = mysqli_query($koneksi, "SELECT id FROM data_kos");
    } else {
        $query = mysqli_query($koneksi, "SELECT id FROM $tabel");
    }
    
    if ($query) {
        return mysqli_num_rows($query);
    }
    return 0; 
}

$total_air = hitungDataAktif($koneksi, 'transaksi_air'); 
$total_kos = hitungDataAktif($koneksi, 'data_kos'); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PureStay</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc; /* Latar belakang soft */
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center; /* Memastikan semua konten ke tengah */
        }

        /* Navigasi Atas - Dibatasi lebarnya agar tidak terlalu pinggir */
        .top-nav {
            display: flex;
            justify-content: flex-end;
            width: 100%;
            max-width: 1100px; /* Batas lebar maksimal */
            padding: 20px;
            box-sizing: border-box;
        }

        .btn-logout-native {
            background: #ef4444;
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.8rem;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
        }

        .btn-logout-native:hover {
            background: #dc2626;
            transform: scale(1.05);
        }

        /* Container Utama - Dibuat ke tengah */
        .dashboard-container {
            width: 100%;
            max-width: 1100px; /* Lebar yang nyaman di mata */
            margin: 0 auto;
            padding: 0 20px 40px 20px;
            box-sizing: border-box;
        }

        .welcome-text {
            text-align: center; /* Teks selamat datang di tengah */
            margin-bottom: 40px;
        }

        /* Penyesuaian Grid agar seimbang */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            justify-content: center;
        }

        .glass-card {
            background: white;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03);
            transition: transform 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
        }

        @media (max-width: 600px) {
            .top-nav {
                justify-content: center;
            }
            .btn-logout-native {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="top-nav">
        <button onclick="konfirmasiKeluar()" class="btn-logout-native">
            🚪 KELUAR AKUN
        </button>
    </div>

    <div class="dashboard-container">
        <div class="welcome-text">
            <h2 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin-bottom: 10px;">Admin PureStay</h2>
            <p style="color: #64748b; font-size: 1.1rem;">Kelola operasional harian kos dan distribusi air Anda secara terpusat.</p>
        </div>

        <div class="dashboard-grid">
            <div class="glass-card" style="padding: 40px;">
                <div style="font-size: 2.5rem; margin-bottom: 15px;">💧</div>
                <h3 style="font-weight: 800; margin-bottom: 5px;">Manajemen Air</h3>
                <p style="color: #64748b; margin-bottom: 25px;">Data pesanan mobil pickup aktif</p>
                
                <div style="background: #f1f5f9; padding: 20px; border-radius: 16px; margin-bottom: 25px;">
                    <div class="stat-number" style="font-size: 2.5rem; font-weight: 800; color: #2563eb;"><?php echo $total_air; ?></div>
                    <p style="font-size: 0.85rem; font-weight: 700; color: #475569; margin: 0;">Transaksi Perlu Diproses</p>
                </div>

                <div class="btn-group" style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="admin_air.php" class="btn-modern" style="background: #2ecc71; color: white; padding: 15px; text-align: center; border-radius: 12px; text-decoration: none; font-weight: 700;">
                        🚚 Kelola Pesanan Aktif
                    </a>
                    <a href="riwayat_air.php" class="btn-outline" style="padding: 15px; text-align: center; border-radius: 12px; text-decoration: none; font-weight: 700; border: 2px solid #e2e8f0; color: #475569;">
                        📜 Riwayat Pembelian Air
                    </a>
                </div>
            </div>

            <div class="glass-card" style="padding: 40px;">
                <div style="font-size: 2.5rem; margin-bottom: 15px;">🏠</div>
                <h3 style="font-weight: 800; margin-bottom: 5px;">Penghuni Kos</h3>
                <p style="color: #64748b; margin-bottom: 25px;">Status hunian & pembayaran sewa</p>

                <div style="background: #f1f5f9; padding: 20px; border-radius: 16px; margin-bottom: 25px;">
                    <div class="stat-number" style="font-size: 2.5rem; font-weight: 800; color: #2563eb;"><?php echo $total_kos; ?></div>
                    <p style="font-size: 0.85rem; font-weight: 700; color: #475569; margin: 0;">Total Record Pembayaran</p>
                </div>

                <div class="btn-group" style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="admin_kos.php" class="btn-modern" style="background: #2ecc71; color: white; padding: 15px; text-align: center; border-radius: 12px; text-decoration: none; font-weight: 700;">
                        🔑 Kelola Hunian & Kamar
                    </a>
                    <a href="riwayat_kos.php" class="btn-outline" style="padding: 15px; text-align: center; border-radius: 12px; text-decoration: none; font-weight: 700; border: 2px solid #e2e8f0; color: #475569;">
                        📜 Riwayat Pembayaran Kos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function konfirmasiKeluar() {
        Swal.fire({
            title: 'Yakin mau keluar?',
            text: "Sesi admin akan berakhir.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            borderRadius: '20px'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout_aksi.php';
            }
        })
    }
    </script>
</body>
</html>