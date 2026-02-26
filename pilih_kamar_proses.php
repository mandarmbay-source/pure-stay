<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id'])) { header('Location: login.php'); exit; }

$user_id = $_SESSION['id'];

// --- 1. FITUR RESET (Jika user ingin ganti kamar) ---
if (isset($_GET['reset'])) {
    mysqli_query($koneksi, "UPDATE users SET no_kamar = NULL WHERE id = '$user_id'");
    header('Location: user_kos_dashboard.php');
    exit;
}

// --- 2. PROSES PILIH KAMAR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['no_kamar'])) {
    $no_kamar = mysqli_real_escape_string($koneksi, $_POST['no_kamar']);

    // [TAMBAHAN KEAMANAN] Cek apakah user ini sebenarnya sudah punya kamar atau belum
    $cek_user = mysqli_query($koneksi, "SELECT no_kamar FROM users WHERE id = '$user_id'");
    $data_user = mysqli_fetch_assoc($cek_user);

    // Jika kolom no_kamar tidak kosong, artinya dia sudah pilih kamar sebelumnya
    if (!empty($data_user['no_kamar'])) {
        echo "<script>alert('Anda sudah memiliki kamar! Reset dulu jika ingin pindah.'); window.location.href='user_kos_dashboard.php';</script>";
        exit;
    }

    // [VALIDASI] Cek apakah kamar tersebut sudah diisi orang lain
    $cek_kamar = mysqli_query($koneksi, "SELECT id FROM users WHERE no_kamar = '$no_kamar'");
    if (mysqli_num_rows($cek_kamar) > 0) {
        echo "<script>alert('Maaf, Kamar $no_kamar baru saja diisi orang lain!'); window.location.href='user_kos_dashboard.php';</script>";
    } else {
        // Jika semua aman, baru jalankan UPDATE
        mysqli_query($koneksi, "UPDATE users SET no_kamar = '$no_kamar' WHERE id = '$user_id'");
        header('Location: user_kos_dashboard.php');
    }
}
?>