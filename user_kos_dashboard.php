<?php
session_start();
require 'koneksi.php';

// 1. Proteksi login
if (!isset($_SESSION['id'])) { 
    header('Location: login.php'); 
    exit; 
}

$user_id = $_SESSION['id'];

// 2. Ambil DATA USER
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$user_id'");
$data = mysqli_fetch_assoc($query);

// 3. Ambil DETAIL PEMBAYARAN TERAKHIR (Termasuk Tanggal)
$q_bayar = mysqli_query($koneksi, "SELECT status, tanggal_bayar FROM data_kos WHERE user_id = '$user_id' ORDER BY id DESC LIMIT 1");
$d_bayar = mysqli_fetch_assoc($q_bayar);

// --- LOGIKA STATUS & WAKTU ---
$tgl_terakhir = "-";
$tgl_jatuh_tempo = "-";
$status_teks = "belum bayar";

// Fungsi Format Tanggal Indonesia
function formatWaktuIndo($tanggal) {
    if (!$tanggal || $tanggal == "-") return "-";
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $timestamp = strtotime($tanggal);
    return date('d', $timestamp) . ' ' . $bulan[(int)date('m', $timestamp)] . ' ' . date('Y', $timestamp) . ' | ' . date('H:i', $timestamp);
}

if ($d_bayar) {
    $status_teks = strtolower($d_bayar['status']);
    $tgl_db = $d_bayar['tanggal_bayar'];
    
    $tgl_terakhir = formatWaktuIndo($tgl_db);
    
    // Hitung Jatuh Tempo (30 Hari setelah bayar terakhir)
    $jt_timestamp = strtotime($tgl_db . " +30 days");
    $tgl_jatuh_tempo = formatWaktuIndo(date('Y-m-d H:i:s', $jt_timestamp));

    // Jika sudah lewat 30 hari dan status sudah lunas, ubah tampilan ke Jatuh Tempo
    if (time() > $jt_timestamp && $status_teks == 'lunas') {
        $status_teks = 'jatuh tempo';
    }
}

$no_kamar = !empty($data['no_kamar']) ? $data['no_kamar'] : null;

$kamar_terisi = []; 
$q_cek_kamar = mysqli_query($koneksi, "SELECT no_kamar FROM users WHERE no_kamar IS NOT NULL");
while ($row = mysqli_fetch_assoc($q_cek_kamar)) {
    $kamar_terisi[] = $row['no_kamar'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PureStay - Dashboard Penghuni</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4f8cf0; --dark: #0f172a; --bg: #f8fafc; --success: #10b981; --warning: #f59e0b; --danger: #ef4444; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg); margin: 0; color: var(--dark); }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        
        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: #64748b; text-decoration: none; font-weight: 600; margin-bottom: 20px; transition: 0.3s; font-size: 0.9rem; }
        .btn-back:hover { color: var(--primary); }

        .room-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; margin-top: 20px; }
        .room-card { background: white; padding: 20px; border-radius: 15px; text-align: center; border: 2px solid transparent; transition: 0.3s; cursor: pointer; position: relative; }
        .room-card.available:hover { border-color: var(--primary); transform: translateY(-5px); }
        .room-card.occupied { background: #e2e8f0; opacity: 0.6; cursor: not-allowed; }
        .room-card input { position: absolute; opacity: 0; }
        .room-card.selected { border-color: var(--primary); background: #eff6ff; }

        .glass-card { background: white; padding: 30px; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .status-badge { padding: 6px 16px; border-radius: 100px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; }
        .status-lunas { background: #dcfce7; color: #15803d; }
        .status-proses { background: #fef3c7; color: #b45309; }
        .status-pending { background: #fee2e2; color: #b91c1c; }
        .status-jt { background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; }

        .info-box { background: #f1f5f9; padding: 20px; border-radius: 16px; }
        .info-label { font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; display: block; margin-bottom: 5px; }
        .info-value { font-size: 1.2rem; font-weight: 800; }

        .btn-pay { background: var(--dark); color: white; border: none; padding: 15px 30px; border-radius: 12px; font-weight: 700; width: 100%; cursor: pointer; transition: 0.3s; margin-top: 20px;}
        .btn-pay:hover { background: var(--primary); }

        select { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; margin-top: 10px; font-family: inherit; }

        /* MODAL CSS */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.9); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .modal-overlay img { max-width: 90%; max-height: 85%; border-radius: 10px; box-shadow: 0 0 20px rgba(255,255,255,0.2); }
        .close-btn { position: absolute; top: 20px; right: 30px; color: white; font-size: 50px; font-weight: bold; cursor: pointer; transition: 0.3s; line-height: 1; }
        .close-btn:hover { color: #ef4444; }
    </style>
    <style>
    :root { 
        --primary: #4f8cf0; 
        --dark: #0f172a; 
        --bg: #f8fafc; 
        --success: #10b981; 
        --warning: #f59e0b; 
        --danger: #ef4444; 
    }

    * { box-sizing: border-box; }

    body { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background-color: var(--bg); 
        margin: 0; 
        color: var(--dark); 
        line-height: 1.5;
    }

    .container { 
        max-width: 900px; 
        margin: 20px auto; 
        padding: 0 15px; /* Padding lebih kecil di HP */
    }

    .btn-back { 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        color: #64748b; 
        text-decoration: none; 
        font-weight: 600; 
        margin-bottom: 20px; 
        transition: 0.3s; 
        font-size: 0.85rem; 
    }

    .glass-card { 
        background: white; 
        padding: 25px; 
        border-radius: 20px; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
    }

    /* GRID KAMAR - Responsif */
    .room-grid { 
        display: grid; 
        grid-template-columns: repeat(3, 1fr); /* Default 3 kolom di HP */
        gap: 10px; 
        margin-top: 20px; 
    }

    .room-card { 
        background: white; 
        padding: 15px 10px; 
        border-radius: 12px; 
        text-align: center; 
        border: 2px solid #f1f5f9; 
        transition: 0.2s; 
        cursor: pointer; 
        position: relative; 
    }

    .room-card.available:hover { border-color: var(--primary); }
    .room-card.occupied { background: #f1f5f9; opacity: 0.5; cursor: not-allowed; border: none; }
    .room-card input { position: absolute; opacity: 0; }
    .room-card.selected { border-color: var(--primary); background: #eff6ff; box-shadow: 0 0 0 3px rgba(79, 140, 240, 0.2); }

    /* INFO BOX - Responsif Grid */
    .dashboard-header {
        display: flex; 
        flex-direction: column; /* Stack di HP */
        gap: 15px;
        margin-bottom: 25px;
    }

    .status-badge { 
        align-self: flex-start; /* Tidak melebar penuh */
        padding: 6px 14px; 
        border-radius: 100px; 
        font-size: 0.75rem; 
        font-weight: 700; 
    }

    .info-grid {
        display: grid; 
        grid-template-columns: 1fr; /* 1 Kolom di HP */
        gap: 15px; 
        margin-bottom: 25px;
    }

    .info-box { 
        background: #f1f5f9; 
        padding: 15px; 
        border-radius: 14px; 
    }

    .info-label { font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px; }
    .info-value { font-size: 1.1rem; font-weight: 800; word-break: break-word; }

    .btn-pay { 
        background: var(--dark); 
        color: white; 
        border: none; 
        padding: 16px; 
        border-radius: 12px; 
        font-weight: 700; 
        width: 100%; 
        cursor: pointer; 
        font-size: 1rem;
        transition: 0.3s; 
    }

    /* MODAL Responsif */
    .modal-overlay img { width: 95%; height: auto; }

    /* MEDIA QUERIES UNTUK LAYAR BESAR (TABLET/PC) */
    @media (min-width: 600px) {
        .container { margin: 40px auto; }
        .glass-card { padding: 40px; }
        .room-grid { grid-template-columns: repeat(5, 1fr); gap: 15px; }
        .dashboard-header { flex-direction: row; justify-content: space-between; align-items: center; }
        .info-grid { grid-template-columns: repeat(3, 1fr); }
        .info-value { font-size: 1.2rem; }
        .room-card { padding: 20px; }
    }

    /* Warna Status */
    .status-lunas { background: #dcfce7; color: #15803d; }
    .status-proses { background: #fef3c7; color: #b45309; }
    .status-pending { background: #fee2e2; color: #b91c1c; }
    .status-jt { background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; }
    
    /* Utility */
    input[type="file"] {
        background: #f8fafc;
        padding: 10px;
        border: 2px dashed #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
    }
</style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-back">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali ke Beranda
    </a>

    <?php if (!$no_kamar): ?>
        <div class="glass-card">
            <h2>Pilih Kamar Anda</h2>
            <p>Silakan pilih kamar yang tersedia untuk memulai.</p>
            <form action="pilih_kamar_proses.php" method="POST">
                <div class="room-grid">
                    <?php 
                    $q_master_kamar = mysqli_query($koneksi, "SELECT nomor_kamar FROM kamar_kos ORDER BY LENGTH(nomor_kamar) ASC, nomor_kamar ASC");
                    while($rk_row = mysqli_fetch_assoc($q_master_kamar)): 
                        $rk = $rk_row['nomor_kamar'];
                        $is_taken = in_array($rk, $kamar_terisi);
                    ?>
                    <label class="room-card <?php echo $is_taken ? 'occupied' : 'available'; ?>" id="card-<?php echo $rk; ?>">
                        <input type="radio" name="no_kamar" value="<?php echo $rk; ?>" <?php echo $is_taken ? 'disabled' : ''; ?> onclick="selectRoom('<?php echo $rk; ?>')">
                        <div class="label">
                            <span style="display:block; font-size: 0.7rem; opacity: 0.6;">NOMOR</span>
                            <span style="font-size: 1.2rem; font-weight: 800;"><?php echo $rk; ?></span>
                            <div style="font-size: 0.6rem; margin-top: 5px;"><?php echo $is_taken ? 'TERISI' : 'KOSONG'; ?></div>
                        </div>
                    </label>
                    <?php endwhile; ?>
                </div>
                <button type="submit" class="btn-pay">Konfirmasi Kamar</button>
            </form>
        </div>

    <?php else: ?>
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h1 style="margin:0;">Dashboard Penghuni</h1>
                <span class="status-badge <?php 
                    if($status_teks == 'lunas') echo 'status-lunas';
                    elseif($status_teks == 'proses') echo 'status-proses';
                    elseif($status_teks == 'jatuh tempo') echo 'status-jt';
                    else echo 'status-pending';
                ?>">
                    <?php echo strtoupper($status_teks); ?>
                </span>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;">
                <div class="info-box">
                    <span class="info-label">Nomor Kamar</span>
                    <div class="info-value" style="color: var(--primary);"><?php echo $no_kamar; ?></div>
                </div>
                <div class="info-box">
                    <span class="info-label">Terakhir Bayar</span>
                    <div class="info-value" style="font-size: 0.95rem;"><?php echo $tgl_terakhir; ?></div>
                </div>
                <div class="info-box" style="border-left: 4px solid <?php echo ($status_teks == 'jatuh tempo') ? 'var(--danger)' : 'var(--success)'; ?>;">
                    <span class="info-label">Jatuh Tempo Berikutnya</span>
                    <div class="info-value" style="font-size: 0.95rem; color: <?php echo ($status_teks == 'jatuh tempo') ? 'var(--danger)' : 'inherit'; ?>;">
                        <?php echo $tgl_jatuh_tempo; ?>
                    </div>
                </div>
            </div>

            <?php if ($status_teks != 'lunas' && $status_teks != 'proses'): ?>
                <div style="border-top: 1px solid #eee; padding-top: 20px;">
                    <h3>Lengkapi Pembayaran</h3>
                    <form action="upload_bukti_proses.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="no_kamar" value="<?php echo $no_kamar; ?>">
                        
                        <div style="margin-bottom: 20px;">
                            <label><b>Pilih Metode Pembayaran:</b></label>
                            <select id="metode" name="metode_bayar" onchange="updateRekening()" required>
                                <option value="">-- Pilih Bank --</option>
                                <option value="BCA">Bank BCA</option>
                                <option value="BRI">Bank BRI</option>
                                <option value="MANDIRI">Bank Mandiri</option>
                                <option value="QRIS">QRIS PureStay</option>
                            </select>
                        </div>

                        <div id="info-pembayaran" style="display:none; background: #f1f5f9; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px dashed #4f8cf0;">
                            <div id="box-rekening">
                                <p id="label-bank" style="font-weight: 800; color: #4f8cf0; margin: 0; text-transform: uppercase;"></p>
                                <p style="margin: 5px 0;">Atas Nama: <b>PureStay Air Mbay</b></p>
                                <p style="font-size: 1.2rem; margin: 0;">No. Rekening: <b id="nomor-rekening"></b></p>
                            </div>

                            <div id="box-qris" style="display:none; text-align: center;">
                                <p style="font-weight: 800; color: #4f8cf0; margin-bottom: 15px;">SCAN QRIS PURESTAY</p>
                                <div style="display: inline-block; background: white; padding: 15px; border-radius: 20px; border: 1px solid #e2e8f0; cursor: pointer;" onclick="openModal()">
                                    <img id="qrisSource" src="uploads/purestay.png" alt="QRIS" style="width: 200px; height: auto; border-radius: 10px; display: block;" onerror="this.src='https://placehold.co/200x250?text=QRIS+Tidak+Ada'">
                                    <div style="margin-top: 10px; font-size: 0.7rem; color: #4f8cf0; font-weight: bold;">🔍 KLIK PERBESAR</div>
                                </div>
                            </div>
                        </div>

                        <label><b>Upload Bukti Transfer:</b></label>
                        <input type="file" name="bukti" required style="margin-top: 10px; width: 100%;">
                        <button type="submit" class="btn-pay">Kirim Bukti Pembayaran</button>
                    </form>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding:<?php
session_start();
require 'koneksi.php';

// 1. Proteksi login
if (!isset($_SESSION['id'])) { 
    header('Location: login.php'); 
    exit; 
}

$user_id = $_SESSION['id'];

// 2. Ambil DATA USER
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$user_id'");
$data = mysqli_fetch_assoc($query);

// 3. Ambil DETAIL PEMBAYARAN TERAKHIR (Termasuk Tanggal)
$q_bayar = mysqli_query($koneksi, "SELECT status, tanggal_bayar FROM data_kos WHERE user_id = '$user_id' ORDER BY id DESC LIMIT 1");
$d_bayar = mysqli_fetch_assoc($q_bayar);

// --- LOGIKA STATUS & WAKTU ---
$tgl_terakhir = "-";
$tgl_jatuh_tempo = "-";
$status_teks = "belum bayar";

// Fungsi Format Tanggal Indonesia
function formatWaktuIndo($tanggal) {
    if (!$tanggal || $tanggal == "-") return "-";
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $timestamp = strtotime($tanggal);
    return date('d', $timestamp) . ' ' . $bulan[(int)date('m', $timestamp)] . ' ' . date('Y', $timestamp) . ' | ' . date('H:i', $timestamp);
}

if ($d_bayar) {
    $status_teks = strtolower($d_bayar['status']);
    $tgl_db = $d_bayar['tanggal_bayar'];
    
    $tgl_terakhir = formatWaktuIndo($tgl_db);
    
    // Hitung Jatuh Tempo (30 Hari setelah bayar terakhir)
    $jt_timestamp = strtotime($tgl_db . " +30 days");
    $tgl_jatuh_tempo = formatWaktuIndo(date('Y-m-d H:i:s', $jt_timestamp));

    // Jika sudah lewat 30 hari dan status sudah lunas, ubah tampilan ke Jatuh Tempo
    if (time() > $jt_timestamp && $status_teks == 'lunas') {
        $status_teks = 'jatuh tempo';
    }
}

$no_kamar = !empty($data['no_kamar']) ? $data['no_kamar'] : null;

$kamar_terisi = []; 
$q_cek_kamar = mysqli_query($koneksi, "SELECT no_kamar FROM users WHERE no_kamar IS NOT NULL");
while ($row = mysqli_fetch_assoc($q_cek_kamar)) {
    $kamar_terisi[] = $row['no_kamar'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PureStay - Dashboard Penghuni</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #4f8cf0; 
            --dark: #0f172a; 
            --bg: #f8fafc; 
            --success: #10b981; 
            --warning: #f59e0b; 
            --danger: #ef4444; 
        }

        * { box-sizing: border-box; }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg); 
            margin: 0; 
            color: var(--dark); 
            line-height: 1.5;
        }

        .container { 
            max-width: 900px; 
            margin: 20px auto; 
            padding: 0 15px; 
        }

        .btn-back { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            color: #64748b; 
            text-decoration: none; 
            font-weight: 600; 
            margin-bottom: 20px; 
            transition: 0.3s; 
            font-size: 0.85rem; 
        }

        .glass-card { 
            background: white; 
            padding: 25px; 
            border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
        }

        .room-grid { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 10px; 
            margin-top: 20px; 
        }

        .room-card { 
            background: white; 
            padding: 15px 10px; 
            border-radius: 12px; 
            text-align: center; 
            border: 2px solid #f1f5f9; 
            transition: 0.2s; 
            cursor: pointer; 
            position: relative; 
        }

        .room-card.available:hover { border-color: var(--primary); }
        .room-card.occupied { background: #f1f5f9; opacity: 0.5; cursor: not-allowed; border: none; }
        .room-card input { position: absolute; opacity: 0; }
        .room-card.selected { border-color: var(--primary); background: #eff6ff; box-shadow: 0 0 0 3px rgba(79, 140, 240, 0.2); }

        .status-badge { 
            padding: 6px 14px; 
            border-radius: 100px; 
            font-size: 0.75rem; 
            font-weight: 700; 
            text-transform: uppercase;
        }

        .info-grid {
            display: grid; 
            grid-template-columns: 1fr; 
            gap: 15px; 
            margin-bottom: 25px;
        }

        .info-box { 
            background: #f1f5f9; 
            padding: 15px; 
            border-radius: 14px; 
        }

        .info-label { font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px; }
        .info-value { font-size: 1.1rem; font-weight: 800; word-break: break-word; }

        .btn-pay { 
            background: var(--dark); 
            color: white; 
            border: none; 
            padding: 16px; 
            border-radius: 12px; 
            font-weight: 700; 
            width: 100%; 
            cursor: pointer; 
            font-size: 1rem;
            transition: 0.3s; 
            margin-top: 20px;
        }
        .btn-pay:hover { background: var(--primary); }

        select { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; margin-top: 10px; font-family: inherit; }

        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.9); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .modal-overlay img { width: 95%; max-height: 85%; border-radius: 10px; box-shadow: 0 0 20px rgba(255,255,255,0.2); }
        .close-btn { position: absolute; top: 20px; right: 30px; color: white; font-size: 50px; font-weight: bold; cursor: pointer; line-height: 1; }

        @media (min-width: 600px) {
            .container { margin: 40px auto; }
            .glass-card { padding: 40px; }
            .room-grid { grid-template-columns: repeat(5, 1fr); gap: 15px; }
            .info-grid { grid-template-columns: repeat(3, 1fr); }
            .info-value { font-size: 1.2rem; }
            .room-card { padding: 20px; }
        }

        .status-lunas { background: #dcfce7; color: #15803d; }
        .status-proses { background: #fef3c7; color: #b45309; }
        .status-pending { background: #fee2e2; color: #b91c1c; }
        .status-jt { background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; }
        
        input[type="file"] {
            background: #f8fafc;
            padding: 10px;
            border: 2px dashed #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-back">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali ke Beranda
    </a>

    <?php if (!$no_kamar): ?>
        <div class="glass-card">
            <h2>Pilih Kamar Anda</h2>
            <p>Silakan pilih kamar yang tersedia untuk memulai.</p>
            <form action="pilih_kamar_proses.php" method="POST">
                <div class="room-grid">
                    <?php 
                    $q_master_kamar = mysqli_query($koneksi, "SELECT nomor_kamar FROM kamar_kos ORDER BY LENGTH(nomor_kamar) ASC, nomor_kamar ASC");
                    while($rk_row = mysqli_fetch_assoc($q_master_kamar)): 
                        $rk = $rk_row['nomor_kamar'];
                        $is_taken = in_array($rk, $kamar_terisi);
                    ?>
                    <label class="room-card <?php echo $is_taken ? 'occupied' : 'available'; ?>" id="card-<?php echo $rk; ?>">
                        <input type="radio" name="no_kamar" value="<?php echo $rk; ?>" <?php echo $is_taken ? 'disabled' : ''; ?> onclick="selectRoom('<?php echo $rk; ?>')">
                        <div class="label">
                            <span style="display:block; font-size: 0.7rem; opacity: 0.6;">NOMOR</span>
                            <span style="font-size: 1.2rem; font-weight: 800;"><?php echo $rk; ?></span>
                            <div style="font-size: 0.6rem; margin-top: 5px;"><?php echo $is_taken ? 'TERISI' : 'KOSONG'; ?></div>
                        </div>
                    </label>
                    <?php endwhile; ?>
                </div>
                <button type="submit" class="btn-pay">Konfirmasi Kamar</button>
            </form>
        </div>

    <?php else: ?>
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px;">
                <h1 style="margin:0; font-size: 1.5rem;">Dashboard Penghuni</h1>
                <span class="status-badge <?php 
                    if($status_teks == 'lunas') echo 'status-lunas';
                    elseif($status_teks == 'proses') echo 'status-proses';
                    elseif($status_teks == 'jatuh tempo') echo 'status-jt';
                    else echo 'status-pending';
                ?>">
                    <?php echo strtoupper($status_teks); ?>
                </span>
            </div>
            
            <div class="info-grid">
                <div class="info-box">
                    <span class="info-label">Nomor Kamar</span>
                    <div class="info-value" style="color: var(--primary);"><?php echo $no_kamar; ?></div>
                </div>
                <div class="info-box">
                    <span class="info-label">Terakhir Bayar</span>
                    <div class="info-value" style="font-size: 0.95rem;"><?php echo $tgl_terakhir; ?></div>
                </div>
                <div class="info-box" style="border-left: 4px solid <?php echo ($status_teks == 'jatuh tempo') ? 'var(--danger)' : 'var(--success)'; ?>;">
                    <span class="info-label">Jatuh Tempo Berikutnya</span>
                    <div class="info-value" style="font-size: 0.95rem; color: <?php echo ($status_teks == 'jatuh tempo') ? 'var(--danger)' : 'inherit'; ?>;">
                        <?php echo $tgl_jatuh_tempo; ?>
                    </div>
                </div>
            </div>

            <?php if ($status_teks != 'lunas' && $status_teks != 'proses'): ?>
                <div style="border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;">
                    <h3>Lengkapi Pembayaran</h3>
                    <form action="upload_bukti_proses.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="no_kamar" value="<?php echo $no_kamar; ?>">
                        
                        <div style="margin-bottom: 20px;">
                            <label><b>Pilih Metode Pembayaran:</b></label>
                            <select id="metode" name="metode_bayar" onchange="updateRekening()" required>
                                <option value="">-- Pilih Bank --</option>
                                <option value="BCA">Bank BCA</option>
                                <option value="BRI">Bank BRI</option>
                                <option value="MANDIRI">Bank Mandiri</option>
                                <option value="QRIS">QRIS PureStay</option>
                            </select>
                        </div>

                        <div id="info-pembayaran" style="display:none; background: #f1f5f9; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px dashed #4f8cf0;">
                            <div id="box-rekening">
                                <p id="label-bank" style="font-weight: 800; color: #4f8cf0; margin: 0; text-transform: uppercase;"></p>
                                <p style="margin: 5px 0;">Atas Nama: <b>PureStay Air Mbay</b></p>
                                <p style="font-size: 1.2rem; margin: 0;">No. Rekening: <b id="nomor-rekening"></b></p>
                            </div>

                            <div id="box-qris" style="display:none; text-align: center;">
                                <p style="font-weight: 800; color: #4f8cf0; margin-bottom: 15px;">SCAN QRIS PURESTAY</p>
                                <div style="display: inline-block; background: white; padding: 15px; border-radius: 20px; border: 1px solid #e2e8f0; cursor: pointer;" onclick="openModal()">
                                    <img id="qrisSource" src="uploads/purestay.png" alt="QRIS" style="width: 200px; height: auto; border-radius: 10px; display: block;" onerror="this.src='https://placehold.co/200x250?text=QRIS+Tidak+Ada'">
                                    <div style="margin-top: 10px; font-size: 0.7rem; color: #4f8cf0; font-weight: bold;">🔍 KLIK PERBESAR</div>
                                </div>
                            </div>
                        </div>

                        <label><b>Upload Bukti Transfer:</b></label>
                        <input type="file" name="bukti" required>
                        <button type="submit" class="btn-pay">Kirim Bukti Pembayaran</button>
                    </form>
                </div>
            <?php else: ?>
                <div style="margin-top: 25px; text-align: center; padding: 30px; background: #dcfce7; border-radius: 20px; color: #15803d; border: 1px solid #b9f6ca;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">✅</div>
                    <div style="font-weight: 800; font-size: 1.1rem;">Pembayaran Berhasil Diverifikasi</div>
                    <p style="margin: 5px 0 0; font-weight: 500; opacity: 0.8;">Terima kasih atas kerja samanya!</p>
                </div>
            <?php endif; ?>
        </div> <?php endif; ?>
</div> <div id="qrisModal" class="modal-overlay" onclick="closeModal()">
    <span class="close-btn">&times;</span>
    <img id="imgFull" src="" onclick="event.stopPropagation()">
</div>

<script>
function selectRoom(id) {
    document.querySelectorAll('.room-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('card-'+id).classList.add('selected');
}

function updateRekening() {
    const metode = document.getElementById("metode").value;
    const infoContainer = document.getElementById("info-pembayaran");
    const boxRek = document.getElementById("box-rekening");
    const boxQris = document.getElementById("box-qris");
    const label = document.getElementById("label-bank");
    const nomor = document.getElementById("nomor-rekening");

    const dataBank = { "BCA": "8832-001-992", "BRI": "0012-01-000234-50-1", "MANDIRI": "123-00-0987-654" };

    if (metode === "") {
        infoContainer.style.display = "none";
    } else if (metode === "QRIS") {
        infoContainer.style.display = "block";
        boxRek.style.display = "none";
        boxQris.style.display = "block";
    } else {
        infoContainer.style.display = "block";
        boxRek.style.display = "block";
        boxQris.style.display = "none";
        label.innerText = "TRANSFER BANK " + metode;
        nomor.innerText = dataBank[metode] || "";
    }
}

function openModal() {
    const modal = document.getElementById("qrisModal");
    const imgFull = document.getElementById("imgFull");
    const source = document.getElementById("qrisSource");
    imgFull.src = source.src;
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
}
<?php
session_start();
require 'koneksi.php';

// 1. Proteksi login
if (!isset($_SESSION['id'])) { 
    header('Location: login.php'); 
    exit; 
}

$user_id = $_SESSION['id'];
$status_notif = "";

// --- LOGIKA PROSES UPLOAD BUKTI (Jika Form Dikirim) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti'])) {
    $no_kamar_post = mysqli_real_escape_string($koneksi, $_POST['no_kamar']);
    $metode = isset($_POST['metode_bayar']) ? mysqli_real_escape_string($koneksi, $_POST['metode_bayar']) : 'Transfer';
    $jumlah_bayar = 400000; 

    $nama_file = $_FILES['bukti']['name'];
    $tmp_file  = $_FILES['bukti']['tmp_name'];
    $ekstensi  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    $nama_baru = "bukti_" . $user_id . "_" . time() . "." . $ekstensi;
    
    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
    
    if (in_array($ekstensi, $allowed_ext)) {
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }

        if (move_uploaded_file($tmp_file, "uploads/" . $nama_baru)) {
            // Simpan ke tabel data_kos
            $query = "INSERT INTO data_kos (user_id, no_kamar, jumlah_bayar, metode_bayar, bukti_bayar, status, tanggal_bayar) 
                      VALUES ('$user_id', '$no_kamar_post', '$jumlah_bayar', '$metode', '$nama_baru', 'proses', NOW())";
            mysqli_query($koneksi, $query);
            
            // Update status di tabel users
            mysqli_query($koneksi, "UPDATE users SET status_bayar = 'proses', bukti_bayar = '$nama_baru' WHERE id = '$user_id'");
            $status_notif = "success";
        } else { $status_notif = "error_upload"; }
    } else { $status_notif = "error_ext"; }
}

// --- AMBIL DATA USER TERBARU ---
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$user_id'");
$data = mysqli_fetch_assoc($query);
$no_kamar = !empty($data['no_kamar']) ? $data['no_kamar'] : null;

// --- AMBIL DETAIL PEMBAYARAN TERAKHIR ---
$q_bayar = mysqli_query($koneksi, "SELECT status, tanggal_bayar FROM data_kos WHERE user_id = '$user_id' ORDER BY id DESC LIMIT 1");
$d_bayar = mysqli_fetch_assoc($q_bayar);

$tgl_terakhir = "-";
$tgl_jatuh_tempo = "-";
$status_teks = "belum bayar";

function formatWaktuIndo($tanggal) {
    if (!$tanggal || $tanggal == "-") return "-";
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $timestamp = strtotime($tanggal);
    return date('d', $timestamp) . ' ' . $bulan[(int)date('m', $timestamp)] . ' ' . date('Y', $timestamp);
}

if ($d_bayar) {
    $status_teks = strtolower($d_bayar['status']);
    $tgl_db = $d_bayar['tanggal_bayar'];
    $tgl_terakhir = formatWaktuIndo($tgl_db);
    $jt_timestamp = strtotime($tgl_db . " +30 days");
    $tgl_jatuh_tempo = formatWaktuIndo(date('Y-m-d', $jt_timestamp));

    if (time() > $jt_timestamp && $status_teks == 'lunas') {
        $status_teks = 'jatuh tempo';
    }
}

// Cek kamar terisi untuk filter pilihan kamar
$kamar_terisi = []; 
$q_cek_kamar = mysqli_query($koneksi, "SELECT no_kamar FROM users WHERE no_kamar IS NOT NULL AND id != '$user_id'");
while ($row = mysqli_fetch_assoc($q_cek_kamar)) { $kamar_terisi[] = $row['no_kamar']; }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PureStay - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary: #4f8cf0; --dark: #0f172a; --bg: #f8fafc; --success: #10b981; --warning: #f59e0b; --danger: #ef4444; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; color: var(--dark); }
        .container { max-width: 900px; margin: 20px auto; padding: 0 15px; }
        .glass-card { background: white; padding: 30px; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 20px; }
        
        /* Room Grid */
        .room-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 10px; margin: 20px 0; }
        .room-card { background: #fff; border: 2px solid #f1f5f9; padding: 15px; border-radius: 15px; text-align: center; cursor: pointer; transition: 0.3s; position: relative; }
        .room-card input { position: absolute; opacity: 0; }
        .room-card.selected { border-color: var(--primary); background: #eff6ff; }
        .room-card.occupied { background: #f1f5f9; opacity: 0.5; cursor: not-allowed; }

        /* Status & Info */
        .status-badge { padding: 6px 14px; border-radius: 100px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .status-lunas { background: #dcfce7; color: #15803d; }
        .status-proses { background: #fef3c7; color: #b45309; }
        .status-jt { background: #fee2e2; color: #ef4444; }
        
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .info-box { background: #f1f5f9; padding: 15px; border-radius: 15px; }
        .info-label { font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase; }
        .info-value { font-size: 1.1rem; font-weight: 800; margin-top: 5px; }

        /* Form Elements */
        select, .btn-pay { width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; font-family: inherit; font-size: 1rem; margin-top: 10px; }
        .btn-pay { background: var(--dark); color: white; border: none; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-pay:hover { background: var(--primary); transform: translateY(-2px); }
        
        .upload-area { border: 2px dashed #cbd5e1; padding: 20px; text-align: center; border-radius: 15px; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        .upload-area:hover { border-color: var(--primary); background: #f0f7ff; }

        .modal-overlay { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:99; justify-content:center; align-items:center; }
    </style>
</head>
<body>

<div class="container">
    <div style="margin-bottom: 20px;">
        <h2 style="margin:0;">Halo, <?php echo $data['nama']; ?> 👋</h2>
        <p style="color: #64748b; margin: 5px 0;">Selamat datang di Dashboard PureStay</p>
    </div>

    <?php if (!$no_kamar): ?>
    <div class="glass-card">
        <h3>Pilih Kamar Anda</h3>
        <p>Silakan tentukan nomor kamar yang akan Anda tempati:</p>
        <form action="pilih_kamar_proses.php" method="POST">
            <div class="room-grid">
                <?php 
                $q_master_kamar = mysqli_query($koneksi, "SELECT nomor_kamar FROM kamar_kos ORDER BY nomor_kamar ASC");
                while($rk = mysqli_fetch_assoc($q_master_kamar)): 
                    $num = $rk['nomor_kamar'];
                    $is_taken = in_array($num, $kamar_terisi);
                ?>
                <label class="room-card <?php echo $is_taken ? 'occupied' : 'available'; ?>" id="label-<?php echo $num; ?>">
                    <input type="radio" name="no_kamar" value="<?php echo $num; ?>" <?php echo $is_taken ? 'disabled' : ''; ?> onchange="selectRoom('<?php echo $num; ?>')">
                    <span style="display:block; font-size: 0.6rem; opacity: 0.6;">NOMOR</span>
                    <span style="font-size: 1.2rem; font-weight: 800;"><?php echo $num; ?></span>
                </label>
                <?php endwhile; ?>
            </div>
            <button type="submit" class="btn-pay">Konfirmasi Kamar</button>
        </form>
    </div>

    <?php else: ?>
    <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin:0;">Informasi Sewa</h3>
            <span class="status-badge <?php echo 'status-'.$status_teks; ?>">
                <?php echo $status_teks; ?>
            </span>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <span class="info-label">Nomor Kamar</span>
                <div class="info-value" style="color:var(--primary)"><?php echo $no_kamar; ?></div>
            </div>
            <div class="info-box">
                <span class="info-label">Pembayaran Terakhir</span>
                <div class="info-value"><?php echo $tgl_terakhir; ?></div>
            </div>
            <div class="info-box">
                <span class="info-label">Jatuh Tempo</span>
                <div class="info-value" style="color: <?php echo ($status_teks == 'jatuh tempo') ? 'var(--danger)' : 'inherit'; ?>">
                    <?php echo $tgl_jatuh_tempo; ?>
                </div>
            </div>
        </div>

        <?php if ($status_teks != 'lunas' && $status_teks != 'proses'): ?>
        <div style="margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
            <h4>Lakukan Pembayaran</h4>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="no_kamar" value="<?php echo $no_kamar; ?>">
                
                <label class="info-label">Metode Pembayaran</label>
                <select name="metode_bayar" id="metode" onchange="updateRekening()" required>
                    <option value="">-- Pilih Bank/E-Wallet --</option>
                    <option value="BCA">Bank BCA</option>
                    <option value="MANDIRI">Bank Mandiri</option>
                    <option value="QRIS">QRIS (Otomatis)</option>
                </select>

                <div id="info-pembayaran" style="display:none; background: #eff6ff; padding: 15px; border-radius: 12px; margin-top: 15px; border: 1px solid #bfdbfe;">
                    <div id="box-rekening">
                        <p id="label-bank" style="font-weight: 800; color: var(--primary); margin: 0;"></p>
                        <p style="margin: 5px 0; font-size: 0.9rem;">No. Rekening: <b id="nomor-rekening" style="font-size: 1.1rem;"></b></p>
                        <p style="margin: 0; font-size: 0.8rem;">A/N: PureStay Residence</p>
                    </div>
                </div>

                <div class="upload-area" onclick="document.getElementById('fileInp').click()">
                    <i class="fa-solid fa-camera" style="font-size: 1.5rem; color: var(--primary);"></i>
                    <p id="file-name" style="margin: 10px 0 0; font-size: 0.85rem; font-weight: 600;">Klik untuk upload bukti bayar</p>
                    <input type="file" name="bukti" id="fileInp" hidden required onchange="showName(this)">
                </div>

                <button type="submit" class="btn-pay">Kirim Konfirmasi Bayar</button>
            </form>
        </div>
        <?php else: ?>
            <div style="text-align: center; padding: 30px; margin-top: 20px; background: #f8fafc; border-radius: 20px;">
                <i class="fa-solid fa-circle-check" style="font-size: 3rem; color: var(--success); opacity: 0.5;"></i>
                <p style="font-weight: 700; margin-top: 15px;">Terima kasih! Pembayaran Anda sedang <?php echo $status_teks; ?>.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
    function selectRoom(num) {
        document.querySelectorAll('.room-card').forEach(c => c.classList.remove('selected'));
        document.getElementById('label-' + num).classList.add('selected');
    }

    function showName(input) {
        if (input.files.length > 0) {
            document.getElementById('file-name').innerText = "✅ " + input.files[0].name;
        }
    }

    function updateRekening() {
        const m = document.getElementById('metode').value;
        const info = document.getElementById('info-pembayaran');
        const lbl = document.getElementById('label-bank');
        const num = document.getElementById('nomor-rekening');

        if(m === "") { info.style.display = "none"; return; }
        info.style.display = "block";

        if(m === "BCA") { lbl.innerText = "BANK BCA"; num.innerText = "123-456-7890"; }
        else if(m === "MANDIRI") { lbl.innerText = "BANK MANDIRI"; num.innerText = "900-00-112233"; }
        else if(m === "QRIS") { lbl.innerText = "QRIS PURESTAY"; num.innerText = "Scan pada menu kasir"; }
    }

    <?php if($status_notif == "success"): ?>
        Swal.fire('Berhasil!', 'Bukti pembayaran telah dikirim.', 'success').then(() => { window.location.href='user_kos_dashboard.php'; });
    <?php endif; ?>
</script>

</body>
</html>
function closeModal() {
    document.getElementById("qrisModal").style.display = "none";
    document.body.style.overflow = "auto";
}

document.addEventListener('keydown', (e) => { if (e.key === "Escape") closeModal(); });
</script>
</body>
</html> 25px; background: #dcfce7; border-radius: 16px; color: #15803d; font-weight: 700; border: 1px solid #b9f6ca;">
                    ✅ Pembayaran Berhasil Diverifikasi. Terima kasih!
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div id="qrisModal" class="modal-overlay" onclick="closeModal()">
    <span class="close-btn">&times;</span>
    <img id="imgFull" src="" onclick="event.stopPropagation()">
</div>

<script>
function selectRoom(id) {
    document.querySelectorAll('.room-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('card-'+id).classList.add('selected');
}

function updateRekening() {
    const metode = document.getElementById("metode").value;
    const infoContainer = document.getElementById("info-pembayaran");
    const boxRek = document.getElementById("box-rekening");
    const boxQris = document.getElementById("box-qris");
    const label = document.getElementById("label-bank");
    const nomor = document.getElementById("nomor-rekening");

    const dataBank = { "BCA": "8832-001-992", "BRI": "0012-01-000234-50-1", "MANDIRI": "123-00-0987-654" };

    if (metode === "") {
        infoContainer.style.display = "none";
    } else if (metode === "QRIS") {
        infoContainer.style.display = "block";
        boxRek.style.display = "none";
        boxQris.style.display = "block";
    } else {
        infoContainer.style.display = "block";
        boxRek.style.display = "block";
        boxQris.style.display = "none";
        label.innerText = "TRANSFER BANK " + metode;
        nomor.innerText = dataBank[metode] || "";
    }
}

function openModal() {
    const modal = document.getElementById("qrisModal");
    const imgFull = document.getElementById("imgFull");
    const source = document.getElementById("qrisSource");
    imgFull.src = source.src;
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
}

function closeModal() {
    document.getElementById("qrisModal").style.display = "none";
    document.body.style.overflow = "auto";
}

document.addEventListener('keydown', (e) => { if (e.key === "Escape") closeModal(); });
</script>
</body>
</html>