<?php
session_start();
require 'koneksi.php';

// Proteksi: Pastikan hanya admin yang bisa memproses
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_transaksi = mysqli_real_escape_string($koneksi, $_POST['id_transaksi']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    // Update status di database
    $query = mysqli_query($koneksi, "UPDATE transaksi_air SET status_pesanan = '$status' WHERE id = '$id_transaksi'");

    if ($query) {
        // Otomatis kembali ke halaman sebelumnya dengan parameter sukses
        header("Location: admin_air.php?status=updated");
        exit;
    } else {
        die("Gagal update status: " . mysqli_error($koneksi));
    }
} else {
    // Jika akses file ini tanpa POST, kembalikan ke dashboard
    header('Location: admin_air.php');
    exit;
}
?>