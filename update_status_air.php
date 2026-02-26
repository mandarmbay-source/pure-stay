<?php
session_start();
require 'koneksi.php';

// 1. Proteksi Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_transaksi'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id_transaksi']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    // 2. Update status di database
    $query_update = mysqli_query($koneksi, "UPDATE transaksi_air SET status_pesanan = '$status' WHERE id = '$id'");

    if ($query_update) {
        // 3. Ambil data untuk keperluan pesan WhatsApp
        $sql = "SELECT t.alamat, t.koordinat, t.metode_ambil, u.nama_lengkap, u.no_hp 
                FROM transaksi_air t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.id = '$id'";
        
        $result = mysqli_query($koneksi, $sql);
        $data = mysqli_fetch_assoc($result);

        $nama = !empty($data['nama_lengkap']) ? $data['nama_lengkap'] : 'Pelanggan';
        $nomer_hp_mentah = !empty($data['no_hp']) ? $data['no_hp'] : '';
        $alamat_kirim = !empty($data['alamat']) ? $data['alamat'] : 'Tidak ada alamat';
        $lokasi_map = !empty($data['koordinat']) ? "https://www.google.com/maps?q=" . $data['koordinat'] : "";

        // Tentukan halaman tujuan (Redirect)
        $redirect_page = ($status == 'Selesai') ? 'monitoring_air.php' : 'admin_air.php';
        $alert_msg = ($status == 'Selesai') ? 'Pesanan telah diselesaikan dan masuk ke arsip!' : 'Status Berhasil Diperbarui!';

        if (!empty($nomer_hp_mentah)) {
            // Bersihkan nomor HP dan format ke 62
            $nomer_bersih = preg_replace('/[^0-9]/', '', $nomer_hp_mentah);
            $nomer_final = (substr($nomer_bersih, 0, 1) === '0') ? '62' . substr($nomer_bersih, 1) : $nomer_bersih;

            // Susun Pesan WA
            $pesan = "Halo *$nama*,\n\n";
            $pesan .= "Status pesanan Air PureStay Anda: *$status*\n";
            
            if ($data['metode_ambil'] == 'antar') {
                $pesan .= "📍 *Alamat Pengiriman:* $alamat_kirim\n";
                if (!empty($data['koordinat'])) $pesan .= "🗺️ *Link Maps:* $lokasi_map";
            } else {
                $pesan .= "🏠 *Metode:* Ambil Sendiri ke Toko";
            }

            $wa_link = "https://api.whatsapp.com/send?phone=$nomer_final&text=" . urlencode($pesan);

            // Tampilkan Alert, Buka WA di tab baru, lalu Redirect ke halaman yang sesuai
            echo "<script>
                    alert('$alert_msg');
                    window.open('$wa_link', '_blank');
                    window.location.href = '$redirect_page';
                  </script>";
        } else {
            // Jika nomor HP tidak ada, langsung redirect tanpa buka WA
            echo "<script>
                    alert('Status diperbarui ke $status, tapi HP pelanggan tidak ada.');
                    window.location.href = '$redirect_page';
                  </script>";
        }
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
} else {
    header('Location: admin_air.php');
}
?>