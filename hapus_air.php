<?php
session_start();
require 'koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $delete = mysqli_query($koneksi, "DELETE FROM transaksi_air WHERE id = $id");

    if($delete) {
        header("Location: riwayat_air.php?msg=success");
    } else {
        header("Location: riwayat_air.php?msg=error");
    }
} else {
    header("Location: riwayat_air.php");
}
exit;