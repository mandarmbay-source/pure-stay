<?php
session_start();
require 'koneksi.php';

if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Proses Hapus
    $delete = mysqli_query($koneksi, "DELETE FROM transaksi_air WHERE id = '$id'");
    
    if($delete) {
        header("Location: admin_air.php?pesan=hapus_berhasil");
    } else {
        echo "Gagal menghapus: " . mysqli_error($koneksi);
    }
} else {
    header("Location: admin_air.php");
}
?>