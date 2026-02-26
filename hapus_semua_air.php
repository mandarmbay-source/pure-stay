<?php
session_start();
require 'koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

// Gunakan TRUNCATE untuk mereset auto-increment ID kembali ke 1
$truncate = mysqli_query($koneksi, "TRUNCATE TABLE transaksi_air");

if($truncate) {
    header("Location: riwayat_air.php?msg=cleared");
} else {
    header("Location: riwayat_air.php?msg=error");
}
exit;