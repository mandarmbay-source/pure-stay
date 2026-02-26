<?php require 'koneksi.php'; if(!isset($_SESSION['username'])) header('Location: index.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard User</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div style="padding: 20px; display:flex; justify-content:space-between; align-items:center;" class="glass-card">
        <h3>Halo, <?php echo $_SESSION['nama']; ?></h3>
        <button onclick="confirmLogout()" class="btn-modern" style="width:auto; background:#ff4757;">Logout</button>
    </div>

    <div class="dashboard-grid">
        <div class="glass-card text-center">
            <h4>Layanan Air</h4>
            <p>Pesan Galon (Mobil Pickup / Ambil)</p>
            <a href="form_bayar_air.php" class="btn-modern" style="text-decoration:none; display:block;">Pesan Sekarang</a>
        </div>
        <div class="glass-card text-center">
            <h4>Layanan Kos</h4>
            <p>Cek Pembayaran & Jatuh Tempo</p>
            <a href="user_kos_dashboard.php" class="btn-modern" style="text-decoration:none; display:block; background:#6366f1;">Cek Kos</a>
        </div>
    </div>

    <script>
    function confirmLogout() {
        Swal.fire({ title: 'Yakin Logout?', text: "Anda harus login kembali nanti", icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Logout' })
        .then((result) => { if (result.isConfirmed) window.location.href='logout.php'; });
    }
    </script>
</body>
</html>