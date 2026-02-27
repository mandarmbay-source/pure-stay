<?php
session_start();
require 'koneksi.php';

// 1. Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

// --- FITUR KONFIRMASI LUNAS ---
if (isset($_GET['konfirmasi'])) {
    $id_konfirmasi = mysqli_real_escape_string($koneksi, $_GET['konfirmasi']);
    
    // Ambil user_id untuk update tabel users
    $cek_user = mysqli_query($koneksi, "SELECT user_id FROM data_kos WHERE id = '$id_konfirmasi'");
    if($d = mysqli_fetch_assoc($cek_user)) {
        $user_id = $d['user_id'];
        // Update status di kedua tabel
        mysqli_query($koneksi, "UPDATE data_kos SET status = 'lunas' WHERE id = '$id_konfirmasi'");
        mysqli_query($koneksi, "UPDATE users SET status_bayar = 'lunas' WHERE id = '$user_id'");
        header("Location: kelola_hunian.php?pesan=terverifikasi");
        exit;
    }
}

// --- LOGIKA HITUNG UNIT ---
$res_total = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kamar_kos"));
$total_kamar = (int)$res_total['total'];

$res_terisi = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(k.nomor_kamar) as total FROM kamar_kos k INNER JOIN users u ON k.nomor_kamar = u.no_kamar"));
$terisi = (int)$res_terisi['total'];
$kosong = $total_kamar - $terisi;

// --- LOGIKA PENGHASILAN BULAN INI ---
$bulan_ini = date('m');
$tahun_ini = date('Y');

$res_pendapatan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(jumlah_bayar) as total FROM data_kos WHERE status = 'lunas' AND MONTH(tanggal_bayar) = '$bulan_ini' AND YEAR(tanggal_bayar) = '$tahun_ini'"));
$pendapatan_total = (float)$res_pendapatan['total'];

// --- QUERY UTAMA GRID ---
$query_kamar = mysqli_query($koneksi, "
    SELECT k.*, u.nama_lengkap, u.id as user_id,
    (SELECT status FROM data_kos WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as status_p,
    (SELECT id FROM data_kos WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as trans_id,
    (SELECT tanggal_bayar FROM data_kos WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as tgl_bayar
    FROM kamar_kos k
    LEFT JOIN users u ON k.nomor_kamar = u.no_kamar
    ORDER BY LENGTH(k.nomor_kamar) ASC, k.nomor_kamar ASC
");

function formatTglIndo($tgl) {
    if (!$tgl) return "-";
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $ts = strtotime($tgl);
    return date('d', $ts) . ' ' . $bulan[(int)date('m', $ts)] . ' ' . date('Y', $ts);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Hunian PureStay</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary: #4f8cf0; --success: #2ecc71; --warning: #f1c40f; --danger: #e74c3c; --dark: #1e293b; }
        body { background: #f8fbff; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; padding-bottom: 50px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 15px; }

        /* Header Responsive */
        .header-area { display: flex; justify-content: space-between; align-items: center; margin: 20px 0; flex-wrap: wrap; gap: 15px; }
        .btn-group { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { padding: 10px 18px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 0.85rem; transition: 0.2s; border: none; cursor: pointer; }
        .btn-dark { background: var(--dark); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-primary { background: var(--primary); color: white; }

        /* Statistik Grid */
        .stats-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-bottom: 30px; }
        .stats-mini-box { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .card { background: white; padding: 20px; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        .income-card { background: linear-gradient(135deg, #1e293b, #334155); color: white; }

        /* Room Grid Responsive */
        .room-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .room-card { background: white; border-radius: 20px; padding: 20px; border: 1px solid #eee; position: relative; display: flex; flex-direction: column; transition: 0.3s; }
        .room-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(79,140,240,0.1); }

        /* Badges */
        .badge { position: absolute; top: 15px; right: 15px; padding: 5px 10px; border-radius: 8px; font-size: 0.65rem; font-weight: 800; }
        .badge-terisi { background: #eff6ff; color: var(--primary); }
        .badge-kosong { background: #ecfdf5; color: var(--success); }
        .pay-tag { font-size: 0.65rem; font-weight: 800; padding: 4px 8px; border-radius: 6px; display: inline-block; margin: 10px 0; }
        .tag-lunas { background: #dcfce7; color: #166534; }
        .tag-proses { background: #fef9c3; color: #854d0e; }

        .timeline { background: #f8fafc; padding: 12px; border-radius: 12px; font-size: 0.8rem; margin: 10px 0; flex-grow: 1; }
        .price { font-weight: 800; color: var(--primary); font-size: 1.1rem; margin-top: 10px; }
        .actions { display: flex; gap: 8px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #f1f5f9; }
        .btn-small { flex: 1; text-align: center; padding: 8px; border-radius: 8px; font-size: 0.75rem; text-decoration: none; font-weight: 700; }

        /* Responsive Breakpoints */
        @media (max-width: 900px) { .stats-grid { grid-template-columns: 1fr; } }
        @media (max-width: 600px) {
            .header-area { flex-direction: column; align-items: flex-start; }
            .btn-group, .stats-mini-box { grid-template-columns: 1fr; width: 100%; }
            .btn-group a { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-area">
        <div>
            <a href="admin_dashboard.php" style="text-decoration:none; color:var(--primary); font-weight:700;">← Dashboard</a>
            <h2 style="margin:5px 0 0 0; font-weight:800;">🏠 Kelola Hunian</h2>
        </div>
        <div class="btn-group">
            <a href="riwayat_pembayaran_kos.php" class="btn btn-dark">📊 Monitoring</a>
            <a href="tambah_unit.php" class="btn btn-success">+ Unit Baru</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stats-mini-box">
            <div class="card" style="text-align:center;">
                <small style="color:var(--text-light)">Total Unit</small>
                <div style="font-size:1.5rem; font-weight:800;"><?= $total_kamar ?></div>
            </div>
            <div class="card" style="text-align:center;">
                <small style="color:var(--primary)">Terisi</small>
                <div style="font-size:1.5rem; font-weight:800; color:var(--primary)"><?= $terisi ?></div>
            </div>
            <div class="card" style="text-align:center;">
                <small style="color:var(--success)">Kosong</small>
                <div style="font-size:1.5rem; font-weight:800; color:var(--success)"><?= $kosong ?></div>
            </div>
        </div>
        <div class="card income-card">
            <small style="opacity:0.8;">PENGHASILAN LUNAS (<?= date('F') ?>)</small>
            <div style="font-size:1.6rem; font-weight:800;">Rp <?= number_format($pendapatan_total, 0, ',', '.') ?></div>
        </div>
    </div>

    <div class="room-grid">
        <?php while($room = mysqli_fetch_assoc($query_kamar)): 
            $is_terisi = !empty($room['nama_lengkap']);
// Pastikan $room adalah array sebelum mengakses indeksnya
$status_raw = (isset($room['status_p']) && $room['status_p'] !== null) ? $room['status_p'] : '';
$st = strtolower((string)$status_raw);            $jt_raw = strtotime($room['tgl_bayar'] . " +30 days");
        ?>
            <div class="room-card">
                <span class="badge <?= $is_terisi ? 'badge-terisi' : 'badge-kosong' ?>">
                    <?= $is_terisi ? 'TERISI' : 'TERSEDIA' ?>
                </span>

                <h3 style="margin:0; font-size:1.2rem;">Kamar <?= $room['nomor_kamar'] ?></h3>
                <small style="color:#94a3b8;"><?= $room['tipe_kamar'] ?></small>

                <div style="margin-top:15px;">
                    <small>Penghuni:</small><br>
                    <b style="font-size:0.95rem;"><?= $is_terisi ? $room['nama_lengkap'] : '<span style="color:#cbd5e1">Kosong</span>' ?></b>
                </div>

                <?php if($is_terisi): ?>
                    <div>
                        <?php if($st == 'lunas'): ?>
                            <span class="pay-tag tag-lunas">✓ TERBAYAR</span>
                        <?php elseif($st == 'proses'): ?>
                            <span class="pay-tag tag-proses">⏳ BUTUH VERIFIKASI</span>
                        <?php else: ?>
                            <span class="pay-tag" style="background:#fee2e2; color:#ef4444;">✗ TUNGGAKAN</span>
                        <?php endif; ?>
                    </div>

                    <div class="timeline">
                        <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                            <span>Bayar:</span> <b><?= formatTglIndo($room['tgl_bayar']) ?></b>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span>Habis:</span> <b style="color:<?= (time() > $jt_raw) ? 'red' : 'green' ?>"><?= formatTglIndo(date('Y-m-d', $jt_raw)) ?></b>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="price">Rp <?= number_format($room['harga_bulanan'], 0, ',', '.') ?><small style="font-size:0.7rem; color:#94a3b8;">/bln</small></div>

                <div class="actions">
                    <a href="edit_unit.php?no=<?= $room['nomor_kamar'] ?>" class="btn-small" style="background:#f1f5f9; color:#475569;">Edit</a>
                    
                    <?php if($st == 'proses'): ?>
                        <button onclick="verifBayar(<?= $room['trans_id'] ?>)" class="btn-small" style="background:var(--success); color:white; border:none; cursor:pointer;">Verifikasi</button>
                    <?php else: ?>
                        <a href="riwayat_pembayaran_kos.php?search=<?= $room['nama_lengkap'] ?>" class="btn-small" style="background:var(--primary); color:white;">Riwayat</a>
                    <?php endif; ?>

                    <?php if($is_terisi): ?>
                        <a href="hapus_penghuni.php?id=<?= $room['user_id'] ?>" class="btn-small" style="background:#fee2e2; color:#ef4444;" onclick="return confirm('Hapus penghuni ini?')">Out</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    function verifBayar(id) {
        Swal.fire({
            title: 'Verifikasi Pembayaran?',
            text: "Pastikan dana sudah masuk ke rekening!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2ecc71',
            confirmButtonText: 'Ya, Lunas!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'kelola_hunian.php?konfirmasi=' + id;
            }
        })
    }

    // Notifikasi sukses
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('pesan')) {
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Status telah diperbarui', timer: 1500, showConfirmButton: false });
    }
</script>
</body>
</html>