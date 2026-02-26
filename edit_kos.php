<?php
session_start();
require 'koneksi.php';

// Proteksi Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header('Location: index.php'); 
    exit; 
}

// 1. Ambil Data (saat halaman dibuka)
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    // Gunakan LEFT JOIN agar data tetap muncul meski user_id bermasalah
    $query = mysqli_query($koneksi, "SELECT data_kos.*, users.nama_lengkap 
                                     FROM data_kos 
                                     LEFT JOIN users ON data_kos.user_id = users.id 
                                     WHERE data_kos.id = '$id'");
    $data = mysqli_fetch_assoc($query);

    if (!$data) {
        die("Data tidak ditemukan di database! Periksa ID: " . $id);
    }
}

// 2. Proses Update (saat tombol Simpan diklik)
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $user_id = $_POST['user_id'];
    $no_kamar = mysqli_real_escape_string($koneksi, $_POST['no_kamar']);
    $jumlah = mysqli_real_escape_string($koneksi, $_POST['jumlah_bayar']);
    $tgl_bayar = $_POST['tanggal_bayar'];
    $status = strtolower(mysqli_real_escape_string($koneksi, $_POST['status'])); // Paksa huruf kecil
    
    $jatuh_tempo = date('Y-m-d', strtotime('+1 month', strtotime($tgl_bayar)));

    // Update data_kos
    $update1 = mysqli_query($koneksi, "UPDATE data_kos SET 
                no_kamar = '$no_kamar',
                jumlah_bayar = '$jumlah',
                status = '$status', 
                tanggal_bayar = '$tgl_bayar',
                jatuh_tempo = '$jatuh_tempo'
                WHERE id = '$id'");

    // Update users (PENTING untuk dashboard penghuni)
    $update2 = mysqli_query($koneksi, "UPDATE users SET 
                status_bayar = '$status', 
                no_kamar = '$no_kamar',
                jatuh_tempo = '$jatuh_tempo' 
                WHERE id = '$user_id'");

    if ($update1 && $update2) {
        echo "<script>alert('Data Berhasil Diperbarui!'); window.location.href='riwayat_pembayaran_admin.php';</script>";
    } else {
        echo "Gagal Update: " . mysqli_error($koneksi);
    }
}
?>