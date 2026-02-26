<?php
session_start();
require 'koneksi.php';

// Menghapus hanya yang statusnya belum Selesai (sesuai yang tampil di halaman kelola)
$query = "DELETE FROM transaksi_air WHERE status_pesanan != 'Selesai'";

if(mysqli_query($koneksi, $query)) {
    header("Location: admin_air.php?pesan=kosongkan_berhasil");
} else {
    echo "Gagal mengosongkan: " . mysqli_error($koneksi);
}
?>