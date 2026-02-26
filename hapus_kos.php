<?php
session_start();
require 'koneksi.php';

// Proteksi Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

if(isset($_GET['id'])){
    $id = $_GET['id'];
    
    // Pastikan nama tabel adalah 'data_kos' sesuai dengan query di riwayat_kos.php
    $query = "DELETE FROM data_kos WHERE id = $id";
    
    if(mysqli_query($koneksi, $query)) {
        header("Location: riwayat_kos.php?status=success");
    } else {
        echo "Gagal menghapus: " . mysqli_error($koneksi);
    }
} else {
    header("Location: riwayat_kos.php");
}
exit;