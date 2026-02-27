<?php
session_start();
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $login_type = isset($_POST['login_type']) ? $_POST['login_type'] : 'user';

    if ($login_type !== 'admin' && $login_type !== 'user') {
        $login_type = 'user';
    }

    // Header SweetAlert
    echo '<!DOCTYPE html><html><head><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script></head><body style="font-family:sans-serif; background:#f0f9ff;">';

    $login_berhasil = false;
    $user_data = [];

    if ($login_type === 'admin') {
        if ($username === 'admin123' && $password === '131276') {
            $login_berhasil = true;
            $user_data = ['id' => '0', 'username' => 'admin123', 'nama_lengkap' => 'Administrator', 'role' => 'admin'];
        } else {
            echo "<script>Swal.fire('Gagal', 'Username/Password Admin salah!', 'error').then(() => { history.back(); });</script>";
            exit;
        }
    } else {
        $specific_role = isset($_POST['specific_role']) ? $_POST['specific_role'] : '';
        if (empty($specific_role)) {
            echo "<script>Swal.fire('Peringatan', 'Pilih tipe akun!', 'warning').then(() => { history.back(); });</script>";
            exit;
        }

        $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND role='$specific_role'");
        $data = mysqli_fetch_assoc($query);

        if ($data && password_verify($password, $data['password'])) {
            $login_berhasil = true;
            $user_data = $data;
        } else {
            echo "<script>Swal.fire('Gagal', 'Akun tidak ditemukan atau password salah!', 'error').then(() => { history.back(); });</script>";
            exit;
        }
    }

    if ($login_berhasil) {
        $_SESSION['id'] = $user_data['id'];
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['nama'] = $user_data['nama_lengkap'];
        $_SESSION['role'] = $user_data['role'];

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil!',
                text: 'Selamat datang, " . $user_data['nama_lengkap'] . "',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                " . ($user_data['role'] === 'admin' ? "window.location.href = 'admin_dashboard.php';" : 
                    ($user_data['role'] === 'user_air' ? "window.location.href = 'user_air_dasboard.php';" : 
                    ($user_data['role'] === 'user_kos' ? "window.location.href = 'user_kos_dashboard.php';" : "window.location.href = 'index.php';"))) . "
            });
        </script>";
    }
    echo '</body></html>';
}