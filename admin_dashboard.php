<?php
session_start();
require 'koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

/**
 * Fungsi untuk menghitung jumlah baris data secara akurat
 */
function hitungDataAktif($koneksi, $tabel) {
    // 1. Perbaikan: Sesuaikan nama tabel dengan database Anda
    // Berdasarkan kode riwayat Anda, tabelnya adalah 'data_kos' bukan 'pembayaran_kos'
    if($tabel == 'transaksi_air') {
        $query = mysqli_query($koneksi, "SELECT id FROM transaksi_air WHERE status_pesanan != 'Selesai'");
    } else if($tabel == 'data_kos') { 
        // Hitung semua riwayat pembayaran kos
        $query = mysqli_query($koneksi, "SELECT id FROM data_kos");
    } else {
        $query = mysqli_query($koneksi, "SELECT id FROM $tabel");
    }
    
    if ($query) {
        return mysqli_num_rows($query);
    }
    return 0; 
}

// 2. PANGGIL DENGAN NAMA TABEL YANG BENAR
$total_air = hitungDataAktif($koneksi, 'transaksi_air'); 
$total_kos = hitungDataAktif($koneksi, 'data_kos'); // Diubah dari pembayaran_kos ke data_kos
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PureStay</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="logout.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
   
</head>
<body>

    <div class="top-nav">
        <a href="javascript:void(0)" onclick="konfirmasiKeluar()" class="btn-logout">
            <span>KELUAR AKUN</span>
        </a>
    </div>

    <div class="dashboard-container">
        <div class="welcome-text">
            <h2>Admin PureStay</h2>
            <p style="color: var(--text-muted);">Kelola operasional harian kos dan distribusi air Anda.</p>
        </div>

        <div class="dashboard-grid">
            <div class="glass-card" style="text-align: left; padding: 40px;">
                <div style="font-size: 2rem;">💧</div>
                <h3>Manajemen Air</h3>
                <p style="color: var(--text-muted);">Data pesanan mobil pickup</p>
                
                <div class="stat-number"><?php echo $total_air; ?></div>
                <p style="font-size: 0.9rem; font-weight: 600;">Total Transaksi Air</p>

                <div class="btn-group">
                    <a href="admin_air.php" class="btn-modern btn-register" style="min-height: auto; padding: 18px; background: #2ecc71;">
                        🚚 Kelola Pesanan Aktif
                    </a>
                    <a href="riwayat_air.php" class="btn-outline" >
                        📜 Riwayat Pembelian Air
                    </a>
                </div>
            </div>

            <div class="glass-card" style="text-align: left; padding: 40px;">
                <div style="font-size: 2rem;">🏠</div>
                <h3>Penghuni Kos</h3>
                <p style="color: var(--text-muted);">Status kamar & pembayaran</p>

                <div class="stat-number"><?php echo $total_kos; ?></div>
                <p style="font-size: 0.9rem; font-weight: 600;">Total Pembayaran Kos</p>

                <div class="btn-group">
                    <a href="admin_kos.php" class="btn-modern btn-register" style="min-height: auto; padding: 18px; background: #2ecc71;">
                        🔑 Kelola Hunian & Kamar
                    </a>
                    <a href="riwayat_kos.php" class="btn-outline">
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
            confirmButtonColor: '#4f8cf0', 
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