<?php
session_start();
require 'koneksi.php';

// Proteksi Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

// Gunakan TRUNCATE untuk mengosongkan tabel dan mereset ID ke 1 kembali
$query = "TRUNCATE TABLE data_kos";

if(mysqli_query($koneksi, $query)) {
    header("Location: riwayat_kos.php?status=cleared");
} else {
    echo "Gagal mengosongkan tabel: " . mysqli_error($koneksi);
}
exit;