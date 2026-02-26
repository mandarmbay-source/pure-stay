<?php
require 'koneksi.php';
$id_transaksi = $_GET['id'];

// Ambil data transaksi untuk mendapatkan user_id
$query_transaksi = mysqli_query($koneksi, "SELECT user_id FROM data_kos WHERE id = '$id_transaksi'");
$data = mysqli_fetch_assoc($query_transaksi);
$user_id = $data['user_id'];

// 1. Update status di tabel data_kos
mysqli_query($koneksi, "UPDATE data_kos SET status = 'lunas' WHERE id = '$id_transaksi'");

// 2. Update status di tabel users agar di dashboard user juga berubah
mysqli_query($koneksi, "UPDATE users SET status_bayar = 'lunas' WHERE id = '$user_id'");

header('Location: riwayat_kos.php');
?>