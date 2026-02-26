<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil data dari form dan bersihkan (Security)
    $nama_lengkap = $koneksi->real_escape_string($_POST['nama_lengkap']);
    $username     = $koneksi->real_escape_string($_POST['username']);
    $password     = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $no_hp        = $koneksi->real_escape_string($_POST['no_hp']); // BARU: Menangkap No HP
    $role         = $_POST['role']; // user_air atau user_kos

    // 2. Cek apakah username sudah ada
    $cek = $koneksi->query("SELECT * FROM users WHERE username='$username'");
    
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><body style="font-family:sans-serif;">';

    if ($cek->num_rows > 0) {
        echo "<script>
            Swal.fire('Gagal', 'Username sudah digunakan!', 'error').then(() => { history.back(); });
        </script>";
    } else {
        // 3. Masukkan data ke database (Termasuk kolom no_hp)
        $query = "INSERT INTO users (nama_lengkap, username, password, no_hp, role) 
                  VALUES ('$nama_lengkap', '$username', '$password', '$no_hp', '$role')";
        
        if ($koneksi->query($query)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil',
                    text: 'Akun Anda sudah terdaftar. Silakan Login!',
                    showConfirmButton: true
                }).then(() => { window.location.href = 'login.php'; });
            </script>";
        } else {
            // Jika error, cek apakah kolom no_hp benar-benar sudah ada di database
            echo "<script>Swal.fire('Gagal', 'Sistem error: " . $koneksi->error . "', 'error');</script>";
        }
    }
    echo '</body>';
}
?>