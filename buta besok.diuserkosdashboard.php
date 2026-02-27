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