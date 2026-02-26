<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id'])) { exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti'])) {
    $user_id = $_SESSION['id'];
    $no_kamar = mysqli_real_escape_string($koneksi, $_POST['no_kamar']);
    
    // AMBIL METODE DARI POST (Pastikan di form HTML ada name="metode_bayar")
    // Jika tidak ada di form, kita beri nilai default saja
    $metode = isset($_POST['metode_bayar']) ? mysqli_real_escape_string($koneksi, $_POST['metode_bayar']) : 'Transfer';
    
    $nama_kos_default = "PureStay Residence"; 
    $tgl_bayar = date('Y-m-d H:i:s');
    $jumlah_bayar = 400000;

    $nama_file = $_FILES['bukti']['name'];
    $tmp_file  = $_FILES['bukti']['tmp_name'];
    $ekstensi  = pathinfo($nama_file, PATHINFO_EXTENSION);
    $nama_baru = "bukti_" . $user_id . "_" . time() . "." . $ekstensi;
    
    if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }

    if (move_uploaded_file($tmp_file, "uploads/" . $nama_baru)) {
        
        // Perbaikan: Gunakan $nama_baru agar nama file di DB sesuai dengan yang di folder uploads
        $query = "INSERT INTO data_kos (user_id, no_kamar, jumlah_bayar, metode_bayar, bukti_bayar, status, tanggal_bayar) 
                  VALUES ('$user_id', '$no_kamar', '$jumlah_bayar', '$metode', '$nama_baru', 'proses', NOW())";
        
        // Perbaikan: Nama variabel harus sama dengan yang di atas ($query)
        mysqli_query($koneksi, $query);
        
        $query_user = "UPDATE users SET status_bayar = 'proses', bukti_bayar = '$nama_baru' WHERE id = '$user_id'";
        mysqli_query($koneksi, $query_user);
        
        echo "<script>alert('Bukti terkirim! Menunggu verifikasi admin.'); window.location.href='user_kos_dashboard.php';</script>";
    } else {
        echo "<script>alert('Gagal mengunggah bukti. Pastikan file valid.'); window.location.href='user_kos_dashboard.php';</script>";
    }
}
?>