<?php
require 'koneksi.php'; 

// 1. Cek Login
if (!isset($_SESSION['id'])) {
    die("Error: Anda harus login terlebih dahulu.");
}

$user_id = $_SESSION['id'];
// Ambil username untuk keperluan foreign key/pencatatan
$nama_user = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ambil data dari form
    $metode     = (isset($_POST['metode'])) ? $_POST['metode'] : 'ambil_sendiri';
    $koordinat  = (isset($_POST['koordinat'])) ? $_POST['koordinat'] : '';
    $alamat_raw = (isset($_POST['alamat']) && !empty($_POST['alamat'])) ? $_POST['alamat'] : '-';
    
    if (isset($koneksi) && $koneksi instanceof mysqli) {
        $alamat = $koneksi->real_escape_string($alamat_raw);
    } else {
        die("Koneksi database gagal.");
    }

    // 1. Ambil data loyalitas user
    $query_u = mysqli_query($koneksi, "SELECT hitungan_loyalitas FROM users WHERE id = '$user_id'");
    $u = mysqli_fetch_assoc($query_u);
    $loyalitas_sekarang = (isset($u['hitungan_loyalitas'])) ? $u['hitungan_loyalitas'] : 0;

    // 2. Tentukan Harga (Satuan dalam ribuan atau disesuaikan)
    // Jika ingin Rp 50.000, ganti 50 menjadi 50000
    $harga_normal = ($metode == 'antar') ? 50000 : 10000;
    
    $is_gratis = ($loyalitas_sekarang >= 5) ? 1 : 0;
    $harga_bayar = ($is_gratis) ? 0 : $harga_normal;
    $update_loyalitas = ($is_gratis) ? 0 : ($loyalitas_sekarang + 1);

    // 3. Simpan ke transaksi_air
    $query = "INSERT INTO transaksi_air (user_id, nama_pembeli, metode_ambil, alamat_tujuan, koordinat_maps, harga, harga_normal, status_gratis, jumlah) 
              VALUES ('$user_id', '$nama_user', '$metode', '$alamat', '$koordinat', '$harga_bayar', '$harga_normal', '$is_gratis', 1)";

  // --- FORMAT RUPIAH UNTUK KONFIRMASI ---
    $tampil_harga = "Rp " . number_format($harga_bayar, 0, ',', '.');

    if (!isset($_POST['konfirmasi_fix'])) {
        $postDataJson = json_encode($_POST);
        
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var dataPesanan = $postDataJson;
                
                Swal.fire({
                    title: 'Konfirmasi Pesanan',
                    html: 'Metode: <b>' + (dataPesanan.metode || 'Ambil Sendiri') + '</b><br>' +
                          'Total Bayar: <b style=\"color:green\">$tampil_harga</b>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Pesan Sekarang!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika klik Ya, buat form kirim ulang
                        var f = document.createElement('form');
                        f.method = 'POST';
                        f.action = '';
                        for (var key in dataPesanan) {
                            var i = document.createElement('input');
                            i.type = 'hidden';
                            i.name = key;
                            i.value = dataPesanan[key];
                            f.appendChild(i);
                        }
                        var flag = document.createElement('input');
                        flag.type = 'hidden';
                        flag.name = 'konfirmasi_fix';
                        flag.value = '1';
                        f.appendChild(flag);
                        document.body.appendChild(f);
                        f.submit();
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        // Jika klik Batal
                        Swal.fire('Dibatalkan', 'Pesanan Anda tidak disimpan.', 'error').then(() => {
                            window.location.href='user_air_dasboard.php';
                        });
                    }
                });
            });
        </script>";
        exit;
    }

    // JIKA SUDAH DIKONFIRMASI, BARU INSERT KE DB
    if (mysqli_query($koneksi, $query)) {
        mysqli_query($koneksi, "UPDATE users SET hitungan_loyalitas = '$update_loyalitas' WHERE id = '$user_id'");
        
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Pesanan Berhasil Disimpan!',
                    icon: 'success'
                }).then(() => {
                    window.location.href='user_air_dasboard.php';
                });
            });
        </script>";
    }
}
?>