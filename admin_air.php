<?php
session_start();
require 'koneksi.php'; 

// 1. Proteksi Halaman Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

// 2. Query Ambil Data - HANYA ANTAR
$query = mysqli_query($koneksi, "SELECT t.*, u.nama_lengkap, u.no_hp 
    FROM transaksi_air t 
    JOIN users u ON t.user_id = u.id 
    WHERE t.status_pesanan != 'Selesai' 
    AND t.metode_ambil = 'antar' 
    ORDER BY t.tanggal DESC");

if (!$query) {
    die("Query Gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengantaran Air - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        .order-card { background: white; border-radius: 20px; padding: 20px; margin-bottom: 20px; border-left: 8px solid #4f8cf0; box-shadow: 0 10px 30px rgba(0,0,0,0.05); position: relative; }
        .badge-antar { background: #fff4e5; color: #ff9800; padding: 5px 12px; border-radius: 10px; font-weight: bold; font-size: 12px; display: inline-block; }
        .btn-maps { background: #4285F4; color: white; padding: 10px 15px; border-radius: 10px; text-decoration: none; display: inline-block; margin-top: 10px; font-size: 14px; font-weight: 600; text-align: center; }
        .dashboard-container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .btn-back-link { text-decoration: none; color: #4f8cf0; font-weight: 600; }
        .btn-hapus-satuan { position: absolute; top: 15px; right: 15px; color: #ff4d4d; text-decoration: none; font-size: 18px; font-weight: bold; padding: 5px 10px; border-radius: 8px; transition: 0.3s; }
        .btn-hapus-satuan:hover { background: #fff1f1; color: #d63031; }
        .btn-hapus-semua { background: #ff4d4d; color: white; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: bold; display: inline-block; margin-bottom: 20px; transition: 0.3s; border: none; cursor: pointer; }
    </style>
</head>
<body style="background: #f8fbff; font-family: 'Plus Jakarta Sans';">

<div class="dashboard-container">
    <br>
    <a href="admin_dashboard.php" class="btn-back-link">← Kembali ke Dashboard</a>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; flex-wrap: wrap; gap: 10px;">
        <h2>🚚 Khusus Pesanan Antar</h2>
        <div style="display: flex; gap: 10px;">
            <a href="monitoring_antar.php" class="btn-hapus-semua" style="background: #10b981;">✅ Riwayat Antar</a>
            <?php if(mysqli_num_rows($query) > 0): ?>
                <a href="hapus_kelola_pesanan_semua_air.php" class="btn-hapus-semua" onclick="return confirm('Kosongkan semua daftar antar?')">🗑️ Kosongkan</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
        <div style="background: #ecfdf5; color: #10b981; padding: 12px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #10b981; font-weight: 600;">
            ✅ Status pengiriman berhasil diperbarui!
        </div>
    <?php endif; ?>

    <?php if(mysqli_num_rows($query) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($query)): ?>
          <?php 
    // 1. Ambil ID
    $id_transaksi = isset($row['id']) ? $row['id'] : 0;

    // 2. Ambil Nama (Cegah error null secara total)
    // Kita cek dulu apakah kolomnya ada, jika tidak ada atau null, beri string kosong baru di-cast ke string
    $nama_raw = isset($row['nama_lengkap']) ? $row['nama_lengkap'] : 'Pelanggan';
    $nama = htmlspecialchars((string)$nama_raw);

    // 3. Ambil No HP & Koordinat (Gunakan (string) sebelum trim)
    $no_hp_raw     = isset($row['no_hp']) ? $row['no_hp'] : '';
    $no_hp         = trim((string)$no_hp_raw);
    
    $koordinat_raw = isset($row['koordinat']) ? $row['koordinat'] : '';
    $koordinat     = trim((string)$koordinat_raw);

    // 4. Penanganan Harga (Gunakan (float) untuk angka)
    $harga_raw    = isset($row['harga']) ? $row['harga'] : 0;
    $harga_clean  = (float)$harga_raw;
    $harga_tampil = "Rp " . number_format($harga_clean, 0, ',', '.');

    // 5. Penanganan Tanggal
    $tanggal_raw   = isset($row['tanggal']) ? $row['tanggal'] : date('Y-m-d H:i:s');
    $tgl_timestamp = strtotime((string)$tanggal_raw);
    $tgl_tampil    = $tgl_timestamp ? date('d M Y, H:i', $tgl_timestamp) : '-';

    // 6. Alamat & Status
    $alamat_raw  = isset($row['alamat']) ? $row['alamat'] : '';
    $alamat      = (string)$alamat_raw;
    
    $status_raw  = isset($row['status_pesanan']) ? $row['status_pesanan'] : 'Proses';
    $status_skrg = (string)$status_raw;
?>

            <div class="order-card">
                <a href="hapus_kelola_pesanan_air.php?id=<?php echo $id_transaksi; ?>" class="btn-hapus-satuan" onclick="return confirm('Hapus pesanan ini?')">✕</a>

                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <span class="badge-antar">📍 STATUS: <?php echo strtoupper($status_skrg); ?></span>
                        <h3 style="margin: 10px 0;"><?php echo htmlspecialchars($nama); ?></h3>
                        <p style="margin: 5px 0;">
                            <?php if ($no_hp != ''): ?>
                                📞 <b>HP:</b> <a href="tel:<?php echo $no_hp; ?>" style="text-decoration: none; color: inherit;"><?php echo htmlspecialchars($no_hp); ?></a><br>
                                <?php $wa_number = preg_replace('/[^0-9]/', '', $no_hp); ?>
                                <a href="https://wa.me/<?php echo $wa_number; ?>" target="_blank" style="color: #25D366; text-decoration: none; font-size: 0.9rem; font-weight: bold;">💬 Chat WhatsApp</a>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div style="text-align: right; padding-right: 30px;">
                        <p style="color: #64748b; font-size: 13px; margin: 0;"><?php echo $tgl_tampil; ?></p>
                        <h4 style="color: #2ecc71; margin: 5px 0; font-weight: 800;"><?php echo $harga_tampil; ?></h4>
                    </div>
                </div>

                <div style="background: #f1f5f9; padding: 15px; border-radius: 12px; margin-top: 15px;">
                    <p style="font-size: 14px; margin: 0; color: #1e293b;">
                        <b>📍 Alamat Pengiriman:</b><br>
                        <?php echo ($alamat == '') ? '<span style="color:red;">Alamat tidak diisi</span>' : htmlspecialchars($alamat); ?>
                    </p>
                    
                    <?php if ($koordinat != '' && $koordinat != '-'): ?>
                        <a href="https://www.google.com/maps?q=<?php echo urlencode($koordinat); ?>" 
                           target="_blank" class="btn-maps">
                           🗺️ Buka Rute Google Maps
                        </a>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #eee;">
                    <form action="update_air.php" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <input type="hidden" name="id_transaksi" value="<?php echo $id_transaksi; ?>">
                        <select name="status" style="padding: 10px; border-radius: 10px; border: 1px solid #ddd; flex-grow: 1; font-family: inherit;">
                            <option value="Proses" <?php if($status_skrg == 'Proses') echo 'selected'; ?>>🟡 Menunggu</option>
                            <option value="Dikirim" <?php if($status_skrg == 'Dikirim') echo 'selected'; ?>>🔵 Sedang Diantar</option>
                            <option value="Selesai">🟢 Selesai (Masuk Monitoring)</option>
                        </select>
                        <button type="submit" style="background: #4f8cf0; color: white; border: none; padding: 10px 25px; border-radius: 10px; cursor: pointer; font-weight: 600;">Update</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="order-card" style="text-align: center; padding: 60px;">
            <p style="color: #64748b;">Tidak ada antrean pengantaran saat ini. ✨</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>