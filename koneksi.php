<?php
// 1. Cek apakah session sudah jalan, jika belum baru jalankan session_start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Konfigurasi Database
$host     = "localhost";
$user     = "root";
$password = "";
$database = "purestay";

$koneksi = mysqli_connect($host, $user, $password, $database);

// 3. Cek Koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>