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

    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><body style="font-family:sans-serif; background:#f0f9ff;">';

    $login_berhasil = false;
    $user_data = [];

    // 1. LOGIKA LOGIN ADMIN (USERNAME & PASSWORD TETAP)
    if ($login_type === 'admin') {
        // Cek apakah username dan password sesuai dengan ketentuan statis
        if ($username === 'admin123' && $password === '131276') {
            $login_berhasil = true;
            // Kita set data manual untuk session karena tidak ambil dari DB
            $user_data = [
                'id' => '0', // ID khusus admin
                'username' => 'admin123',
                'nama_lengkap' => 'Administrator',
                'role' => 'admin'
            ];
        } else {
            echo "<script>
                Swal.fire('Gagal', 'Username atau Password Admin salah!', 'error').then(() => { history.back(); });
            </script>";
            exit;
        }
    } 
    
    // 2. LOGIKA LOGIN USER (TETAP MENGGUNAKAN DATABASE)
    else {
        $specific_role = isset($_POST['specific_role']) ? $_POST['specific_role'] : '';

        if (empty($specific_role)) {
            echo "<script>
                Swal.fire('Peringatan', 'Silakan pilih tipe akun (Pelanggan Air/Penghuni Kos)!', 'warning').then(() => { history.back(); });
            </script>";
            exit;
        }

        $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND role='$specific_role'");
        $data = mysqli_fetch_assoc($query);

        if ($data) {
            if (password_verify($password, $data['password'])) {
                $login_berhasil = true;
                $user_data = $data;
            } else {
                echo "<script>
                    Swal.fire('Gagal', 'Password salah!', 'error').then(() => { history.back(); });
                </script>";
                exit;
            }
        } else {
            echo "<script>
                Swal.fire('Gagal', 'Akun tidak ditemukan atau tipe akun (Air/Kos) salah!', 'error').then(() => { history.back(); });
            </script>";
            exit;
        }
    }

    // 3. PROSES SETELAH LOGIN BERHASIL
    if ($login_berhasil) {
        $_SESSION['id'] = $user_data['id'];
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['nama'] = $user_data['nama_lengkap'];
        $_SESSION['role'] = $user_data['role'];

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil!',
                text: 'Selamat datang kembali, " . $user_data['nama_lengkap'] . "',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {";
                
                if ($user_data['role'] === 'admin') {
                    echo "window.location.href = 'admin_dashboard.php';";
                } elseif ($user_data['role'] === 'user_air') {
                    echo "window.location.href = 'user_air_dasboard.php';";
                } elseif ($user_data['role'] === 'user_kos') {
                    echo "window.location.href = 'user_kos_dashboard.php';";
                } else {
                    echo "window.location.href = 'index.php';";
                }
                
        echo "});</script>";
    }
    echo '</body>';
}
?>