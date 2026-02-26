<?php
session_start();
require 'koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header('Location: index.php'); 
    exit; 
}

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Update status menjadi Lunas
    $update = mysqli_query($koneksi, "UPDATE transaksi_air SET status_pembayaran = 'Lunas' WHERE id = '$id'");
    
    if($update) {
        header('Location: riwayat_air.php?status=sukses');
    } else {
        echo "Gagal mengonfirmasi.";
    }
}
?>