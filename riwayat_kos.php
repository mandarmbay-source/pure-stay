<?php
session_start();
require 'koneksi.php';

// Proteksi Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header('Location: index.php'); 
    exit; 
}

// Ambil data pembayaran - Pastikan JOIN ke users untuk mendapatkan nama
$query = mysqli_query($koneksi, "SELECT data_kos.*, users.nama_lengkap 
    FROM data_kos 
    JOIN users ON data_kos.user_id = users.id 
    ORDER BY data_kos.tanggal_bayar DESC");

/**
 * Fungsi Format Tanggal Indonesia
 */
function formatWaktuIndo($tanggal) {
    if (!$tanggal) return "-";
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $timestamp = strtotime($tanggal);
    $tgl = date('d', $timestamp);
    $bln = (int)date('m', $timestamp);
    $thn = date('Y', $timestamp);
    $jam = date('H:i', $timestamp);
    
    return $tgl . ' ' . $bulan[$bln] . ' ' . $thn . ' | ' . $jam;
}

/**
 * Fungsi Cek Jatuh Tempo Bersih
 * Menghitung selisih 30 hari dari tanggal bayar
 */
function tampilanStatusJatuhTempo($tanggal_bayar) {
    $tgl_bayar = new DateTime($tanggal_bayar);
    $tgl_sekarang = new DateTime();
    
    // Hitung tanggal jatuh tempo (Tanggal Bayar + 30 Hari)
    $tgl_jt = clone $tgl_bayar;
    $tgl_jt->modify('+30 days');
    
    // Cek apakah sudah lewat dari tanggal jatuh tempo
    if ($tgl_sekarang > $tgl_jt) {
        $selisih = $tgl_jt->diff($tgl_sekarang)->days;
        return '
            <div style="margin-bottom: 4px;"><span style="background: #fee2e2; color: #ef4444; padding: 4px 8px; border-radius: 6px; font-weight: 800; font-size: 0.7rem; border: 1px solid #fecaca;">🚨 JATUH TEMPO</span></div>
            <small style="color: #ef4444; font-weight: 600;">Lewat '.$selisih.' hari</small>
        ';
    }
    
    return '
        <div style="margin-bottom: 4px;"><span class="status-lunas">✓ TERBAYAR</span></div>
        <small style="color: #64748b;">Hingga: '.$tgl_jt->format('d M Y').'</small>
    ';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pembayaran Kos - PureStay</title>
    <link rel="stylesheet" href="riwayat_air.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .btn-action { padding: 8px 12px; border-radius: 8px; text-decoration: none; font-size: 0.8rem; font-weight: 600; transition: 0.3s; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
        .btn-edit { background: #e0e7ff; color: #4f8cf0; }
        .btn-delete { background: #fee2e2; color: #ef4444; }
        .status-lunas { background: #dcfce7; color: #15803d; padding: 4px 8px; border-radius: 6px; font-weight: 800; font-size: 0.7rem; }
        .img-bukti { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid #f1f5f9; transition: 0.3s; }
        .img-bukti:hover { transform: scale(1.1); border-color: #4f8cf0; }
        .time-text { font-size: 0.75rem; color: #64748b; display: block; margin-top: 2px; }
        small { display: block; line-height: 1; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <a href="admin_dashboard.php" class="btn-back-link">← Kembali</a>
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px;">
        <div>
            <h2 style="margin: 0; font-weight: 800;">🏠 Konfirmasi Kos</h2>
            <p style="color: #64748b; margin-top: 5px;">Masa berlaku sewa otomatis dihitung 30 hari sejak tanggal bayar.</p><br>
        </div>
        <button onclick="hapusSemua()" class="btn-action" style="background: #1e293b; color: white; padding: 12px 20px; border-radius: 12px;">🗑️ Hapus</button><br>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; background: #f8fafc;">
                        <th style="padding: 15px;">No</th>
                        <th>Penghuni</th>
                        <th>Waktu Bayar</th>
                        <th>Total</th>
                        <th>Bukti</th>
                        <th>Status Sewa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $no = 1;
                if(mysqli_num_rows($query) > 0):
                    while($row = mysqli_fetch_assoc($query)): 
                ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 15px;"><?php echo $no++; ?></td>
                        <td>
                            <strong style="color: #1e293b;"><?php echo $row['nama_lengkap']; ?></strong>
                            <span class="time-text">Kamar <?php echo $row['no_kamar']; ?></span>
                        </td>
                        <td style="font-size: 0.85rem; font-weight: 600;">
                            <?php echo formatWaktuIndo($row['tanggal_bayar']); ?>
                        </td>
                        <td style="font-weight: 800; color: #1e293b;">
                            Rp <?php echo number_format((float)$row['jumlah_bayar'], 0, ',', '.'); ?>
                        </td>
                        <td>
                            <?php if(!empty($row['bukti_bayar'])): ?>
                                <img src="uploads/<?php echo $row['bukti_bayar']; ?>" 
                                     class="img-bukti" 
                                     onclick="viewImage('uploads/<?php echo $row['bukti_bayar']; ?>')">
                            <?php else: ?>
                                <span style="color: #cbd5e1; font-style: italic; font-size: 0.75rem;">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            if (strtolower($row['status']) != 'lunas') {
                                echo '<span style="background: #fef9c3; color: #a16207; padding: 4px 8px; border-radius: 6px; font-weight: 800; font-size: 0.7rem;">⏳ PROSES</span>';
                            } else {
                                echo tampilanStatusJatuhTempo($row['tanggal_bayar']);
                            }
                            ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <?php if (strtolower($row['status']) != 'lunas'): ?>
                                    <a href="verifikasi_bayar.php?id=<?php echo $row['id']; ?>" 
                                       class="btn-action" 
                                       style="background: #2ecc71; color: white;" 
                                       onclick="return confirm('Lakukan verifikasi?')">✓</a>
                                <?php endif; ?>
                                <a href="edit_kos.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit">✏️</a>
                                <button onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn-action btn-delete">🗑️</button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:50px; color:#94a3b8;">Belum ada riwayat transaksi.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function viewImage(path) {
    Swal.fire({
        imageUrl: path,
        imageAlt: 'Bukti Transfer',
        confirmButtonColor: '#4f8cf0',
        confirmButtonText: 'Tutup'
    });
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus data ini?',
        text: "Data yang dihapus tidak bisa dikembalikan.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus'
    }).then((result) => {
        if (result.isConfirmed) { window.location.href = 'hapus_kos.php?id=' + id; }
    })
}

function hapusSemua() {
    Swal.fire({
        title: 'Kosongkan Semua?',
        text: "Seluruh riwayat pembayaran akan dihapus permanen!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#1e293b',
        confirmButtonText: 'Ya, Hapus Semua'
    }).then((result) => {
        if (result.isConfirmed) { window.location.href = 'hapus_semua_kos.php'; }
    })
}
</script>
</body>
</html>