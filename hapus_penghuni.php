<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // 1. Update tabel users: Kosongkan nomor kamar agar kamar kembali 'Menyala' (Tersedia)
    $query_update = "UPDATE users SET no_kamar = NULL WHERE id = '$user_id'";
    
    if (mysqli_query($koneksi, $query_update)) {
        // 2. Opsional: Hapus riwayat pembayaran jika ingin benar-benar bersih
        // mysqli_query($koneksi, "DELETE FROM data_kos WHERE user_id = '$user_id'");

        header('Location: admin_kos.php?status=success_hapus');
    } else {
        header('Location: admin_kos.php?status=error');
    }
}
?>