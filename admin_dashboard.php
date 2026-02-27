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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --bg-soft: #f8fafc;
            --glass: white;
        }

        body {
            background-color: var(--bg-soft);
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* --- NAVIGATION & HAMBURGER --- */
        .header-nav {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            padding: 20px 30px;
            box-sizing: border-box;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .hamburger-menu {
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 6px;
            z-index: 1100;
            padding: 10px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .hamburger-menu span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: var(--text-dark);
            border-radius: 3px;
            transition: 0.3s;
        }

        /* Animasi Hamburger ke X */
        .hamburger-menu.active span:nth-child(1) { transform: translateY(9px) rotate(45deg); }
        .hamburger-menu.active span:nth-child(2) { opacity: 0; }
        .hamburger-menu.active span:nth-child(3) { transform: translateY(-9px) rotate(-45deg); }

        /* SIDEBAR NAVIGATION */
        .side-nav {
            position: fixed;
            top: 0;
            right: -100%;
            width: 300px;
            height: 100%;
            background: white;
            box-shadow: -10px 0 30px rgba(0,0,0,0.05);
            z-index: 1050;
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 100px 25px 40px 25px;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .side-nav.active { right: 0; }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(4px);
            display: none;
            z-index: 1040;
        }

        .overlay.active { display: block; }

        .menu-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            margin-bottom: 15px;
            font-weight: 800;
        }

        .menu-item {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
            padding: 16px;
            border-radius: 14px;
            transition: 0.3s;
            margin-bottom: 5px;
            display: block;
        }

        .menu-item:hover { background: #f1f5f9; color: var(--primary); }

        .menu-item.logout {
            margin-top: auto;
            color: var(--danger);
            background: #fff1f2;
            text-align: center;
            border: 1px solid #ffe4e6;
        }

        .menu-item.logout:hover { background: var(--danger); color: white; }

        /* --- DASHBOARD CONTENT --- */
        .dashboard-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px 60px 20px;
            text-align: center;
        }

        .welcome-text h2 {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .welcome-text p {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-bottom: 50px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 30px;
        }

        .glass-card {
            background: white;
            border-radius: 28px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 15px 35px rgba(0,0,0,0.03);
            transition: 0.3s;
            padding: 40px;
            text-align: left;
        }

        .glass-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.06); }

        .stat-box {
            background: #f1f5f9;
            padding: 24px;
            border-radius: 20px;
            margin: 25px 0;
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-main {
            background: #2ecc71;
            color: white;
            padding: 16px;
            text-align: center;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-main:hover { background: #27ae60; transform: scale(1.02); }

        .btn-outline {
            padding: 16px;
            text-align: center;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 700;
            border: 2px solid #e2e8f0;
            color: var(--text-muted);
            transition: 0.3s;
        }

        .btn-outline:hover { background: #f8fafc; color: var(--text-dark); border-color: #cbd5e1; }

        @media (max-width: 600px) {
            .welcome-text h2 { font-size: 1.8rem; }
            .dashboard-grid { grid-template-columns: 1fr; }
            .glass-card { padding: 30px; }
        }
    </style>
</head>
<body>

    <div class="header-nav">
        <div class="hamburger-menu" id="hamburger" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>
    
    <nav class="side-nav" id="sideNav">
        <div class="menu-title">Menu Utama</div>
        <a href="admin_air.php" class="menu-item">Manajemen Air</a>
        <a href="admin_kos.php" class="menu-item">Manajemen Kos</a>
        
        <a href="javascript:void(0)" onclick="konfirmasiKeluar()" class="menu-item logout">
            KELUAR AKUN
        </a>
    </nav>

    <div class="dashboard-container">
        <div class="welcome-text">
            <h2>Admin PureStay</h2>
            <p>Kelola operasional harian kos dan distribusi air secara terpusat.</p>
        </div>

        <div class="dashboard-grid">
            <div class="glass-card">
                <div style="font-size: 2.5rem; margin-bottom: 15px;">💧</div>
                <h3 style="font-weight: 800; margin: 0;">Manajemen Air</h3>
                <p style="color: var(--text-muted); margin: 5px 0 0 0;">Pesanan armada aktif</p>
                
                <div class="stat-box">
                    <div class="stat-number"><?php echo $total_air; ?></div>
                    <p style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin: 5px 0 0 0;">Transaksi Perlu Diproses</p>
                </div>

                <div class="btn-group">
                    <a href="admin_air.php" class="btn-main">Kelola Pesanan Aktif</a>
                    <a href="riwayat_air.php" class="btn-outline">Riwayat Pembelian</a>
                </div>
            </div>

            <div class="glass-card">
                <div style="font-size: 2.5rem; margin-bottom: 15px;">🏠</div>
                <h3 style="font-weight: 800; margin: 0;">Penghuni Kos</h3>
                <p style="color: var(--text-muted); margin: 5px 0 0 0;">Status hunian & sewa</p>

                <div class="stat-box">
                    <div class="stat-number"><?php echo $total_kos; ?></div>
                    <p style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin: 5px 0 0 0;">Total Data Pembayaran</p>
                </div>

                <div class="btn-group">
                    <a href="admin_kos.php" class="btn-main">Kelola Hunian</a>
                    <a href="riwayat_kos.php" class="btn-outline">Riwayat Sewa</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleMenu() {
            document.getElementById('hamburger').classList.toggle('active');
            document.getElementById('sideNav').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }

        function konfirmasiKeluar() {
            // Tutup menu side nav dulu agar estetik
            toggleMenu();

            Swal.fire({
                title: 'Yakin mau keluar?',
                text: "Sesi admin akan berakhir sekarang.",
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